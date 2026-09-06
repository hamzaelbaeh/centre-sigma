@extends('layouts.app')
@section('title','Élèves sortants')
@section('content')
<div class="page-head"><div><h1>Élèves sortants</h1><div class="sub">Les élèves sortants ne sont jamais supprimés : leur historique complet est conservé.</div></div>
<a class="btn btn-gold" href="{{ route('departures.create') }}">Enregistrer un départ</a></div>
<div class="card table-wrap"><table class="data">
<thead><tr><th>ÉLÈVE</th><th>DATE DE SORTIE</th><th>CLASSE</th><th>RAISON</th><th>DESTINATION</th><th>SOLDE DÛ</th><th>DOCUMENTS REMIS</th><th>STATUT</th><th>ACTIONS</th></tr></thead>
<tbody>
@forelse($departures as $d)
<tr>
<td>{{ $d->display_name }}</td><td>{{ $d->date_sortie->format('d/m/Y') }}</td><td>{{ $d->student?->schoolClass?->nom }}</td>
<td>{{ $d->raison }}</td><td>{{ $d->destination }}</td><td>{{ number_format($d->solde_du,2,',',' ') }}</td><td>{{ $d->documents_remis }}</td><td>{{ $d->statut }}</td>
<td><form method="POST" action="{{ route('departures.destroy',$d) }}" onsubmit="return confirm('Retirer ?')">@csrf @method('DELETE')<button class="btn btn-sm btn-danger">Supprimer</button></form></td>
</tr>
@empty<tr><td colspan="9" class="empty">Aucune sortie enregistrée</td></tr>@endforelse
</tbody></table></div>{{ $departures->links() }}
@endsection
