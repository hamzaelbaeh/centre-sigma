@extends('layouts.app')
@section('title','Tableau de bord')
@section('content')
@php $fmt = fn($n) => number_format((float)$n, 2, ',', ' ').' DH'; @endphp
<div class="page-head">
 <div><h1>Tableau de bord</h1><div class="sub">{{ now()->translatedFormat('l d/m/Y') }} · {{ now()->translatedFormat('F Y') }}</div></div>
 <div class="actions">
  <a class="btn btn-outline" href="{{ route('payments.index') }}">Paiements élèves</a>
  <a class="btn btn-gold" href="{{ route('attendance.index') }}">Présences</a>
 </div>
</div>
<div class="grid grid-6" style="margin-bottom:14px">
@foreach([['Nouvel élève', route('students.create')],['Paiement', route('payments.index')],['Présences', route('attendance.index')],['Nouvelle dépense', route('expenses.create')],['Formation', route('trainings.create')],['Rapports', route('reports.index')]] as $qa)
<a class="card quick-card" href="{{ $qa[1] }}"><div class="qi">*</div><div style="font-size:12.5px;font-weight:600">{{ $qa[0] }}</div></a>
@endforeach
</div>
<div class="grid grid-4" style="margin-bottom:14px">
<a class="card" href="{{ route('students.index') }}"><div class="kpi-icon kpi-blue">E</div><div class="stat-num">{{ $eleves }}</div><div class="stat-label">Élèves actifs</div></a>
<a class="card" href="{{ route('teachers.index') }}"><div class="kpi-icon kpi-teal">T</div><div class="stat-num">{{ $enseignants }}</div><div class="stat-label">Enseignants</div></a>
<a class="card" href="{{ route('classes.index') }}"><div class="kpi-icon kpi-orange">C</div><div class="stat-num">{{ $classes }}</div><div class="stat-label">Classes actives</div></a>
<a class="card" href="{{ route('staff.index') }}"><div class="kpi-icon kpi-gray">A</div><div class="stat-num">{{ $employes }}</div><div class="stat-label">Employés admin.</div></a>
</div>
<div class="grid grid-4" style="margin-bottom:14px">
<div class="card"><div class="stat-num" style="font-size:22px">{{ $fmt($encaisseMois) }}</div><div class="stat-label">Encaissé ce mois</div></div>
<div class="card"><div class="stat-num" style="font-size:22px">{{ $fmt($restant) }}</div><div class="stat-label">Restant</div></div>
<div class="card"><div class="stat-num" style="font-size:22px">{{ $fmt($salairesAPayer) }}</div><div class="stat-label">Salaires à payer</div></div>
<div class="card"><div class="stat-num" style="font-size:22px">{{ $fmt($depenses) }}</div><div class="stat-label">Dépenses</div></div>
</div>
<div class="grid grid-3" style="margin-bottom:14px">
<div class="card"><div class="stat-label">Résultat financier du mois</div><div class="stat-num" style="font-size:24px;color:{{ $resultat < 0 ? '#dc2626' : '#16a34a' }}">{{ $fmt($resultat) }}</div><div class="muted">Recettes - dépenses - salaires payés</div></div>
<div class="card"><div class="stat-num" style="font-size:24px">{{ $taux }}%</div><div class="stat-label">Taux de collecte</div><div class="progress"><span style="width:{{ min(100,$taux) }}%"></span></div><div class="muted" style="margin-top:8px">Encaissé: {{ $fmt($encaisseMois) }} | Attendu: {{ $fmt($attendu) }}</div></div>
<div class="card"><div class="stat-num" style="font-size:24px">{{ $absencesToday }}</div><div class="stat-label">Absences aujourd'hui</div><a class="btn btn-sm btn-outline" style="margin-top:10px" href="{{ route('attendance.index') }}">Voir les présences</a></div>
</div>
<div class="grid grid-2" style="margin-bottom:14px">
<div class="card"><h3>Évolution financière — 6 derniers mois</h3>
<table class="data"><thead><tr><th>Mois</th><th>Recettes</th><th>Dépenses</th><th>Salaires</th></tr></thead><tbody>
@foreach($chartMonths as $m)
<tr><td>{{ $m['label'] }}</td><td>{{ number_format($m['recettes'],2,',',' ') }}</td><td>{{ number_format($m['depenses'],2,',',' ') }}</td><td>{{ number_format($m['salaires'],2,',',' ') }}</td></tr>
@endforeach
</tbody></table></div>
<div class="card"><h3>Répartition du mois</h3><div class="empty"><div class="muted">Recettes {{ $fmt($encaisseMois) }} · Dépenses {{ $fmt($depenses) }}</div></div></div>
</div>
<div class="grid grid-2">
<div class="card"><h3>Paiements en retard ({{ $overdue->count() }})</h3>
@if($overdue->isEmpty())<div class="empty"><div class="ok">OK</div>Aucun impayé</div>
@else<div class="table-wrap"><table class="data"><thead><tr><th>ÉLÈVE</th><th>CLASSE</th><th>SOLDE DÛ</th></tr></thead><tbody>
@foreach($overdue as $p)<tr><td>{{ $p->student?->full_name }}</td><td>{{ $p->student?->schoolClass?->nom }}</td><td><span class="money-red">{{ number_format($p->restant,2,',',' ') }} DH</span></td></tr>@endforeach
</tbody></table></div>@endif</div>
<div>
<div class="card" style="margin-bottom:14px"><h3>Enseignants à payer — {{ now()->translatedFormat('F Y') }}</h3><div class="empty"><div class="ok">OK</div>Aucune paie en attente. <a href="{{ route('payroll_teachers.index') }}">Générer la paie</a></div></div>
<div class="card" style="margin-bottom:14px"><h3>Documents expirants (30 j.)</h3><div class="empty"><div class="ok">OK</div>Aucun document expirant</div></div>
<div class="card"><h3>Élèves ayant quitté l'établissement</h3><div class="empty">Aucune sortie enregistrée — <a href="{{ route('departures.index') }}">voir</a></div></div>
</div></div>
@endsection
