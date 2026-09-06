@extends('layouts.app')
@section('title','Nouvelle matière')
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
<div class="page-head"><div><h1>Nouvelle matière</h1></div></div>
<form class="card" method="POST" action="{{ route('subjects.store') }}">@csrf

<div class="form-row"><div class="form-group"><label>Nom <span class="req">*</span></label><input class="form-input" style="width:100%" name="nom" value="{{ old('nom',$subject->nom ?? '') }}" required></div>
<div class="form-group"><label>Code</label><input class="form-input" style="width:100%" name="code" value="{{ old('code',$subject->code ?? '') }}"></div></div>
<div class="form-row"><div class="form-group"><label>Niveau</label><input class="form-input" style="width:100%" name="niveau" value="{{ old('niveau',$subject->niveau ?? '') }}"></div>
<div class="form-group"><label>Heures/semaine</label><input class="form-input" style="width:100%" type="number" step="0.5" name="heures_semaine" value="{{ old('heures_semaine',$subject->heures_semaine ?? '') }}"></div></div>
<div class="form-group"><label>Classes</label><div class="checkboxes">@foreach($classes as $c)<label><input type="checkbox" name="classes[]" value="{{ $c->id }}" @checked(collect(old('classes', isset($subject)?$subject->classes->pluck('id')->all():[]))->contains($c->id))> {{ $c->nom }}</label>@endforeach</div></div>
<div class="form-group"><label>Enseignants</label><div class="checkboxes">@foreach($teachers as $t)<label><input type="checkbox" name="teachers[]" value="{{ $t->id }}" @checked(collect(old('teachers', isset($subject)?$subject->teachers->pluck('id')->all():[]))->contains($t->id))> {{ $t->full_name }}</label>@endforeach</div></div>
<button class="btn btn-gold" type="submit">Enregistrer</button></form>
@endsection
