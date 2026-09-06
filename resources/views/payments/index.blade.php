@extends('layouts.app')
@section('title','Paiements élèves')
@section('content')
<div class="page-head"><div><h1>Paiements élèves</h1></div>
<div class="actions">
<a class="btn btn-outline" href="{{ route('payments.impayes') }}">État des impayés</a>
<a class="btn btn-ghost" href="#">Excel</a>
<form method="POST" action="{{ route('payments.generate') }}">@csrf
<input type="hidden" name="periode" value="{{ $periode }}">
<button class="btn btn-gold" type="submit">Générer les mensualités</button>
</form>
</div></div>
<div class="grid grid-4" style="margin-bottom:14px">
<div class="card"><div class="stat-num" style="font-size:20px">{{ number_format($today,2,',',' ') }} DH</div><div class="stat-label">Encaissé aujourd'hui</div></div>
<div class="card"><div class="stat-num" style="font-size:20px">{{ number_format($month,2,',',' ') }} DH</div><div class="stat-label">Encaissé ce mois</div></div>
<div class="card"><div class="stat-num" style="font-size:20px">{{ number_format($total,2,',',' ') }} DH</div><div class="stat-label">Total encaissé</div></div>
<div class="card"><div class="stat-num" style="font-size:20px">{{ number_format($remaining,2,',',' ') }} DH</div><div class="stat-label">Restant</div></div>
</div>
<form class="filters card" method="GET">
<input type="month" name="periode" value="{{ $periode }}">
<input name="q" value="{{ request('q') }}" placeholder="Rechercher...">
<button class="btn btn-gold">Filtrer</button>
</form>
<div class="card table-wrap"><table class="data">
<thead><tr><th>ÉLÈVE</th><th>CLASSE</th><th>TYPE</th><th>MONTANT</th><th>PAYÉ</th><th>RESTANT</th><th>STATUT</th><th>ACTIONS</th></tr></thead>
<tbody>
@forelse($payments as $p)
<tr>
<td>{{ $p->student?->full_name }}</td><td>{{ $p->student?->schoolClass?->nom }}</td><td>{{ $p->type }}</td>
<td>{{ number_format($p->montant,2,',',' ') }}</td><td>{{ number_format($p->paye,2,',',' ') }}</td>
<td>{{ number_format($p->restant,2,',',' ') }}</td>
<td><span class="badge {{ $p->statut==='Soldé'?'badge-green':($p->statut==='Partiel'?'badge-amber':'badge-red') }}">{{ $p->statut }}</span></td>
<td>
@if($p->restant>0)
<details><summary class="btn btn-sm btn-gold">Encaisser</summary>
<form method="POST" action="{{ route('payments.encaisser',$p) }}" style="padding:8px;min-width:220px">@csrf
<div class="form-group"><label>Date <span class="req">*</span></label><input class="form-input" style="width:100%" type="date" name="date" value="{{ date('Y-m-d') }}" required></div>
<div class="form-group"><label>Montant (DH) <span class="req">*</span></label><input class="form-input" style="width:100%" type="number" step="0.01" name="montant" value="{{ $p->restant }}" required><div class="muted">Paiement partiel autorisé.</div></div>
<div class="form-group"><label>Mode de paiement</label><select class="form-select" style="width:100%" name="mode">@foreach(['Espèces','Virement','Chèque','Carte'] as $m)<option>{{ $m }}</option>@endforeach</select></div>
<div class="form-group"><label>Référence</label><input class="form-input" style="width:100%" name="reference"></div>
<button class="btn btn-gold" type="submit">Valider</button>
</form></details>
@endif
</td>
</tr>
@empty<tr><td colspan="8" class="empty">Aucune facture</td></tr>@endforelse
</tbody></table></div>{{ $payments->links() }}
@endsection
