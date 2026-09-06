@extends('layouts.app')
@section('title','Enregistrer un départ')
@section('content')
<div class="page-head"><div><h1>Enregistrer un départ</h1></div></div>
<form class="card" method="POST" action="{{ route('departures.store') }}">@csrf
<div class="form-group"><label>Élève <span class="req">*</span></label>
<select class="form-select" style="width:100%" name="student_id"><option value="">— Élève externe / vide —</option>@foreach($students as $s)<option value="{{ $s->id }}">{{ $s->full_name }}</option>@endforeach</select>
<input class="form-input" style="width:100%;margin-top:6px" name="nom_externe" placeholder="Nom si élève externe">
</div>
<div class="form-row"><div class="form-group"><label>Date de sortie <span class="req">*</span></label><input class="form-input" style="width:100%" type="date" name="date_sortie" value="{{ date('Y-m-d') }}" required></div>
<div class="form-group"><label>Statut <span class="req">*</span></label><select class="form-select" style="width:100%" name="statut" required>@foreach(['Transféré','Abandonné','Diplômé','Exclu'] as $s)<option>{{ $s }}</option>@endforeach</select></div></div>
<div class="form-row"><div class="form-group"><label>Raison</label><input class="form-input" style="width:100%" name="raison"></div>
<div class="form-group"><label>Destination</label><input class="form-input" style="width:100%" name="destination"></div></div>
<div class="form-group"><label>Documents remis</label><input class="form-input" style="width:100%" name="documents_remis"></div>
<div class="form-group"><label>Observation</label><textarea class="form-input" style="width:100%" name="observation" rows="2"></textarea></div>
<button class="btn btn-gold" type="submit">Enregistrer</button>
</form>
@endsection
