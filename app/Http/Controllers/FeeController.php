<?php
namespace App\Http\Controllers;

use App\Models\Subject;
use Illuminate\Http\Request;

class FeeController extends Controller
{
    public function index()
    {
        $subjects = Subject::orderBy('niveau')->orderBy('nom')->get();
        return view('fees.index', compact('subjects'));
    }

    public function store(Request $request)
    {
        $prixInput = $request->input('subjects');
        if (is_array($prixInput)) {
            // subjects[id][prix] shape
            $prixMap = [];
            foreach ($prixInput as $id => $row) {
                $prixMap[$id] = is_array($row) ? ($row['prix'] ?? null) : $row;
            }
        } else {
            $prixMap = $request->input('prix', []);
        }

        $request->merge(['prix' => $prixMap]);
        $validated = $request->validate([
            'prix' => 'required|array',
            'prix.*' => 'nullable|numeric|min:0',
        ]);

        foreach ($validated['prix'] as $id => $prix) {
            Subject::whereKey($id)->update(['prix' => $prix ?? 0]);
        }

        return back()->with('success', __('Prix des matières enregistrés.'));
    }
}
