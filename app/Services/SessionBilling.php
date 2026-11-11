<?php
namespace App\Services;

use App\Models\Attendance;
use App\Models\Payment;
use App\Models\PaymentTransaction;
use App\Models\Student;
use App\Models\StudentAlert;
use App\Models\Subject;
use Carbon\Carbon;

/**
 * Session-based billing, independent of the timetable.
 * Sessions per month of a subject = subjects.sessions_per_week (default 2) x 4.
 * session_price = monthly fee (student_subject.prix) / sessions per month.
 * Registration month: the parent pays the FULL month; consumed = remaining sessions x session price;
 * the difference is a credit (advance) deducted from the next invoice.
 * Income stats use payments.paye per period = amount earned for that month (credit applied counts in the month it is used).
 */
class SessionBilling
{
    public const SUSPENDED = 'Suspendu temporairement';
    public const WITHDRAWN = 'Retiré';

    /**
     * @param array $subjects [subject_id => monthly prix]
     * @param array|null $remaining [subject_id => remaining sessions this month] (null = full month)
     */
    public static function breakdown(array $subjects, string $periode, ?array $remaining = null): array
    {
        $models = Subject::whereIn('id', array_keys($subjects))->get()->keyBy('id');
        $lines = [];
        $total = 0.0; $full = 0.0; $sessions = 0;
        foreach ($subjects as $id => $prix) {
            $sub = $models[$id] ?? null;
            if (!$sub) continue;
            $prix = (float) $prix;
            $spm = $sub->sessions_per_month;
            $rem = isset($remaining[$id]) && $remaining[$id] !== '' ? (int) $remaining[$id] : $spm;
            $rem = max(0, min($rem, $spm));
            $unit = round($prix / $spm, 2);
            $amount = $rem >= $spm ? $prix : round($prix / $spm * $rem, 2);
            $lines[] = [
                'subject_id' => (int) $id, 'subject' => $sub->nom, 'monthly' => $prix,
                'sessions_month' => $spm, 'sessions' => $rem, 'session_price' => $unit, 'amount' => $amount,
            ];
            $total += $amount; $full += $prix; $sessions += $rem;
        }
        return ['lines' => $lines, 'total' => round($total, 2), 'full' => round($full, 2), 'sessions' => $sessions, 'periode' => $periode];
    }

    public static function studentSubjects(Student $student): array
    {
        $student->loadMissing('subjects');
        $out = [];
        foreach ($student->subjects as $s) {
            $p = $s->pivot->prix ?? null;
            $out[$s->id] = ($p !== null && $p !== '') ? (float) $p : (float) ($s->prix ?? 0);
        }
        return $out;
    }

    /** Credit (advance) the student still has available to deduct from a new invoice. */
    public static function availableCredit(Student $student): float
    {
        $p = Payment::where('student_id', $student->id)->where('statut', '!=', 'Annulé');
        return max(0, round((float) (clone $p)->sum('credit_out') - (float) $p->sum('credit_in'), 2));
    }

    /**
     * @param array|null $remaining [subject_id => remaining sessions] for the registration month, null = full month
     * @param float $cash cash received now (registration: the full month)
     */
    public static function createInvoice(Student $student, string $periode, ?array $remaining = null, float $cash = 0, ?int $yearId = null): ?Payment
    {
        $b = self::breakdown(self::studentSubjects($student), $periode, $remaining);
        if ($b['full'] <= 0) return null;
        $consumed = $b['total'];
        $cash = min(max(0, $cash), $b['full']);
        $creditIn = min(self::availableCredit($student), $consumed);
        $paye = min($cash, max(0, $consumed - $creditIn)) + $creditIn;
        $creditOut = max(0, round($cash - max(0, $consumed - $creditIn), 2));
        $payment = Payment::create([
            'student_id' => $student->id, 'type' => 'Mensualité', 'montant' => $consumed, 'paye' => $paye,
            'full_amount' => $b['full'], 'credit_in' => $creditIn, 'credit_out' => $creditOut,
            'periode' => $periode, 'sessions' => $b['sessions'], 'details' => $b['lines'],
            'statut' => $paye <= 0 && $consumed > 0 ? 'Non payé' : ($paye >= $consumed ? 'Soldé' : 'Partiel'),
            'school_year_id' => $yearId ?? $student->school_year_id,
        ]);
        if ($cash > 0) {
            PaymentTransaction::create(['payment_id' => $payment->id, 'date' => now()->toDateString(), 'montant' => $cash, 'mode' => 'Espèces']);
        }
        return $payment;
    }

    /** Oldest open invoice (nothing paid counts as unpaid; partial counts as unpaid too). */
    public static function oldestDue(Student $student): ?Payment
    {
        return Payment::where('student_id', $student->id)->activeDue()->orderBy('periode')->first();
    }

    /** Present/late sessions attended while the oldest invoice is still open. */
    public static function unpaidAttended(Student $student): int
    {
        $due = self::oldestDue($student);
        if (!$due) return 0;
        $since = Carbon::createFromFormat('Y-m-d', $due->periode.'-01')->startOfDay();
        if ($student->date_inscription && $student->date_inscription->gt($since)) $since = $student->date_inscription->copy();
        return Attendance::where('student_id', $student->id)->whereIn('status', ['present', 'retard'])
            ->whereDate('date', '>=', $since->toDateString())->count();
    }

    /** Called after attendance is saved. 2nd unpaid session => alert + temporary suspension. */
    public static function checkAfterAttendance(Student $student): void
    {
        if ($student->statut !== 'Actif') return;
        if (self::unpaidAttended($student) < 2) return;
        $student->update(['statut' => self::SUSPENDED, 'suspendu_at' => now()]);
        StudentAlert::create([
            'student_id' => $student->id, 'type' => 'second_unpaid',
            'message' => $student->full_name.' حضر الحصة الثانية وما خلّصش — تم توقيفه مؤقتاً',
        ]);
    }

    /** Called after a payment: if nothing is due anymore, reactivate. */
    public static function checkAfterPayment(Student $student): void
    {
        if ($student->statut === self::SUSPENDED && !self::oldestDue($student)) {
            $student->update(['statut' => 'Actif', 'suspendu_at' => null]);
            StudentAlert::where('student_id', $student->id)->whereNull('read_at')->update(['read_at' => now()]);
        }
    }
}
