<?php
namespace App\Services;

use App\Models\Payment;
use App\Models\PaymentTransaction;
use App\Models\Student;
use Carbon\Carbon;
use Mpdf\Mpdf;

/** Monthly students report (PDF): students grouped by parent, due / paid / status for one month. */
class MonthlyReport
{
    public const MONTHS = ['يناير', 'فبراير', 'مارس', 'أبريل', 'ماي', 'يونيو', 'يوليوز', 'غشت', 'شتنبر', 'أكتوبر', 'نونبر', 'دجنبر'];

    public static function monthLabel(string $periode): string
    {
        $d = Carbon::createFromFormat('Y-m-d', $periode.'-01');
        return self::MONTHS[$d->month - 1].' '.$d->year;
    }

    public static function data(string $periode): array
    {
        SessionBilling::finalizeEstimates();
        $start = Carbon::createFromFormat('Y-m-d', $periode.'-01')->startOfDay();
        $end = $start->copy()->endOfMonth();
        $invoices = Payment::where('periode', $periode)->where('type', 'Mensualité')->where('statut', '!=', 'Annulé')
            ->get()->groupBy('student_id');
        $students = Student::with(['parents' => fn ($q) => $q->orderBy('parent_student.id'), 'subjects', 'schoolClass'])
            ->where(function ($q) use ($invoices, $end) {
                $q->whereIn('id', $invoices->keys()->all())
                  ->orWhere(fn ($w) => $w->whereIn('statut', ['Actif', SessionBilling::SUSPENDED])
                      ->where(fn ($d) => $d->whereNull('date_inscription')->orWhere('date_inscription', '<=', $end->toDateString()))
                      ->whereHas('subjects'));
            })->get();

        $rows = $students->map(function (Student $s) use ($invoices, $start, $end) {
            $inv = $invoices->get($s->id);
            $due = $inv ? (float) $inv->sum('montant') : 0.0;
            $paye = $inv ? (float) $inv->sum('paye') : 0.0;
            $creditIn = $inv ? (float) $inv->sum('credit_in') : 0.0;
            $creditOut = $inv ? (float) $inv->sum('credit_out') : 0.0;
            $cash = (float) PaymentTransaction::whereIn('payment_id', Payment::where('student_id', $s->id)->pluck('id'))
                ->whereBetween('date', [$start->toDateString(), $end->toDateString()])->sum('montant');
            if (!$inv) { $status = 'بدون فاتورة'; }
            elseif ($paye >= $due) { $status = 'مؤدّى'; }
            elseif ($paye > 0) { $status = 'جزئي'."\n".'المتبقي '.RegistrationReceipt::money($due - $paye); }
            else { $status = 'غير مؤدّى'; }
            $parent = $s->parents->first();
            return [
                'student' => $s, 'parent' => $parent,
                'subjects' => $s->subjects->pluck('nom')->implode('، ') ?: '—',
                'due' => $due, 'cash' => $cash, 'credit_in' => $creditIn, 'credit_out' => $creditOut,
                'estimated' => $inv ? (bool) $inv->contains('estimated', true) : false,
                'status' => $status, 'paid_ok' => $inv && $paye >= $due,
            ];
        })->sortBy([
            fn ($a, $b) => ($a['parent']?->numero ?? PHP_INT_MAX) <=> ($b['parent']?->numero ?? PHP_INT_MAX),
            fn ($a, $b) => $a['student']->id <=> $b['student']->id,
        ])->values();

        $groups = $rows->groupBy(fn ($r) => $r['parent']?->id ?? 0)->values();
        return [
            'periode' => $periode, 'label' => self::monthLabel($periode), 'groups' => $groups,
            'total_due' => $rows->sum('due'), 'total_cash' => $rows->sum('cash'),
            'count' => $rows->count(), 'parents' => $rows->pluck('parent.id')->filter()->unique()->count(),
            'logo' => public_path('images/logo-sigma-bw.png'),
            'generated' => now()->format('d/m/Y H:i'),
        ];
    }

    public static function pdf(string $periode): string
    {
        $dir = storage_path('app/mpdf');
        if (!is_dir($dir)) @mkdir($dir, 0775, true);
        $mpdf = new Mpdf([
            'mode' => 'utf-8', 'format' => 'A4-L', 'orientation' => 'L',
            'margin_left' => 10, 'margin_right' => 10, 'margin_top' => 10, 'margin_bottom' => 14, 'margin_footer' => 5,
            'tempDir' => $dir, 'default_font' => 'xbriyaz', 'default_font_size' => 10,
            'autoScriptToLang' => false, 'autoLangToFont' => false,
        ]);
        $mpdf->SetDirectionality('rtl');
        $mpdf->SetTitle('Rapport mensuel '.$periode);
        $mpdf->SetAuthor('Centre Sigma');
        $mpdf->SetFooter('<div dir="ltr" style="direction:ltr;text-align:center;font-size:8pt">{PAGENO} / {nbpg}</div>');
        $mpdf->WriteHTML(view('reports.monthly_pdf', self::data($periode))->render());
        return $mpdf->Output('', \Mpdf\Output\Destination::STRING_RETURN);
    }
}
