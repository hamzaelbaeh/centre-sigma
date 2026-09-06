<?php
namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\SchoolClass;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Http\Request;

class ClassController extends Controller
{
    public function index()
    {
        $classes = SchoolClass::with(['teacher','students','room'])->withCount(['students as occupancy' => function($q){
            $q->where('statut','Actif');
        }])->orderBy('nom')->get();
        return view('classes.index', compact('classes'));
    }

    public function create()
    {
        $teachers = Teacher::where('statut','Actif')->orderBy('nom')->get();
        $rooms = Room::active()->get();
        return view('classes.create', compact('teachers','rooms'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nom' => 'required|string',
            'niveau' => 'nullable|string',
            'room_id' => 'nullable|exists:rooms,id',
            'teacher_id' => 'nullable|exists:teachers,id',
            'capacite' => 'nullable|integer|min:1',
        ]);
        $data['school_year_id'] = SchoolYear::active()?->id;
        $data['salle'] = null;
        if (!empty($data['room_id'])) {
            $room = Room::find($data['room_id']);
            $data['salle'] = $room?->nom;
        }
        SchoolClass::create($data);
        return redirect()->route('classes.index')->with('success', __('Classe créée.'));
    }

    public function show(SchoolClass $class)
    {
        $class->load(['teacher','students','subjects','timetableSlots.subject','room']);
        $assignableStudents = Student::with('schoolClass')->orderBy('nom')->orderBy('prenom')->get();
        return view('classes.show', compact('class','assignableStudents'));
    }

    public function edit(SchoolClass $class)
    {
        $teachers = Teacher::where('statut','Actif')->orderBy('nom')->get();
        $rooms = Room::active()->get();
        $class->load('students');
        $assignableStudents = Student::with('schoolClass')->orderBy('nom')->orderBy('prenom')->get();
        return view('classes.edit', compact('class','teachers','rooms','assignableStudents'));
    }

    public function update(Request $request, SchoolClass $class)
    {
        $data = $request->validate([
            'nom' => 'required|string',
            'niveau' => 'nullable|string',
            'room_id' => 'nullable|exists:rooms,id',
            'teacher_id' => 'nullable|exists:teachers,id',
            'capacite' => 'nullable|integer|min:1',
        ]);
        if (!empty($data['room_id'])) {
            $room = Room::find($data['room_id']);
            $data['salle'] = $room?->nom;
        } else {
            $data['room_id'] = null;
            $data['salle'] = null;
        }
        $class->update($data);
        return redirect()->route('classes.index')->with('success', __('Classe mise à jour.'));
    }

    public function destroy(SchoolClass $class)
    {
        $class->delete();
        return redirect()->route('classes.index')->with('success', __('Classe supprimée.'));
    }

    /**
     * Sync students assigned to this class (manual assignment).
     * Checked students get class_id = this class; previously assigned but unchecked get class_id = null.
     */
    public function syncStudents(Request $request, SchoolClass $class)
    {
        $data = $request->validate([
            'students' => 'nullable|array',
            'students.*' => 'exists:students,id',
        ]);
        $ids = array_values(array_unique(array_map('intval', $data['students'] ?? [])));

        Student::where('class_id', $class->id)
            ->whereNotIn('id', $ids ?: [0])
            ->update(['class_id' => null]);

        if (!empty($ids)) {
            Student::whereIn('id', $ids)->update(['class_id' => $class->id]);
        }

        return back()->with('success', __('Affectation des élèves mise à jour.'));
    }
}
