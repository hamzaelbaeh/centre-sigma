<?php
namespace App\Http\Controllers;

use App\Models\Staff;
use App\Models\StaffPayroll;
use App\Models\TeacherPayroll;
use Illuminate\Http\Request;

class StaffPayrollController extends Controller
{
    public function index(Request $request)
    {
        $periode = $request->get('periode', now()->format('Y-m'));
        $payrolls = StaffPayroll::with('staff')->where('periode',$periode)->get();
        return view('staff_payroll.index', compact('payrolls','periode'));
    }

    public function generate(Request $request)
    {
        $periode = $request->get('periode', now()->format('Y-m'));
        foreach (Staff::where('statut','Actif')->get() as $s) {
            $net = TeacherPayroll::calcNet($s->salaire, 0, 0, 0);
            StaffPayroll::updateOrCreate(
                ['staff_id'=>$s->id,'periode'=>$periode],
                ['brut'=>$s->salaire,'primes'=>0,'avances'=>0,'retenues'=>0,'net'=>$net,'statut'=>'En attente']
            );
        }
        return back()->with('success','Paie du personnel générée.');
    }

    public function update(Request $request, StaffPayroll $payroll)
    {
        $data = $request->validate([
            'brut'=>'required|numeric','primes'=>'nullable|numeric','avances'=>'nullable|numeric','retenues'=>'nullable|numeric',
        ]);
        $data['primes'] = $data['primes'] ?? 0;
        $data['avances'] = $data['avances'] ?? 0;
        $data['retenues'] = $data['retenues'] ?? 0;
        $data['net'] = TeacherPayroll::calcNet($data['brut'],$data['primes'],$data['avances'],$data['retenues']);
        $payroll->update($data);
        return back()->with('success','Fiche mise à jour.');
    }

    public function pay(StaffPayroll $payroll)
    {
        $payroll->update(['statut'=>'Payé']);
        return back()->with('success','Paie payée.');
    }
}
