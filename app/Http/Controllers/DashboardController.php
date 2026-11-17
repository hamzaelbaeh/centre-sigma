<?php
namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Payment;
use App\Models\PaymentTransaction;
use App\Models\SchoolClass;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\StudentAlert;
use App\Models\Subject;
use App\Models\TimetableSlot;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Director dashboard (read-only: never writes, never finalizes invoices).
 * Due / collected per month follow the billing logic: the month's invoices (payments.montant = consumed, payments.paye = paid incl. credit).
 * For the current/future month, active students that have no invoice yet (invoices are generated at month end) are counted
 * with their monthly fee (sum of student_subject.prix) and flagged as an estimate.
 */
class DashboardController extends Controller
{
    public const MONTHS = [1=>'يناير',2=>'فبراير',3=>'مارس',4=>'أبريل',5=>'ماي',6=>'يونيو',7=>'يوليوز',8=>'غشت',9=>'شتنبر',10=>'أكتوبر',11=>'نونبر',12=>'دجنبر'];
    public const DAYS_AR = [1=>'الاثنين',2=>'الثلاثاء',3=>'الأربعاء',4=>'الخميس',5=>'الجمعة',6=>'السبت',7=>'الأحد'];
    public const DAYS_FR = [1=>'Lundi',2=>'Mardi',3=>'Mercredi',4=>'Jeudi',5=>'Vendredi',6=>'Samedi',7=>'Dimanche'];
    public const LEFT = ['Retiré','Abandonné','Transféré','Exclu','Diplômé'];
    public const CYCLES = [
        'primaire' => ['label'=>'الابتدائي','color'=>'#2563eb'],
        'college'  => ['label'=>'الإعدادي','color'=>'#0f766e'],
        'lycee'    => ['label'=>'الثانوي','color'=>'#b45309'],
        'other'    => ['label'=>'بدون مستوى','color'=>'#64748b'],
    ];
    public const LEVELS_AR = [
        '1ère année primaire'=>'الأول ابتدائي','2ème année primaire'=>'الثاني ابتدائي','3ème année primaire'=>'الثالث ابتدائي',
        '4ème année primaire'=>'الرابع ابتدائي','5ème année primaire'=>'الخامس ابتدائي','6ème année primaire'=>'السادس ابتدائي',
        '1ère année collège'=>'الأولى إعدادي','2ème année collège'=>'الثانية إعدادي','3ème année collège'=>'الثالثة إعدادي',
        'Tronc commun'=>'الجذع المشترك','1ère bac'=>'الأولى باك','2ème bac'=>'الثانية باك','Non scolarisé'=>'غير متمدرس',
    ];

    public static function cycleOf(?string $n): string
    {
        $n = (string) $n;
        if ($n === '') return 'other';
        if (str_contains($n, 'primaire')) return 'primaire';
        if (str_contains($n, 'collège') || str_contains($n, 'college')) return 'college';
        if (str_contains($n, 'bac') || str_contains($n, 'Tronc')) return 'lycee';
        return 'other';
    }

    public static function monthLabel(string $periode): string
    {
        $c = Carbon::createFromFormat('Y-m-d', $periode.'-01');
        return self::MONTHS[(int) $c->month].' '.$c->year;
    }

