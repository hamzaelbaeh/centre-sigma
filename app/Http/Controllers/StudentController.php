<?php
namespace App\Http\Controllers;

use App\Models\ParentGuardian;
use App\Models\SchoolClass;
use App\Models\SchoolYear;
use App\Models\Student;
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
        return view('students.create', compact('classes','parents'));
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
            'class_id' => 'nullable|exists:classes,id',
            'date_inscription' => 'nullable|date',
            'statut' => 'nullable|string',
            'code_massar' => 'nullable|string',
            'photo' => 'nullable|image|max:2048',
            'parents' => 'nullable|array',
        ]);
        $data['statut'] = $data['statut'] ?? 'Actif';
        $data['school_year_id'] = SchoolYear::active()?->id;
        if (!$request->filled('matricule')) {
            $data['matricule'] = 'EL'.str_pad((string)(Student::max('id')+1), 5, '0', STR_PAD_LEFT);
        }
        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('photos', 'public');
        }
        $parents = $data['parents'] ?? [];
        unset($data['parents']);
        $student = Student::create($data);
        $student->parents()->sync($parents);
        return redirect()->route('students.index')->with('success', __('Élève créé avec succès.'));
    }

    public function show(Student $student)
    {
        $student->load(['schoolClass','parents','payments']);
        return view('students.show', compact('student'));
    }

    public function edit(Student $student)
    {
        $classes = SchoolClass::orderBy('nom')->get();
        $parents = ParentGuardian::orderBy('nom')->get();
        $student->load('parents');
        return view('students.edit', compact('student','classes','parents'));
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
            'class_id' => 'nullable|exists:classes,id',
            'date_inscription' => 'nullable|date',
            'statut' => 'nullable|string',
            'code_massar' => 'nullable|string',
            'photo' => 'nullable|image|max:2048',
            'parents' => 'nullable|array',
        ]);
        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('photos', 'public');
        }
        $parents = $data['parents'] ?? [];
        unset($data['parents']);
        $student->update($data);
        $student->parents()->sync($parents);
        return redirect()->route('students.index')->with('success', __('Élève mis à jour.'));
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
}
