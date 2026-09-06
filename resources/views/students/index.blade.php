@extends('layouts.app')
@section('title','Élèves')
@section('content')
<div class="page-head">
 <div><h1>Élèves</h1><div class="sub">Gestion des élèves</div></div>
 <div class="actions">
  <button type="button" class="btn btn-ghost" onclick="window.print()">Imprimer</button>
  <a class="btn btn-ghost" href="{{ route('students.export', request()->query()) }}">{{ __('Excel') }}</a>
  <a class="btn btn-ghost" href="{{ route('students.import') }}">{{ __('Importer') }}</a>
  <a class="btn btn-gold" href="{{ route('students.create') }}">Nouvel élève</a>
 </div>
</div>
<form class="filters card" method="GET">
 <input type="text" name="q" value="{{ request('q') }}" placeholder="Recherche...">
 <select name="class_id"><option value="">Toutes</option>@foreach($classes as $c)<option value="{{ $c->id }}" @selected(request('class_id')==$c->id)>{{ $c->nom }}</option>@endforeach</select>
 <select name="statut"><option value="">Tous</option>@foreach(['Actif','Suspendu','Transféré','Abandonné','Diplômé','Exclu'] as $s)<option value="{{ $s }}" @selected(request('statut')==$s)>{{ $s }}</option>@endforeach</select>
 <button class="btn btn-gold" type="submit">Filtrer</button>
</form>
<div class="card table-wrap">
<table class="data">
<thead><tr><th>MATRICULE</th><th>ÉLÈVE</th><th>DATE DE NAISSANCE</th><th>CLASSE</th><th>TÉLÉPHONE</th><th>DATE D'INSCRIPTION</th><th>STATUT</th><th>ACTIONS</th></tr></thead>
<tbody>
@forelse($students as $st)
<tr>
 <td>{{ $st->matricule }}</td>
 <td><strong>{{ $st->full_name }}</strong></td>
 <td>{{ optional($st->date_naissance)->format('d/m/Y') }}</td>
 <td>{{ $st->schoolClass?->nom }}</td>
 <td>{{ $st->telephone }}</td>
 <td>{{ optional($st->date_inscription)->format('d/m/Y') }}</td>
 <td><span class="badge {{ $st->statut==='Actif'?'badge-green':'badge-gray' }}">{{ $st->statut }}</span></td>
 <td>
  <a class="btn btn-sm btn-ghost" href="{{ route('students.show',$st) }}">Fiche</a>
  <a class="btn btn-sm btn-ghost" href="{{ route('students.edit',$st) }}">Modifier</a>
  <form style="display:inline" method="POST" action="{{ route('students.destroy',$st) }}" onsubmit="return confirm('Supprimer ?')">@csrf @method('DELETE')<button class="btn btn-sm btn-danger" type="submit">Supprimer</button></form>
 </td>
</tr>
@empty
<tr><td colspan="8" class="empty">Aucun élève</td></tr>
@endforelse
</tbody></table>
</div>
{{ $students->links() }}
@endsection