    public function index(Request $request)
    {
        $today = today();
        $curPer = $today->format('Y-m');
        $year = SchoolYear::active();
        $p = $request->get('p', 'current');
        $mParam = $request->get('m');

        // ---------- Period ----------
        if ($p === 'year' && $year) {
            $start = Carbon::parse($year->date_debut)->startOfMonth();
            $endCap = Carbon::parse($year->date_fin)->endOfMonth();
            $end = $today->copy()->endOfMonth()->min($endCap);
            if ($end->lt($start)) $end = $start->copy()->endOfMonth();
            $label = 'السنة الدراسية '.$year->nom;
            $prevStart = $start->copy()->subYear(); $prevEnd = $end->copy()->subYear()->endOfMonth();
            $prevLabel = 'السنة الماضية';
        } else {
            if ($p === 'prev') $base = $today->copy()->subMonthNoOverflow();
            elseif ($p === 'month' && $mParam && preg_match('/^\d{4}-\d{2}$/', $mParam)) $base = Carbon::createFromFormat('Y-m-d', $mParam.'-01');
            else { $p = 'current'; $base = $today->copy(); }
            $start = $base->copy()->startOfMonth(); $end = $base->copy()->endOfMonth();
            $label = self::monthLabel($start->format('Y-m'));
            $prevStart = $start->copy()->subMonthNoOverflow()->startOfMonth(); $prevEnd = $prevStart->copy()->endOfMonth();
            $prevLabel = self::monthLabel($prevStart->format('Y-m'));
        }
        $selectedMonth = $start->format('Y-m');

        // ---------- Data (read-only) ----------
        $students = Student::with([
            'subjects', 'parents', 'schoolClass',
            'payments' => fn ($q) => $q->where('statut', '!=', 'Annulé'),
        ])->get();
        $subjects = Subject::with('teachers')->orderBy('id')->get()->keyBy('id');

        $cur = $this->periodStats($students, $start, $end, $curPer);
        $prev = $this->periodStats($students, $prevStart, $prevEnd, $curPer);

        $kpi = [
            'due' => $cur['due'], 'paid' => $cur['paid'], 'rest' => max(0, $cur['due'] - $cur['paid']),
            'rate' => $cur['due'] > 0 ? round($cur['paid'] / $cur['due'] * 100) : 0,
            'estimated' => $cur['estimated'], 'cash' => $this->cash($start, $end),
            'active' => $cur['active'], 'suspended' => $cur['suspended'], 'new' => $cur['new'], 'left' => $cur['left'],
        ];
        $kpiPrev = [
            'due' => $prev['due'], 'paid' => $prev['paid'], 'rest' => max(0, $prev['due'] - $prev['paid']),
            'active' => $prev['active'], 'new' => $prev['new'], 'left' => $prev['left'],
            'rate' => $prev['due'] > 0 ? round($prev['paid'] / $prev['due'] * 100) : 0,
        ];

        // ---------- Cycles ----------
        $cycles = [];
        foreach (self::CYCLES as $key => $meta) {
            $c = $cur['cycles'][$key] ?? null;
            if ($key === 'other' && (!$c || $c['students'] === 0)) continue;
            $c = $c ?: ['students'=>0,'due'=>0,'paid'=>0,'levels'=>[]];
            $levels = [];
            foreach (Student::NIVEAUX_SCOLAIRES as $lv) {
                if (self::cycleOf($lv) !== $key) continue;
                $levels[] = ['label' => self::LEVELS_AR[$lv] ?? $lv, 'n' => $c['levels'][$lv] ?? 0];
            }
            if ($key === 'other' && isset($c['levels'][''])) $levels[] = ['label'=>'غير محدد','n'=>$c['levels']['']];
            $cycles[$key] = $meta + $c + ['rest' => max(0, $c['due'] - $c['paid']), 'levelRows' => $levels,
                'rate' => $c['due'] > 0 ? round($c['paid'] / $c['due'] * 100) : 0];
        }

        // ---------- Chart: 6 months ending at the selected period end ----------
        $chart = [];
        $endMonth = $end->copy()->startOfMonth();
        for ($i = 5; $i >= 0; $i--) {
            $m = $endMonth->copy()->subMonthsNoOverflow($i);
            $s = $this->periodStats($students, $m->copy()->startOfMonth(), $m->copy()->endOfMonth(), $curPer, false);
            $chart[] = ['label' => self::MONTHS[(int) $m->month], 'due' => round($s['due']), 'paid' => round($s['paid'])];
        }

        // ---------- Subjects ----------
        $slots = TimetableSlot::with(['subject', 'teacher', 'schoolClass.room'])->get();
        $subjectRows = [];
        foreach ($subjects as $sid => $sub) {
            $act = $students->filter(fn ($s) => $this->isActiveAt($s, $end) && $s->subjects->contains('id', $sid));
            $teachers = $sub->teachers->map(fn ($t) => $t->full_name)
                ->merge($slots->where('subject_id', $sid)->map(fn ($sl) => $sl->teacher?->full_name))
                ->filter()->unique()->values();
            $due = $cur['bySubject'][$sid] ?? 0;
            if ($act->isEmpty() && $due <= 0) continue;
            $subjectRows[] = ['name' => $sub->nom, 'teachers' => $teachers->implode('، '), 'students' => $act->count(), 'due' => $due];
        }
        usort($subjectRows, fn ($a, $b) => $b['due'] <=> $a['due']);
        $donut = collect($subjectRows)->filter(fn ($r) => $r['due'] > 0)->values();

        // ---------- Unpaid (open invoices up to the end of the period), oldest first ----------
        $endPer = $end->format('Y-m');
        $unpaid = [];
        foreach ($students as $s) {
            $open = $s->payments->filter(fn ($pm) => $pm->periode <= $endPer && (float) $pm->paye < (float) $pm->montant - 0.004)->sortBy('periode');
            if ($open->isEmpty()) continue;
            $first = $open->first();
            $phone = $s->parents->pluck('telephone')->filter()->first() ?: $s->telephone;
            $unpaid[] = [
                'student' => $s, 'level' => self::LEVELS_AR[$s->niveau_scolaire] ?? ($s->niveau_scolaire ?: '—'),
                'since' => $first->periode, 'sinceLabel' => self::monthLabel($first->periode),
                'created' => $first->created_at, 'months' => $open->count(),
                'rest' => $open->sum(fn ($pm) => max(0, (float) $pm->montant - (float) $pm->paye)),
                'phone' => $phone, 'wa' => self::waLink($phone),
                'payUrl' => route('payments.index', ['periode' => $first->periode, 'q' => $s->nom]),
                'days' => $first->created_at ? (int) $first->created_at->copy()->startOfDay()->diffInDays($today) : 0,
            ];
        }
        usort($unpaid, fn ($a, $b) => [$a['since'], $a['created']] <=> [$b['since'], $b['created']]);

        // ---------- Today's sessions ----------
        $todayFr = self::DAYS_FR[$today->dayOfWeekIso];
        $todaySlots = $slots->where('jour', $todayFr)->sortBy('debut')->values();
        $nextDay = null;
        if ($todaySlots->isEmpty() && $slots->isNotEmpty()) {
            for ($i = 1; $i <= 7; $i++) {
                $d = $today->copy()->addDays($i);
                $ds = $slots->where('jour', self::DAYS_FR[$d->dayOfWeekIso]);
                if ($ds->isNotEmpty()) { $nextDay = ['label' => self::DAYS_AR[$d->dayOfWeekIso].' '.$d->format('d/m'), 'slots' => $ds->sortBy('debut')->values()]; break; }
            }
        }

        // ---------- New registrations ----------
        $newStudents = $students->filter(fn ($s) => $s->date_inscription && $s->date_inscription->betweenIncluded($start, $end))
            ->sortByDesc('date_inscription')->values();

        // ---------- Smart alerts ----------
        $alerts = [];
        $late = collect($unpaid)->filter(fn ($u) => $u['days'] > 10);
        if ($late->isNotEmpty()) {
            $alerts[] = ['level' => 'red', 'title' => $late->count().' تلاميذ عندهم أداء متأخر أكثر من 10 أيام',
                'text' => $late->take(6)->map(fn ($u) => $u['student']->full_name)->implode('، ').($late->count() > 6 ? '…' : ''),
                'url' => route('payments.impayes')];
        }
        $susp = $students->where('statut', \App\Services\SessionBilling::SUSPENDED);
        if ($susp->isNotEmpty()) {
            $alerts[] = ['level' => 'amber', 'title' => $susp->count().' تلاميذ موقوفين مؤقتا (حضرو بلا أداء)',
                'text' => $susp->take(6)->map(fn ($s) => $s->full_name)->implode('، '), 'url' => route('payments.stats')];
        }
        $abs = Attendance::where('status', 'absent')->whereDate('date', '>=', $today->copy()->subDays(30)->toDateString())
            ->selectRaw('student_id, count(*) n')->groupBy('student_id')->having('n', '>=', 3)->get();
        if ($abs->isNotEmpty()) {
            $names = $students->whereIn('id', $abs->pluck('student_id'))->map(fn ($s) => $s->full_name);
            $alerts[] = ['level' => 'amber', 'title' => $abs->count().' تلاميذ غابو 3 مرات أو أكثر فآخر 30 يوم',
                'text' => $names->take(6)->implode('، '), 'url' => route('attendance.absences')];
        }
        foreach (SchoolClass::with('room')->get() as $cl) {
            $cap = (int) ($cl->capacite ?: $cl->room?->capacite);
            if ($cap <= 0) continue;
            $n = $students->where('class_id', $cl->id)->filter(fn ($s) => $this->isActiveAt($s, $today))->count();
            if ($n >= $cap * 0.9) {
                $alerts[] = ['level' => $n >= $cap ? 'red' : 'amber', 'title' => $cl->nom.' عامر: '.$n.' / '.$cap,
                    'text' => 'القسم وصل '.round($n / $cap * 100).'% من الطاقة الاستيعابية', 'url' => route('classes.show', $cl)];
            }
        }
        $noClass = $students->filter(fn ($s) => $this->isActiveAt($s, $today) && !$s->class_id);
        if ($noClass->isNotEmpty()) {
            $alerts[] = ['level' => 'blue', 'title' => $noClass->count().' تلاميذ بلا قسم',
                'text' => 'خاصهم يتوزعو على الأقسام باش تتحسب الحصص ديالهم من استعمال الزمن', 'url' => route('classes.index')];
        }
        $noSub = $students->filter(fn ($s) => $this->isActiveAt($s, $today) && $s->subjects->isEmpty());
        if ($noSub->isNotEmpty()) {
            $alerts[] = ['level' => 'blue', 'title' => $noSub->count().' تلاميذ بلا مواد',
                'text' => $noSub->take(6)->map(fn ($s) => $s->full_name)->implode('، '), 'url' => route('students.index')];
        }
        $unread = StudentAlert::whereNull('read_at')->count();
        if ($unread) {
            $alerts[] = ['level' => 'amber', 'title' => $unread.' إشعارات غير مقروءة', 'text' => 'شوف صفحة الإشعارات', 'url' => route('payments.stats').'#alerts'];
        }

        $months = [];
        $m0 = $year ? Carbon::parse($year->date_debut)->startOfMonth() : $today->copy()->subMonths(11)->startOfMonth();
        $m1 = $year ? Carbon::parse($year->date_fin)->startOfMonth() : $today->copy()->startOfMonth();
        for ($m = $m0->copy(); $m->lte($m1); $m->addMonthNoOverflow()) $months[] = ['v' => $m->format('Y-m'), 'l' => self::monthLabel($m->format('Y-m'))];

        $todayLabel = self::DAYS_AR[$today->dayOfWeekIso].' '.$today->format('d').' '.self::MONTHS[(int) $today->month].' '.$today->year;

        return view('dashboard.index', compact(
            'p', 'selectedMonth', 'label', 'prevLabel', 'start', 'end', 'year', 'kpi', 'kpiPrev', 'cycles', 'chart', 'subjectRows', 'donut',
            'unpaid', 'todaySlots', 'nextDay', 'newStudents', 'alerts', 'months', 'todayLabel', 'curPer'
        ));
    }

