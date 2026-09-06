@extends('layouts.app')
@section('title','Parents / Tuteurs')
@section('content')
<div class="page-head"><div><h1>Parents / Tuteurs</h1></div><a class="btn btn-gold" href="{{ route('parents.create') }}">Nouveau parent</a></div>
<form class="filters card" method="GET"><input name="q" value="{{ request('q') }}" placeholder="Recherche..."><button class="btn btn-gold" type="submit">Filtrer</button></form>
<div class="card table-wrap"><table class="data">
<thead><tr><th>PARENT</th><th>TÉLÉPHONE</th><th>EMAIL</th><th>PROFESSION</th><th>ENFANTS</th><th>ACTIONS</th></tr></thead>
<tbody>
@forelse($parents as $p)
<tr>
<td><strong>{{ $p->full_name }}</strong></td><td>{{ $p->telephone }}</td><td>{{ $p->email }}</td><td>{{ $p->profession }}</td>
<td>{{ $p->students->pluck('full_name')->join(', ') ?: '—' }}</td>
<td><a class="btn btn-sm btn-ghost" href="{{ route('parents.edit',$p) }}">Modifier</a>
<form style="display:inline" method="POST" action="{{ route('parents.destroy',$p) }}" onsubmit="return confirm('Supprimer ?')">@csrf @method('DELETE')<button class="btn btn-sm btn-danger">Supprimer</button></form></td>
</tr>
@empty<tr><td colspan="6" class="empty">Aucun parent</td></tr>@endforelse
</tbody></table></div>{{ $parents->links() }}
@endsection
