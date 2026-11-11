<?php
namespace App\Http\Controllers;

use App\Models\ParentGuardian;
use App\Models\Student;
use App\Services\Matricule;
use Illuminate\Http\Request;

class ParentController extends Controller
{
    public function index(Request $request)
    {
        $q = ParentGuardian::withCount('students');
        if ($s = $request->get('q')) {
            $q->where(function($w) use ($s) {
                $w->where('nom','like',"%$s%")->orWhere('prenom','like',"%$s%")
                  ->orWhere('telephone','like',"%$s%")->orWhere('email','like',"%$s%")
                  ->orWhere('numero', ltrim($s, '0') === '' ? -1 : (int) ltrim($s, '0'));
            });
        }
        $parents = $q->orderBy('nom')->paginate(25)->withQueryString();
        return view('parents.index', compact('parents'));
    }

    /** AJAX autocomplete for the registration form: search by phone or name. */
    public function search(Request $request)
    {
        $s = trim((string) $request->get('q'));
        if (mb_strlen($s) < 2) return response()->json([]);
        $digits = Matricule::phoneKey($s);
        $parents = ParentGuardian::withCount('students')->where(function ($w) use ($s, $digits) {
            $w->where('nom', 'like', "%$s%")->orWhere('prenom', 'like', "%$s%")->orWhere('telephone', 'like', "%$s%");
            if (strlen($digits) >= 3) $w->orWhere('telephone', 'like', "%$digits%");
        })->orderBy('nom')->limit(10)->get();
        return response()->json($parents->map(fn ($p) => [
            'id' => $p->id, 'numero' => $p->numero_pad, 'display_id' => $p->display_id,
            'nom' => $p->full_name, 'telephone' => $p->telephone, 'children' => $p->students_count,
        ]));
    }

    /** AJAX: the list of this parent's children (shown when clicking the children count). */
    public function children(ParentGuardian $parent)
    {
        $students = $parent->students()->with('schoolClass')->orderBy('students.id')->get();
        return response()->json([
            'parent' => ['display_id' => $parent->display_id, 'nom' => $parent->full_name],
            'students' => $students->map(fn ($s) => [
                'id' => $s->id, 'url' => route('students.show', $s), 'nom' => $s->full_name, 'matricule' => $s->matricule,
                'niveau' => $s->niveau_scolaire ? __($s->niveau_scolaire) : '—',
                'classe' => $s->schoolClass?->nom ?: '—', 'statut' => __($s->statut),
            ]),
        ]);
    }

    public function create()
    {
        $students = Student::orderBy('nom')->get();
        return view('parents.create', compact('students'));
    }

    private function rules(?ParentGuardian $parent = null): array
    {
        return [
            'nom' => 'required|string',
            'prenom' => 'nullable|string',
            'cin' => 'nullable|string',
            'telephone' => ['nullable', 'string', function ($attr, $value, $fail) use ($parent) {
                if (filled($value) && Matricule::findParentByPhone($value, $parent?->id)) {
                    $fail(__('Ce numéro de téléphone existe déjà pour un autre parent.'));
                }
            }],
            'email' => 'nullable|email',
            'adresse' => 'nullable|string',
            'profession' => 'nullable|string',
            'students' => 'nullable|array',
        ];
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());
        $students = $data['students'] ?? [];
        unset($data['students']);
        $parent = ParentGuardian::create($data);
        $parent->students()->sync($students);
        Matricule::refreshMany($students);
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
        $data = $request->validate($this->rules($parent));
        $students = $data['students'] ?? [];
        unset($data['students']);
        $before = $parent->students()->pluck('students.id')->all();
        $parent->update($data);
        $parent->students()->sync($students);
        Matricule::refreshMany(array_merge($before, $students));
        return redirect()->route('parents.index')->with('success', __('Parent mis à jour.'));
    }

    public function destroy(ParentGuardian $parent)
    {
        $ids = $parent->students()->pluck('students.id')->all();
        $parent->delete();
        Matricule::refreshMany($ids);
        return redirect()->route('parents.index')->with('success', __('Parent supprimé.'));
    }
}
