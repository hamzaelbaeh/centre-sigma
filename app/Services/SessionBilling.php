<?php
namespace App\Services;

use App\Models\Attendance;
use App\Models\Payment;
use App\Models\PaymentTransaction;
use App\Models\Student;
use App\Models\StudentAlert;
use App\Models\Subject;
use App\Models\TimetableSlot;
use Carbon\Carbon;

/**
 * Session-based billing.
 * Sessions of a subject in a month = number of dates in that month whose weekday
 * matches a timetable slot (class + subject). Fallback: subjects.sessions_per_month, else 4.
 * session_price = monthly fee (student_subject.prix) / full-month sessions.
 */
class SessionBilling
{
    public const SUSPENDED = 'Suspendu temporairement';
    public const WITHDRAWN = 'Retiré';
    public const DAYS = ['lundi'=>1,'mardi'=>2,'mercredi'=>3,'jeudi'=>4,'vendredi'=>5,'samedi'=>6,'dimanche'=>7];

    /** @return int[] ISO weekdays (one entry per weekly slot) */
    public static function weekdays(?int $classId, int $subjectId): array
    {
        if (!$classId) return [];
        return TimetableSlot::where('class_id', $classId)->where('subject_id', $subjectId)->pluck('jour')
            ->map(fn ($j) => self::DAYS[mb_strtolower(trim((string) $j))] ?? null)->filter()->values()->all();
    }

    public static function countSessions(array $weekdays, Carbon $from, Carbon $to): int
    {
        $n = 0;
        for ($d = $from->copy()->startOfDay(); $d->lte($to); $d->addDay()) {
            foreach ($weekdays as $w) if ($d->dayOfWeekIso === $w) $n++;
        }
        return $n;
    }

    /**
     * @param array $subjects [subject_id => prix]
     * @param Carbon|null $from null = full month
     */
    public static function breakdown(?int $classId, array $subjects, string $periode, ?Carbon $from = null): array
    {
        $start = Carbon::createFromFormat('Y-m-d', $periode.'-01')->startOfDay();
        $end = $start->copy()->endOfMonth();
        $from = $from && $from->gt($start) ? $from->copy()->startOfDay() : $start;
        $models = Subject::whereIn('id', array_keys($subjects))->get()->keyBy('id');
        $lines = [];
        $total = 0.0; $sessions = 0;
        foreach ($subjects as $id => $prix) {
            $sub = $models[$id] ?? null;
            if (!$sub) continue;
            $prix = (float) $prix;
            $days = self::weekdays($classId, (int) $id);
            if ($days) {
                $full = self::countSessions($days, $start, $end);
                $remaining = self::countSessions($days, $from, $end);
                $source = 'schedule';
            } else {
                $full = (int) ($sub->sessions_per_month ?: 4);
                $daysInMonth = $start->daysInMonth;
                $remaining = $from->equalTo($start) ? $full
                    : (int) round($full * ($daysInMonth - $from->day + 1) / $daysInMonth);
                $source = 'default';
            }
            $unit = $full > 0 ? round($prix / $full, 2) : 0;
            $amount = $remaining >= $full ? $prix : round($unit * $remaining, 2);
            $lines[] = [
                'subject_id' => (int) $id, 'subject' => $sub->nom, 'monthly' => $prix,
                'sessions_month' => $full, 'sessions' => $remaining, 'session_price' => $unit,
                'amount' => $amount, 'source' => $source,
            ];
            $total += $amount; $sessions += $remaining;
        }
        return ['lines' => $lines, 'total' => round($total, 2), 'sessions' => $sessions, 'periode' => $periode];
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

    public static function createInvoice(Student $student, string $periode, ?Carbon $from = null, float $paye = 0, ?int $yearId = null): ?Payment
    {
        $b = self::breakdown($student->class_id, self::studentSubjects($student), $periode, $from);
        if ($b['total'] <= 0) return null;
        $paye = min(max(0, $paye), $b['total']);
        $payment = Payment::create([
            'student_id' => $student->id, 'type' => 'Mensualité', 'montant' => $b['total'], 'paye' => $paye,
            'periode' => $periode, 'sessions' => $b['sessions'], 'details' => $b['lines'],
            'statut' => $paye <= 0 ? 'Non payé' : ($paye >= $b['total'] ? 'Soldé' : 'Partiel'),
            'school_year_id' => $yearId ?? $student->school_year_id,
        ]);
        if ($paye > 0) {
            PaymentTransaction::create(['payment_id' => $payment->id, 'date' => now()->toDateString(), 'montant' => $paye, 'mode' => 'Espèces']);
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
