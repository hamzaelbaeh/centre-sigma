@extends('layouts.app')
@section('title','Paie employés')
@section('content')
<div class="page-head"><div><h1>Paie employés</h1></div>
<form method="POST" action="{{ route('staff_payroll.generate') }}">@csrf<input type="hidden" name="periode" value="{{ $periode }}"><button class="btn btn-gold">Générer la paie</button></form></div>
<form class="filters card" method="GET"><input type="month" name="periode" value="{{ $periode }}"><button class="btn btn-gold">Filtrer</button></form>
<div class="card table-wrap"><table class="data">
<thead><tr><th>EMPLOYÉS ADMIN.</th><th>POSTE</th><th>BRUT</th><th>PRIMES</th><th>AVANCES</th><th>RETENUES</th><th>NET</th><th>STATUT</th><th>ACTIONS</th></tr></thead>
<tbody>
@forelse($payrolls as $p)
<tr>
<td>{{ $p->staff?->full_name }}</td><td>{{ $p->staff?->poste }}</td>
<td>{{ number_format($p->brut,2,',',' ') }}</td><td>{{ number_format($p->primes,2,',',' ') }}</td><td>{{ number_format($p->avances,2,',',' ') }}</td><td>{{ number_format($p->retenues,2,',',' ') }}</td>
<td><strong>{{ number_format($p->net,2,',',' ') }}</strong></td><td>{{ $p->statut }}</td>
<td>
<details><summary class="btn btn-sm btn-ghost">Modifier</summary>
<form method="POST" action="{{ route('staff_payroll.update',$p) }}" style="padding:8px">@csrf @method('PUT')
<input class="form-input" type="number" step="0.01" name="brut" value="{{ $p->brut }}" placeholder="Brut">
<input class="form-input" type="number" step="0.01" name="primes" value="{{ $p->primes }}" placeholder="Primes">
<input class="form-input" type="number" step="0.01" name="avances" value="{{ $p->avances }}" placeholder="Avances">
<input class="form-input" type="number" step="0.01" name="retenues" value="{{ $p->retenues }}" placeholder="Retenues">
<button class="btn btn-sm btn-gold">OK</button>
</form></details>
@if($p->statut!=='Payé')<form method="POST" action="{{ route('staff_payroll.pay',$p) }}">@csrf<button class="btn btn-sm btn-gold">Payer</button></form>@endif
</td>
</tr>
@empty<tr><td colspan="9" class="empty">Aucune fiche — NET = BRUT + PRIMES − AVANCES − RETENUES</td></tr>@endforelse
</tbody></table></div>
@endsection
