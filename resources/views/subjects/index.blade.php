@extends('layouts.app')
@section('title','Matières')
@section('content')
<div class="page-head"><div><h1>Matières</h1><div class="sub">Une matière peut être liée à plusieurs classes et plusieurs enseignants.</div></div>
<a class="btn btn-gold" href="{{ route('subjects.create') }}">Nouvelle matière</a></div>
<form class="filters card" method="GET"><select name="niveau"><option value="">Tous les niveaux</option>@foreach($subjects->pluck('niveau')->filter()->unique() as $n)<option value="{{ $n }}" @selected(request('niveau')==$n)>{{ $n }}</option>@endforeach</select><button class="btn btn-gold">Filtrer</button></form>
<div class="card table-wrap"><table class="data">
<thead><tr><th>CODE</th><th>MATIÈRES</th><th>NIVEAU</th><th>HEURES/SEMAINE</th><th>CLASSES</th><th>ENSEIGNANTS</th><th>ACTIONS</th></tr></thead>
<tbody>
@foreach($subjects as $s)
<tr>
<td>{{ $s->code }}</td><td><strong>{{ $s->nom }}</strong></td><td>{{ $s->niveau }}</td><td>{{ $s->heures_semaine }}</td>
<td>{{ $s->classes->pluck('nom')->join(', ') }}</td><td>{{ $s->teachers->pluck('full_name')->join(', ') }}</td>
<td><a class="btn btn-sm btn-ghost" href="{{ route('subjects.edit',$s) }}">Modifier</a>
<form style="display:inline" method="POST" action="{{ route('subjects.destroy',$s) }}" onsubmit="return confirm('Supprimer ?')">@csrf @method('DELETE')<button class="btn btn-sm btn-danger">Supprimer</button></form></td>
</tr>
@endforeach
</tbody></table></div>
@endsection
