@extends('layouts.app')
@section('title',__('Dépenses'))
@section('content')
<div class="page-head"><div><h1>{{ __('Dépenses') }}</h1></div>
<div class="actions"><button class="btn btn-ghost" onclick="window.print()">{{ __('Imprimer') }}</button><a class="btn btn-ghost" href="{{ route('expenses.export', request()->query()) }}">{{ __('Excel') }}</a><a class="btn btn-gold" href="{{ route('expenses.create') }}">{{ __('Nouvelle dépense') }}</a></div></div>
<form class="filters card" method="GET"><input type="month" name="periode" value="{{ $periode }}"><input name="q" value="{{ request('q') }}" placeholder="{{ __('Rechercher') }}"><button class="btn btn-gold">{{ __('Filtrer') }}</button></form>
<div class="card table-wrap"><table class="data">
<thead><tr><th>{{ __('DATE') }}</th><th>{{ __('CATÉGORIE') }}</th><th>{{ __('FOURNISSEUR') }}</th><th>{{ __('DESCRIPTION') }}</th><th>{{ __('MONTANT') }}</th><th>{{ __('MODE') }}</th><th>{{ __('ACTIONS') }}</th></tr></thead>
<tbody>
@foreach($expenses as $e)
<tr>
<td>{{ $e->date->format('d/m/Y') }}</td><td>{{ $e->categorie }}</td><td>{{ $e->fournisseur }}</td><td>{{ $e->description }}</td>
<td>{{ number_format($e->montant,2,',',' ') }} DH</td><td>{{ $e->mode }}</td>
<td><a class="btn btn-sm btn-ghost" href="{{ route('expenses.edit',$e) }}">{{ __('Modifier') }}</a>
<form style="display:inline" method="POST" action="{{ route('expenses.destroy',$e) }}" onsubmit="return confirm(@json(__('Supprimer ?')))">@csrf @method('DELETE')<button class="btn btn-sm btn-danger">{{ __('Supprimer') }}</button></form></td>
</tr>
@endforeach
</tbody></table></div>{{ $expenses->links() }}
@endsection
