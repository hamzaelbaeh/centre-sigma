@extends('layouts.app')
@section('title','Nouvelle dépense')
@section('content')
@php
    $student = $student ?? null;
    $parent = $parent ?? null;
    $class = $class ?? null;
    $teacher = $teacher ?? null;
    $subject = $subject ?? null;
    $staff = $staff ?? null;
    $expense = $expense ?? null;
    $training = $training ?? null;
    $user = $user ?? null;
@endphp
<div class="page-head"><div><h1>Nouvelle dépense</h1></div></div>
<form class="card" method="POST" action="{{ route('expenses.store') }}">@csrf

<div class="form-row"><div class="form-group"><label>Date <span class="req">*</span></label><input class="form-input" style="width:100%" type="date" name="date" value="{{ old('date', optional($expense->date ?? null)->format('Y-m-d') ?? date('Y-m-d')) }}" required></div>
<div class="form-group"><label>Catégorie <span class="req">*</span></label><select class="form-select" style="width:100%" name="categorie" required>@foreach(['Salaires','Enseignants','Personnel administratif','Loyer','Électricité','Eau','Internet','Fournitures scolaires','Matériel informatique','Maintenance','Transport','Publicité','Assurance','Impôts/taxes','Autres'] as $c)<option value="{{ $c }}" @selected(old('categorie',$expense->categorie ?? '')==$c)>{{ $c }}</option>@endforeach</select></div></div>
<div class="form-row"><div class="form-group"><label>Fournisseur</label><input class="form-input" style="width:100%" name="fournisseur" value="{{ old('fournisseur',$expense->fournisseur ?? '') }}"></div>
<div class="form-group"><label>Montant (DH) <span class="req">*</span></label><input class="form-input" style="width:100%" type="number" step="0.01" name="montant" value="{{ old('montant',$expense->montant ?? '') }}" required></div></div>
<div class="form-group"><label>Description</label><textarea class="form-input" style="width:100%" name="description" rows="2">{{ old('description',$expense->description ?? '') }}</textarea></div>
<div class="form-row"><div class="form-group"><label>Mode de paiement</label><select class="form-select" style="width:100%" name="mode">@foreach(['Espèces','Virement','Chèque','Carte'] as $m)<option value="{{ $m }}" @selected(old('mode',$expense->mode ?? '')==$m)>{{ $m }}</option>@endforeach</select></div>
<div class="form-group"><label>Référence</label><input class="form-input" style="width:100%" name="reference" value="{{ old('reference',$expense->reference ?? '') }}"></div></div>
<button class="btn btn-gold" type="submit">Enregistrer</button></form>
@endsection
