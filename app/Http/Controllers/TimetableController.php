<?php
namespace App\Http\Controllers;

use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TimetableSlot;
use Illuminate\Http\Request;

class TimetableController extends Controller
{
    public function index(Request $request)
    {
        $view = $request->get('vue', 'classe');
        $classId = $request->get('class_id');
        $teacherId = $request->get('teacher_id');
        $classes = SchoolClass::orderBy('nom')->get();
        $teachers = Teacher::where('statut','Actif')->orderBy('nom')->get();
        $subjects = Subject::orderBy('nom')->get();
        $jours = ['Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'];

        $slotsQ = TimetableSlot::with(['schoolClass','subject','teacher']);
        if ($view === 'enseignant' && $teacherId) $slotsQ->where('teacher_id', $teacherId);
        elseif ($classId) $slotsQ->where('class_id', $classId);
        elseif ($classes->first()) {
            $classId = $classes->first()->id;
            $slotsQ->where('class_id', $classId);
        }
        $slots = $slotsQ->orderBy('debut')->get();

        return view('timetable.index', compact('slots','classes','teachers','subjects','jours','view','classId','teacherId'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'nullable|exists:subjects,id',
            'teacher_id' => 'nullable|exists:teachers,id',
            'jour' => 'nullable|string',
            'debut' => 'required',
            'fin' => 'required|after:debut',
            'salle' => 'nullable|string',
        ]);
        TimetableSlot::create($data);
        return back()->with('success', __('Créneau ajouté.'));
    }

    public function destroy(TimetableSlot $timetable)
    {
        $timetable->delete();
        return back()->with('success', __('Créneau supprimé.'));
    }
}
