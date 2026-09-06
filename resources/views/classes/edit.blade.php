@extends('layouts.app')
@section('title','Modifier classe')
@section('content')
<div class="page-head"><div><h1>Modifier classe</h1></div></div>
<form class="card" method="POST" action="{{ route('classes.update',$class) }}">@csrf @method('PUT')

<div class="form-group"><label>Classe <span class="req">*</span></label><input class="form-input" style="width:100%" name="nom" value="{{ old('nom',$class->nom ?? '') }}" required></div>
<div class="form-row">
<div class="form-group"><label>Niveau</label><select class="form-select" style="width:100%" name="niveau">@foreach(['','Maternelle','Primaire','Collège','Lycée','Formation'] as $n)<option value="{{ $n }}" @selected(old('niveau',$class->niveau ?? '')==$n)>{{ $n ?: '—' }}</option>@endforeach</select></div>
<div class="form-group"><label>Salle</label><input class="form-input" style="width:100%" name="salle" value="{{ old('salle',$class->salle ?? '') }}"></div>
</div>
<div class="form-row">
<div class="form-group"><label>Enseignant principal</label><select class="form-select" style="width:100%" name="teacher_id"><option value="">—</option>@foreach($teachers as $t)<option value="{{ $t->id }}" @selected(old('teacher_id',$class->teacher_id ?? '')==$t->id)>{{ $t->full_name }}</option>@endforeach</select></div>
<div class="form-group"><label>Capacité</label><input class="form-input" style="width:100%" type="number" name="capacite" value="{{ old('capacite',$class->capacite ?? 30) }}"></div>
</div>
<button class="btn btn-gold" type="submit">Enregistrer</button></form>
@endsection
