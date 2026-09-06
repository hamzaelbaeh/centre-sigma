<?php
namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\SchoolClass;
use App\Models\SchoolYear;
use App\Models\Student;
use Illuminate\Http\Request;

class YearController extends Controller
{
    public function index()
    {
        $years = SchoolYear::orderByDesc('date_debut')->get()->map(function($y){
            $y->classes_count = SchoolClass::where('school_year_id',$y->id)->count();
            $y->students_count = Student::where('school_year_id',$y->id)->count();
            $y->recettes = Payment::where('school_year_id',$y->id)->sum('paye');
            return $y;
        });
        return view('years.index', compact('years'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nom'=>'required|string','date_debut'=>'nullable|date','date_fin'=>'nullable|date',
        ]);
        SchoolYear::create($data);
        return back()->with('success',__('Année scolaire créée.'));
    }

    public function update(Request $request, SchoolYear $year)
    {
        $data = $request->validate([
            'nom'=>'required|string','date_debut'=>'nullable|date','date_fin'=>'nullable|date',
        ]);
        $year->update($data);
        return back()->with('success',__('Année mise à jour.'));
    }

    public function activate(SchoolYear $year)
    {
        SchoolYear::query()->update(['is_active'=>false]);
        $year->update(['is_active'=>true]);
        return back()->with('success',__('Année activée. Les données des autres années sont conservées.'));
    }

    public function destroy(SchoolYear $year)
    {
        if ($year->is_active) return back()->withErrors(['error'=>'Impossible de supprimer l\'année active.']);
        $year->delete();
        return back()->with('success',__('Année supprimée.'));
    }
}
