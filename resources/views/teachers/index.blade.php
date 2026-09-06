@extends('layouts.app')
@section('title','Enseignants')
@section('content')
<div class="page-head"><div><h1>Enseignants</h1></div>
<div class="actions"><button class="btn btn-ghost" onclick="window.print()">Imprimer</button><a class="btn btn-ghost" href="{{ route('teachers.export', request()->query()) }}">{{ __('Excel') }}</a><a class="btn btn-gold" href="{{ route('teachers.create') }}">Nouvel enseignant</a></div></div>
<form class="filters card" method="GET"><input name="q" value="{{ request('q') }}" placeholder="Recherche..."><button class="btn btn-gold">Filtrer</button></form>
<div class="card table-wrap"><table class="data">
<thead><tr><th>MATRICULE</th><th>ENSEIGNANTS</th><th>SPÉCIALITÉ</th><th>MATIÈRES</th><th>MODE DE PAIEMENT</th><th>CLASSES</th><th>STATUT</th><th>ACTIONS</th></tr></thead>
<tbody>
@forelse($teachers as $t)
<tr>
<td>{{ $t->matricule }}</td><td><strong>{{ $t->full_name }}</strong></td><td>{{ $t->specialite }}</td>
<td>{{ $t->subjects->pluck('nom')->join(', ') }}</td><td>{{ $t->mode_paiement }} ({{ number_format($t->valeur_dh,2,',',' ') }} DH)</td>
<td>{{ $t->classes->pluck('nom')->join(', ') }}</td>
<td><span class="badge {{ $t->statut==='Actif'?'badge-green':'badge-gray' }}">{{ $t->statut }}</span></td>
<td><a class="btn btn-sm btn-ghost" href="{{ route('teachers.edit',$t) }}">Modifier</a>
<form style="display:inline" method="POST" action="{{ route('teachers.destroy',$t) }}" onsubmit="return confirm('Supprimer ?')">@csrf @method('DELETE')<button class="btn btn-sm btn-danger">Supprimer</button></form></td>
</tr>
@empty<tr><td colspan="8" class="empty">Aucun enseignant</td></tr>@endforelse
</tbody></table></div>{{ $teachers->links() }}
@endsection
