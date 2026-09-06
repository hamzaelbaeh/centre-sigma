<?php
namespace App\Http\Controllers;

use App\Models\Teacher;
use App\Models\TeacherPayroll;
use Illuminate\Http\Request;

class TeacherPayrollController extends Controller
{
    public function index(Request $request)
    {
        $periode = $request->get('periode', now()->format('Y-m'));
        $payrolls = TeacherPayroll::with('teacher')->where('periode', $periode)->orderBy('id')->get();
        return view('payroll_teachers.index', compact('payrolls','periode'));
    }

    public function generate(Request $request)
    {
        $periode = $request->get('periode', now()->format('Y-m'));
        $teachers = Teacher::where('statut','Actif')->get();
        $n = 0;
        foreach ($teachers as $t) {
            $brut = (float)$t->valeur_dh;
            $net = TeacherPayroll::calcNet($brut, 0, 0, 0);
            TeacherPayroll::updateOrCreate(
                ['teacher_id'=>$t->id,'periode'=>$periode],
                [
                    'mode_paiement'=>$t->mode_paiement,
                    'base'=>$brut,'taux'=>$t->mode_paiement==='Pourcentage'?$t->valeur_dh:0,
                    'brut'=>$brut,'primes'=>0,'avances'=>0,'retenues'=>0,'net'=>$net,'statut'=>'En attente',
                ]
            );
            $n++;
        }
        return back()->with('success', __(':n fiche(s) de paie générée(s).', ['n' => $n]));
    }

    public function pay(TeacherPayroll $payroll)
    {
        $payroll->update(['statut'=>'Payé']);
        return back()->with('success', __('Paie marquée comme payée.'));
    }
}
