<?php
namespace App\Http\Controllers;

use App\Models\ParentGuardian;
use App\Models\Payment;
use App\Models\PaymentTransaction;
use App\Models\SchoolClass;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $q = Student::with(['schoolClass','parents']);
        if ($search = $request->get('q')) {
            $q->where(function($w) use ($search) {
                $w->where('nom','like',"%$search%")
                  ->orWhere('prenom','like',"%$search%")
                  ->orWhere('matricule','like',"%$search%")
                  ->orWhere('telephone','like',"%$search%");
            });
        }
        if ($request->filled('class_id')) $q->where('class_id', $request->class_id);
        if ($request->filled('statut')) $q->where('statut', $request->statut);
        $students = $q->orderBy('nom')->paginate(25)->withQueryString();
        $classes = SchoolClass::orderBy('nom')->get();
        return view('students.index', compact('students','classes'));
    }

    public function create()
    {
        $classes = SchoolClass::orderBy('nom')->get();
        $parents = ParentGuardian::orderBy('nom')->get();
        $subjects = Subject::orderBy('nom')->get();
        return view('students.create', compact('classes','parents','subjects'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'date_naissance' => 'nullable|date',
            'lieu_naissance' => 'nullable|string',
            'sexe' => 'nullable|in:M,F',
            'cin' => 'nullable|string',
            'telephone' => 'nullable|string',
            'email' => 'nullable|email',
            'adresse' => 'nullable|string',
            'niveau_scolaire' => 'nullable|string|in:'.implode(',', \App\Models\Student::NIVEAUX_SCOLAIRES),
            'class_id' => 'nullable|exists:classes,id',
            'date_inscription' => 'nullable|date',
            'statut' => 'nullable|string',
            'code_massar' => 'nullable|string',
            'photo' => 'nullable|image|max:2048',
            'parents' => 'nullable|array',
            'subjects' => 'nullable|array',
            'subjects.*' => 'exists:subjects,id',
            'subject_prix' => 'nullable|array',
            'subject_prix.*' => 'nullable|numeric|min:0',
            'payment_statut' => 'nullable|in:Non payé,Partiel,Soldé',
            'payment_paye' => 'nullable|numeric|min:0',
        ]);
        if (($data['payment_statut'] ?? 'Non payé') === 'Partiel') {
            $request->validate([
                'payment_paye' => 'required|numeric|min:0.01',
            ]);
            $data['payment_paye'] = $request->input('payment_paye');
        }
        $data['statut'] = $data['statut'] ?? 'Actif';
        $data['school_year_id'] = SchoolYear::active()?->id;
        if (!$request->filled('matricule')) {
            $data['matricule'] = 'EL'.str_pad((string)(Student::max('id')+1), 5, '0', STR_PAD_LEFT);
        }
        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('photos', 'public');
        }
        $parents = $data['parents'] ?? [];
        $subjectIds = $data['subjects'] ?? [];
        $subjectPrix = $data['subject_prix'] ?? [];
        $paymentStatut = $data['payment_statut'] ?? 'Non payé';
        $paymentPaye = $data['payment_paye'] ?? null;
        unset($data['parents'], $data['subjects'], $data['subject_prix'], $data['payment_statut'], $data['payment_paye']);
        $student = Student::create($data);
        $student->parents()->sync($parents);
        $this->syncStudentSubjects($student, $subjectIds, $subjectPrix);
        $this->createInitialMensualite($student, $paymentStatut, $paymentPaye);
        return redirect()->route('students.index')->with('success', __('Élève créé avec succès.'));
    }

    public function show(Student $student)
    {
        $student->load(['schoolClass','parents','payments','subjects']);
        $unpaidPayments = $student->payments
            ->filter(fn ($p) => $p->statut !== 'Annulé' && (float)$p->paye < (float)$p->montant)
            ->sortByDesc('periode')
            ->values();
        return view('students.show', compact('student', 'unpaidPayments'));
    }

    public function edit(Student $student)
    {
        $classes = SchoolClass::orderBy('nom')->get();
        $parents = ParentGuardian::orderBy('nom')->get();
        $subjects = Subject::orderBy('nom')->get();
        $student->load(['parents','subjects']);
        return view('students.edit', compact('student','classes','parents','subjects'));
    }

    public function update(Request $request, Student $student)
    {
        $data = $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'date_naissance' => 'nullable|date',
            'lieu_naissance' => 'nullable|string',
            'sexe' => 'nullable|in:M,F',
            'cin' => 'nullable|string',
            'telephone' => 'nullable|string',
            'email' => 'nullable|email',
            'adresse' => 'nullable|string',
            'niveau_scolaire' => 'nullable|string|in:'.implode(',', \App\Models\Student::NIVEAUX_SCOLAIRES),
            'class_id' => 'nullable|exists:classes,id',
            'date_inscription' => 'nullable|date',
            'statut' => 'nullable|string',
            'code_massar' => 'nullable|string',
            'photo' => 'nullable|image|max:2048',
            'parents' => 'nullable|array',
            'subjects' => 'nullable|array',
            'subjects.*' => 'exists:subjects,id',
            'subject_prix' => 'nullable|array',
            'subject_prix.*' => 'nullable|numeric|min:0',
        ]);
        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('photos', 'public');
        }
        $parents = $data['parents'] ?? [];
        $subjectIds = $data['subjects'] ?? [];
        $subjectPrix = $data['subject_prix'] ?? [];
        unset($data['parents'], $data['subjects'], $data['subject_prix']);
        $student->update($data);
        $student->parents()->sync($parents);
        $this->syncStudentSubjects($student, $subjectIds, $subjectPrix);
        return redirect()->route('students.index')->with('success', __('Élève mis à jour.'));
    }

    public function updateStatut(Request $request, Student $student)
    {
        $data = $request->validate([
            'statut' => 'required|in:Actif,Suspendu,Transféré,Abandonné,Diplômé,Exclu',
        ]);
        $student->update(['statut' => $data['statut']]);
        return back()->with('success', __('Statut élève mis à jour.'));
    }

    public function cancelPayments(Request $request, Student $student)
    {
        $data = $request->validate([
            'payment_ids' => 'required|array|min:1',
            'payment_ids.*' => 'integer|distinct',
        ]);

        $payments = Payment::where('student_id', $student->id)
            ->whereIn('id', $data['payment_ids'])
            ->get();

        if ($payments->count() !== count($data['payment_ids'])) {
            return back()->withErrors(['payment_ids' => __('Factures invalides pour cet élève.')]);
        }

        $cancelled = 0;
        foreach ($payments as $payment) {
            if (!$payment->isCancelable()) {
                continue;
            }
            $payment->statut = 'Annulé';
            $payment->save();
            $cancelled++;
        }

        if ($cancelled === 0) {
            return back()->withErrors(['payment_ids' => __('Aucune facture annulable sélectionnée (restant doit être > 0).')]);
        }

        return back()->with('success', __(':n facture(s) annulée(s).', ['n' => $cancelled]));
    }

    public function destroy(Student $student)
    {
        $student->delete();
        return redirect()->route('students.index')->with('success', __('Élève supprimé.'));
    }

    public function importForm()
    {
        return view('students.import');
    }

    public function importTemplate()
    {
        $headers = [
            'nom', 'prenom', 'date_naissance', 'sexe', 'telephone', 'email',
            'adresse', 'classe', 'statut', 'code_massar',
        ];
        return response()->streamDownload(function () use ($headers) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, $headers, ';');
            fputcsv($out, [
                'Alaoui', 'Sara', '2012-05-14', 'F', '0600000000', 'sara@example.com',
                'Casablanca', '6ème A', 'Actif', 'M123456',
            ], ';');
            fclose($out);
        }, 'modele-eleves.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function importStore(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:5120',
        ]);

        $path = $request->file('file')->getRealPath();
        $handle = fopen($path, 'r');
        if ($handle === false) {
            return back()->withErrors(['file' => 'Impossible de lire le fichier.']);
        }

        $first = fgets($handle);
        if ($first === false) {
            fclose($handle);
            return back()->withErrors(['file' => 'Fichier vide.']);
        }
        // Strip UTF-8 BOM
        $first = preg_replace('/^\xEF\xBB\xBF/', '', $first);
        $delimiter = substr_count($first, ';') >= substr_count($first, ',') ? ';' : ',';
        $headers = str_getcsv(trim($first), $delimiter);
        $headers = array_map(fn ($h) => strtolower(trim($h)), $headers);

        $created = 0;
        $updated = 0;
        $yearId = SchoolYear::active()?->id;
        $classes = SchoolClass::all()->keyBy(fn ($c) => mb_strtolower(trim($c->nom)));

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            if (count(array_filter($row, fn ($v) => $v !== null && trim((string)$v) !== '')) === 0) {
                continue;
            }
            $data = [];
            foreach ($headers as $i => $key) {
                $data[$key] = isset($row[$i]) ? trim((string) $row[$i]) : null;
            }

            $nom = $data['nom'] ?? null;
            $prenom = $data['prenom'] ?? null;
            if (!$nom || !$prenom) {
                continue;
            }

            $payload = [
                'nom' => $nom,
                'prenom' => $prenom,
                'date_naissance' => !empty($data['date_naissance']) ? $data['date_naissance'] : null,
                'sexe' => !empty($data['sexe']) ? strtoupper(substr($data['sexe'], 0, 1)) : null,
                'telephone' => $data['telephone'] ?? null,
                'email' => $data['email'] ?? null,
                'adresse' => $data['adresse'] ?? null,
                'statut' => $data['statut'] ?? 'Actif',
                'code_massar' => $data['code_massar'] ?? null,
            ];
            if (!empty($data['classe'])) {
                $cls = $classes->get(mb_strtolower(trim($data['classe'])));
                if ($cls) {
                    $payload['class_id'] = $cls->id;
                }
            }

            $student = null;
            if (!empty($data['matricule'])) {
                $student = Student::where('matricule', $data['matricule'])->first();
            }
            if (!$student) {
                $student = Student::where('nom', $nom)->where('prenom', $prenom)->first();
            }

            if ($student) {
                $student->update($payload);
                $updated++;
            } else {
                $payload['matricule'] = !empty($data['matricule'])
                    ? $data['matricule']
                    : 'EL'.str_pad((string) (Student::max('id') + 1), 5, '0', STR_PAD_LEFT);
                $payload['school_year_id'] = $yearId;
                $payload['date_inscription'] = now()->toDateString();
                Student::create($payload);
                $created++;
            }
        }
        fclose($handle);

        return redirect()->route('students.index')
            ->with('success', __('Import terminé : :created créé(s), :updated mis à jour.', ['created' => $created, 'updated' => $updated]));
    }

    /**
     * Sync enrolled subjects with explicit prix (from form, else subject default).
     */
    private function syncStudentSubjects(Student $student, array $subjectIds, array $subjectPrix): void
    {
        $subjectIds = array_values(array_unique(array_map('intval', $subjectIds)));
        $defaults = Subject::whereIn('id', $subjectIds)->pluck('prix', 'id');
        $sync = [];
        foreach ($subjectIds as $id) {
            if (array_key_exists($id, $subjectPrix) && $subjectPrix[$id] !== null && $subjectPrix[$id] !== '') {
                $prix = (float) $subjectPrix[$id];
            } elseif (array_key_exists((string) $id, $subjectPrix) && $subjectPrix[(string) $id] !== null && $subjectPrix[(string) $id] !== '') {
                $prix = (float) $subjectPrix[(string) $id];
            } else {
                $prix = (float) ($defaults[$id] ?? 0);
            }
            $sync[$id] = ['prix' => $prix];
        }
        $student->subjects()->sync($sync);
    }

    /**
     * Create first mensualité from enrolled subject prices and optional initial payment.
     */
    private function createInitialMensualite(Student $student, string $paymentStatut, $paymentPaye): void
    {
        $montant = $student->monthlyFeeAmount();
        if ($montant <= 0) {
            return;
        }
        $periode = now()->format('Y-m');
        $paye = 0.0;
        if ($paymentStatut === 'Soldé') {
            $paye = $montant;
        } elseif ($paymentStatut === 'Partiel') {
            $paye = min((float) $paymentPaye, $montant);
        }
        $statut = $paye <= 0 ? 'Non payé' : ($paye >= $montant ? 'Soldé' : 'Partiel');

        $payment = Payment::create([
            'student_id' => $student->id,
            'type' => 'Mensualité',
            'montant' => $montant,
            'paye' => $paye,
            'periode' => $periode,
            'statut' => $statut,
            'school_year_id' => $student->school_year_id,
        ]);

        if ($paye > 0) {
            PaymentTransaction::create([
                'payment_id' => $payment->id,
                'date' => now()->toDateString(),
                'montant' => $paye,
                'mode' => 'Espèces',
            ]);
        }
    }
}
