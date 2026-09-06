<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Expense;
use App\Models\Payment;
use App\Models\SchoolClass;
use App\Models\StaffPayroll;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\TeacherPayroll;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function students(Request $request): StreamedResponse
    {
        $q = Student::with('schoolClass');
        if ($search = $request->get('q')) {
            $q->where(function ($w) use ($search) {
                $w->where('nom', 'like', "%$search%")
                    ->orWhere('prenom', 'like', "%$search%")
                    ->orWhere('matricule', 'like', "%$search%")
                    ->orWhere('telephone', 'like', "%$search%");
            });
        }
        if ($request->filled('class_id')) {
            $q->where('class_id', $request->class_id);
        }
        if ($request->filled('statut')) {
            $q->where('statut', $request->statut);
        }

        $headers = [
            'matricule', 'nom', 'prenom', 'date_naissance', 'classe', 'telephone',
            'email', 'adresse', 'sexe', 'date_inscription', 'statut', 'code_massar',
        ];

        return $this->streamCsv('eleves.csv', $headers, function ($out) use ($q) {
            $q->orderBy('nom')->chunk(200, function ($rows) use ($out) {
                foreach ($rows as $st) {
                    fputcsv($out, [
                        $st->matricule,
                        $st->nom,
                        $st->prenom,
                        optional($st->date_naissance)->format('Y-m-d'),
                        $st->schoolClass?->nom,
                        $st->telephone,
                        $st->email,
                        $st->adresse,
                        $st->sexe,
                        optional($st->date_inscription)->format('Y-m-d'),
                        $st->statut,
                        $st->code_massar,
                    ], ';');
                }
            });
        });
    }

    public function teachers(Request $request): StreamedResponse
    {
        $q = Teacher::with(['subjects', 'classes']);
        if ($s = $request->get('q')) {
            $q->where(function ($w) use ($s) {
                $w->where('nom', 'like', "%$s%")->orWhere('prenom', 'like', "%$s%")
                    ->orWhere('matricule', 'like', "%$s%")->orWhere('specialite', 'like', "%$s%");
            });
        }

        $headers = [
            'matricule', 'nom', 'prenom', 'specialite', 'matieres', 'mode_paiement',
            'valeur_dh', 'classes', 'telephone', 'email', 'statut', 'date_embauche',
        ];

        return $this->streamCsv('enseignants.csv', $headers, function ($out) use ($q) {
            $q->orderBy('nom')->chunk(200, function ($rows) use ($out) {
                foreach ($rows as $t) {
                    fputcsv($out, [
                        $t->matricule,
                        $t->nom,
                        $t->prenom,
                        $t->specialite,
                        $t->subjects->pluck('nom')->join(', '),
                        $t->mode_paiement,
                        $t->valeur_dh,
                        $t->classes->pluck('nom')->join(', '),
                        $t->telephone,
                        $t->email,
                        $t->statut,
                        optional($t->date_embauche)->format('Y-m-d'),
                    ], ';');
                }
            });
        });
    }

    public function expenses(Request $request): StreamedResponse
    {
        $q = Expense::query();
        if ($periode = $request->get('periode')) {
            $from = $periode.'-01';
            $to = date('Y-m-t', strtotime($from));
            $q->whereBetween('date', [$from, $to]);
        }
        if ($s = $request->get('q')) {
            $q->where(function ($w) use ($s) {
                $w->where('categorie', 'like', "%$s%")->orWhere('fournisseur', 'like', "%$s%")
                    ->orWhere('description', 'like', "%$s%");
            });
        }

        $headers = ['date', 'categorie', 'fournisseur', 'description', 'montant', 'mode', 'reference'];

        return $this->streamCsv('depenses.csv', $headers, function ($out) use ($q) {
            $q->orderByDesc('date')->chunk(200, function ($rows) use ($out) {
                foreach ($rows as $e) {
                    fputcsv($out, [
                        optional($e->date)->format('Y-m-d'),
                        $e->categorie,
                        $e->fournisseur,
                        $e->description,
                        $e->montant,
                        $e->mode,
                        $e->reference,
                    ], ';');
                }
            });
        });
    }

    public function payments(Request $request): StreamedResponse
    {
        $periode = $request->get('periode', now()->format('Y-m'));
        $q = Payment::with(['student.schoolClass']);
        if ($periode) {
            $q->where('periode', $periode);
        }
        if ($s = $request->get('q')) {
            $q->whereHas('student', function ($w) use ($s) {
                $w->where('nom', 'like', "%$s%")->orWhere('prenom', 'like', "%$s%");
            });
        }

        $headers = [
            'eleve', 'classe', 'type', 'periode', 'montant', 'paye', 'restant', 'statut',
        ];

        return $this->streamCsv('paiements.csv', $headers, function ($out) use ($q) {
            $q->orderByDesc('id')->chunk(200, function ($rows) use ($out) {
                foreach ($rows as $p) {
                    fputcsv($out, [
                        $p->student?->full_name,
                        $p->student?->schoolClass?->nom,
                        $p->type,
                        $p->periode,
                        $p->montant,
                        $p->paye,
                        $p->restant,
                        $p->statut,
                    ], ';');
                }
            });
        });
    }

    public function reports(Request $request): StreamedResponse
    {
        $tab = $request->get('tab', 'scolaire');
        $periode = $request->get('periode', now()->format('Y-m'));
        $from = $periode.'-01';
        $to = date('Y-m-t', strtotime($from));

        $tab = match ($tab) {
            'scolaires' => 'scolaire',
            'financiers' => 'financier',
            default => $tab,
        };

        if ($tab === 'financier') {
            $headers = ['indicateur', 'valeur'];
            $recettes = Payment::where('periode', $periode)->sum('paye');
            $depenses = Expense::whereBetween('date', [$from, $to])->sum('montant');
            $salaires = TeacherPayroll::where('periode', $periode)->sum('net')
                + StaffPayroll::where('periode', $periode)->sum('net');
            $rows = [
                ['Recettes', $recettes],
                ['Depenses', $depenses],
                ['Salaires net', $salaires],
                ['Benefice', $recettes - $depenses - $salaires],
            ];
            $impayes = Payment::with('student.schoolClass')->whereRaw('paye < montant')->limit(200)->get();

            return $this->streamCsv('rapport-financier.csv', $headers, function ($out) use ($rows, $impayes) {
                foreach ($rows as $r) {
                    fputcsv($out, $r, ';');
                }
                fputcsv($out, [], ';');
                fputcsv($out, ['eleve', 'classe', 'montant', 'paye', 'restant', 'statut'], ';');
                foreach ($impayes as $p) {
                    fputcsv($out, [
                        $p->student?->full_name,
                        $p->student?->schoolClass?->nom,
                        $p->montant,
                        $p->paye,
                        $p->restant,
                        $p->statut,
                    ], ';');
                }
            });
        }

        if ($tab === 'enseignants') {
            $headers = ['matricule', 'enseignant', 'mode', 'valeur', 'heures_semaine', 'total_paye'];
            $teachers = Teacher::with('subjects')->where('statut', 'Actif')->get();

            return $this->streamCsv('rapport-enseignants.csv', $headers, function ($out) use ($teachers) {
                foreach ($teachers as $t) {
                    fputcsv($out, [
                        $t->matricule,
                        $t->full_name,
                        $t->mode_paiement,
                        $t->valeur_dh,
                        $t->subjects->sum('heures_semaine'),
                        $t->payrolls()->where('statut', 'Payé')->sum('net'),
                    ], ';');
                }
            });
        }

        // scolaire (default)
        $headers = ['classe', 'niveau', 'effectif', 'absences_mois'];
        $effectif = SchoolClass::withCount(['students as n' => fn ($q) => $q->where('statut', 'Actif')])->get();
        $absences = Attendance::where('status', 'absent')
            ->whereBetween('date', [$from, $to])
            ->selectRaw('class_id, count(*) as c')->groupBy('class_id')->pluck('c', 'class_id');

        return $this->streamCsv('rapport-scolaire.csv', $headers, function ($out) use ($effectif, $absences) {
            foreach ($effectif as $c) {
                fputcsv($out, [
                    $c->nom,
                    $c->niveau,
                    $c->n,
                    $absences[$c->id] ?? 0,
                ], ';');
            }
        });
    }

    /**
     * @param  callable(resource): void  $writer
     */
    private function streamCsv(string $filename, array $headers, callable $writer): StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $writer) {
            $out = fopen('php://output', 'w');
            // UTF-8 BOM so Excel opens accents correctly
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, $headers, ';');
            $writer($out);
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
