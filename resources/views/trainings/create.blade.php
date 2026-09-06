@extends('layouts.app')
@section('title',__('Nouvelle formation'))
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
<div class="page-head"><div><h1>{{ __('Nouvelle formation') }}</h1></div></div>
<form class="card" method="POST" action="{{ route('trainings.store') }}">@csrf

<div class="form-group"><label>{{ __('Nom') }} <span class="req">*</span></label><input class="form-input" style="width:100%" name="nom" value="{{ old('nom',$training->nom ?? '') }}" required></div>
<div class="form-row"><div class="form-group"><label>{{ __('Formateur') }}</label><input class="form-input" style="width:100%" name="formateur" value="{{ old('formateur',$training->formateur ?? '') }}"></div>
<div class="form-group"><label>{{ __('Durée') }}</label><input class="form-input" style="width:100%" name="duree" value="{{ old('duree',$training->duree ?? '') }}"></div></div>
<div class="form-group"><label>{{ __('Description') }}</label><textarea class="form-input" style="width:100%" name="description" rows="2">{{ old('description',$training->description ?? '') }}</textarea></div>
<div class="form-row"><div class="form-group"><label>{{ __('Date début') }}</label><input class="form-input" style="width:100%" type="date" name="date_debut" value="{{ old('date_debut', optional($training->date_debut ?? null)->format('Y-m-d')) }}"></div>
<div class="form-group"><label>{{ __('Date fin') }}</label><input class="form-input" style="width:100%" type="date" name="date_fin" value="{{ old('date_fin', optional($training->date_fin ?? null)->format('Y-m-d')) }}"></div></div>
<div class="form-row"><div class="form-group"><label>{{ __('Prix (DH)') }}</label><input class="form-input" style="width:100%" type="number" step="0.01" name="prix" value="{{ old('prix',$training->prix ?? '') }}"></div>
<div class="form-group"><label>{{ __('Salle') }}</label><input class="form-input" style="width:100%" name="salle" value="{{ old('salle',$training->salle ?? '') }}"></div></div>
<div class="form-row"><div class="form-group"><label>{{ __('Classe/Niveau') }}</label><input class="form-input" style="width:100%" name="classe_niveau" value="{{ old('classe_niveau',$training->classe_niveau ?? '') }}"></div>
<div class="form-group"><label>{{ __('Nombre de séances') }}</label><input class="form-input" style="width:100%" type="number" name="nombre_seances" value="{{ old('nombre_seances',$training->nombre_seances ?? '') }}"></div></div>
<div class="form-group"><label>{{ __('Matières') }}</label><div class="checkboxes">@foreach($subjects as $s)<label><input type="checkbox" name="subjects[]" value="{{ $s->id }}" @checked(collect(old('subjects', isset($training)?$training->subjects->pluck('id')->all():[]))->contains($s->id))> {{ $s->nom }}</label>@endforeach</div></div>
<button class="btn btn-gold" type="submit">{{ __('Enregistrer') }}</button></form>
@endsection
