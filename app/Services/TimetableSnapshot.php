<?php
namespace App\Services;

use App\Models\Subject;
use App\Models\TimetableSlot;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Weekly snapshots of the timetable.
 * The live timetable (timetable_slots) is always editable. The OFFICIAL timetable of a week is its final state:
 *  - the snapshot of the current week is rebuilt from the live timetable on every edit (and whenever billing reads it);
 *  - when a week ends it is frozen (a later edit applies to the new week only).
 * Weeks nobody touched are frozen lazily with the (unchanged) live timetable, taken BEFORE the edit is applied.
 */
class TimetableSnapshot
{
    public const DAYS = ['lundi' => 0, 'mardi' => 1, 'mercredi' => 2, 'jeudi' => 3, 'vendredi' => 4, 'samedi' => 5, 'dimanche' => 6];

    private static array $cache = [];

    public static function weekStart(Carbon $d): Carbon
    {
        return $d->copy()->startOfDay()->subDays($d->dayOfWeekIso - 1);
    }

    public static function dayIndex(?string $jour): ?int
    {
        return self::DAYS[mb_strtolower(trim((string) $jour))] ?? null;
    }

    private static function copyLive(string $week): void
    {
        $now = now();
        $rows = TimetableSlot::all()->map(fn ($s) => [
            'week_start' => $week, 'class_id' => $s->class_id, 'subject_id' => $s->subject_id, 'teacher_id' => $s->teacher_id,
            'jour' => $s->jour, 'debut' => $s->debut, 'fin' => $s->fin, 'salle' => $s->salle, 'created_at' => $now, 'updated_at' => $now,
        ])->all();
        DB::table('timetable_week_slots')->where('week_start', $week)->delete();
        foreach (array_chunk($rows, 200) as $chunk) DB::table('timetable_week_slots')->insert($chunk);
    }

    /** Freeze every past week that has no snapshot yet, using the live timetable as it is NOW (call before applying an edit). */
    public static function freezeGaps(): void
    {
        $cur = self::weekStart(now());
        $last = DB::table('timetable_weeks')->max('week_start');
        if (!$last) return; // history not started yet: ensureCurrent() starts it
        $w = Carbon::parse($last)->addWeek();
        while ($w->lt($cur)) {
            if (!DB::table('timetable_weeks')->where('week_start', $w->toDateString())->exists()) {
                self::copyLive($w->toDateString());
                DB::table('timetable_weeks')->insert(['week_start' => $w->toDateString(), 'created_at' => now(), 'updated_at' => now()]);
            }
            $w->addWeek();
        }
    }

    /** Rebuild the current week's snapshot from the live timetable. */
    public static function syncCurrent(): void
    {
        $cur = self::weekStart(now())->toDateString();
        self::copyLive($cur);
        if (!DB::table('timetable_weeks')->where('week_start', $cur)->exists()) {
            DB::table('timetable_weeks')->insert(['week_start' => $cur, 'created_at' => now(), 'updated_at' => now()]);
        }
        self::$cache = [];
    }

    public static function beforeChange(): void
    {
        self::freezeGaps();
    }

    public static function afterChange(): void
    {
        self::syncCurrent();
    }

    private static bool $fresh = false;

    public static function forget(): void { self::$fresh = false; self::$cache = []; }

    public static function ensureCurrent(): void
    {
        self::freezeGaps();
        self::syncCurrent();
        self::$fresh = true;
    }

    /**
     * Sessions held for a class+subject between two dates (inclusive), from weekly snapshots.
     * Weeks with no snapshot data for the class use the subject estimate (sessions_per_week x 4, pro rata).
     * Future weeks are projected from the live timetable.
     * @return array{held:int, actual:int, est:int, source:string}
     */
    public static function held(?int $classId, Subject $subject, Carbon $from, Carbon $to): array
    {
        if (!self::$fresh) self::ensureCurrent();
        $from = $from->copy()->startOfDay(); $to = $to->copy()->startOfDay();
        $N = max(1, $from->daysInMonth);
        $cur = self::weekStart(now());

        $weeks = DB::table('timetable_weeks')->pluck('week_start')->map(fn ($w) => substr($w, 0, 10))->flip();
        $first = $classId ? DB::table('timetable_week_slots')->where('class_id', $classId)->min('week_start') : null;
        $first = $first ? substr($first, 0, 10) : null;
        $liveRows = $classId ? TimetableSlot::where('class_id', $classId)->where('subject_id', $subject->id)->get() : collect();
        $liveClassHas = $classId ? TimetableSlot::where('class_id', $classId)->exists() : false;
        $snap = $classId ? DB::table('timetable_week_slots')->where('class_id', $classId)->where('subject_id', $subject->id)
            ->whereBetween('week_start', [self::weekStart($from)->toDateString(), self::weekStart($to)->toDateString()])->get()->groupBy(fn ($r) => substr($r->week_start, 0, 10)) : collect();

        $actual = 0; $uncovered = 0;
        for ($d = $from->copy(); $d->lte($to); $d->addDay()) {
            $w = self::weekStart($d);
            $ws = $w->toDateString();
            $idx = $d->dayOfWeekIso - 1;
            if ($w->gt($cur)) {
                if (!$liveClassHas) { $uncovered++; continue; }
                $actual += $liveRows->filter(fn ($r) => self::dayIndex($r->jour) === $idx)->count();
            } else {
                if (!$classId || !$first || !isset($weeks[$ws]) || $ws < $first) { $uncovered++; continue; }
                $actual += ($snap[$ws] ?? collect())->filter(fn ($r) => self::dayIndex($r->jour) === $idx)->count();
            }
        }
        $est = $uncovered ? (int) round($subject->sessions_per_month * $uncovered / $N) : 0;
        $source = ($uncovered === 0 || $est === 0) ? 'actual' : ($actual === 0 ? 'estimate' : 'mixed');
        return ['held' => $actual + $est, 'actual' => $actual, 'est' => $est, 'source' => $source];
    }
}
