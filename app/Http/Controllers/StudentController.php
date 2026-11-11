<?php
namespace App\Http\Controllers;

use App\Models\ParentGuardian;
use App\Models\Payment;
use App\Models\PaymentTransaction;
use App\Models\SchoolClass;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\Subject;
use App\Services\Matricule;
use App\Services\SessionBilling;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        $subjects = Subject::orderBy('nom')->get();
        return view('students.create', compact('classes','subjects'));
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
            'parent_id' => 'nullable|exists:parents,id',
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
        $np = (array) $request->input('new_parent', []);
        $wantsNewParent = !$request->filled('parent_id')
            && (filled($np['nom'] ?? null) || filled($np['telephone'] ?? null) || filled($np['prenom'] ?? null));
        if ($wantsNewParent) {
            $request->validate([
                'new_parent.nom' => 'required|string|max:255',
                'new_parent.prenom' => 'nullable|string|max:255',
                'new_parent.telephone' => 'required|string|max:30',
            ]);
        }
        $data['statut'] = $data['statut'] ?? 'Actif';
        $data['school_year_id'] = SchoolYear::active()?->id;
        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('photos', 'public');
        }
        $subjectIds = $data['subjects'] ?? [];
        $subjectPrix = $data['subject_prix'] ?? [];
        $paymentStatut = $data['payment_statut'] ?? 'Non payé';
        $paymentPaye = $data['payment_paye'] ?? null;
        $parentIds = $data['parents'] ?? [];
        unset($data['parents'], $data['parent_id'], $data['subjects'], $data['subject_prix'], $data['payment_statut'], $data['payment_paye']);
        $data['date_inscription'] = $data['date_inscription'] ?? now()->toDateString();

        $student = DB::transaction(function () use ($data, $request, $np, $wantsNewParent, $parentIds, $subjectIds, $subjectPrix, $paymentStatut, $paymentPaye) {
            // parent: existing (picked) or matched by phone, else created inline with the next number
            $parent = null;
            if ($request->filled('parent_id')) {
                $parent = ParentGuardian::find($request->input('parent_id'));
            } elseif ($wantsNewParent) {
                $parent = Matricule::findParentByPhone($np['telephone'])
                    ?? ParentGuardian::create([
                        'nom' => trim($np['nom']), 'prenom' => filled($np['prenom'] ?? null) ? trim($np['prenom']) : null,
                        'telephone' => trim($np['telephone']),
                    ]);
            }
            if ($parent) $parentIds = [$parent->id];

            $data['matricule'] = Matricule::next($data['niveau_scolaire'] ?? null);
            $student = Student::create($data);
            $student->parents()->sync($parentIds);
            Matricule::refresh($student);
            $this->syncStudentSubjects($student, $subjectIds, $subjectPrix);
            $this->createInitialMensualite($student, $paymentStatut, $paymentPaye);
            return $student;
        });

        return redirect()->route('students.show', $student)
            ->with('success', __('Élève créé avec succès.'))
            ->with('receipt_new', true);
    }

    public function show(Student $student)
    {
        $student->load(['schoolClass','parents','payments','subjects']);
        $unpaidPayments = $student->payments
            ->filter(fn ($p) => $p->statut !== 'Annulé' && (float)$p->paye < (float)$p->montant)
            ->sortByDesc('periode')
            ->values();
        $periode = today()->format('Y-m');
        $sessionRows = $student->subjects->map(function ($sub) use ($student, $periode) {
            return ['subject' => $sub] + SessionBilling::countUpTo($student, $sub, $periode, today());
        });
        $adjustments = \App\Models\SessionAdjustment::with(['subject', 'user'])->where('student_id', $student->id)->orderByDesc('id')->limit(30)->get();
        return view('students.show', compact('student', 'unpaidPayments', 'sessionRows', 'adjustments', 'periode'));
    }

    /** Manual correction of the sessions held this month for one subject (up to today). */
    public function adjustSessions(Request $request, Student $student)
    {
        $data = $request->validate([
            'subject_id' => 'required|integer',
            'new_count' => 'required|integer|min:0|max:62',
            'note' => 'nullable|string|max:500',
        ]);
        $subject = $student->subjects()->where('subjects.id', $data['subject_id'])->first();
        abort_unless($subject, 404);
        $cur = SessionBilling::countUpTo($student, $subject, today()->format('Y-m'), today());
        if ((int) $data['new_count'] === $cur['count']) {
            return back()->with('success', __('Aucun changement : le nombre de séances est déjà :n.', ['n' => $cur['count']]));
        }
        SessionBilling::adjustSessions($student, $subject, (int) $data['new_count'], $data['note'] ?? null, \Illuminate\Support\Facades\Auth::id());
        return back()->with('success', __('Séances corrigées : :old → :new (:subject). La facture du mois est mise à jour.', ['old' => $cur['count'], 'new' => $data['new_count'], 'subject' => $subject->nom]));
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
        Matricule::refresh($student->fresh());
        $this->syncStudentSubjects($student, $subjectIds, $subjectPrix);
        return redirect()->route('students.index')->with('success', __('Élève mis à jour.'));
    }

    public function updateStatut(Request $request, Student $student)
    {
        $data = $request->validate([
            'statut' => 'required|in:Actif,Suspendu temporairement,Retiré,Suspendu,Transféré,Abandonné,Diplômé,Exclu',
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
            'niveau_scolaire', 'parent_nom', 'parent_prenom', 'parent_telephone',
        ];
        return response()->streamDownload(function () use ($headers) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, $headers, ';');
            fputcsv($out, [
                'Alaoui', 'Sara', '2012-05-14', 'F', '0600000000', 'sara@example.com',
                'Casablanca', '6ème A', 'Actif', 'M123456',
                '3ème année collège', 'Alaoui', 'Karim', '0611223344',
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

            $niveau = $data['niveau_scolaire'] ?? ($data['niveau'] ?? null);
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
            if ($niveau && in_array($niveau, Student::NIVEAUX_SCOLAIRES, true)) {
                $payload['niveau_scolaire'] = $niveau;
            }
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
                $this->importLinkParent($student, $data, false);
                $updated++;
            } else {
                $payload['matricule'] = !empty($data['matricule'])
                    ? $data['matricule']
                    : Matricule::next($payload['niveau_scolaire'] ?? null);
                $payload['school_year_id'] = $yearId;
                $payload['date_inscription'] = now()->toDateString();
                $student = Student::create($payload);
                $this->importLinkParent($student, $data, true);
                $created++;
            }
        }
        fclose($handle);

        return redirect()->route('students.index')
            ->with('success', __('Import terminé : :created créé(s), :updated mis à jour.', ['created' => $created, 'updated' => $updated]));
    }

    /**
     * Excel/CSV import: link the parent (match by phone when given, else create); no parent info = no link/suffix.
     * Columns: parent_telephone, parent_nom, parent_prenom.
     */
    private function importLinkParent(Student $student, array $row, bool $isNew): void
    {
        $phone = trim((string) ($row['parent_telephone'] ?? ($row['telephone_parent'] ?? '')));
        $pnom = trim((string) ($row['parent_nom'] ?? ''));
        $pprenom = trim((string) ($row['parent_prenom'] ?? ''));
        if ($phone === '' && $pnom === '') return;
        if (!$isNew && $student->parents()->exists()) return;
        $parent = $phone !== '' ? Matricule::findParentByPhone($phone) : null;
        if (!$parent) {
            $parent = ParentGuardian::create([
                'nom' => $pnom !== '' ? $pnom : $student->nom,
                'prenom' => $pprenom !== '' ? $pprenom : null,
                'telephone' => $phone !== '' ? $phone : null,
            ]);
        }
        $student->parents()->syncWithoutDetaching([$parent->id]);
        Matricule::refresh($student->fresh());
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
     * First invoice at registration: parent pays the FULL month; consumed = sessions held from the registration date
     * to month end x session price (estimate until the month is closed); the difference is credit for the next month.
     */
    private function createInitialMensualite(Student $student, string $paymentStatut, $paymentPaye): void
    {
        $student->load('subjects');
        $from = $student->date_inscription ? \Carbon\Carbon::parse($student->date_inscription) : now();
        $periode = $from->format('Y-m');
        $full = SessionBilling::breakdown($student->class_id, SessionBilling::studentSubjects($student), $periode, $from)['full'];
        $cash = $paymentStatut === 'Soldé' ? $full : ($paymentStatut === 'Partiel' ? (float) $paymentPaye : 0);
        SessionBilling::createInvoice($student, $periode, $from, $cash);
    }

    /** AJAX: estimate shown at registration (sessions held from the date to month end per subject). */
    public function billingPreview(Request $request)
    {
        $classId = $request->integer('class_id') ?: null;
        $subjects = [];
        foreach ((array) $request->input('subjects', []) as $id => $prix) {
            $subjects[(int) $id] = (float) $prix;
        }
        $from = $request->filled('date') ? \Carbon\Carbon::parse($request->input('date')) : now();
        return response()->json(SessionBilling::breakdown($classId, $subjects, $from->format('Y-m'), $from));
    }

    public function withdraw(Student $student)
    {
        $student->update(['statut' => \App\Services\SessionBilling::WITHDRAWN, 'retire_at' => now()]);
        \App\Models\StudentAlert::where('student_id', $student->id)->whereNull('read_at')->update(['read_at' => now()]);
        return back()->with('success', __('التلميذ تسجّل كمنسحب (ما مهتمش).'));
    }
}
