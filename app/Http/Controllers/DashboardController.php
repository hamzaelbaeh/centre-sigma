<?php
namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Departure;
use App\Models\Expense;
use App\Models\Payment;
use App\Models\SchoolClass;
use App\Models\Staff;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\TeacherPayroll;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $month = now()->format('Y-m');
        $eleves = Student::where('statut', 'Actif')->count();
        $enseignants = Teacher::where('statut', 'Actif')->count();
        $classes = SchoolClass::count();
        $employes = Staff::where('statut', 'Actif')->count();

        $start = now()->startOfMonth()->toDateString();
        $end = now()->endOfMonth()->toDateString();
        $encaisseMois = Payment::where('periode', $month)->sum('paye');
        if ($encaisseMois == 0) {
            $encaisseMois = \App\Models\PaymentTransaction::whereBetween('date', [$start, $end])->sum('montant');
        }
        $restant = Payment::selectRaw('SUM(montant - paye) as r')->value('r') ?? 0;
        $salairesAPayer = Teacher::where('statut','Actif')->sum('valeur_dh') + Staff::where('statut','Actif')->sum('salaire');
        $depenses = Expense::whereBetween('date', [$start, $end])->sum('montant');
        $salairesPayes = TeacherPayroll::where('periode',$month)->where('statut','Payé')->sum('net')
            + \App\Models\StaffPayroll::where('periode',$month)->where('statut','Payé')->sum('net');
        $resultat = $encaisseMois - $depenses - $salairesPayes;
        $attendu = Payment::notCancelled()->where('periode', $month)->sum('montant');
        $taux = $attendu > 0 ? round(($encaisseMois / $attendu) * 100) : 0;
        $absencesToday = Attendance::whereDate('date', today())->where('status','absent')->count();

        $overdue = Payment::with(['student.schoolClass'])
            ->activeDue()
            ->orderByRaw('(montant - paye) desc')
            ->limit(10)
            ->get();

        $chartMonths = [];
        for ($i = 5; $i >= 0; $i--) {
            $m = now()->subMonths($i);
            $key = $m->format('Y-m');
            $label = $m->translatedFormat('M Y');
            $ms = $m->copy()->startOfMonth()->toDateString();
            $me = $m->copy()->endOfMonth()->toDateString();
            $chartMonths[] = [
                'label' => $label,
                'recettes' => (float) \App\Models\PaymentTransaction::whereBetween('date', [$ms, $me])->sum('montant'),
                'depenses' => (float) Expense::whereBetween('date', [$ms, $me])->sum('montant'),
                'salaires' => (float) TeacherPayroll::where('periode',$key)->where('statut','Payé')->sum('net')
                    + (float) \App\Models\StaffPayroll::where('periode',$key)->where('statut','Payé')->sum('net'),
            ];
        }

        return view('dashboard.index', compact(
            'eleves','enseignants','classes','employes','encaisseMois','restant','salairesAPayer',
            'depenses','resultat','taux','attendu','absencesToday','overdue','chartMonths','month'
        ));
    }
}
