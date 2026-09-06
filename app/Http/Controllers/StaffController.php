<?php
namespace App\Http\Controllers;

use App\Models\Staff;
use Illuminate\Http\Request;

class StaffController extends Controller
{
    public function index()
    {
        $staff = Staff::orderBy('nom')->paginate(25);
        return view('staff.index', compact('staff'));
    }

    public function create() { return view('staff.create'); }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nom'=>'required|string','prenom'=>'required|string','cin'=>'nullable|string',
            'telephone'=>'nullable|string','poste'=>'nullable|string','date_embauche'=>'nullable|date',
            'salaire'=>'required|numeric|min:0','mode_paiement'=>'nullable|string','statut'=>'nullable|string',
        ]);
        $data['statut'] = $data['statut'] ?? 'Actif';
        $data['matricule'] = 'EMP'.str_pad((string)(Staff::max('id')+1), 4, '0', STR_PAD_LEFT);
        Staff::create($data);
        return redirect()->route('staff.index')->with('success',__('Employé créé.'));
    }

    public function edit(Staff $staff) { return view('staff.edit', compact('staff')); }

    public function update(Request $request, Staff $staff)
    {
        $data = $request->validate([
            'nom'=>'required|string','prenom'=>'required|string','cin'=>'nullable|string',
            'telephone'=>'nullable|string','poste'=>'nullable|string','date_embauche'=>'nullable|date',
            'salaire'=>'required|numeric|min:0','mode_paiement'=>'nullable|string','statut'=>'nullable|string',
        ]);
        $staff->update($data);
        return redirect()->route('staff.index')->with('success',__('Employé mis à jour.'));
    }

    public function destroy(Staff $staff)
    {
        $staff->delete();
        return redirect()->route('staff.index')->with('success',__('Employé supprimé.'));
    }
}
