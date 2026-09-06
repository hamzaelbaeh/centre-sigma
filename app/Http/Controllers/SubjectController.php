<?php
namespace App\Http\Controllers;

use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    public function index(Request $request)
    {
        $q = Subject::with(['classes','teachers']);
        if ($request->filled('niveau')) $q->where('niveau', $request->niveau);
        $subjects = $q->orderBy('nom')->get();
        return view('subjects.index', compact('subjects'));
    }

    public function create()
    {
        $classes = SchoolClass::orderBy('nom')->get();
        $teachers = Teacher::where('statut','Actif')->orderBy('nom')->get();
        return view('subjects.create', compact('classes','teachers'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nom' => 'required|string',
            'code' => 'nullable|string',
            'niveau' => 'nullable|string',
            'heures_semaine' => 'nullable|numeric',
            'classes' => 'nullable|array',
            'teachers' => 'nullable|array',
        ]);
        $classes = $data['classes'] ?? [];
        $teachers = $data['teachers'] ?? [];
        unset($data['classes'], $data['teachers']);
        $subject = Subject::create($data);
        $subject->classes()->sync($classes);
        $subject->teachers()->sync($teachers);
        return redirect()->route('subjects.index')->with('success', 'Matière créée.');
    }

    public function edit(Subject $subject)
    {
        $classes = SchoolClass::orderBy('nom')->get();
        $teachers = Teacher::where('statut','Actif')->orderBy('nom')->get();
        $subject->load(['classes','teachers']);
        return view('subjects.edit', compact('subject','classes','teachers'));
    }

    public function update(Request $request, Subject $subject)
    {
        $data = $request->validate([
            'nom' => 'required|string',
            'code' => 'nullable|string',
            'niveau' => 'nullable|string',
            'heures_semaine' => 'nullable|numeric',
            'classes' => 'nullable|array',
            'teachers' => 'nullable|array',
        ]);
        $classes = $data['classes'] ?? [];
        $teachers = $data['teachers'] ?? [];
        unset($data['classes'], $data['teachers']);
        $subject->update($data);
        $subject->classes()->sync($classes);
        $subject->teachers()->sync($teachers);
        return redirect()->route('subjects.index')->with('success', 'Matière mise à jour.');
    }

    public function destroy(Subject $subject)
    {
        $subject->delete();
        return redirect()->route('subjects.index')->with('success', 'Matière supprimée.');
    }
}
