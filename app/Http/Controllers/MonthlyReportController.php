<?php
namespace App\Http\Controllers;

use App\Services\MonthlyReport;
use Illuminate\Http\Request;

class MonthlyReportController extends Controller
{
    public function show(Request $request)
    {
        return $this->respond($request, 'inline');
    }

    public function download(Request $request)
    {
        return $this->respond($request, 'attachment');
    }

    private function respond(Request $request, string $disposition)
    {
        $periode = $request->validate(['periode' => ['nullable', 'regex:/^\d{4}-(0[1-9]|1[0-2])$/']])['periode'] ?? now()->format('Y-m');
        $pdf = MonthlyReport::pdf($periode);
        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => $disposition.'; filename="rapport-eleves-'.$periode.'.pdf"',
            'Content-Length' => strlen($pdf),
            'Cache-Control' => 'private, max-age=0, must-revalidate',
        ]);
    }
}
