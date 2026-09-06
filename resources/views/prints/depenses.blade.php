<!DOCTYPE html><html lang="fr"><head><meta charset="utf-8"><title>{{ $title ?? 'Document' }}</title>
<link rel="stylesheet" href="{{ asset('css/app.css') }}"><style>body{background:#fff}</style></head><body>
<div class="print-page">
<div class="no-print" style="margin-bottom:12px"><button class="btn btn-gold" onclick="window.print()">{{ __('Imprimer') }}</button></div>
<h2>{{ $settings->nom_etablissement ?? 'NOOR ACADEMY' }}</h2>
<p class="muted">{{ $settings->sous_titre ?? '' }} · {{ $settings->adresse ?? '' }} · {{ $settings->telephone ?? '' }}</p>
<hr>

<h1>{{ __('État des dépenses') }}</h1>
<table class="data"><thead><tr><th>{{ __('DATE') }}</th><th>{{ __('CATÉGORIE') }}</th><th>{{ __('FOURNISSEUR') }}</th><th>{{ __('MONTANT') }}</th></tr></thead>
<tbody>@foreach($expenses as $e)<tr><td>{{ $e->date->format('d/m/Y') }}</td><td>{{ $e->categorie }}</td><td>{{ $e->fournisseur }}</td><td>{{ number_format($e->montant,2,',',' ') }}</td></tr>@endforeach</tbody></table>
</div></body></html>
