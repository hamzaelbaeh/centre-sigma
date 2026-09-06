<?php
namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Expense;
use App\Models\Payment;
use App\Models\SchoolClass;
use App\Models\Setting;
use App\Models\StaffPayroll;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\TeacherPayroll;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    public function index()
    {
        $students = Student::orderBy('nom')->get();
        $classes = SchoolClass::orderBy('nom')->get();
        return view('documents.index', compact('students','classes'));
    }

    public function print(Request $request, string $type)
    {
        $settings = Setting::current();
        return match($type) {
            'recu' => $this->printRecu($request, $settings),
            'certificat' => $this->printStudentDoc($request, $settings, 'Certificat de scolarité'),
            'attestation' => $this->printStudentDoc($request, $settings, 'Attestation d\'inscription'),
            'releve' => $this->printReleve($request, $settings),
            'liste_eleves' => $this->printListeEleves($request, $settings),
            'absences' => $this->printAbsences($request, $settings),
            'impayes' => view('prints.impayes', ['payments'=>Payment::with('student.schoolClass')->whereRaw('paye<montant')->get(),'settings'=>$settings]),
            'enseignants' => view('prints.enseignants', ['teachers'=>Teacher::orderBy('nom')->get(),'settings'=>$settings]),
            'salaires' => view('prints.salaires', [
                'teacherPayrolls'=>TeacherPayroll::with('teacher')->get(),
                'staffPayrolls'=>StaffPayroll::with('staff')->get(),
                'settings'=>$settings,
            ]),
            'depenses' => view('prints.depenses', ['expenses'=>Expense::orderByDesc('date')->get(),'settings'=>$settings]),
            default => abort(404),
        };
    }

    private function printRecu(Request $request, $settings)
    {
        $student = Student::with('schoolClass')->findOrFail($request->student_id);
        $payment = Payment::where('student_id',$student->id)->latest()->first();
        return view('prints.recu', compact('student','payment','settings'));
    }

    private function printStudentDoc(Request $request, $settings, $title)
    {
        $student = Student::with('schoolClass')->findOrFail($request->student_id);
        return view('prints.student_doc', compact('student','settings','title'));
    }

    private function printReleve(Request $request, $settings)
    {
        $student = Student::with(['schoolClass','payments.transactions'])->findOrFail($request->student_id);
        return view('prints.releve', compact('student','settings'));
    }

    private function printListeEleves(Request $request, $settings)
    {
        $class = SchoolClass::with('students')->findOrFail($request->class_id);
        return view('prints.liste_eleves', compact('class','settings'));
    }

    private function printAbsences(Request $request, $settings)
    {
        $class = SchoolClass::findOrFail($request->class_id);
        $absences = Attendance::with('student')->where('class_id',$class->id)->where('status','absent')->orderByDesc('date')->get();
        return view('prints.absences', compact('class','absences','settings'));
    }
}
