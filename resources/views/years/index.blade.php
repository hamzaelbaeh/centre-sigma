@extends('layouts.app')
@section('title',__('Années scolaires'))
@section('content')
<div class="page-head"><div><h1>{{ __('Années scolaires') }}</h1>
<div class="sub">{{ __("Chaque année scolaire conserve ses classes, élèves, paiements, absences et emplois du temps. Le passage à une nouvelle année n'efface aucune donnée.") }}</div></div></div>
<div class="grid grid-2">
<div class="card table-wrap"><table class="data">
<thead><tr><th>{{ __('ANNÉE') }}</th><th>{{ __('PÉRIODE') }}</th><th>{{ __('CLASSES') }}</th><th>{{ __('ÉLÈVE') }}</th><th>{{ __('RECETTES') }}</th><th>{{ __('STATUT') }}</th><th>{{ __('ACTIONS') }}</th></tr></thead>
<tbody>
@foreach($years as $y)
<tr>
<td><strong>{{ $y->nom }}</strong></td>
<td>{{ optional($y->date_debut)->format('d/m/Y') }} → {{ optional($y->date_fin)->format('d/m/Y') }}</td>
<td>{{ $y->classes_count }}</td><td>{{ $y->students_count }}</td><td>{{ number_format($y->recettes,2,',',' ') }} DH</td>
<td>@if($y->is_active)<span class="badge badge-green">Active</span>@else<span class="badge badge-gray">Inactive</span>@endif</td>
<td>
@if(!$y->is_active)<form style="display:inline" method="POST" action="{{ route('years.activate',$y) }}">@csrf<button class="btn btn-sm btn-gold">{{ __('Activer') }}</button></form>@endif
<form style="display:inline" method="POST" action="{{ route('years.destroy',$y) }}" onsubmit="return confirm(@json(__('Supprimer ?')))">@csrf @method('DELETE')<button class="btn btn-sm btn-danger">{{ __('Supprimer') }}</button></form>
</td>
</tr>
@endforeach
</tbody></table></div>
<div class="card">
<h3>{{ __('Nouvelle année') }}</h3>
<form method="POST" action="{{ route('years.store') }}">@csrf
<div class="form-group"><label>{{ __('Nom') }} <span class="req">*</span></label><input class="form-input" style="width:100%" name="nom" required placeholder="2027/2028"></div>
<div class="form-group"><label>{{ __('Date de début') }}</label><input class="form-input" style="width:100%" type="date" name="date_debut"></div>
<div class="form-group"><label>{{ __('Date de fin') }}</label><input class="form-input" style="width:100%" type="date" name="date_fin"></div>
<button class="btn btn-gold" type="submit">{{ __('Créer') }}</button>
</form>
</div>
</div>
@endsection
