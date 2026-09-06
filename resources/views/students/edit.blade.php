@extends('layouts.app')
@section('title','Modifier élève')
@section('content')
<div class="page-head"><div><h1>Modifier — {{ $student->full_name }}</h1></div><a class="btn btn-ghost" href="{{ route('students.index') }}">Retour</a></div>
<form class="card" method="POST" action="{{ route('students.update', $student) }}" enctype="multipart/form-data">@csrf @method('PUT')

<div class="form-row">
 <div class="form-group"><label>Nom <span class="req">*</span></label><input class="form-input" style="width:100%" name="nom" value="{{ old('nom', $student->nom ?? '') }}" required></div>
 <div class="form-group"><label>Prénom <span class="req">*</span></label><input class="form-input" style="width:100%" name="prenom" value="{{ old('prenom', $student->prenom ?? '') }}" required></div>
</div>
<div class="form-row">
 <div class="form-group"><label>Date de naissance</label><input class="form-input" style="width:100%" type="date" name="date_naissance" value="{{ old('date_naissance', optional($student->date_naissance ?? null)->format('Y-m-d')) }}"></div>
 <div class="form-group"><label>Lieu de naissance</label><input class="form-input" style="width:100%" name="lieu_naissance" value="{{ old('lieu_naissance', $student->lieu_naissance ?? '') }}"></div>
</div>
<div class="form-row">
 <div class="form-group"><label>Sexe</label><select class="form-select" style="width:100%" name="sexe"><option value="">—</option><option value="M" @selected(old('sexe',$student->sexe ?? '')=='M')>M</option><option value="F" @selected(old('sexe',$student->sexe ?? '')=='F')>F</option></select></div>
 <div class="form-group"><label>CIN</label><input class="form-input" style="width:100%" name="cin" value="{{ old('cin', $student->cin ?? '') }}"></div>
</div>
<div class="form-row">
 <div class="form-group"><label>Téléphone</label><input class="form-input" style="width:100%" name="telephone" value="{{ old('telephone', $student->telephone ?? '') }}"></div>
 <div class="form-group"><label>Email</label><input class="form-input" style="width:100%" type="email" name="email" value="{{ old('email', $student->email ?? '') }}"></div>
</div>
<div class="form-group"><label>Adresse</label><textarea class="form-input" style="width:100%" name="adresse" rows="2">{{ old('adresse', $student->adresse ?? '') }}</textarea></div>
<div class="form-row">
 <div class="form-group"><label>Classe</label><select class="form-select" style="width:100%" name="class_id"><option value="">—</option>@foreach($classes as $c)<option value="{{ $c->id }}" @selected(old('class_id', $student->class_id ?? '')==$c->id)>{{ $c->nom }}</option>@endforeach</select></div>
 <div class="form-group"><label>Date d'inscription</label><input class="form-input" style="width:100%" type="date" name="date_inscription" value="{{ old('date_inscription', optional($student->date_inscription ?? null)->format('Y-m-d')) }}"></div>
</div>
<div class="form-row">
 <div class="form-group"><label>Statut</label><select class="form-select" style="width:100%" name="statut">@foreach(['Actif','Suspendu','Transféré','Abandonné','Diplômé','Exclu'] as $s)<option value="{{ $s }}" @selected(old('statut', $student->statut ?? 'Actif')==$s)>{{ $s }}</option>@endforeach</select></div>
 <div class="form-group"><label>Code Massar</label><input class="form-input" style="width:100%" name="code_massar" value="{{ old('code_massar', $student->code_massar ?? '') }}"></div>
</div>
<div class="form-group"><label>Photo</label><input class="form-input" style="width:100%" type="file" name="photo" accept="image/*"></div>
<div class="form-group"><label>Parent / Tuteur</label><div class="checkboxes">@foreach($parents as $p)<label><input type="checkbox" name="parents[]" value="{{ $p->id }}" @checked(collect(old('parents', isset($student)?$student->parents->pluck('id')->all():[]))->contains($p->id))> {{ $p->full_name }}</label>@endforeach</div></div>

<button class="btn btn-gold" type="submit">Enregistrer</button>
</form>
@endsection
