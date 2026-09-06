@extends('layouts.app')
@section('title','Modifier enseignant')
@section('content')
<div class="page-head"><div><h1>Modifier enseignant</h1></div></div>
<form class="card" method="POST" action="{{ route('teachers.update',$teacher) }}">@csrf @method('PUT')

<div class="form-row"><div class="form-group"><label>Nom <span class="req">*</span></label><input class="form-input" style="width:100%" name="nom" value="{{ old('nom',$teacher->nom ?? '') }}" required></div>
<div class="form-group"><label>Prénom <span class="req">*</span></label><input class="form-input" style="width:100%" name="prenom" value="{{ old('prenom',$teacher->prenom ?? '') }}" required></div></div>
<div class="form-row"><div class="form-group"><label>CIN</label><input class="form-input" style="width:100%" name="cin" value="{{ old('cin',$teacher->cin ?? '') }}"></div>
<div class="form-group"><label>Téléphone</label><input class="form-input" style="width:100%" name="telephone" value="{{ old('telephone',$teacher->telephone ?? '') }}"></div></div>
<div class="form-row"><div class="form-group"><label>Email</label><input class="form-input" style="width:100%" type="email" name="email" value="{{ old('email',$teacher->email ?? '') }}"></div>
<div class="form-group"><label>Date d'embauche</label><input class="form-input" style="width:100%" type="date" name="date_embauche" value="{{ old('date_embauche', optional($teacher->date_embauche ?? null)->format('Y-m-d')) }}"></div></div>
<div class="form-group"><label>Adresse</label><textarea class="form-input" style="width:100%" name="adresse" rows="2">{{ old('adresse',$teacher->adresse ?? '') }}</textarea></div>
<div class="form-row"><div class="form-group"><label>Spécialité</label><input class="form-input" style="width:100%" name="specialite" value="{{ old('specialite',$teacher->specialite ?? '') }}"></div>
<div class="form-group"><label>Statut</label><select class="form-select" style="width:100%" name="statut">@foreach(['Actif','Sorti'] as $s)<option value="{{ $s }}" @selected(old('statut',$teacher->statut ?? 'Actif')==$s)>{{ $s }}</option>@endforeach</select></div></div>
<div class="form-row"><div class="form-group"><label>Mode de paiement</label><select class="form-select" style="width:100%" name="mode_paiement">@foreach(['Mensuel','Pourcentage','Horaire'] as $m)<option value="{{ $m }}" @selected(old('mode_paiement',$teacher->mode_paiement ?? 'Mensuel')==$m)>{{ $m }}</option>@endforeach</select></div>
<div class="form-group"><label>Valeur DH <span class="req">*</span></label><input class="form-input" style="width:100%" type="number" step="0.01" name="valeur_dh" value="{{ old('valeur_dh',$teacher->valeur_dh ?? '') }}" required></div></div>
<button class="btn btn-gold" type="submit">Enregistrer</button></form>
@endsection
