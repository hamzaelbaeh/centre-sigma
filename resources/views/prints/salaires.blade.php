<!DOCTYPE html><html lang="fr"><head><meta charset="utf-8"><title>{{ $title ?? 'Document' }}</title>
<link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ @filemtime(public_path('css/app.css')) ?: time() }}"><style>body{background:#fff}</style></head><body>
<div class="print-page">
<div class="no-print" style="margin-bottom:12px"><button class="btn btn-gold" onclick="window.print()">{{ __('Imprimer') }}</button></div>
<h2>{{ $settings->nom_etablissement ?? 'NOOR ACADEMY' }}</h2>
<p class="muted">{{ $settings->sous_titre ?? '' }} · {{ $settings->adresse ?? '' }} · {{ $settings->telephone ?? '' }}</p>
<hr>

<h1>{{ __('État des salaires') }}</h1>
<h3>{{ __('Enseignants') }}</h3>
<table class="data"><thead><tr><th>{{ __('ENSEIGNANT') }}</th><th>{{ __('PÉRIODE') }}</th><th>{{ __('NET') }}</th><th>{{ __('STATUT') }}</th></tr></thead>
<tbody>@foreach($teacherPayrolls as $p)<tr><td>{{ $p->teacher?->full_name }}</td><td>{{ $p->periode }}</td><td>{{ number_format($p->net,2,',',' ') }}</td><td>{{ $p->statut }}</td></tr>@endforeach</tbody></table>
<h3>{{ __('Personnel') }}</h3>
<table class="data"><thead><tr><th>{{ __('EMPLOYÉ') }}</th><th>{{ __('PÉRIODE') }}</th><th>{{ __('NET') }}</th><th>{{ __('STATUT') }}</th></tr></thead>
<tbody>@foreach($staffPayrolls as $p)<tr><td>{{ $p->staff?->full_name }}</td><td>{{ $p->periode }}</td><td>{{ number_format($p->net,2,',',' ') }}</td><td>{{ $p->statut }}</td></tr>@endforeach</tbody></table>
</div></body></html>
