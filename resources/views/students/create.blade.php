@extends('layouts.app')
@section('title',__('Nouvel élève'))
@section('content')
@php
    $student = $student ?? null;
    $oldParent = old('parent_id') ? \App\Models\ParentGuardian::find(old('parent_id')) : null;
@endphp
<div class="page-head"><div><h1>{{ __('Nouvel élève') }}</h1></div><a class="btn btn-ghost" href="{{ route('students.index') }}">{{ __('Retour') }}</a></div>
<form class="card" method="POST" action="{{ route('students.store') }}" enctype="multipart/form-data" id="student-create-form">@csrf

<div class="form-row">
 <div class="form-group"><label>{{ __('Nom') }} <span class="req">*</span></label><input class="form-input" style="width:100%" name="nom" value="{{ old('nom', $student->nom ?? '') }}" required></div>
 <div class="form-group"><label>{{ __('Prénom') }} <span class="req">*</span></label><input class="form-input" style="width:100%" name="prenom" value="{{ old('prenom', $student->prenom ?? '') }}" required></div>
</div>
<div class="form-row">
 <div class="form-group"><label>{{ __('Date de naissance') }}</label><input class="form-input" style="width:100%" type="date" name="date_naissance" value="{{ old('date_naissance', optional($student->date_naissance ?? null)->format('Y-m-d')) }}"></div>
 <div class="form-group"><label>{{ __('Lieu de naissance') }}</label><input class="form-input" style="width:100%" name="lieu_naissance" value="{{ old('lieu_naissance', $student->lieu_naissance ?? '') }}"></div>
</div>
<div class="form-row">
 <div class="form-group"><label>{{ __('Sexe') }}</label><select class="form-select" style="width:100%" name="sexe"><option value="">—</option><option value="M" @selected(old('sexe',$student->sexe ?? '')=='M')>M</option><option value="F" @selected(old('sexe',$student->sexe ?? '')=='F')>F</option></select></div>
 <div class="form-group"><label>{{ __('CIN') }}</label><input class="form-input" style="width:100%" name="cin" value="{{ old('cin', $student->cin ?? '') }}"></div>
</div>
<div class="form-row">
 <div class="form-group"><label>{{ __('Téléphone') }}</label><input class="form-input" style="width:100%" name="telephone" value="{{ old('telephone', $student->telephone ?? '') }}"></div>
 <div class="form-group"><label>{{ __('Email') }}</label><input class="form-input" style="width:100%" type="email" name="email" value="{{ old('email', $student->email ?? '') }}"></div>
</div>
<div class="form-group"><label>{{ __('Adresse') }}</label><textarea class="form-input" style="width:100%" name="adresse" rows="2">{{ old('adresse', $student->adresse ?? '') }}</textarea></div>
<div class="form-row">
 <div class="form-group">
  <label>{{ __('Niveau scolaire') }}</label>
  <select class="form-select" style="width:100%" name="niveau_scolaire">
    <option value="">—</option>
    @foreach(\App\Models\Student::NIVEAUX_SCOLAIRES as $niv)
      <option value="{{ $niv }}" @selected(old('niveau_scolaire', $student->niveau_scolaire ?? '')==$niv)>{{ __($niv) }}</option>
    @endforeach
  </select>
 </div>
 <div class="form-group"><label>{{ __("Date d'inscription") }}</label><input class="form-input" style="width:100%" type="date" name="date_inscription" value="{{ old('date_inscription', optional($student->date_inscription ?? null)->format('Y-m-d')) }}"></div>
</div>
<div class="form-group">
  <label>{{ __('Classe') }} <span class="muted">({{ __('optionnel') }})</span></label>
  <select class="form-select" style="width:100%" name="class_id"><option value="">—</option>@foreach($classes as $c)<option value="{{ $c->id }}" @selected(old('class_id', $student->class_id ?? '')==$c->id)>{{ $c->nom }}</option>@endforeach</select>
  <div class="sub">{{ __('يتم توزيع التلاميذ على الأقسام يدوياً من صفحة الأقسام') }}</div>
</div>
<div class="form-row">
 <div class="form-group"><label>{{ __('Statut') }}</label><select class="form-select" style="width:100%" name="statut">@foreach(['Actif','Suspendu temporairement','Retiré','Suspendu','Transféré','Abandonné','Diplômé','Exclu'] as $s)<option value="{{ $s }}" @selected(old('statut', $student->statut ?? 'Actif')==$s)>{{ __($s) }}</option>@endforeach</select></div>
 <div class="form-group"><label>{{ __('Code Massar') }}</label><input class="form-input" style="width:100%" name="code_massar" value="{{ old('code_massar', $student->code_massar ?? '') }}"></div>
