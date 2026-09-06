<!DOCTYPE html><html lang="{{ app()->getLocale() }}" @if(app()->getLocale()==='ar') dir="rtl" @endif><head><meta charset="utf-8"><title>{{ $title ?? 'Document' }}</title>
<link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ @filemtime(public_path('css/app.css')) ?: time() }}"><style>body{background:#fff}</style></head><body>
<div class="print-page">
<div class="no-print" style="margin-bottom:12px"><button class="btn btn-gold" onclick="window.print()">{{ __('Imprimer') }}</button></div>
<h2>{{ $settings->nom_etablissement ?? 'NOOR ACADEMY' }}</h2>
<p class="muted">{{ $settings->sous_titre ?? '' }} · {{ $settings->adresse ?? '' }} · {{ $settings->telephone ?? '' }}</p>
<hr>

<h1>{{ __('Reçu de paiement') }}</h1>
<p>{{ __('Élève :') }} <strong>{{ $student->full_name }}</strong> ({{ $student->matricule }})</p>
<p>Classe : {{ $student->schoolClass?->nom }}</p>
@if($payment)
<p>Type : {{ $payment->type }} · Montant : {{ number_format($payment->montant,2,',',' ') }} DH · Payé : {{ number_format($payment->paye,2,',',' ') }} DH</p>
<p>Statut : {{ $payment->statut }}</p>
@else
<p>Aucun paiement enregistré.</p>
@endif
<p style="margin-top:40px">Date : {{ now()->format('d/m/Y') }}</p>
</div></body></html>
