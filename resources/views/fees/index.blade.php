@extends('layouts.app')
@section('title',__('Configuration des frais'))
@section('content')
<div class="page-head"><div><h1>{{ __('Configuration des frais —') }} {{ $year->nom ?? '2026/2027' }}</h1>
<div class="sub">{{ __('Définissez les frais par classe. La mensualité est calculée automatiquement à partir des prix des matières liées à la classe. Mettre 0 pour supprimer un frais.') }}</div></div></div>
<form method="POST" action="{{ route('fees.store') }}">@csrf
<div class="grid grid-2">
@foreach($classes as $c)
@php
  $f=$c->fee;
  $mensualiteCalculee = (float) $c->subjects->sum('prix');
@endphp
<div class="card">
<h3>{{ $c->nom }}</h3>
<div class="form-group">
  <label>{{ __('Mensualité (matières)') }} (DH)</label>
  <input class="form-input" style="width:100%;background:#f5f5f5" type="text" value="{{ number_format($mensualiteCalculee, 2, ',', ' ') }}" readonly>
  <div class="sub">{{ __('Somme des prix des matières liées à cette classe. Modifiez les prix dans Matières.') }}</div>
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
