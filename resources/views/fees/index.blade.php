@extends('layouts.app')
@section('title',__('Configuration des frais'))
@section('content')
<div class="page-head"><div><h1>{{ __('Configuration des frais —') }} {{ $year->nom ?? '2026/2027' }}</h1>
<div class="sub">{{ __('Définissez les frais par classe (inscription, transport, etc.). La mensualité est calculée par élève selon les matières choisies à l\'inscription, pas selon les matières de la classe. Mettre 0 pour supprimer un frais.') }}</div></div></div>
<form method="POST" action="{{ route('fees.store') }}">@csrf
<div class="grid grid-2">
@foreach($classes as $c)
@php $f=$c->fee; @endphp
<div class="card">
<h3>{{ $c->nom }}</h3>
<div class="form-group">
  <label>{{ __('Mensualité') }}</label>
  <div class="sub">{{ __('Les matières liées à la classe servent au programme uniquement. La facturation mensuelle vient des matières inscrites pour chaque élève (prix saisi à l\'inscription).') }}</div>
  @if($c->subjects->isNotEmpty())
  <div class="muted" style="margin-top:6px">{{ __('Matières du programme') }} :
    {{ $c->subjects->pluck('nom')->join(', ') }}
  </div>
  @endif
</div>
@foreach(['inscription'=>'Inscription','transport'=>'Transport','cantine'=>'Cantine','activites'=>'Activités','formation'=>'Formation','autres'=>'Autres frais'] as $k=>$label)
<div class="form-group"><label>{{ __($label) }} (DH)</label><input class="form-input" style="width:100%" type="number" step="0.01" name="fees[{{ $c->id }}][{{ $k }}]" value="{{ old("fees.{$c->id}.{$k}", $f->$k ?? 0) }}"></div>
@endforeach
</div>
@endforeach
</div>
<button class="btn btn-gold" style="margin-top:14px" type="submit">{{ __('Enregistrer') }}</button>
</form>
@endsection
