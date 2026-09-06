@extends('layouts.app')
@section('title',__('Élèves sortants'))
@section('content')
<div class="page-head"><div><h1>{{ __('Élèves sortants') }}</h1><div class="sub">{{ __('Les élèves sortants ne sont jamais supprimés : leur historique complet est conservé.') }}</div></div>
<a class="btn btn-gold" href="{{ route('departures.create') }}">{{ __('Enregistrer un départ') }}</a></div>
<div class="card table-wrap"><table class="data">
<thead><tr><th>{{ __('ÉLÈVE') }}</th><th>{{ __('DATE DE SORTIE') }}</th><th>{{ __('CLASSE') }}</th><th>{{ __('RAISON') }}</th><th>{{ __('DESTINATION') }}</th><th>{{ __('SOLDE DÛ') }}</th><th>{{ __('DOCUMENTS REMIS') }}</th><th>{{ __('STATUT') }}</th><th>{{ __('ACTIONS') }}</th></tr></thead>
<tbody>
@forelse($departures as $d)
<tr>
<td>{{ $d->display_name }}</td><td>{{ $d->date_sortie->format('d/m/Y') }}</td><td>{{ $d->student?->schoolClass?->nom }}</td>
<td>{{ $d->raison }}</td><td>{{ $d->destination }}</td><td>{{ number_format($d->solde_du,2,',',' ') }}</td><td>{{ $d->documents_remis }}</td><td>{{ $d->statut }}</td>
<td><form method="POST" action="{{ route('departures.destroy',$d) }}" onsubmit="return confirm(@json(__('Retirer ?')))">@csrf @method('DELETE')<button class="btn btn-sm btn-danger">{{ __('Supprimer') }}</button></form></td>
</tr>
@empty<tr><td colspan="9" class="empty">{{ __('Aucune sortie enregistrée') }}</td></tr>@endforelse
</tbody></table></div>{{ $departures->links() }}
@endsection
