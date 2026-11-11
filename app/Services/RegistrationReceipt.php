<?php
namespace App\Services;

use App\Models\Student;
use Mpdf\Mpdf;

/** Registration receipt: one A4 page, black & white, Arabic RTL; top = centre archive copy, bottom = parent payment receipt. */
class RegistrationReceipt
{
    public const PHONE = '0653602238';
    public const ADDRESS = 'مركز سيكما – جماعة مزوضة';

    private static function ar(?string $key): string
    {
        return $key ? (string) __($key, [], 'ar') : '—';
    }

    public static function money($n): string
    {
        $n = round((float) $n, 2);
        return (floor($n) == $n ? number_format($n, 0, '', '') : number_format($n, 2, ',', '')).' DH';
    }

    public static function data(Student $student): array
    {
        $student->loadMissing(['schoolClass', 'parents', 'subjects']);
        $parent = $student->parents()->orderBy('parent_student.id')->first();
        $pay = $student->registrationPayment();

        $lines = [];
        if ($pay && is_array($pay->details) && $pay->details) {
            $lines = $pay->details;
        } else {
            foreach ($student->subjects as $s) {
                $prix = $s->pivot->prix !== null && $s->pivot->prix !== '' ? (float) $s->pivot->prix : (float) $s->prix;
                $spm = $s->sessions_per_month;
                $lines[] = ['subject' => $s->nom, 'monthly' => $prix, 'sessions_month' => $spm, 'sessions' => $spm,
                    'session_price' => round($prix / $spm, 2), 'amount' => $prix];
            }
        }
        $full = $pay && $pay->full_amount !== null ? (float) $pay->full_amount : (float) collect($lines)->sum('monthly');
        $consumed = $pay ? (float) $pay->montant : (float) collect($lines)->sum('amount');
        $credit = $pay ? (float) $pay->credit_out : 0.0;
        $cash = $pay ? (float) $pay->transactions()->sum('montant') : 0.0;
        $date = $pay?->transactions()->orderBy('date')->value('date') ?? $student->date_inscription ?? now();

        return [
            'student' => $student,
            'niveau' => self::ar($student->niveau_scolaire),
            'classe' => $student->schoolClass?->nom ?: '—',
            'parent' => $parent,
            'parent_id' => $parent ? $parent->numero_pad.'-'.$parent->children_count : '—',
            'lines' => $lines,
            'subjects' => collect($lines)->pluck('subject')->implode('، '),
            'full' => $full, 'consumed' => $consumed, 'credit' => $credit, 'cash' => $cash,
            'next_due' => max(0, $full - $credit),
            'estimated' => $pay ? (bool) $pay->estimated : false,
            'receipt_no' => 'REC-'.str_pad((string) ($pay?->id ?? $student->id), 5, '0', STR_PAD_LEFT),
            'date' => \Carbon\Carbon::parse($date)->format('d/m/Y'),
            'reg_date' => optional($student->date_inscription)->format('d/m/Y') ?: '—',
            'phone' => self::PHONE, 'address' => self::ADDRESS,
            'logo' => public_path('images/logo-sigma-bw.png'),
        ];
    }

    /** Renders at increasing compaction levels until everything fits on exactly one A4 page. */
    public static function pdf(Student $student): string
    {
        $data = self::data($student);
        for ($level = 0; $level <= 3; $level++) {
            $mpdf = self::make();
            $mpdf->SetTitle('Reçu d\'inscription '.$student->matricule);
            $mpdf->WriteHTML(view('receipts.registration', $data + ['level' => $level])->render());
            if ($mpdf->page <= 1 || $level === 3) {
                return $mpdf->Output('', \Mpdf\Output\Destination::STRING_RETURN);
            }
        }
    }

    private static function make(): Mpdf
    {
        $dir = storage_path('app/mpdf');
        if (!is_dir($dir)) @mkdir($dir, 0775, true);
        $mpdf = new Mpdf([
            'mode' => 'utf-8', 'format' => 'A4', 'orientation' => 'P',
            'margin_left' => 10, 'margin_right' => 10, 'margin_top' => 10, 'margin_bottom' => 10,
            'margin_header' => 0, 'margin_footer' => 0,
            'tempDir' => $dir,
            'default_font' => 'xbriyaz', 'default_font_size' => 10,
            'autoScriptToLang' => false, 'autoLangToFont' => false,
        ]);
        $mpdf->SetDirectionality('rtl');
        $mpdf->SetAuthor('Centre Sigma');
        return $mpdf;
    }
}
