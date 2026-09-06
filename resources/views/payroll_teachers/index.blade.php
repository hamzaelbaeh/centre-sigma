@extends('layouts.app')
@section('title','Paie enseignants')
@section('content')
<div class="page-head"><div><h1>Paie enseignants</h1></div>
<div class="actions">
<a class="btn btn-outline" href="{{ route('documents.print',['type'=>'salaires']) }}" target="_blank">État des salaires</a>
<form method="POST" action="{{ route('payroll_teachers.generate') }}">@csrf<input type="hidden" name="periode" value="{{ $periode }}"><button class="btn btn-gold">Générer la paie</button></form>
</div></div>
<form class="filters card" method="GET"><input type="month" name="periode" value="{{ $periode }}"><button class="btn btn-gold">Filtrer</button></form>
<div class="card table-wrap">
@if($payrolls->isEmpty())
<div class="empty"><div class="ok">OK</div>Aucune fiche de paie</div>
@else
<table class="data">
<thead><tr><th>ENSEIGNANTS</th><th>MODE DE PAIEMENT</th><th>BASE</th><th>%/TAUX</th><th>BRUT</th><th>PRIMES</th><th>AVANCES</th><th>RETENUES</th><th>NET</th><th>STATUT</th><th>ACTIONS</th></tr></thead>
<tbody>
@foreach($payrolls as $p)
<tr>
<td>{{ $p->teacher?->full_name }}</td><td>{{ $p->mode_paiement }}</td><td>{{ number_format($p->base,2,',',' ') }}</td><td>{{ number_format($p->taux,2,',',' ') }}</td>
<td>{{ number_format($p->brut,2,',',' ') }}</td><td>{{ number_format($p->primes,2,',',' ') }}</td><td>{{ number_format($p->avances,2,',',' ') }}</td><td>{{ number_format($p->retenues,2,',',' ') }}</td>
<td><strong>{{ number_format($p->net,2,',',' ') }}</strong></td><td>{{ $p->statut }}</td>
<td>@if($p->statut!=='Payé')<form method="POST" action="{{ route('payroll_teachers.pay',$p) }}">@csrf<button class="btn btn-sm btn-gold">Payer</button></form>@endif</td>
</tr>
@endforeach
</tbody></table>
@endif
</div>
@endsection
