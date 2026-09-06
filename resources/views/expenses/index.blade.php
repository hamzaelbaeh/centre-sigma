@extends('layouts.app')
@section('title','Dépenses')
@section('content')
<div class="page-head"><div><h1>Dépenses</h1></div>
<div class="actions"><button class="btn btn-ghost" onclick="window.print()">Imprimer</button><a class="btn btn-ghost" href="#">Excel</a><a class="btn btn-gold" href="{{ route('expenses.create') }}">Nouvelle dépense</a></div></div>
<form class="filters card" method="GET"><input type="month" name="periode" value="{{ $periode }}"><input name="q" value="{{ request('q') }}" placeholder="Rechercher"><button class="btn btn-gold">Filtrer</button></form>
<div class="card table-wrap"><table class="data">
<thead><tr><th>DATE</th><th>CATÉGORIE</th><th>FOURNISSEUR</th><th>DESCRIPTION</th><th>MONTANT</th><th>MODE</th><th>ACTIONS</th></tr></thead>
<tbody>
@foreach($expenses as $e)
<tr>
<td>{{ $e->date->format('d/m/Y') }}</td><td>{{ $e->categorie }}</td><td>{{ $e->fournisseur }}</td><td>{{ $e->description }}</td>
<td>{{ number_format($e->montant,2,',',' ') }} DH</td><td>{{ $e->mode }}</td>
<td><a class="btn btn-sm btn-ghost" href="{{ route('expenses.edit',$e) }}">Modifier</a>
<form style="display:inline" method="POST" action="{{ route('expenses.destroy',$e) }}" onsubmit="return confirm('Supprimer ?')">@csrf @method('DELETE')<button class="btn btn-sm btn-danger">Supprimer</button></form></td>
</tr>
@endforeach
</tbody></table></div>{{ $expenses->links() }}
@endsection
