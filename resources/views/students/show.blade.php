@extends('layouts.app')
@section('title',__('Fiche élève'))
@section('content')
<div class="page-head">
 <div><h1>{{ __('Fiche —') }} {{ $student->full_name }}</h1><div class="sub">{{ $student->matricule }} · {{ __($student->statut) }}</div></div>
 <div class="actions"><a class="btn btn-ghost" href="{{ route('students.edit',$student) }}">{{ __('Modifier') }}</a><a class="btn btn-ghost" href="{{ route('students.index') }}">{{ __('Retour') }}</a></div>
</div>
<div class="grid grid-2">
<div class="card">
 <p><strong>{{ __('Niveau scolaire:') }}</strong> {{ $student->niveau_scolaire ? __($student->niveau_scolaire) : '—' }}</p>
 <p><strong>{{ __('Classe:') }}</strong> {{ $student->schoolClass?->nom ?: '—' }}</p>
 <p><strong>{{ __('Naissance:') }}</strong> {{ optional($student->date_naissance)->format('d/m/Y') }} — {{ $student->lieu_naissance }}</p>
 <p><strong>{{ __('Sexe:') }}</strong> {{ $student->sexe }} · <strong>{{ __('CIN:') }}</strong> {{ $student->cin }}</p>
 <p><strong>{{ __('Tél:') }}</strong> {{ $student->telephone }} · <strong>{{ __('Email:') }}</strong> {{ $student->email }}</p>
 <p><strong>{{ __('Adresse:') }}</strong> {{ $student->adresse }}</p>
 <p><strong>{{ __('Inscription:') }}</strong> {{ optional($student->date_inscription)->format('d/m/Y') }}</p>
 <p><strong>{{ __('Massar:') }}</strong> {{ $student->code_massar }}</p>

 <h3 style="margin-top:16px">{{ __('Mettre non actif') }}</h3>
 <p class="muted" style="margin-bottom:8px">{{ __('Changer rapidement le statut (Suspendu, Abandonné, etc.).') }}</p>
 <form method="POST" action="{{ route('students.statut', $student) }}" class="filters" style="padding:0;border:0;box-shadow:none;background:transparent">
  @csrf
  <select class="form-select" name="statut" style="min-width:180px">
   @foreach(['Actif','Suspendu','Transféré','Abandonné','Diplômé','Exclu'] as $s)
    <option value="{{ $s }}" @selected($student->statut===$s)>{{ __($s) }}</option>
   @endforeach
  </select>
  <button class="btn btn-outline" type="submit">{{ __('Mettre à jour le statut') }}</button>
 </form>
</div>
<div class="card">
 <h3>{{ __('Matières inscrites') }}</h3>
 <table class="data"><thead><tr><th>{{ __('Matière') }}</th><th>{{ __('Prix facturé') }}</th></tr></thead><tbody>
 @forelse($student->subjects as $sub)
 @php
   $prix = $sub->pivot->prix !== null && $sub->pivot->prix !== '' ? (float)$sub->pivot->prix : (float)$sub->prix;
 @endphp
 <tr><td>{{ $sub->nom }}</td><td>{{ number_format($prix, 2, ',', ' ') }} DH</td></tr>
 @empty
 <tr><td colspan="2" class="muted">{{ __('Aucune matière') }}</td></tr>
 @endforelse
 </tbody></table>
 <p style="margin-top:8px"><strong>{{ __('Total mensuel estimé') }} :</strong> {{ number_format($student->monthlyFeeAmount(), 2, ',', ' ') }} DH</p>

 <h3>{{ __('Parents / Tuteurs') }}</h3>
 <ul>@forelse($student->parents as $p)<li>{{ $p->full_name }} — {{ $p->telephone }}</li>@empty<li class="muted">{{ __('Aucun') }}</li>@endforelse</ul>
 <h3>{{ __('Paiements') }}</h3>
 <table class="data"><thead><tr><th>{{ __('TYPE') }}</th><th>{{ __('PÉRIODE') }}</th><th>{{ __('MONTANT') }}</th><th>{{ __('PAYÉ') }}</th><th>{{ __('STATUT') }}</th></tr></thead><tbody>
 @forelse($student->payments as $pay)
 <tr>
  <td>{{ $pay->type }}</td>
  <td>{{ $pay->periode }}</td>
  <td>{{ number_format($pay->montant,2,',',' ') }}</td>
  <td>{{ number_format($pay->paye,2,',',' ') }}</td>
  <td><span class="badge {{ $pay->statut==='Soldé'?'badge-green':($pay->statut==='Partiel'?'badge-amber':($pay->statut==='Annulé'?'badge-gray':'badge-red')) }}">{{ __($pay->statut) }}</span></td>
 </tr>
 @empty
 <tr><td colspan="5" class="muted">{{ __('Aucun') }}</td></tr>
 @endforelse
 </tbody></table>
</div>
</div>

<div class="card" style="margin-top:14px">
 <h3>{{ __('Factures non payées') }}</h3>
 <p class="muted" style="margin-bottom:10px">{{ __('Annuler une ou plusieurs factures (restant > 0). Les factures annulées n’apparaissent plus comme dues.') }}</p>
 @if($unpaidPayments->isEmpty())
  <p class="muted">{{ __('Aucune facture non payée.') }}</p>
 @else
 <form method="POST" action="{{ route('students.payments.cancel', $student) }}" onsubmit="return confirm(@json(__('Confirmer l’annulation des factures sélectionnées ?')));">
  @csrf
  <div class="table-wrap">
  <table class="data">
   <thead>
    <tr>
     <th style="width:40px"><input type="checkbox" id="select-all-unpaid" title="{{ __('Tout sélectionner') }}"></th>
     <th>{{ __('TYPE') }}</th>
     <th>{{ __('PÉRIODE') }}</th>
     <th>{{ __('MONTANT') }}</th>
     <th>{{ __('PAYÉ') }}</th>
     <th>{{ __('RESTANT') }}</th>
     <th>{{ __('STATUT') }}</th>
    </tr>
   </thead>
   <tbody>
   @foreach($unpaidPayments as $pay)
    <tr>
     <td><input type="checkbox" class="unpaid-cb" name="payment_ids[]" value="{{ $pay->id }}"></td>
     <td>{{ $pay->type }}</td>
     <td>{{ $pay->periode }}</td>
     <td>{{ number_format($pay->montant,2,',',' ') }}</td>
     <td>{{ number_format($pay->paye,2,',',' ') }}</td>
     <td><span class="money-red">{{ number_format($pay->restant,2,',',' ') }}</span></td>
     <td><span class="badge {{ $pay->statut==='Partiel'?'badge-amber':'badge-red' }}">{{ __($pay->statut) }}</span></td>
    </tr>
   @endforeach
   </tbody>
  </table>
  </div>
  <div class="actions" style="margin-top:12px;justify-content:flex-start">
   <button class="btn btn-danger" type="submit">{{ __('Annuler les factures sélectionnées') }}</button>
  </div>
 </form>
 @endif
</div>
@endsection
@push('scripts')
<script>
(function(){
  const all = document.getElementById('select-all-unpaid');
  if (!all) return;
  all.addEventListener('change', function(){
    document.querySelectorAll('.unpaid-cb').forEach(cb => { cb.checked = all.checked; });
  });
})();
</script>
@endpush
