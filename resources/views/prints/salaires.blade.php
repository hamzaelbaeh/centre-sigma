<!DOCTYPE html><html lang="fr"><head><meta charset="utf-8"><title>{{ $title ?? 'Document' }}</title>
<link rel="stylesheet" href="{{ asset('css/app.css') }}"><style>body{background:#fff}</style></head><body>
<div class="print-page">
<div class="no-print" style="margin-bottom:12px"><button class="btn btn-gold" onclick="window.print()">Imprimer</button></div>
<h2>{{ $settings->nom_etablissement ?? 'NOOR ACADEMY' }}</h2>
<p class="muted">{{ $settings->sous_titre ?? '' }} · {{ $settings->adresse ?? '' }} · {{ $settings->telephone ?? '' }}</p>
<hr>

<h1>État des salaires</h1>
<h3>Enseignants</h3>
<table class="data"><thead><tr><th>ENSEIGNANT</th><th>PÉRIODE</th><th>NET</th><th>STATUT</th></tr></thead>
<tbody>@foreach($teacherPayrolls as $p)<tr><td>{{ $p->teacher?->full_name }}</td><td>{{ $p->periode }}</td><td>{{ number_format($p->net,2,',',' ') }}</td><td>{{ $p->statut }}</td></tr>@endforeach</tbody></table>
<h3>Personnel</h3>
<table class="data"><thead><tr><th>EMPLOYÉ</th><th>PÉRIODE</th><th>NET</th><th>STATUT</th></tr></thead>
<tbody>@foreach($staffPayrolls as $p)<tr><td>{{ $p->staff?->full_name }}</td><td>{{ $p->periode }}</td><td>{{ number_format($p->net,2,',',' ') }}</td><td>{{ $p->statut }}</td></tr>@endforeach</tbody></table>
</div></body></html>
