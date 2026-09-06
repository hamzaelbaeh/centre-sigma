@extends('layouts.app')
@section('title','Configuration des frais')
@section('content')
<div class="page-head"><div><h1>Configuration des frais — {{ $year->nom ?? '2026/2027' }}</h1>
<div class="sub">Définissez les frais par classe. La mensualité est utilisée par la génération automatique des paiements. Mettre 0 pour supprimer un frais.</div></div></div>
<form method="POST" action="{{ route('fees.store') }}">@csrf
<div class="grid grid-2">
@foreach($classes as $c)
@php $f=$c->fee; @endphp
<div class="card">
<h3>{{ $c->nom }}</h3>
@foreach(['inscription'=>'Inscription','mensualite'=>'Mensualité','transport'=>'Transport','cantine'=>'Cantine','activites'=>'Activités','formation'=>'Formation','autres'=>'Autres frais'] as $k=>$label)
<div class="form-group"><label>{{ $label }} (DH)</label><input class="form-input" style="width:100%" type="number" step="0.01" name="fees[{{ $c->id }}][{{ $k }}]" value="{{ old("fees.{$c->id}.{$k}", $f->$k ?? 0) }}"></div>
@endforeach
</div>
@endforeach
</div>
<button class="btn btn-gold" style="margin-top:14px" type="submit">Enregistrer</button>
</form>
@endsection
