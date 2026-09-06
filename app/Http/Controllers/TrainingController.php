<?php
namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Subject;
use App\Models\Training;
use App\Models\TrainingParticipant;
use Illuminate\Http\Request;

class TrainingController extends Controller
{
    public function index()
    {
        $trainings = Training::withCount('participants')->orderByDesc('id')->get();
        return view('trainings.index', compact('trainings'));
    }

    public function create()
    {
        $subjects = Subject::orderBy('nom')->get();
        return view('trainings.create', compact('subjects'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nom'=>'required|string','formateur'=>'nullable|string','description'=>'nullable|string',
            'duree'=>'nullable|string','date_debut'=>'nullable|date','date_fin'=>'nullable|date',
            'prix'=>'nullable|numeric','salle'=>'nullable|string','classe_niveau'=>'nullable|string',
            'nombre_seances'=>'nullable|integer','subjects'=>'nullable|array',
        ]);
        $subjects = $data['subjects'] ?? [];
        unset($data['subjects']);
        $t = Training::create($data);
        $t->subjects()->sync($subjects);
        return redirect()->route('trainings.show',$t)->with('success',__('Formation créée.'));
    }

    public function show(Training $training)
    {
        $training->load(['participants.student','subjects']);
        $students = Student::where('statut','Actif')->orderBy('nom')->get();
        return view('trainings.show', compact('training','students'));
    }

    public function edit(Training $training)
    {
        $subjects = Subject::orderBy('nom')->get();
        $training->load('subjects');
        return view('trainings.edit', compact('training','subjects'));
    }

    public function update(Request $request, Training $training)
    {
        $data = $request->validate([
            'nom'=>'required|string','formateur'=>'nullable|string','description'=>'nullable|string',
            'duree'=>'nullable|string','date_debut'=>'nullable|date','date_fin'=>'nullable|date',
            'prix'=>'nullable|numeric','salle'=>'nullable|string','classe_niveau'=>'nullable|string',
            'nombre_seances'=>'nullable|integer','subjects'=>'nullable|array',
        ]);
        $subjects = $data['subjects'] ?? [];
        unset($data['subjects']);
        $training->update($data);
        $training->subjects()->sync($subjects);
        return redirect()->route('trainings.show',$training)->with('success',__('Formation mise à jour.'));
    }

    public function destroy(Training $training)
    {
        $training->delete();
        return redirect()->route('trainings.index')->with('success',__('Formation supprimée.'));
    }

    public function enroll(Request $request, Training $training)
    {
        $data = $request->validate([
            'student_id'=>'nullable|exists:students,id',
            'nom_externe'=>'nullable|string',
            'telephone'=>'nullable|string',
            'montant_paye'=>'nullable|numeric',
        ]);
        $training->participants()->create($data);
        return back()->with('success',__('Participant inscrit.'));
    }

    public function updateParticipant(Request $request, TrainingParticipant $participant)
    {
        $data = $request->validate([
            'montant_paye'=>'nullable|numeric','resultat'=>'nullable|string','certificat'=>'nullable|boolean',
        ]);
        $data['certificat'] = $request->boolean('certificat');
        $participant->update($data);
        return back()->with('success',__('Participant mis à jour.'));
    }
}
