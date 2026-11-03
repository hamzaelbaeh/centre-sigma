<?php
namespace App\Http\Controllers;

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
        $q = Payment::with(['student.schoolClass'])->notCancelled();
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
        $remaining = Payment::notCancelled()->selectRaw('SUM(montant - paye) as r')->value('r') ?? 0;

        return view('payments.index', compact('payments','periode','today','month','total','remaining'));
    }

    public function encaisser(Request $request, Payment $payment)
    {
        if ($payment->statut === 'Annulé') {
            return back()->withErrors(['montant' => __('Impossible d\'encaisser une facture annulée.')]);
        }
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
        if ($payment->student) \App\Services\SessionBilling::checkAfterPayment($payment->student);
        return back()->with('success', __('Paiement encaissé.'));
    }

    public function generateMensualites(Request $request)
    {
        $periode = $request->get('periode', now()->format('Y-m'));
        $year = SchoolYear::active();
        $students = Student::with(['subjects'])
            ->whereIn('statut', ['Actif', \App\Services\SessionBilling::SUSPENDED])
            ->get();
        $created = 0;
        foreach ($students as $student) {
            $exists = Payment::where('student_id', $student->id)
                ->where('type', 'Mensualité')
                ->where('periode', $periode)
                ->where('statut', '!=', 'Annulé')
                ->exists();
            if ($exists) continue;
            // Later months = full month sessions of the class schedule.
            if (\App\Services\SessionBilling::createInvoice($student, $periode, null, 0, $year?->id)) $created++;
        }
        return back()->with('success', __(':n mensualité(s) générée(s).', ['n' => $created]));
    }

    public function stats()
    {
        $S = \App\Services\SessionBilling::class;
        $monthStart = now()->startOfMonth()->toDateString();
        $due = Student::whereHas('payments', fn ($q) => $q->activeDue())
            ->whereIn('statut', ['Actif', $S::SUSPENDED])->with('schoolClass')->get();
        $rows = $due->map(fn ($s) => ['student' => $s, 'attended' => $S::unpaidAttended($s)]);
        $groups = [
            'new' => $rows->filter(fn ($r) => $r['student']->date_inscription && $r['student']->date_inscription->toDateString() >= $monthStart)->values(),
            'one' => $rows->filter(fn ($r) => $r['attended'] === 1 && $r['student']->statut === 'Actif')->values(),
            'two' => $rows->filter(fn ($r) => $r['student']->statut === $S::SUSPENDED)->values(),
            'all' => $rows->values(),
        ];
        $withdrawn = Student::where('statut', $S::WITHDRAWN)->with('schoolClass')->orderByDesc('retire_at')->get()
            ->map(fn ($s) => ['student' => $s, 'attended' => \App\Models\Attendance::where('student_id', $s->id)->whereIn('status', ['present', 'retard'])->count()]);
        $alerts = \App\Models\StudentAlert::with('student.schoolClass')->whereNull('read_at')->latest()->get();
        return view('payments.stats', compact('groups', 'withdrawn', 'alerts'));
    }

    public function readAlert(\App\Models\StudentAlert $alert)
    {
        $alert->update(['read_at' => now()]);
        return back();
    }

    public function impayes()
    {
        $payments = Payment::with(['student.schoolClass'])
            ->activeDue()
            ->orderByRaw('(montant-paye) desc')
            ->get();
        return view('payments.impayes', compact('payments'));
    }
}
