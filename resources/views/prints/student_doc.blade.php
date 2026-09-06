<!DOCTYPE html><html lang="{{ app()->getLocale() }}" @if(app()->getLocale()==='ar') dir="rtl" @endif><head><meta charset="utf-8"><title>{{ $title ?? __('Document') }}</title>
<link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ @filemtime(public_path('css/app.css')) ?: time() }}"><style>body{background:#fff}</style></head><body>
<div class="print-page">
<div class="no-print" style="margin-bottom:12px"><button class="btn btn-gold" onclick="window.print()">{{ __('Imprimer') }}</button></div>
<h2>{{ $settings->nom_etablissement ?? 'NOOR ACADEMY' }}</h2>
<p class="muted">{{ $settings->sous_titre ?? '' }} · {{ $settings->adresse ?? '' }} · {{ $settings->telephone ?? '' }}</p>
<hr>

<h1>{{ $title }}</h1>
<p>{{ __("Nous certifions que l'élève :name, matricule :matricule,", ['name' => $student->full_name, 'matricule' => $student->matricule]) }}</p>
<p>{{ __('né(e) le :date à :lieu,', ['date' => optional($student->date_naissance)->format('d/m/Y'), 'lieu' => $student->lieu_naissance]) }}</p>
<p>{{ __("est inscrit(e) en classe de :class pour l'année scolaire :year.", ['class' => $student->schoolClass?->nom, 'year' => '2026/2027']) }}</p>
<p style="margin-top:40px">{{ __('Fait à :place, le :date', ['place' => $settings->adresse ?: 'Casablanca', 'date' => now()->format('d/m/Y')]) }}</p>
</div></body></html>