    private function isActiveAt(Student $s, Carbon $date): bool
    {
        if ($s->date_inscription && $s->date_inscription->gt($date)) return false;
        if (in_array($s->statut, self::LEFT, true)) {
            $left = $s->retire_at ?? $s->updated_at;
            return $left && $left->gt($date);
        }
        return true;
    }

    private function leftBetween(Student $s, Carbon $a, Carbon $b): bool
    {
        if (!in_array($s->statut, self::LEFT, true) || $s->statut === 'Diplômé') return false;
        $left = $s->retire_at ?? $s->updated_at;
        return $left && $left->betweenIncluded($a, $b);
    }

    private function cash(Carbon $a, Carbon $b): float
    {
        return (float) PaymentTransaction::whereBetween('date', [$a->toDateString(), $b->toDateString()])
            ->whereHas('payment', fn ($q) => $q->where('statut', '!=', 'Annulé'))->sum('montant');
    }

    /** Due/paid aggregated over every month of [$a, $b]. */
    private function periodStats(Collection $students, Carbon $a, Carbon $b, string $curPer, bool $full = true): array
    {
        $r = ['due' => 0.0, 'paid' => 0.0, 'estimated' => 0.0, 'cycles' => [], 'bySubject' => [],
            'active' => 0, 'suspended' => 0, 'new' => 0, 'left' => 0];
        $pers = [];
        for ($m = $a->copy()->startOfMonth(); $m->lte($b); $m->addMonthNoOverflow()) $pers[] = $m->format('Y-m');

        foreach ($students as $s) {
            $cy = self::cycleOf($s->niveau_scolaire);
            $r['cycles'][$cy] ??= ['students' => 0, 'due' => 0.0, 'paid' => 0.0, 'levels' => []];
            foreach ($pers as $per) {
                $mEnd = Carbon::createFromFormat('Y-m-d', $per.'-01')->endOfMonth();
                $inv = $s->payments->where('periode', $per);
                if ($inv->isNotEmpty()) {
                    foreach ($inv as $pm) {
                        $r['due'] += (float) $pm->montant; $r['paid'] += (float) $pm->paye;
                        $r['cycles'][$cy]['due'] += (float) $pm->montant; $r['cycles'][$cy]['paid'] += (float) $pm->paye;
                        if ($full) {
                            $lines = is_array($pm->details) ? $pm->details : [];
                            if ($lines) foreach ($lines as $l) { $sid = (int) ($l['subject_id'] ?? 0); $r['bySubject'][$sid] = ($r['bySubject'][$sid] ?? 0) + (float) ($l['amount'] ?? 0); }
                            else foreach ($s->subjects as $sub) { $r['bySubject'][$sub->id] = ($r['bySubject'][$sub->id] ?? 0) + (float) $sub->pivot->prix; }
                        }
                    }
                } elseif ($per >= $curPer && $this->isActiveAt($s, $mEnd) && !in_array($s->statut, self::LEFT, true)) {
                    // invoice not generated yet (month-end): monthly fee from the student's subject prices
                    $fee = $s->monthlyFeeAmount();
                    $r['due'] += $fee; $r['estimated'] += $fee; $r['cycles'][$cy]['due'] += $fee;
                    if ($full) foreach ($s->subjects as $sub) { $r['bySubject'][$sub->id] = ($r['bySubject'][$sub->id] ?? 0) + (float) ($sub->pivot->prix ?? $sub->prix); }
                }
            }
            if ($this->isActiveAt($s, $b)) {
                $r['active']++;
                $r['cycles'][$cy]['students']++;
                $lv = (string) $s->niveau_scolaire;
                $r['cycles'][$cy]['levels'][$lv] = ($r['cycles'][$cy]['levels'][$lv] ?? 0) + 1;
                if ($s->statut === \App\Services\SessionBilling::SUSPENDED) $r['suspended']++;
            }
            if ($s->date_inscription && $s->date_inscription->betweenIncluded($a, $b)) $r['new']++;
            if ($this->leftBetween($s, $a, $b)) $r['left']++;
        }
        return $r;
    }

    public static function waLink(?string $phone): ?string
    {
        $d = preg_replace('/\D+/', '', (string) $phone);
        if ($d === '') return null;
        if (str_starts_with($d, '00')) $d = substr($d, 2);
        if (str_starts_with($d, '0')) $d = '212'.substr($d, 1);
        elseif (strlen($d) === 9) $d = '212'.$d;
        return 'https://wa.me/'.$d;
    }
}
