@extends('layouts.app')
@section('title',__('Parents / Tuteurs'))
@section('content')
<div class="page-head"><div><h1>{{ __('Parents / Tuteurs') }}</h1></div><a class="btn btn-gold" href="{{ route('parents.create') }}">{{ __('Nouveau parent') }}</a></div>
<form class="filters card" method="GET"><input name="q" value="{{ request('q') }}" placeholder="{{ __('Recherche...') }}"><button class="btn btn-gold" type="submit">{{ __('Filtrer') }}</button></form>
<div class="card table-wrap"><table class="data">
<thead><tr><th>{{ __('PARENT') }}</th><th>{{ __('TÉLÉPHONE') }}</th><th>{{ __('EMAIL') }}</th><th>{{ __('PROFESSION') }}</th><th>{{ __('ENFANTS') }}</th><th>{{ __('ACTIONS') }}</th></tr></thead>
<tbody>
@forelse($parents as $p)
<tr>
<td><strong>{{ $p->full_name }}</strong></td><td>{{ $p->telephone }}</td><td>{{ $p->email }}</td><td>{{ $p->profession }}</td>
<td>{{ $p->students->pluck('full_name')->join(', ') ?: '—' }}</td>
<td><a class="btn btn-sm btn-ghost" href="{{ route('parents.edit',$p) }}">{{ __('Modifier') }}</a>
<form style="display:inline" method="POST" action="{{ route('parents.destroy',$p) }}" onsubmit="return confirm(@json(__('Supprimer ?')))">@csrf @method('DELETE')<button class="btn btn-sm btn-danger">{{ __('Supprimer') }}</button></form></td>
</tr>
@empty<tr><td colspan="6" class="empty">{{ __('Aucun parent') }}</td></tr>@endforelse
</tbody></table></div>{{ $parents->links() }}
@endsection
