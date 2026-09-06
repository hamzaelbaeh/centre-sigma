@extends('layouts.app')
@section('title','Modifier dépense')
@section('content')
<div class="page-head"><div><h1>Modifier dépense</h1></div></div>
<form class="card" method="POST" action="{{ route('expenses.update',$expense) }}">@csrf @method('PUT')

<div class="form-row"><div class="form-group"><label>Date <span class="req">*</span></label><input class="form-input" style="width:100%" type="date" name="date" value="{{ old('date', optional($expense->date ?? null)->format('Y-m-d') ?? date('Y-m-d')) }}" required></div>
<div class="form-group"><label>Catégorie <span class="req">*</span></label><select class="form-select" style="width:100%" name="categorie" required>@foreach(['Salaires','Enseignants','Personnel administratif','Loyer','Électricité','Eau','Internet','Fournitures scolaires','Matériel informatique','Maintenance','Transport','Publicité','Assurance','Impôts/taxes','Autres'] as $c)<option value="{{ $c }}" @selected(old('categorie',$expense->categorie ?? '')==$c)>{{ $c }}</option>@endforeach</select></div></div>
<div class="form-row"><div class="form-group"><label>Fournisseur</label><input class="form-input" style="width:100%" name="fournisseur" value="{{ old('fournisseur',$expense->fournisseur ?? '') }}"></div>
<div class="form-group"><label>Montant (DH) <span class="req">*</span></label><input class="form-input" style="width:100%" type="number" step="0.01" name="montant" value="{{ old('montant',$expense->montant ?? '') }}" required></div></div>
<div class="form-group"><label>Description</label><textarea class="form-input" style="width:100%" name="description" rows="2">{{ old('description',$expense->description ?? '') }}</textarea></div>
<div class="form-row"><div class="form-group"><label>Mode de paiement</label><select class="form-select" style="width:100%" name="mode">@foreach(['Espèces','Virement','Chèque','Carte'] as $m)<option value="{{ $m }}" @selected(old('mode',$expense->mode ?? '')==$m)>{{ $m }}</option>@endforeach</select></div>
<div class="form-group"><label>Référence</label><input class="form-input" style="width:100%" name="reference" value="{{ old('reference',$expense->reference ?? '') }}"></div></div>
<button class="btn btn-gold" type="submit">Enregistrer</button></form>
@endsection
