@extends('layouts.app')
@section('title',__('Tarifs des matières'))
@section('content')
<div class="page-head"><div><h1>{{ __('Tarifs des matières') }}</h1>
<div class="sub">{{ __('Prix par défaut de chaque matière. Le prix réel facturé est défini à l\'inscription de l\'élève.') }}</div></div>
<a class="btn btn-gold" href="{{ route('subjects.create') }}">{{ __('Nouvelle matière') }}</a></div>
<form method="POST" action="{{ route('fees.store') }}">@csrf
<div class="card table-wrap"><table class="data">
<thead><tr>
  <th>{{ __('CODE') }}</th>
  <th>{{ __('MATIÈRES') }}</th>
  <th>{{ __('NIVEAU') }}</th>
  <th>{{ __('Prix (DH)') }}</th>
</tr></thead>
<tbody>
@forelse($subjects as $s)
<tr>
  <td>{{ $s->code }}</td>
  <td><strong>{{ $s->nom }}</strong></td>
  <td>{{ $s->niveau }}</td>
  <td>
    <input class="form-input" style="width:120px" type="number" step="0.01" min="0"
           name="subjects[{{ $s->id }}][prix]"
           value="{{ old("subjects.{$s->id}.prix", $s->prix ?? 0) }}">
  </td>
</tr>
@empty
<tr><td colspan="4" class="muted">{{ __('Aucune matière. Créez-en une pour définir un prix.') }}</td></tr>
@endforelse
</tbody></table></div>
@if($subjects->isNotEmpty())
<button class="btn btn-gold" style="margin-top:14px" type="submit">{{ __('Enregistrer') }}</button>
@endif
</form>
@endsection
