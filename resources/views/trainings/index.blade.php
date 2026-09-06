@extends('layouts.app')
@section('title','Formations')
@section('content')
<div class="page-head"><div><h1>Formations</h1></div><a class="btn btn-gold" href="{{ route('trainings.create') }}">Nouvelle formation</a></div>
<div class="card table-wrap"><table class="data">
<thead><tr><th>FORMATIONS</th><th>DATES</th><th>PRIX</th><th>PART.</th><th>ACTIONS</th></tr></thead>
<tbody>
@foreach($trainings as $t)
<tr>
<td><strong>{{ $t->nom }}</strong><div class="muted">{{ $t->formateur }}</div></td>
<td>{{ optional($t->date_debut)->format('d/m/Y') }} → {{ optional($t->date_fin)->format('d/m/Y') }}</td>
<td>{{ number_format($t->prix,2,',',' ') }} DH</td><td>{{ $t->participants_count }}</td>
<td><a class="btn btn-sm btn-ghost" href="{{ route('trainings.show',$t) }}">Détails</a>
<a class="btn btn-sm btn-ghost" href="{{ route('trainings.edit',$t) }}">Modifier</a></td>
</tr>
@endforeach
</tbody></table></div>
@endsection
