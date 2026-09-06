<?php
namespace App\Http\Controllers;

use App\Models\Fee;
use App\Models\SchoolClass;
use App\Models\SchoolYear;
use Illuminate\Http\Request;

class FeeController extends Controller
{
    public function index()
    {
        $year = SchoolYear::active();
        $classes = SchoolClass::with(['fee' => function($q) use ($year) {
            if ($year) $q->where('school_year_id', $year->id);
        }])->orderBy('nom')->get();
        return view('fees.index', compact('classes','year'));
    }

    public function store(Request $request)
    {
        $year = SchoolYear::active();
        $request->validate(['fees' => 'required|array']);
        foreach ($request->fees as $classId => $row) {
            Fee::updateOrCreate(
                ['class_id' => $classId, 'school_year_id' => $year?->id],
                [
                    'inscription' => $row['inscription'] ?? 0,
                    'mensualite' => $row['mensualite'] ?? 0,
                    'transport' => $row['transport'] ?? 0,
                    'cantine' => $row['cantine'] ?? 0,
                    'activites' => $row['activites'] ?? 0,
                    'formation' => $row['formation'] ?? 0,
                    'autres' => $row['autres'] ?? 0,
                ]
            );
        }
        return back()->with('success', 'Frais enregistrés.');
    }
}