</div>
<div class="form-group"><label>{{ __('Photo') }}</label><input class="form-input" style="width:100%" type="file" name="photo" accept="image/*"></div>
<div class="form-group" id="parent-field">
  <label>{{ __('Parent / Tuteur') }}</label>
  <input type="hidden" name="parent_id" id="parent_id" value="{{ old('parent_id') }}">
  <div id="parent-picked" style="display:none;align-items:center;gap:10px;padding:8px 10px;border:1px solid #16a34a;background:#f0fdf4;border-radius:8px">
    <span id="parent-picked-label" style="font-weight:600"></span>
    <button type="button" class="btn btn-sm btn-ghost" id="parent-clear">{{ __('Changer') }}</button>
  </div>
  <div id="parent-search-box" style="position:relative">
    <input class="form-input" style="width:100%" type="text" id="parent_search" autocomplete="off" placeholder="{{ __('Rechercher un parent par téléphone ou nom...') }}">
    <div id="parent-results" style="display:none;position:absolute;z-index:50;inset-inline:0;background:#fff;border:1px solid #cbd5e1;border-radius:8px;max-height:220px;overflow:auto;box-shadow:0 6px 18px rgba(0,0,0,.12)"></div>
    <div class="sub" id="parent-hint">{{ __('Si le parent est introuvable, remplissez les champs ci-dessous : il sera créé et lié automatiquement.') }}</div>
  </div>
  <div id="new-parent-box" style="margin-top:10px;padding:10px;border:1px dashed #cbd5e1;border-radius:8px">
    <div class="sub" style="margin-bottom:6px"><strong>{{ __('Nouveau parent') }}</strong></div>
    <div class="form-row">
      <div class="form-group"><label>{{ __('Nom') }}</label><input class="form-input" style="width:100%" name="new_parent[nom]" value="{{ old('new_parent.nom') }}"></div>
      <div class="form-group"><label>{{ __('Prénom') }}</label><input class="form-input" style="width:100%" name="new_parent[prenom]" value="{{ old('new_parent.prenom') }}"></div>
      <div class="form-group"><label>{{ __('Téléphone') }}</label><input class="form-input" style="width:100%" name="new_parent[telephone]" value="{{ old('new_parent.telephone') }}"></div>
    </div>
  </div>
</div>
<script>
(function(){
  const hid = document.getElementById('parent_id'), inp = document.getElementById('parent_search'), res = document.getElementById('parent-results');
  const picked = document.getElementById('parent-picked'), lbl = document.getElementById('parent-picked-label');
  const searchBox = document.getElementById('parent-search-box'), newBox = document.getElementById('new-parent-box');
  const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
  let t;
  function pick(p){
    hid.value = p.id;
    lbl.innerHTML = '<span dir="ltr">' + esc(p.display_id) + '</span> — ' + esc(p.nom) + ' — <span dir="ltr">' + esc(p.telephone || '') + '</span>';
    picked.style.display = 'flex'; searchBox.style.display = 'none'; newBox.style.display = 'none'; res.style.display = 'none';
    newBox.querySelectorAll('input').forEach(i => i.value = '');
  }
  document.getElementById('parent-clear').addEventListener('click', () => {
    hid.value = ''; picked.style.display = 'none'; searchBox.style.display = 'block'; newBox.style.display = 'block'; inp.value = ''; inp.focus();
  });
  inp.addEventListener('input', () => {
    clearTimeout(t);
    const q = inp.value.trim();
    if (q.length < 2) { res.style.display = 'none'; return; }
    t = setTimeout(() => {
      fetch('{{ route('parents.search') }}?q=' + encodeURIComponent(q), {headers:{'Accept':'application/json'}}).then(r => r.json()).then(list => {
        if (!list.length) { res.innerHTML = '<div style="padding:8px 10px" class="muted">{{ __('Aucun parent trouvé — remplissez les champs « Nouveau parent ».') }}</div>'; res.style.display = 'block'; return; }
        res.innerHTML = list.map((p, i) => '<div class="parent-opt" data-i="' + i + '" style="padding:8px 10px;cursor:pointer;border-bottom:1px solid #f1f5f9"><strong dir="ltr">' + esc(p.display_id) + '</strong> — ' + esc(p.nom) + ' — <span dir="ltr">' + esc(p.telephone || '') + '</span></div>').join('');
        res.style.display = 'block';
        res.querySelectorAll('.parent-opt').forEach(el => el.addEventListener('click', () => pick(list[+el.dataset.i])));
      });
    }, 250);
  });
  document.addEventListener('click', e => { if (!e.target.closest('#parent-search-box')) res.style.display = 'none'; });
  @if($oldParent)
  pick({!! json_encode(['id'=>$oldParent->id,'display_id'=>$oldParent->display_id,'nom'=>$oldParent->full_name,'telephone'=>$oldParent->telephone], JSON_UNESCAPED_UNICODE) !!});
  @endif
})();
</script>

@include('students.partials.subjects')

