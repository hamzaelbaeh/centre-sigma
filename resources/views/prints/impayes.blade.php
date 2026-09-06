<!DOCTYPE html><html lang="fr"><head><meta charset="utf-8"><title>{{ $title ?? 'Document' }}</title>
<link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ @filemtime(public_path('css/app.css')) ?: time() }}"><style>body{background:#fff}</style></head><body>
<div class="print-page">
<div class="no-print" style="margin-bottom:12px"><button class="btn btn-gold" onclick="window.print()">{{ __('Imprimer') }}</button></div>
<h2>{{ $settings->nom_etablissement ?? 'NOOR ACADEMY' }}</h2>
<p class="muted">{{ $settings->sous_titre ?? '' }} · {{ $settings->adresse ?? '' }} · {{ $settings->telephone ?? '' }}</p>
<hr>

<h1>{{ __('État des impayés') }}</h1>
<table class="data"><thead><tr><th>{{ __('ÉLÈVE') }}</th><th>{{ __('CLASSE') }}</th><th>{{ __('TYPE') }}</th><th>{{ __('RESTANT') }}</th></tr></thead>
<tbody>@foreach($payments as $p)<tr><td>{{ $p->student?->full_name }}</td><td>{{ $p->student?->schoolClass?->nom }}</td><td>{{ $p->type }}</td><td>{{ number_format($p->restant,2,',',' ') }}</td></tr>@endforeach</tbody></table>
</div></body></html>
