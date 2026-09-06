<?php
namespace App\Http\Controllers;

use App\Models\ParentGuardian;
use App\Models\Student;
use Illuminate\Http\Request;

class ParentController extends Controller
{
    public function index(Request $request)
    {
        $q = ParentGuardian::with('students');
        if ($s = $request->get('q')) {
            $q->where(function($w) use ($s) {
                $w->where('nom','like',"%$s%")->orWhere('prenom','like',"%$s%")
                  ->orWhere('telephone','like',"%$s%")->orWhere('email','like',"%$s%");
            });
        }
        $parents = $q->orderBy('nom')->paginate(25)->withQueryString();
        return view('parents.index', compact('parents'));
    }

    public function create()
    {
        $students = Student::orderBy('nom')->get();
        return view('parents.create', compact('students'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nom' => 'required|string',
            'prenom' => 'nullable|string',
            'cin' => 'nullable|string',
            'telephone' => 'nullable|string',
            'email' => 'nullable|email',
            'adresse' => 'nullable|string',
            'profession' => 'nullable|string',
            'students' => 'nullable|array',
        ]);
        $students = $data['students'] ?? [];
        unset($data['students']);
        $parent = ParentGuardian::create($data);
        $parent->students()->sync($students);
        return redirect()->route('parents.index')->with('success', __('Parent créé.'));
    }

    public function edit(ParentGuardian $parent)
    {
        $students = Student::orderBy('nom')->get();
        $parent->load('students');
        return view('parents.edit', compact('parent','students'));
    }

    public function update(Request $request, ParentGuardian $parent)
    {
        $data = $request->validate([
            'nom' => 'required|string',
            'prenom' => 'nullable|string',
            'cin' => 'nullable|string',
            'telephone' => 'nullable|string',
            'email' => 'nullable|email',
            'adresse' => 'nullable|string',
            'profession' => 'nullable|string',
            'students' => 'nullable|array',
        ]);
        $students = $data['students'] ?? [];
        unset($data['students']);
        $parent->update($data);
        $parent->students()->sync($students);
        return redirect()->route('parents.index')->with('success', __('Parent mis à jour.'));
    }

    public function destroy(ParentGuardian $parent)
    {
        $parent->delete();
        return redirect()->route('parents.index')->with('success', __('Parent supprimé.'));
    }
}
