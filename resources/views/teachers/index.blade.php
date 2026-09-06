@extends('layouts.app')
@section('title',__('Enseignants'))
@section('content')
<div class="page-head"><div><h1>{{ __('Enseignants') }}</h1></div>
<div class="actions"><button class="btn btn-ghost" onclick="window.print()">{{ __('Imprimer') }}</button><a class="btn btn-ghost" href="{{ route('teachers.export', request()->query()) }}">{{ __('Excel') }}</a><a class="btn btn-gold" href="{{ route('teachers.create') }}">{{ __('Nouvel enseignant') }}</a></div></div>
<form class="filters card" method="GET"><input name="q" value="{{ request('q') }}" placeholder="{{ __('Recherche...') }}"><button class="btn btn-gold">{{ __('Filtrer') }}</button></form>
<div class="card table-wrap"><table class="data">
<thead><tr><th>{{ __('MATRICULE') }}</th><th>{{ __('ENSEIGNANTS') }}</th><th>{{ __('SPÉCIALITÉ') }}</th><th>{{ __('MATIÈRES') }}</th><th>{{ __('MODE DE PAIEMENT') }}</th><th>{{ __('CLASSES') }}</th><th>{{ __('STATUT') }}</th><th>{{ __('ACTIONS') }}</th></tr></thead>
<tbody>
@forelse($teachers as $t)
<tr>
<td>{{ $t->matricule }}</td><td><strong>{{ $t->full_name }}</strong></td><td>{{ $t->specialite }}</td>
<td>{{ $t->subjects->pluck('nom')->join(', ') }}</td><td>{{ $t->mode_paiement }} ({{ number_format($t->valeur_dh,2,',',' ') }} DH)</td>
<td>{{ $t->classes->pluck('nom')->join(', ') }}</td>
<td><span class="badge {{ $t->statut==='Actif'?'badge-green':'badge-gray' }}">{{ $t->statut }}</span></td>
<td><a class="btn btn-sm btn-ghost" href="{{ route('teachers.edit',$t) }}">{{ __('Modifier') }}</a>
<form style="display:inline" method="POST" action="{{ route('teachers.destroy',$t) }}" onsubmit="return confirm(@json(__('Supprimer ?')))">@csrf @method('DELETE')<button class="btn btn-sm btn-danger">{{ __('Supprimer') }}</button></form></td>
</tr>
@empty<tr><td colspan="8" class="empty">{{ __('Aucun enseignant') }}</td></tr>@endforelse
</tbody></table></div>{{ $teachers->links() }}
@endsection
