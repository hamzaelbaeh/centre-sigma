<?php
namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $classes = SchoolClass::orderBy('nom')->get();
        $subjects = Subject::orderBy('nom')->get();
        $classId = $request->get('class_id', $classes->first()?->id);
        $date = $request->get('date', now()->toDateString());
        $subjectId = $request->get('subject_id');

        $students = $classId
            ? Student::where('class_id', $classId)->where('statut','Actif')->orderBy('nom')->get()
            : collect();

        $existing = Attendance::whereDate('date', $date)
            ->when($classId, fn($q) => $q->where('class_id', $classId))
            ->when($subjectId, fn($q) => $q->where('subject_id', $subjectId))
            ->get()->keyBy('student_id');

        $monthStart = now()->startOfMonth()->toDateString();
        $monthStats = [
            'present' => Attendance::where('date','>=',$monthStart)->where('status','present')->count(),
            'absent' => Attendance::where('date','>=',$monthStart)->where('status','absent')->count(),
            'retard' => Attendance::where('date','>=',$monthStart)->where('status','retard')->count(),
        ];

        return view('attendance.index', compact('classes','subjects','students','existing','classId','date','subjectId','monthStats'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'date' => 'required|date',
            'records' => 'required|array',
        ]);
        foreach ($request->records as $studentId => $row) {
            Attendance::updateOrCreate(
                [
                    'student_id' => $studentId,
                    'date' => $request->date,
                    'subject_id' => $request->subject_id ?: null,
                ],
                [
                    'class_id' => $request->class_id,
                    'status' => $row['status'] ?? 'present',
                    'justifie' => !empty($row['justifie']),
                    'motif' => $row['motif'] ?? null,
                ]
            );
        }
        return back()->with('success', 'Présences enregistrées.');
    }

    public function absences(Request $request)
    {
        $absences = Attendance::with(['student.schoolClass','subject'])
            ->where('status','absent')
            ->when($request->class_id, fn($q) => $q->where('class_id', $request->class_id))
            ->orderByDesc('date')
            ->paginate(50);
        $classes = SchoolClass::orderBy('nom')->get();
        return view('attendance.absences', compact('absences','classes'));
    }
}
