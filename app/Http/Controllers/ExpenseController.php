<?php
namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $periode = $request->get('periode');
        $q = Expense::query();
        if ($periode) {
            $from = $periode.'-01';
            $to = date('Y-m-t', strtotime($from));
            $q->whereBetween('date', [$from, $to]);
        }
        if ($s = $request->get('q')) {
            $q->where(function($w) use ($s) {
                $w->where('categorie','like',"%$s%")->orWhere('fournisseur','like',"%$s%")
                  ->orWhere('description','like',"%$s%");
            });
        }
        $expenses = $q->orderByDesc('date')->paginate(30)->withQueryString();
        return view('expenses.index', compact('expenses','periode'));
    }

    public function create() { return view('expenses.create'); }

    public function store(Request $request)
    {
        $data = $request->validate([
            'date'=>'required|date','categorie'=>'required|string','fournisseur'=>'nullable|string',
            'description'=>'nullable|string','montant'=>'required|numeric|min:0',
            'mode'=>'nullable|string','reference'=>'nullable|string',
        ]);
        Expense::create($data);
        return redirect()->route('expenses.index')->with('success',__('Dépense créée.'));
    }

    public function edit(Expense $expense) { return view('expenses.edit', compact('expense')); }

    public function update(Request $request, Expense $expense)
    {
        $data = $request->validate([
            'date'=>'required|date','categorie'=>'required|string','fournisseur'=>'nullable|string',
            'description'=>'nullable|string','montant'=>'required|numeric|min:0',
            'mode'=>'nullable|string','reference'=>'nullable|string',
        ]);
        $expense->update($data);
        return redirect()->route('expenses.index')->with('success',__('Dépense mise à jour.'));
    }

    public function destroy(Expense $expense)
    {
        $expense->delete();
        return redirect()->route('expenses.index')->with('success',__('Dépense supprimée.'));
    }
}
