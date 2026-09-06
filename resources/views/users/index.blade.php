@extends('layouts.app')
@section('title',__('Utilisateurs'))
@section('content')
<div class="page-head"><div><h1>{{ __('Utilisateurs') }}</h1></div><a class="btn btn-gold" href="{{ route('users.create') }}">{{ __('Nouvel utilisateur') }}</a></div>
<div class="card table-wrap"><table class="data">
<thead><tr><th>{{ __("NOM D'UTILISATEUR") }}</th><th>{{ __('NOM COMPLET') }}</th><th>{{ __('RÔLE') }}</th><th>{{ __('STATUT') }}</th><th>{{ __('ACTIONS') }}</th></tr></thead>
<tbody>
@foreach($users as $u)
<tr>
<td>{{ $u->username }}</td><td>{{ $u->name }}</td><td>{{ $u->role }}</td>
<td><span class="badge {{ $u->is_active?'badge-green':'badge-red' }}">{{ $u->is_active?'Actif':'Inactif' }}</span></td>
<td><a class="btn btn-sm btn-ghost" href="{{ route('users.edit',$u) }}">{{ __('Modifier') }}</a>
<form style="display:inline" method="POST" action="{{ route('users.destroy',$u) }}" onsubmit="return confirm(@json(__('Supprimer ?')))">@csrf @method('DELETE')<button class="btn btn-sm btn-danger">{{ __('Supprimer') }}</button></form></td>
</tr>
@endforeach
</tbody></table></div>{{ $users->links() }}
@endsection
