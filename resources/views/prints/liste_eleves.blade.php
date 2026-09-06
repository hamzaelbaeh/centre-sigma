<!DOCTYPE html><html lang="fr"><head><meta charset="utf-8"><title>{{ $title ?? 'Document' }}</title>
<link rel="stylesheet" href="{{ asset('css/app.css') }}"><style>body{background:#fff}</style></head><body>
<div class="print-page">
<div class="no-print" style="margin-bottom:12px"><button class="btn btn-gold" onclick="window.print()">Imprimer</button></div>
<h2>{{ $settings->nom_etablissement ?? 'NOOR ACADEMY' }}</h2>
<p class="muted">{{ $settings->sous_titre ?? '' }} · {{ $settings->adresse ?? '' }} · {{ $settings->telephone ?? '' }}</p>
<hr>

<h1>Liste des élèves — {{ $class->nom }}</h1>
<table class="data"><thead><tr><th>#</th><th>MATRICULE</th><th>NOM</th><th>PRÉNOM</th><th>STATUT</th></tr></thead>
<tbody>@foreach($class->students as $i=>$s)<tr><td>{{ $i+1 }}</td><td>{{ $s->matricule }}</td><td>{{ $s->nom }}</td><td>{{ $s->prenom }}</td><td>{{ $s->statut }}</td></tr>@endforeach</tbody></table>
</div></body></html>
