<?php
namespace App\Http\Controllers;

use App\Models\ParentGuardian;
use App\Models\SchoolClass;
use App\Models\SchoolYear;
use App\Models\Student;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $q = Student::with(['schoolClass','parents']);
        if ($search = $request->get('q')) {
            $q->where(function($w) use ($search) {
                $w->where('nom','like',"%$search%")
                  ->orWhere('prenom','like',"%$search%")
                  ->orWhere('matricule','like',"%$search%")
                  ->orWhere('telephone','like',"%$search%");
            });
        }
        if ($request->filled('class_id')) $q->where('class_id', $request->class_id);
        if ($request->filled('statut')) $q->where('statut', $request->statut);
        $students = $q->orderBy('nom')->paginate(25)->withQueryString();
        $classes = SchoolClass::orderBy('nom')->get();
        return view('students.index', compact('students','classes'));
    }

    public function create()
    {
        $classes = SchoolClass::orderBy('nom')->get();
        $parents = ParentGuardian::orderBy('nom')->get();
        return view('students.create', compact('classes','parents'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'date_naissance' => 'nullable|date',
            'lieu_naissance' => 'nullable|string',
            'sexe' => 'nullable|in:M,F',
            'cin' => 'nullable|string',
            'telephone' => 'nullable|string',
            'email' => 'nullable|email',
            'adresse' => 'nullable|string',
            'class_id' => 'nullable|exists:classes,id',
            'date_inscription' => 'nullable|date',
            'statut' => 'nullable|string',
            'code_massar' => 'nullable|string',
            'photo' => 'nullable|image|max:2048',
            'parents' => 'nullable|array',
        ]);
        $data['statut'] = $data['statut'] ?? 'Actif';
        $data['school_year_id'] = SchoolYear::active()?->id;
        if (!$request->filled('matricule')) {
            $data['matricule'] = 'EL'.str_pad((string)(Student::max('id')+1), 5, '0', STR_PAD_LEFT);
        }
        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('photos', 'public');
        }
        $parents = $data['parents'] ?? [];
        unset($data['parents']);
        $student = Student::create($data);
        $student->parents()->sync($parents);
        return redirect()->route('students.index')->with('success', 'Élève créé avec succès.');
    }

    public function show(Student $student)
    {
        $student->load(['schoolClass','parents','payments']);
        return view('students.show', compact('student'));
    }

    public function edit(Student $student)
    {
        $classes = SchoolClass::orderBy('nom')->get();
        $parents = ParentGuardian::orderBy('nom')->get();
        $student->load('parents');
        return view('students.edit', compact('student','classes','parents'));
    }

    public function update(Request $request, Student $student)
    {
        $data = $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'date_naissance' => 'nullable|date',
            'lieu_naissance' => 'nullable|string',
            'sexe' => 'nullable|in:M,F',
            'cin' => 'nullable|string',
            'telephone' => 'nullable|string',
            'email' => 'nullable|email',
            'adresse' => 'nullable|string',
            'class_id' => 'nullable|exists:classes,id',
            'date_inscription' => 'nullable|date',
            'statut' => 'nullable|string',
            'code_massar' => 'nullable|string',
            'photo' => 'nullable|image|max:2048',
            'parents' => 'nullable|array',
        ]);
        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('photos', 'public');
        }
        $parents = $data['parents'] ?? [];
        unset($data['parents']);
        $student->update($data);
        $student->parents()->sync($parents);
        return redirect()->route('students.index')->with('success', 'Élève mis à jour.');
    }

    public function destroy(Student $student)
    {
        $student->delete();
        return redirect()->route('students.index')->with('success', 'Élève supprimé.');
    }
}
