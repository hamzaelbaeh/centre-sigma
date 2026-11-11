<?php
namespace App\Services;

use App\Models\Attendance;
use App\Models\Payment;
use App\Models\PaymentTransaction;
use App\Models\SessionAdjustment;
use App\Models\Student;
use App\Models\StudentAlert;
use App\Models\Subject;
use Carbon\Carbon;

/**
 * Session-based billing driven by the timetable AS ACTUALLY HELD (weekly snapshots, see TimetableSnapshot).
 * session_price = monthly fee (student_subject.prix) / sessions held that month for the student's class+subject.
 * Registration month: the parent pays the FULL month; consumed = sessions held from the registration date to month end
 * x session price (an ESTIMATE until the month is closed, then recomputed from the real timetable).
 * The difference paid - consumed is a credit deducted from the next invoice.
 * subjects.sessions_per_week (x4) is only the fallback/estimate when no timetable data exists.
 * Income stats use payments.paye per period = amount earned for that month.
 */
class SessionBilling
{
    public const SUSPENDED = 'Suspendu temporairement';
    public const WITHDRAWN = 'Retiré';

    /**
     * @param array $subjects [subject_id => monthly prix]
     * @param Carbon|null $from null = full month
     */
    public static function breakdown(?int $classId, array $subjects, string $periode, ?Carbon $from = null, ?int $studentId = null): array
    {
        $start = Carbon::createFromFormat('Y-m-d', $periode.'-01')->startOfDay();
        $end = $start->copy()->endOfMonth()->startOfDay();
        $from = $from && $from->gt($start) ? $from->copy()->startOfDay() : $start;
        $models = Subject::whereIn('id', array_keys($subjects))->get()->keyBy('id');
        $lines = [];
        $total = 0.0; $full = 0.0; $sessions = 0;
        foreach ($subjects as $id => $prix) {
            $sub = $models[$id] ?? null;
            if (!$sub) continue;
            $prix = (float) $prix;
            $whole = TimetableSnapshot::held($classId, $sub, $start, $end);
            $part = $from->equalTo($start) ? $whole : TimetableSnapshot::held($classId, $sub, $from, $end);
            $adj = $studentId ? self::adjustment($studentId, (int) $id, $periode) : null;
            if ($adj) {
                // manual correction: corrected count up to the correction date + timetable sessions after it
                $asOf = $adj->as_of->copy()->startOfDay();
                $after = $asOf->lt($end) ? TimetableSnapshot::held($classId, $sub, $asOf->copy()->addDay()->max($from), $end)['held'] : 0;
                $part = ['held' => $adj->new_count + $after, 'source' => 'adjusted'];
            }
            $unit = $whole['held'] > 0 ? round($prix / $whole['held'], 2) : 0;
            $amount = $part['held'] >= $whole['held'] ? $prix : round($prix / $whole['held'] * $part['held'], 2);
            $lines[] = [
                'subject_id' => (int) $id, 'subject' => $sub->nom, 'monthly' => $prix,
                'sessions_month' => $whole['held'], 'sessions' => $part['held'], 'session_price' => $unit,
                'amount' => $amount, 'adjusted' => (bool) $adj, 'source' => $adj ? 'adjusted' : ($whole['source'] === 'actual' && $part['source'] === 'actual' ? 'actual' : ($whole['source'] === 'estimate' ? 'estimate' : 'mixed')),
            ];
            $total += $amount; $full += $prix; $sessions += $part['held'];
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

    /** Money split of an invoice: [paye, credit_out] from consumed amount, credit used and cash received. */
    private static function split(float $consumed, float $creditIn, float $cash): array
    {
        $creditUsed = min($creditIn, $consumed);
        $net = max(0, $consumed - $creditUsed);
        return [round(min($cash, $net) + $creditUsed, 2), max(0, round($cash - $net, 2)), $creditUsed];
    }

    private static function statut(float $paye, float $consumed): string
    {
        return $paye <= 0 && $consumed > 0 ? 'Non payé' : ($paye >= $consumed ? 'Soldé' : 'Partiel');
    }

    /**
     * @param Carbon|null $from registration date (consumed counted from that date); null = regular full-month invoice
     * @param float $cash cash received now (registration: the full month)
     */
    public static function createInvoice(Student $student, string $periode, ?Carbon $from = null, float $cash = 0, ?int $yearId = null): ?Payment
    {
        $b = self::breakdown($student->class_id, self::studentSubjects($student), $periode, $from, $student->id);
        if ($b['full'] <= 0) return null;
        $start = Carbon::createFromFormat('Y-m-d', $periode.'-01')->startOfDay();
        $estimated = $from !== null && $from->copy()->startOfDay()->gt($start) && $start->copy()->endOfMonth()->gte(today());
        $consumed = $b['total'];
        $cash = min(max(0, $cash), $b['full']);
        [$paye, $creditOut, $creditIn] = self::split($consumed, min(self::availableCredit($student), $consumed), $cash);
        $payment = Payment::create([
            'student_id' => $student->id, 'type' => 'Mensualité', 'montant' => $consumed, 'paye' => $paye,
            'full_amount' => $b['full'], 'credit_in' => $creditIn, 'credit_out' => $creditOut, 'estimated' => $estimated,
            'periode' => $periode, 'sessions' => $b['sessions'], 'details' => $b['lines'],
            'statut' => self::statut($paye, $consumed),
            'school_year_id' => $yearId ?? $student->school_year_id,
        ]);
        if ($cash > 0) {
            PaymentTransaction::create(['payment_id' => $payment->id, 'date' => now()->toDateString(), 'montant' => $cash, 'mode' => 'Espèces']);
        }
        return $payment;
    }

    /**
     * Month close: recompute registration-month invoices that were only estimated, from the real (frozen) timetable.
     * Applies to months before $beforePeriode and to any month that has already ended. Returns the number finalized.
     */
    public static function finalizeEstimates(?string $beforePeriode = null): int
    {
        $n = 0;
        foreach (Payment::where('estimated', true)->where('statut', '!=', 'Annulé')->with('student')->get() as $p) {
            $end = Carbon::createFromFormat('Y-m-d', $p->periode.'-01')->endOfMonth()->startOfDay();
            if (!(($beforePeriode && $p->periode < $beforePeriode) || $end->lt(today()))) continue;
            if (!$p->student) continue;
            self::recompute($p, true);
            $n++;
        }
        return $n;
    }

    /**
     * Recompute an existing monthly invoice from the (real) timetable and manual corrections, keeping cash and credit.
     * $final=false keeps it flagged as an estimate (month not closed yet) so it is recomputed again at month end.
     */
    public static function recompute(Payment $p, bool $final): void
    {
        $s = $p->student;
        $from = $s->date_inscription ? Carbon::parse($s->date_inscription) : null;
        $b = self::breakdown($s->class_id, self::studentSubjects($s), $p->periode, $from, $s->id);
        $cash = (float) $p->transactions()->sum('montant');
        [$paye, $creditOut, $creditIn] = self::split($b['total'], (float) $p->credit_in, $cash);
        $p->update([
            'montant' => $b['total'], 'paye' => $paye, 'credit_out' => $creditOut, 'credit_in' => $creditIn,
            'full_amount' => $b['full'], 'sessions' => $b['sessions'], 'details' => $b['lines'], 'estimated' => !$final,
            'statut' => self::statut($paye, $b['total']),
        ]);
    }

    /** Latest manual correction for a student+subject+month. */
    public static function adjustment(int $studentId, int $subjectId, string $periode): ?SessionAdjustment
    {
        return SessionAdjustment::where('student_id', $studentId)->where('subject_id', $subjectId)->where('periode', $periode)
            ->orderByDesc('id')->first();
    }

    /**
     * Sessions counted for a student+subject from the start of the month (or registration) up to $date inclusive.
     * @return array{system:int, count:int, adjusted:bool} system = timetable count, count = effective (with correction)
     */
    public static function countUpTo(Student $student, Subject $subject, string $periode, Carbon $date): array
    {
        $start = Carbon::createFromFormat('Y-m-d', $periode.'-01')->startOfDay();
        $date = $date->copy()->startOfDay();
        $from = $student->date_inscription && $student->date_inscription->gt($start) ? $student->date_inscription->copy()->startOfDay() : $start;
        $system = $date->lt($from) ? 0 : TimetableSnapshot::held($student->class_id, $subject, $from, $date)['held'];
        $adj = self::adjustment($student->id, $subject->id, $periode);
        if (!$adj || $adj->as_of->gt($date)) return ['system' => $system, 'count' => $system, 'adjusted' => false];
        $asOf = $adj->as_of->copy()->startOfDay();
        $after = $date->gt($asOf) ? TimetableSnapshot::held($student->class_id, $subject, $asOf->copy()->addDay()->max($from), $date)['held'] : 0;
        return ['system' => $system, 'count' => $adj->new_count + $after, 'adjusted' => true];
    }

    /** Save a manual correction for the current month; the month's invoice (if any) is recomputed immediately. */
    public static function adjustSessions(Student $student, Subject $subject, int $newCount, ?string $note, ?int $userId): ?SessionAdjustment
    {
        $periode = today()->format('Y-m');
        $cur = self::countUpTo($student, $subject, $periode, today());
        $adj = SessionAdjustment::create([
            'student_id' => $student->id, 'subject_id' => $subject->id, 'periode' => $periode, 'as_of' => today()->toDateString(),
            'old_count' => $cur['count'], 'new_count' => $newCount, 'user_id' => $userId, 'note' => $note ?: null,
        ]);
        $inv = Payment::where('student_id', $student->id)->where('type', 'Mensualité')->where('periode', $periode)
            ->where('statut', '!=', 'Annulé')->first();
        if ($inv) self::recompute($inv, false);
        return $adj;
    }

    /**
     * Month-end routine for invoices of month $periode:
     *  1) registration-month estimates of earlier months are recomputed from the real timetable (credit becomes final);
     *  2) a regular full-month invoice is created for every student without one (credit deducted).
     * @return array{0:int,1:int} [invoices created, estimates finalized]
     */
    public static function closeMonthAndGenerate(string $periode): array
    {
        TimetableSnapshot::ensureCurrent();
        $finalized = self::finalizeEstimates($periode);
        $year = \App\Models\SchoolYear::active();
        $created = 0;
        $students = Student::with('subjects')->whereIn('statut', ['Actif', self::SUSPENDED])->get();
        foreach ($students as $student) {
            if ($student->date_inscription && $student->date_inscription->format('Y-m') > $periode) continue;
            $exists = Payment::where('student_id', $student->id)->where('type', 'Mensualité')
                ->where('periode', $periode)->where('statut', '!=', 'Annulé')->exists();
            if ($exists) continue;
            if (self::createInvoice($student, $periode, null, 0, $year?->id)) $created++;
        }
        return [$created, $finalized];
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
