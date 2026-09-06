<?php
namespace App\Http\Controllers;

use App\Models\SchoolClass;
use App\Models\SchoolYear;
use App\Models\Teacher;
use Illuminate\Http\Request;

class ClassController extends Controller
{
    public function index()
    {
        $classes = SchoolClass::with(['teacher','students'])->withCount(['students as occupancy' => function($q){
            $q->where('statut','Actif');
        }])->orderBy('nom')->get();
        return view('classes.index', compact('classes'));
    }

    public function create()
    {
        $teachers = Teacher::where('statut','Actif')->orderBy('nom')->get();
        return view('classes.create', compact('teachers'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nom' => 'required|string',
            'niveau' => 'nullable|string',
            'salle' => 'nullable|string',
            'teacher_id' => 'nullable|exists:teachers,id',
            'capacite' => 'nullable|integer|min:1',
        ]);
        $data['school_year_id'] = SchoolYear::active()?->id;
        SchoolClass::create($data);
        return redirect()->route('classes.index')->with('success', 'Classe créée.');
    }

    public function show(SchoolClass $class)
    {
        $class->load(['teacher','students','subjects','timetableSlots.subject']);
        return view('classes.show', compact('class'));
    }

    public function edit(SchoolClass $class)
    {
        $teachers = Teacher::where('statut','Actif')->orderBy('nom')->get();
        return view('classes.edit', compact('class','teachers'));
    }

    public function update(Request $request, SchoolClass $class)
    {
        $data = $request->validate([
            'nom' => 'required|string',
            'niveau' => 'nullable|string',
            'salle' => 'nullable|string',
            'teacher_id' => 'nullable|exists:teachers,id',
            'capacite' => 'nullable|integer|min:1',
        ]);
        $class->update($data);
        return redirect()->route('classes.index')->with('success', 'Classe mise à jour.');
    }

    public function destroy(SchoolClass $class)
    {
        $class->delete();
        return redirect()->route('classes.index')->with('success', 'Classe supprimée.');
    }
}
