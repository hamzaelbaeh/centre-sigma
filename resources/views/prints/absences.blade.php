<!DOCTYPE html><html lang="fr"><head><meta charset="utf-8"><title>{{ $title ?? 'Document' }}</title>
<link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ @filemtime(public_path('css/app.css')) ?: time() }}"><style>body{background:#fff}</style></head><body>
<div class="print-page">
<div class="no-print" style="margin-bottom:12px"><button class="btn btn-gold" onclick="window.print()">{{ __('Imprimer') }}</button></div>
<h2>{{ $settings->nom_etablissement ?? 'NOOR ACADEMY' }}</h2>
<p class="muted">{{ $settings->sous_titre ?? '' }} · {{ $settings->adresse ?? '' }} · {{ $settings->telephone ?? '' }}</p>
<hr>

<h1>{{ __('État des absences —') }} {{ $class->nom }}</h1>
<table class="data"><thead><tr><th>{{ __('DATE') }}</th><th>{{ __('ÉLÈVE') }}</th><th>{{ __('JUSTIFIÉ') }}</th><th>{{ __('MOTIF') }}</th></tr></thead>
<tbody>@foreach($absences as $a)<tr><td>{{ $a->date->format('d/m/Y') }}</td><td>{{ $a->student?->full_name }}</td><td>{{ $a->justifie?__('Oui'):__('Non') }}</td><td>{{ $a->motif }}</td></tr>@endforeach</tbody></table>
</div></body></html>
