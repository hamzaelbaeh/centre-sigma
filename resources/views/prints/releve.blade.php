<!DOCTYPE html><html lang="fr"><head><meta charset="utf-8"><title>{{ $title ?? 'Document' }}</title>
<link rel="stylesheet" href="{{ asset('css/app.css') }}"><style>body{background:#fff}</style></head><body>
<div class="print-page">
<div class="no-print" style="margin-bottom:12px"><button class="btn btn-gold" onclick="window.print()">Imprimer</button></div>
<h2>{{ $settings->nom_etablissement ?? 'NOOR ACADEMY' }}</h2>
<p class="muted">{{ $settings->sous_titre ?? '' }} · {{ $settings->adresse ?? '' }} · {{ $settings->telephone ?? '' }}</p>
<hr>

<h1>Relevé des paiements</h1>
<p>Élève : <strong>{{ $student->full_name }}</strong> — {{ $student->schoolClass?->nom }}</p>
<table class="data"><thead><tr><th>TYPE</th><th>PÉRIODE</th><th>MONTANT</th><th>PAYÉ</th><th>STATUT</th></tr></thead>
<tbody>@foreach($student->payments as $p)<tr><td>{{ $p->type }}</td><td>{{ $p->periode }}</td><td>{{ number_format($p->montant,2,',',' ') }}</td><td>{{ number_format($p->paye,2,',',' ') }}</td><td>{{ $p->statut }}</td></tr>@endforeach</tbody></table>
</div></body></html>
