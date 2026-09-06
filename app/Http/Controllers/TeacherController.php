<?php
namespace App\Http\Controllers;

use App\Models\Teacher;
use Illuminate\Http\Request;

class TeacherController extends Controller
{
    public function index(Request $request)
    {
        $q = Teacher::with(['subjects','classes']);
        if ($s = $request->get('q')) {
            $q->where(function($w) use ($s) {
                $w->where('nom','like',"%$s%")->orWhere('prenom','like',"%$s%")
                  ->orWhere('matricule','like',"%$s%")->orWhere('specialite','like',"%$s%");
            });
        }
        $teachers = $q->orderBy('nom')->paginate(25)->withQueryString();
        return view('teachers.index', compact('teachers'));
    }

    public function create() { return view('teachers.create'); }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nom' => 'required|string',
            'prenom' => 'required|string',
            'cin' => 'nullable|string',
            'telephone' => 'nullable|string',
            'email' => 'nullable|email',
            'adresse' => 'nullable|string',
            'date_embauche' => 'nullable|date',
            'specialite' => 'nullable|string',
            'statut' => 'nullable|string',
            'mode_paiement' => 'nullable|string',
            'valeur_dh' => 'required|numeric|min:0',
        ]);
        $data['statut'] = $data['statut'] ?? 'Actif';
        $data['mode_paiement'] = $data['mode_paiement'] ?? 'Mensuel';
        $data['matricule'] = 'ENS'.str_pad((string)(Teacher::max('id')+1), 4, '0', STR_PAD_LEFT);
        Teacher::create($data);
        return redirect()->route('teachers.index')->with('success', __('Enseignant créé.'));
    }

    public function edit(Teacher $teacher) { return view('teachers.edit', compact('teacher')); }

    public function update(Request $request, Teacher $teacher)
    {
        $data = $request->validate([
            'nom' => 'required|string',
            'prenom' => 'required|string',
            'cin' => 'nullable|string',
            'telephone' => 'nullable|string',
            'email' => 'nullable|email',
            'adresse' => 'nullable|string',
            'date_embauche' => 'nullable|date',
            'specialite' => 'nullable|string',
            'statut' => 'nullable|string',
            'mode_paiement' => 'nullable|string',
            'valeur_dh' => 'required|numeric|min:0',
        ]);
        $teacher->update($data);
        return redirect()->route('teachers.index')->with('success', __('Enseignant mis à jour.'));
    }

    public function destroy(Teacher $teacher)
    {
        $teacher->delete();
        return redirect()->route('teachers.index')->with('success', __('Enseignant supprimé.'));
    }
}
