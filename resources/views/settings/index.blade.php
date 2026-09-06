@extends('layouts.app')
@section('title','Paramètres')
@section('content')
<div class="page-head"><div><h1>Paramètres</h1></div></div>
<div class="grid grid-2">
<form class="card" method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data" id="settingsForm">@csrf
<div class="form-group"><label>Nom établissement <span class="req">*</span></label><input class="form-input" style="width:100%" name="nom_etablissement" id="nom_etab" value="{{ old('nom_etablissement',$settings->nom_etablissement) }}" required></div>
<div class="form-group"><label>Sous-titre</label><input class="form-input" style="width:100%" name="sous_titre" id="sous_titre" value="{{ old('sous_titre',$settings->sous_titre) }}"></div>
<div class="form-group"><label>Adresse</label><input class="form-input" style="width:100%" name="adresse" value="{{ old('adresse',$settings->adresse) }}"></div>
<div class="form-row"><div class="form-group"><label>Téléphone</label><input class="form-input" style="width:100%" name="telephone" value="{{ old('telephone',$settings->telephone) }}"></div>
<div class="form-group"><label>Email</label><input class="form-input" style="width:100%" type="email" name="email" value="{{ old('email',$settings->email) }}"></div></div>
<div class="form-group"><label>Logo (PNG/JPG/GIF/WebP/SVG — max 2Mo)</label><input class="form-input" style="width:100%" type="file" name="logo" accept=".png,.jpg,.jpeg,.gif,.webp,.svg"></div>
<div class="form-row"><div class="form-group"><label>Couleur principale</label><input class="form-input" style="width:100%" type="color" name="couleur_principale" id="c_gold" value="{{ $settings->couleur_principale }}"></div>
<div class="form-group"><label>Couleur barre latérale</label><input class="form-input" style="width:100%" type="color" name="couleur_sidebar" id="c_side" value="{{ $settings->couleur_sidebar }}"></div></div>
<div class="actions">
<button class="btn btn-gold" type="submit">Enregistrer</button>
<a class="btn btn-ghost" href="{{ route('settings.index') }}">Annuler</a>
</div>
</form>
<div>
<div class="card" id="previewSidebar" style="background:{{ $settings->couleur_sidebar }};color:#fff;min-height:280px">
<div style="display:flex;gap:10px;align-items:center;margin-bottom:16px">
<div id="prevLogo" style="width:40px;height:40px;border-radius:10px;background:{{ $settings->couleur_principale }};color:#081f52;display:flex;align-items:center;justify-content:center;font-weight:800">N</div>
<div><strong id="prevNom">{{ $settings->nom_etablissement }}</strong><div class="muted" id="prevSous" style="color:#93c5fd">{{ $settings->sous_titre }}</div></div>
</div>
<div id="prevActive" style="background:{{ $settings->couleur_principale }};color:#081f52;padding:10px;border-radius:10px;font-weight:700;margin-bottom:8px">Tableau de bord</div>
<div style="color:#93c5fd;padding:8px">Élèves</div>
<div style="color:#93c5fd;padding:8px">Paiements</div>
<p class="muted" style="color:#94a3b8;margin-top:20px">Live sidebar preview</p>
</div>
@if($settings->logo)
<form method="POST" action="{{ route('settings.logo') }}" style="margin-top:10px">@csrf @method('DELETE')<button class="btn btn-danger" type="submit">Supprimer Logo</button></form>
@endif
</div>
</div>
@endsection
@push('scripts')
<script src="{{ asset('js/settings.js') }}"></script>
@endpush
