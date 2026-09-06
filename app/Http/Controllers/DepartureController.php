<?php
namespace App\Http\Controllers;

use App\Models\Departure;
use App\Models\Payment;
use App\Models\Student;
use Illuminate\Http\Request;

class DepartureController extends Controller
{
    public function index()
    {
        $departures = Departure::with('student.schoolClass')->orderByDesc('date_sortie')->paginate(25);
        return view('departures.index', compact('departures'));
    }

    public function create()
    {
        $students = Student::where('statut','Actif')->orderBy('nom')->get();
        return view('departures.create', compact('students'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'student_id'=>'nullable|exists:students,id',
            'nom_externe'=>'nullable|string',
            'date_sortie'=>'required|date',
            'statut'=>'required|string',
            'raison'=>'nullable|string',
            'destination'=>'nullable|string',
            'documents_remis'=>'nullable|string',
            'observation'=>'nullable|string',
        ]);
        $solde = 0;
        if (!empty($data['student_id'])) {
            $solde = Payment::where('student_id',$data['student_id'])->selectRaw('SUM(montant-paye) as r')->value('r') ?? 0;
            Student::where('id',$data['student_id'])->update(['statut'=>$data['statut']]);
        }
        $data['solde_du'] = $solde;
        Departure::create($data);
        return redirect()->route('departures.index')->with('success','Départ enregistré. Historique conservé.');
    }

    public function destroy(Departure $departure)
    {
        $departure->delete();
        return back()->with('success','Enregistrement de départ retiré.');
    }
}
