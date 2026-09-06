@extends('layouts.app')
@section('title',__('Nouvel élève'))
@section('content')
@php
    $student = $student ?? null;
@endphp
<div class="page-head"><div><h1>{{ __('Nouvel élève') }}</h1></div><a class="btn btn-ghost" href="{{ route('students.index') }}">{{ __('Retour') }}</a></div>
<form class="card" method="POST" action="{{ route('students.store') }}" enctype="multipart/form-data" id="student-create-form">@csrf

<div class="form-row">
 <div class="form-group"><label>{{ __('Nom') }} <span class="req">*</span></label><input class="form-input" style="width:100%" name="nom" value="{{ old('nom', $student->nom ?? '') }}" required></div>
 <div class="form-group"><label>{{ __('Prénom') }} <span class="req">*</span></label><input class="form-input" style="width:100%" name="prenom" value="{{ old('prenom', $student->prenom ?? '') }}" required></div>
</div>
<div class="form-row">
 <div class="form-group"><label>{{ __('Date de naissance') }}</label><input class="form-input" style="width:100%" type="date" name="date_naissance" value="{{ old('date_naissance', optional($student->date_naissance ?? null)->format('Y-m-d')) }}"></div>
 <div class="form-group"><label>{{ __('Lieu de naissance') }}</label><input class="form-input" style="width:100%" name="lieu_naissance" value="{{ old('lieu_naissance', $student->lieu_naissance ?? '') }}"></div>
</div>
<div class="form-row">
 <div class="form-group"><label>{{ __('Sexe') }}</label><select class="form-select" style="width:100%" name="sexe"><option value="">—</option><option value="M" @selected(old('sexe',$student->sexe ?? '')=='M')>M</option><option value="F" @selected(old('sexe',$student->sexe ?? '')=='F')>F</option></select></div>
 <div class="form-group"><label>{{ __('CIN') }}</label><input class="form-input" style="width:100%" name="cin" value="{{ old('cin', $student->cin ?? '') }}"></div>
</div>
<div class="form-row">
 <div class="form-group"><label>{{ __('Téléphone') }}</label><input class="form-input" style="width:100%" name="telephone" value="{{ old('telephone', $student->telephone ?? '') }}"></div>
 <div class="form-group"><label>{{ __('Email') }}</label><input class="form-input" style="width:100%" type="email" name="email" value="{{ old('email', $student->email ?? '') }}"></div>
</div>
<div class="form-group"><label>{{ __('Adresse') }}</label><textarea class="form-input" style="width:100%" name="adresse" rows="2">{{ old('adresse', $student->adresse ?? '') }}</textarea></div>
<div class="form-row">
 <div class="form-group"><label>{{ __('Classe') }}</label><select class="form-select" style="width:100%" name="class_id"><option value="">—</option>@foreach($classes as $c)<option value="{{ $c->id }}" @selected(old('class_id', $student->class_id ?? '')==$c->id)>{{ $c->nom }}</option>@endforeach</select></div>
 <div class="form-group"><label>{{ __("Date d'inscription") }}</label><input class="form-input" style="width:100%" type="date" name="date_inscription" value="{{ old('date_inscription', optional($student->date_inscription ?? null)->format('Y-m-d')) }}"></div>
</div>
<div class="form-row">
 <div class="form-group"><label>{{ __('Statut') }}</label><select class="form-select" style="width:100%" name="statut">@foreach(['Actif','Suspendu','Transféré','Abandonné','Diplômé','Exclu'] as $s)<option value="{{ $s }}" @selected(old('statut', $student->statut ?? 'Actif')==$s)>{{ __($s) }}</option>@endforeach</select></div>
 <div class="form-group"><label>{{ __('Code Massar') }}</label><input class="form-input" style="width:100%" name="code_massar" value="{{ old('code_massar', $student->code_massar ?? '') }}"></div>
</div>
<div class="form-group"><label>{{ __('Photo') }}</label><input class="form-input" style="width:100%" type="file" name="photo" accept="image/*"></div>
<div class="form-group"><label>{{ __('Parent / Tuteur') }}</label><div class="checkboxes">@foreach($parents as $p)<label><input type="checkbox" name="parents[]" value="{{ $p->id }}" @checked(collect(old('parents', isset($student)?$student->parents->pluck('id')->all():[]))->contains($p->id))> {{ $p->full_name }}</label>@endforeach</div></div>

@include('students.partials.subjects')

<div class="form-group" id="initial-payment-section" style="margin-top:16px;padding-top:12px;border-top:1px solid #eee">
  <label>{{ __('Paiement initial (mensualité)') }}</label>
  <div class="sub" style="margin-bottom:8px">{{ __('Le montant de la mensualité est la somme des prix facturés des matières cochées. Indiquez si l\'élève a déjà payé.') }}</div>
  <div class="form-row">
    <div class="form-group">
      <label>{{ __('Statut du paiement') }}</label>
      <select class="form-select" style="width:100%" name="payment_statut" id="payment_statut">
        <option value="Non payé" @selected(old('payment_statut','Non payé')==='Non payé')>{{ __('Non payé') }}</option>
        <option value="Partiel" @selected(old('payment_statut')==='Partiel')>{{ __('Partiel') }}</option>
        <option value="Soldé" @selected(old('payment_statut')==='Soldé')>{{ __('Soldé') }}</option>
      </select>
    </div>
    <div class="form-group" id="payment-paye-group">
      <label>{{ __('Montant payé (DH)') }}</label>
      <input class="form-input" style="width:100%" type="number" step="0.01" min="0" name="payment_paye" id="payment_paye" value="{{ old('payment_paye') }}">
      <div class="sub">{{ __('Total mensuel estimé') }} : <span id="payment-total-hint">0,00</span> DH</div>
    </div>
  </div>
</div>
<script>
(function(){
  const statut = document.getElementById('payment_statut');
  const paye = document.getElementById('payment_paye');
  const group = document.getElementById('payment-paye-group');
  function syncPaye(){
    if (!statut || !paye) return;
    if (statut.value === 'Non payé') {
      paye.value = '';
      paye.required = false;
      if (group) group.style.opacity = '0.5';
    } else if (statut.value === 'Soldé') {
      const hint = document.getElementById('payment-total-hint');
      // parse localized number roughly
      const raw = (hint && hint.textContent || '0').replace(/\s/g,'').replace(',','.');
      const total = parseFloat(raw) || 0;
      paye.value = total.toFixed(2);
      paye.required = false;
      if (group) group.style.opacity = '1';
    } else {
      paye.required = true;
      if (group) group.style.opacity = '1';
    }
  }
  if (statut) statut.addEventListener('change', syncPaye);
  syncPaye();
})();
</script>

<button class="btn btn-gold" type="submit">{{ __('Enregistrer') }}</button>
</form>
@endsection
