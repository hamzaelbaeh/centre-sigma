<?php
namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Departure;
use App\Models\Expense;
use App\Models\Payment;
use App\Models\SchoolClass;
use App\Models\StaffPayroll;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\TeacherPayroll;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'scolaires');
        $periode = $request->get('periode', now()->format('Y-m'));

        $actifs = Student::where('statut','Actif')->count();
        $sortants = Departure::count();
        $effectif = SchoolClass::withCount(['students as n' => fn($q)=>$q->where('statut','Actif')])->get();
        $from = $periode.'-01';
        $to = date('Y-m-t', strtotime($from));
        $absences = Attendance::where('status','absent')
            ->whereBetween('date', [$from, $to])
            ->selectRaw('class_id, count(*) as c')->groupBy('class_id')->pluck('c','class_id');

        $recettes = Payment::where('periode',$periode)->sum('paye');
        $depenses = Expense::whereBetween('date', [$from, $to])->sum('montant');
        $salaires = TeacherPayroll::where('periode',$periode)->sum('net') + StaffPayroll::where('periode',$periode)->sum('net');
        $benefice = $recettes - $depenses - $salaires;
        $impayes = Payment::with('student.schoolClass')->whereRaw('paye < montant')->limit(50)->get();

        $teachers = Teacher::with('subjects')->where('statut','Actif')->get();

        return view('reports.index', compact(
            'tab','periode','actifs','sortants','effectif','absences',
            'recettes','depenses','salaires','benefice','impayes','teachers'
        ));
    }
}
