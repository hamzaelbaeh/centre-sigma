@extends('layouts.app')
@section('title','Documents et impressions')
@section('content')
<div class="page-head"><div><h1>Documents et impressions</h1><div class="sub">Sélectionnez puis imprimez — pas de CRUD ici.</div></div></div>
<div class="grid grid-3">
<div class="card">
<h3>Documents élève</h3>
<form method="GET" target="_blank">
<select class="form-select" style="width:100%;margin-bottom:10px" name="student_id" id="docStudent" required>
<option value="">Choisir un élève…</option>
@foreach($students as $s)<option value="{{ $s->id }}">{{ $s->full_name }}</option>@endforeach
</select>
</form>
<div class="actions" style="flex-direction:column;align-items:stretch">
@foreach([['recu','Reçu de paiement'],['certificat','Certificat de scolarité'],['attestation',"Attestation d'inscription"],['releve','Relevé des paiements']] as [$t,$l])
<a class="btn btn-ghost doc-link" data-type="{{ $t }}" href="#">🖨 {{ $l }}</a>
@endforeach
</div>
</div>
<div class="card">
<h3>Documents classe</h3>
<select class="form-select" style="width:100%;margin-bottom:10px" id="docClass">
<option value="">Classe…</option>
@foreach($classes as $c)<option value="{{ $c->id }}">{{ $c->nom }}</option>@endforeach
</select>
<a class="btn btn-ghost class-link" data-type="liste_eleves" href="#">🖨 Liste des élèves</a>
<a class="btn btn-ghost class-link" data-type="absences" href="#">🖨 État des absences</a>
</div>
<div class="card">
<h3>Documents institution</h3>
<a class="btn btn-ghost" target="_blank" href="{{ route('documents.print',['type'=>'impayes']) }}">🖨 État des impayés</a>
<a class="btn btn-ghost" target="_blank" href="{{ route('documents.print',['type'=>'enseignants']) }}">🖨 Liste des enseignants</a>
<a class="btn btn-ghost" target="_blank" href="{{ route('documents.print',['type'=>'salaires']) }}">🖨 État des salaires</a>
<a class="btn btn-ghost" target="_blank" href="{{ route('documents.print',['type'=>'depenses']) }}">🖨 État des dépenses</a>
</div>
</div>
@endsection
@push('scripts')
<script src="{{ asset('js/documents.js') }}"></script>
@endpush
