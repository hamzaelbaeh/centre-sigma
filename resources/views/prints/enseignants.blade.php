<!DOCTYPE html><html lang="fr"><head><meta charset="utf-8"><title>{{ $title ?? 'Document' }}</title>
<link rel="stylesheet" href="{{ asset('css/app.css') }}"><style>body{background:#fff}</style></head><body>
<div class="print-page">
<div class="no-print" style="margin-bottom:12px"><button class="btn btn-gold" onclick="window.print()">{{ __('Imprimer') }}</button></div>
<h2>{{ $settings->nom_etablissement ?? 'NOOR ACADEMY' }}</h2>
<p class="muted">{{ $settings->sous_titre ?? '' }} · {{ $settings->adresse ?? '' }} · {{ $settings->telephone ?? '' }}</p>
<hr>

<h1>{{ __('Liste des enseignants') }}</h1>
<table class="data"><thead><tr><th>{{ __('MATRICULE') }}</th><th>{{ __('NOM') }}</th><th>{{ __('SPÉCIALITÉ') }}</th><th>{{ __('MODE') }}</th><th>{{ __('VALEUR') }}</th><th>{{ __('STATUT') }}</th></tr></thead>
<tbody>@foreach($teachers as $t)<tr><td>{{ $t->matricule }}</td><td>{{ $t->full_name }}</td><td>{{ $t->specialite }}</td><td>{{ $t->mode_paiement }}</td><td>{{ number_format($t->valeur_dh,2,',',' ') }}</td><td>{{ $t->statut }}</td></tr>@endforeach</tbody></table>
</div></body></html>
