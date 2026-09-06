<!DOCTYPE html><html lang="fr"><head><meta charset="utf-8"><title>{{ $title ?? 'Document' }}</title>
<link rel="stylesheet" href="{{ asset('css/app.css') }}"><style>body{background:#fff}</style></head><body>
<div class="print-page">
<div class="no-print" style="margin-bottom:12px"><button class="btn btn-gold" onclick="window.print()">Imprimer</button></div>
<h2>{{ $settings->nom_etablissement ?? 'NOOR ACADEMY' }}</h2>
<p class="muted">{{ $settings->sous_titre ?? '' }} · {{ $settings->adresse ?? '' }} · {{ $settings->telephone ?? '' }}</p>
<hr>

<h1>{{ $title }}</h1>
<p>Nous certifions que l'élève <strong>{{ $student->full_name }}</strong>, matricule {{ $student->matricule }},</p>
<p>né(e) le {{ optional($student->date_naissance)->format('d/m/Y') }} à {{ $student->lieu_naissance }},</p>
<p>est inscrit(e) en classe de <strong>{{ $student->schoolClass?->nom }}</strong> pour l'année scolaire 2026/2027.</p>
<p style="margin-top:40px">Fait à {{ $settings->adresse ?: 'Casablanca' }}, le {{ now()->format('d/m/Y') }}</p>
</div></body></html>
