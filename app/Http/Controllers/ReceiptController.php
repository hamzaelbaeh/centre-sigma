<?php
namespace App\Http\Controllers;

use App\Models\Student;
use App\Services\RegistrationReceipt;

class ReceiptController extends Controller
{
    /** Opens the PDF inline in the browser (so it can be printed directly). */
    public function show(Student $student)
    {
        return $this->respond($student, 'inline');
    }

    public function download(Student $student)
    {
        return $this->respond($student, 'attachment');
    }

    private function respond(Student $student, string $disposition)
    {
        $pdf = RegistrationReceipt::pdf($student);
        $name = 'recu-'.preg_replace('/[^A-Za-z0-9_-]+/', '', $student->matricule ?: (string) $student->id).'.pdf';
        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => $disposition.'; filename="'.$name.'"',
            'Content-Length' => strlen($pdf),
            'Cache-Control' => 'private, max-age=0, must-revalidate',
        ]);
    }
}
