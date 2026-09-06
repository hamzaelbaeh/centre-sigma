@extends('layouts.app')
@section('title', __('Import élèves'))
@section('content')
<div class="page-head">
  <div>
    <h1>{{ __('Import élèves') }}</h1>
    <div class="sub">CSV : nom, prenom, date_naissance, sexe, telephone, email, adresse, classe, statut, code_massar</div>
  </div>
  <div class="actions">
    <a class="btn btn-ghost" href="{{ route('students.index') }}">{{ __('Retour à la liste') }}</a>
    <a class="btn btn-outline" href="{{ route('students.import.template') }}">{{ __('Télécharger le modèle CSV') }}</a>
  </div>
</div>
<div class="card" style="max-width:560px">
  <form method="POST" action="{{ route('students.import.store') }}" enctype="multipart/form-data">
    @csrf
    <div class="form-group">
      <label>{{ __('Fichier CSV') }} <span class="req">*</span></label>
      <input class="form-input" style="width:100%" type="file" name="file" accept=".csv,text/csv" required>
    </div>
    <p class="muted">Mise à jour si matricule (si fourni) ou nom+prénom existent déjà. Séparateur ; ou ,.</p>
    <div class="actions" style="margin-top:12px">
      <button class="btn btn-gold" type="submit">{{ __("Lancer l'import") }}</button>
      <a class="btn btn-ghost" href="{{ route('students.index') }}">{{ __('Annuler') }}</a>
    </div>
  </form>
</div>
@endsection
