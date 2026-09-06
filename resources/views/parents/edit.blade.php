@extends('layouts.app')
@section('title',__('Modifier parent'))
@section('content')
<div class="page-head"><div><h1>{{ __('Modifier parent') }}</h1></div><a class="btn btn-ghost" href="{{ route('parents.index') }}">{{ __('Retour') }}</a></div>
<form class="card" method="POST" action="{{ route('parents.update',$parent) }}">@csrf @method('PUT')

<div class="form-row">
<div class="form-group"><label>{{ __('Nom') }} <span class="req">*</span></label><input class="form-input" style="width:100%" name="nom" value="{{ old('nom',$parent->nom ?? '') }}" required></div>
<div class="form-group"><label>{{ __('Prénom') }}</label><input class="form-input" style="width:100%" name="prenom" value="{{ old('prenom',$parent->prenom ?? '') }}"></div>
</div>
<div class="form-row">
<div class="form-group"><label>{{ __('CIN') }}</label><input class="form-input" style="width:100%" name="cin" value="{{ old('cin',$parent->cin ?? '') }}"></div>
<div class="form-group"><label>{{ __('Téléphone') }}</label><input class="form-input" style="width:100%" name="telephone" value="{{ old('telephone',$parent->telephone ?? '') }}"></div>
</div>
<div class="form-row">
<div class="form-group"><label>{{ __('Email') }}</label><input class="form-input" style="width:100%" type="email" name="email" value="{{ old('email',$parent->email ?? '') }}"></div>
<div class="form-group"><label>{{ __('Profession') }}</label><input class="form-input" style="width:100%" name="profession" value="{{ old('profession',$parent->profession ?? '') }}"></div>
</div>
<div class="form-group"><label>{{ __('Adresse') }}</label><textarea class="form-input" style="width:100%" name="adresse" rows="2">{{ old('adresse',$parent->adresse ?? '') }}</textarea></div>
<div class="form-group"><label>{{ __('Enfants (élèves)') }}</label><div class="checkboxes">@foreach($students as $s)<label><input type="checkbox" name="students[]" value="{{ $s->id }}" @checked(collect(old('students', isset($parent)?$parent->students->pluck('id')->all():[]))->contains($s->id))> {{ $s->full_name }}</label>@endforeach</div></div>

<button class="btn btn-gold" type="submit">{{ __('Enregistrer') }}</button></form>
@endsection
