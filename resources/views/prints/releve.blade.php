<!DOCTYPE html><html lang="{{ app()->getLocale() }}" @if(app()->getLocale()==='ar') dir="rtl" @endif><head><meta charset="utf-8"><title>{{ $title ?? 'Document' }}</title>
<link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ @filemtime(public_path('css/app.css')) ?: time() }}"><style>body{background:#fff}</style></head><body>
<div class="print-page">
<div class="no-print" style="margin-bottom:12px"><button class="btn btn-gold" onclick="window.print()">{{ __('Imprimer') }}</button></div>
<h2>{{ $settings->nom_etablissement ?? 'NOOR ACADEMY' }}</h2>
<p class="muted">{{ $settings->sous_titre ?? '' }} · {{ $settings->adresse ?? '' }} · {{ $settings->telephone ?? '' }}</p>
<hr>

<h1>{{ __('Relevé des paiements') }}</h1>
<p>{{ __('Élève :') }} <strong>{{ $student->full_name }}</strong> — {{ $student->schoolClass?->nom }}</p>
<table class="data"><thead><tr><th>{{ __('TYPE') }}</th><th>{{ __('PÉRIODE') }}</th><th>{{ __('MONTANT') }}</th><th>{{ __('PAYÉ') }}</th><th>{{ __('STATUT') }}</th></tr></thead>
<tbody>@foreach($student->payments as $p)<tr><td>{{ $p->type }}</td><td>{{ $p->periode }}</td><td>{{ number_format($p->montant,2,',',' ') }}</td><td>{{ number_format($p->paye,2,',',' ') }}</td><td>{{ $p->statut }}</td></tr>@endforeach</tbody></table>
</div></body></html>
