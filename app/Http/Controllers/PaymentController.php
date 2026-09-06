<?php
namespace App\Http\Controllers;

use App\Models\Fee;
use App\Models\Payment;
use App\Models\PaymentTransaction;
use App\Models\SchoolYear;
use App\Models\Student;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $periode = $request->get('periode', now()->format('Y-m'));
        $q = Payment::with(['student.schoolClass']);
        if ($periode) $q->where('periode', $periode);
        if ($s = $request->get('q')) {
            $q->whereHas('student', function($w) use ($s) {
                $w->where('nom','like',"%$s%")->orWhere('prenom','like',"%$s%");
            });
        }
        $payments = $q->orderByDesc('id')->paginate(30)->withQueryString();

        $today = PaymentTransaction::whereDate('date', today())->sum('montant');
        $month = PaymentTransaction::whereBetween('date', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()])->sum('montant');
        $total = Payment::sum('paye');
        $remaining = Payment::selectRaw('SUM(montant - paye) as r')->value('r') ?? 0;

        return view('payments.index', compact('payments','periode','today','month','total','remaining'));
    }

    public function encaisser(Request $request, Payment $payment)
    {
        $data = $request->validate([
            'date' => 'required|date',
            'montant' => 'required|numeric|min:0.01',
            'mode' => 'nullable|string',
            'reference' => 'nullable|string',
        ]);
        $montant = min((float)$data['montant'], $payment->restant);
        PaymentTransaction::create([
            'payment_id' => $payment->id,
            'date' => $data['date'],
            'montant' => $montant,
            'mode' => $data['mode'] ?? 'Espèces',
            'reference' => $data['reference'] ?? null,
        ]);
        $payment->paye = (float)$payment->paye + $montant;
        $payment->refreshStatut();
        return back()->with('success', 'Paiement encaissé.');
    }

    public function generateMensualites(Request $request)
    {
        $periode = $request->get('periode', now()->format('Y-m'));
        $year = SchoolYear::active();
        $students = Student::where('statut','Actif')->whereNotNull('class_id')->get();
        $created = 0;
        foreach ($students as $student) {
            $fee = Fee::where('class_id', $student->class_id)
                ->when($year, fn($q) => $q->where('school_year_id', $year->id))
                ->first();
            if (!$fee || $fee->mensualite <= 0) continue;
            $exists = Payment::where('student_id',$student->id)->where('type','Mensualité')->where('periode',$periode)->exists();
            if ($exists) continue;
            Payment::create([
                'student_id' => $student->id,
                'type' => 'Mensualité',
                'montant' => $fee->mensualite,
                'paye' => 0,
                'periode' => $periode,
                'statut' => 'Non payé',
                'school_year_id' => $year?->id,
            ]);
            $created++;
        }
        return back()->with('success', "$created mensualité(s) générée(s).");
    }

    public function impayes()
    {
        $payments = Payment::with(['student.schoolClass'])
            ->whereRaw('paye < montant')
            ->orderByRaw('(montant-paye) desc')
            ->get();
        return view('payments.impayes', compact('payments'));
    }
}
