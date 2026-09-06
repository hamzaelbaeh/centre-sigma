@extends('layouts.app')
@section('title',__('Matières'))
@section('content')
<div class="page-head"><div><h1>{{ __('Matières') }}</h1><div class="sub">{{ __('Une matière peut être liée à plusieurs classes et plusieurs enseignants.') }}</div></div>
<a class="btn btn-gold" href="{{ route('subjects.create') }}">{{ __('Nouvelle matière') }}</a></div>
<form class="filters card" method="GET"><select name="niveau"><option value="">{{ __('Tous les niveaux') }}</option>@foreach($subjects->pluck('niveau')->filter()->unique() as $n)<option value="{{ $n }}" @selected(request('niveau')==$n)>{{ __($n) }}</option>@endforeach</select><button class="btn btn-gold">{{ __('Filtrer') }}</button></form>
<div class="card table-wrap"><table class="data">
<thead><tr><th>{{ __('CODE') }}</th><th>{{ __('MATIÈRES') }}</th><th>{{ __('NIVEAU') }}</th><th>{{ __('HEURES/SEMAINE') }}</th><th>{{ __('PRIX') }}</th><th>{{ __('CLASSES') }}</th><th>{{ __('ENSEIGNANTS') }}</th><th>{{ __('ACTIONS') }}</th></tr></thead>
<tbody>
@foreach($subjects as $s)
<tr>
<td>{{ $s->code }}</td><td><strong>{{ $s->nom }}</strong></td><td>{{ $s->niveau }}</td><td>{{ $s->heures_semaine }}</td><td>{{ number_format((float)$s->prix, 2, ',', ' ') }} DH</td>
<td>{{ $s->classes->pluck('nom')->join(', ') }}</td><td>{{ $s->teachers->pluck('full_name')->join(', ') }}</td>
<td><a class="btn btn-sm btn-ghost" href="{{ route('subjects.edit',$s) }}">{{ __('Modifier') }}</a>
<form style="display:inline" method="POST" action="{{ route('subjects.destroy',$s) }}" onsubmit="return confirm(@json(__('Supprimer ?')))">@csrf @method('DELETE')<button class="btn btn-sm btn-danger">{{ __('Supprimer') }}</button></form></td>
</tr>
@endforeach
</tbody></table></div>
@endsection