<div class="form-group" id="initial-payment-section" style="margin-top:16px;padding-top:12px;border-top:1px solid #eee">
  <label>{{ __('Paiement initial (mensualité)') }}</label>
  <div class="sub" style="margin-bottom:8px">{{ __('Le parent paie le mois COMPLET à l\'inscription. Montant consommé = séances tenues de l\'inscription à la fin du mois × prix de la séance ; la différence est un crédit déduit du mois suivant.') }}</div>
  <div id="billing-preview" class="sub" style="margin-bottom:10px;padding:8px 10px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;display:none"></div>
  <div class="form-row">
    <div class="form-group">
      <label>{{ __('Statut du paiement') }}</label>
      <select class="form-select" style="width:100%" name="payment_statut" id="payment_statut">
        <option value="Non payé" @selected(old('payment_statut','Soldé')==='Non payé')>{{ __('Non payé') }}</option>
        <option value="Partiel" @selected(old('payment_statut')==='Partiel')>{{ __('Partiel') }}</option>
        <option value="Soldé" @selected(old('payment_statut','Soldé')==='Soldé')>{{ __('Soldé') }}</option>
      </select>
    </div>
    <div class="form-group" id="payment-paye-group">
      <label>{{ __('Montant payé (DH)') }} <span class="muted">({{ __('mois complet') }})</span></label>
      <input class="form-input" style="width:100%" type="number" step="0.01" min="0" name="payment_paye" id="payment_paye" value="{{ old('payment_paye') }}">
      <div class="sub">{{ __('Total mensuel estimé') }} : <span id="payment-total-hint">0,00</span> DH</div>
    </div>
  </div>
</div>
<script>
(function(){
  const statut = document.getElementById('payment_statut');
  const paye = document.getElementById('payment_paye');
  const group = document.getElementById('payment-paye-group');
  function syncPaye(){
    if (!statut || !paye) return;
    if (statut.value === 'Non payé') {
      paye.value = '';
      paye.required = false;
      if (group) group.style.opacity = '0.5';
    } else if (statut.value === 'Soldé') {
      const hint = document.getElementById('payment-total-hint');
      // parse localized number roughly
      const raw = (hint && hint.textContent || '0').replace(/\s/g,'').replace(',','.');
      const total = parseFloat(raw) || 0;
      paye.value = total.toFixed(2);
      paye.required = false;
      if (group) group.style.opacity = '1';
    } else {
      paye.required = true;
      if (group) group.style.opacity = '1';
    }
  }
  if (statut) statut.addEventListener('change', syncPaye);
  syncPaye();
})();
</script>

<button class="btn btn-gold" type="submit">{{ __('Enregistrer') }}</button>
</form>
@unless(isset($student))
<script>
(function(){
  const box = document.getElementById('billing-preview');
  const sec = document.getElementById('student-subjects-section');
  const cls = document.querySelector('select[name=class_id]');
  const dateInp = document.querySelector('input[name=date_inscription]');
  if (!box || !sec) return;
  const fmt = n => Number(n).toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2, useGrouping:false}) + ' DH';
  const L = {full:@json(__('Mois complet payé')), consumed:@json(__('Consommé ce mois (estimation)')), credit:@json(__('Crédit reporté (estimation)')), next:@json(__('À payer le mois 2 (estimation)')), pick:@json(__('Cochez des matières pour voir le calcul.')), est:@json(__('estimation')), note:@json(__('Estimation selon l\'emploi du temps ; le calcul final se fait à la fin du mois selon les séances réellement tenues.'))};
  let t;
  function load(){
    const p = new URLSearchParams(); let any = false;
    if (cls && cls.value) p.append('class_id', cls.value);
    if (dateInp && dateInp.value) p.append('date', dateInp.value);
    sec.querySelectorAll('.subject-enroll-row').forEach(r => {
      const c = r.querySelector('.subject-check'), i = r.querySelector('.subject-prix');
      if (c && c.checked) { any = true; p.append('subjects['+c.value+']', i.value || 0); }
    });
    if (!any) { box.innerHTML = L.pick; box.style.display = 'block'; return; }
    fetch('{{ route('students.billing_preview') }}?' + p.toString(), {headers:{'Accept':'application/json'}}).then(r => r.json()).then(d => {
      let h = '';
      d.lines.forEach(l => {
        h += '<div>' + l.subject + ': ' + l.sessions + '/' + l.sessions_month + ' × ' + fmt(l.session_price) + ' = ' + fmt(l.amount) + (l.source !== 'actual' ? ' <span style="color:#b45309">(' + L.est + ')</span>' : '') + '</div>';
      });
      const credit = Math.max(0, d.full - d.total);
      h += '<hr style="border:0;border-top:1px solid #e2e8f0;margin:6px 0"><strong>' + L.full + ': ' + fmt(d.full) + ' · ' + L.consumed + ': ' + fmt(d.total) + ' · ' + L.credit + ': ' + fmt(credit) + ' · ' + L.next + ': ' + fmt(Math.max(0, d.full - credit)) + '</strong><div class="muted" style="margin-top:4px">' + L.note + '</div>';
      box.innerHTML = h; box.style.display = 'block';
    });
  }
  function q(){ clearTimeout(t); t = setTimeout(load, 300); }
  sec.addEventListener('change', q); sec.addEventListener('input', q);
  if (cls) cls.addEventListener('change', q);
  if (dateInp) dateInp.addEventListener('change', q);
  q();
})();
</script>
@endunless
@endsection
