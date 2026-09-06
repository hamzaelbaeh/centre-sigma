@extends('layouts.app')
@section('title',__('Modifier employé'))
@section('content')
<div class="page-head"><div><h1>{{ __('Modifier employé') }}</h1></div></div>
<form class="card" method="POST" action="{{ route('staff.update',$staff) }}">@csrf @method('PUT')

<div class="form-row"><div class="form-group"><label>{{ __('Nom') }} <span class="req">*</span></label><input class="form-input" style="width:100%" name="nom" value="{{ old('nom',$staff->nom ?? '') }}" required></div>
<div class="form-group"><label>{{ __('Prénom') }} <span class="req">*</span></label><input class="form-input" style="width:100%" name="prenom" value="{{ old('prenom',$staff->prenom ?? '') }}" required></div></div>
<div class="form-row"><div class="form-group"><label>{{ __('CIN') }}</label><input class="form-input" style="width:100%" name="cin" value="{{ old('cin',$staff->cin ?? '') }}"></div>
<div class="form-group"><label>{{ __('Téléphone') }}</label><input class="form-input" style="width:100%" name="telephone" value="{{ old('telephone',$staff->telephone ?? '') }}"></div></div>
<div class="form-row"><div class="form-group"><label>{{ __('Poste') }}</label><select class="form-select" style="width:100%" name="poste">@foreach(['Directeur','Secrétaire','Comptable','Surveillant','Agent administratif','Réceptionniste',"Agent d'entretien",'Chauffeur','Autre'] as $p)<option value="{{ $p }}" @selected(old('poste',$staff->poste ?? '')==$p)>{{ __($p) }}</option>@endforeach</select></div>
<div class="form-group"><label>{{ __("Date d'embauche") }}</label><input class="form-input" style="width:100%" type="date" name="date_embauche" value="{{ old('date_embauche', optional($staff->date_embauche ?? null)->format('Y-m-d')) }}"></div></div>
<div class="form-row"><div class="form-group"><label>{{ __('Salaire mensuel (DH)') }} <span class="req">*</span></label><input class="form-input" style="width:100%" type="number" step="0.01" name="salaire" value="{{ old('salaire',$staff->salaire ?? '') }}" required></div>
<div class="form-group"><label>{{ __('Mode de paiement') }}</label><select class="form-select" style="width:100%" name="mode_paiement">@foreach(['Espèces','Virement','Chèque','Carte'] as $m)<option value="{{ $m }}" @selected(old('mode_paiement',$staff->mode_paiement ?? '')==$m)>{{ __($m) }}</option>@endforeach</select></div></div>
<div class="form-group"><label>{{ __('Statut') }}</label><select class="form-select" style="width:100%" name="statut">@foreach(['Actif','Sorti'] as $s)<option value="{{ $s }}" @selected(old('statut',$staff->statut ?? 'Actif')==$s)>{{ __($s) }}</option>@endforeach</select></div>
<button class="btn btn-gold" type="submit">{{ __('Enregistrer') }}</button></form>
@endsection
