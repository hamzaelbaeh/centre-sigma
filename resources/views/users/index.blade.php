@extends('layouts.app')
@section('title','Utilisateurs')
@section('content')
<div class="page-head"><div><h1>Utilisateurs</h1></div><a class="btn btn-gold" href="{{ route('users.create') }}">Nouvel utilisateur</a></div>
<div class="card table-wrap"><table class="data">
<thead><tr><th>NOM D'UTILISATEUR</th><th>NOM COMPLET</th><th>RÔLE</th><th>STATUT</th><th>ACTIONS</th></tr></thead>
<tbody>
@foreach($users as $u)
<tr>
<td>{{ $u->username }}</td><td>{{ $u->name }}</td><td>{{ $u->role }}</td>
<td><span class="badge {{ $u->is_active?'badge-green':'badge-red' }}">{{ $u->is_active?'Actif':'Inactif' }}</span></td>
<td><a class="btn btn-sm btn-ghost" href="{{ route('users.edit',$u) }}">Modifier</a>
<form style="display:inline" method="POST" action="{{ route('users.destroy',$u) }}" onsubmit="return confirm('Supprimer ?')">@csrf @method('DELETE')<button class="btn btn-sm btn-danger">Supprimer</button></form></td>
</tr>
@endforeach
</tbody></table></div>{{ $users->links() }}
@endsection
