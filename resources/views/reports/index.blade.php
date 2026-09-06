@extends('layouts.app')
@section('title',__('Rapports'))
@section('content')
<div class="page-head"><div><h1>{{ __('Rapports') }}</h1></div>
<div class="actions"><button class="btn btn-ghost" onclick="window.print()">{{ __('Imprimer') }}</button><a class="btn btn-ghost" href="{{ route('reports.export', ['tab' => $tab, 'periode' => $periode]) }}">{{ __('Excel') }}</a></div></div>
<form class="filters card" method="GET">
<input type="month" name="periode" value="{{ $periode }}">
<input type="hidden" name="tab" value="{{ $tab }}">
<button class="btn btn-gold">{{ __('Filtrer') }}</button>
</form>
<div class="tabs">
<a class="tab {{ $tab==='scolaires'?'active':'' }}" href="{{ route('reports.index',['tab'=>'scolaires','periode'=>$periode]) }}">{{ __('Rapports scolaires') }}</a>
<a class="tab {{ $tab==='financiers'?'active':'' }}" href="{{ route('reports.index',['tab'=>'financiers','periode'=>$periode]) }}">{{ __('Rapports financiers') }}</a>
<a class="tab {{ $tab==='enseignants'?'active':'' }}" href="{{ route('reports.index',['tab'=>'enseignants','periode'=>$periode]) }}">{{ __('Rapports enseignants') }}</a>
</div>
@if($tab==='scolaires')
<div class="grid grid-2" style="margin-bottom:14px">
<div class="card"><div class="stat-num">{{ $actifs }}</div><div class="stat-label">{{ __('Élèves actifs') }}</div></div>
<div class="card"><div class="stat-num">{{ $sortants }}</div><div class="stat-label">{{ __('Élèves sortants') }}</div></div>
</div>
<div class="card"><h3>{{ __('Effectif par classe/niveau') }}</h3>
<table class="data"><thead><tr><th>{{ __('CLASSE') }}</th><th>{{ __('NIVEAU') }}</th><th>{{ __('EFFECTIF') }}</th><th>{{ __('ABSENCES (mois)') }}</th></tr></thead>
<tbody>@foreach($effectif as $c)<tr><td>{{ $c->nom }}</td><td>{{ $c->niveau }}</td><td>{{ $c->n }}</td><td>{{ $absences[$c->id] ?? 0 }}</td></tr>@endforeach</tbody></table></div>
@elseif($tab==='financiers')
<div class="grid grid-4" style="margin-bottom:14px">
<div class="card"><div class="stat-num" style="font-size:20px">{{ number_format($recettes,2,',',' ') }}</div><div class="stat-label">{{ __('Recettes') }}</div></div>
<div class="card"><div class="stat-num" style="font-size:20px">{{ number_format($depenses,2,',',' ') }}</div><div class="stat-label">{{ __('Dépenses') }}</div></div>
<div class="card"><div class="stat-num" style="font-size:20px">{{ number_format($salaires,2,',',' ') }}</div><div class="stat-label">{{ __('Salaires net') }}</div></div>
<div class="card"><div class="stat-num" style="font-size:20px">{{ number_format($benefice,2,',',' ') }}</div><div class="stat-label">{{ __('Bénéfice') }}</div></div>
</div>
<div class="card"><h3>{{ __('Impayés') }}</h3>
<table class="data"><thead><tr><th>{{ __('ÉLÈVE') }}</th><th>{{ __('CLASSE') }}</th><th>{{ __('RESTANT') }}</th></tr></thead>
<tbody>@foreach($impayes as $p)<tr><td>{{ $p->student?->full_name }}</td><td>{{ $p->student?->schoolClass?->nom }}</td><td>{{ number_format($p->restant,2,',',' ') }}</td></tr>@endforeach</tbody></table></div>
@else
<div class="card"><h3>{{ __('Rapports enseignants') }}</h3>
<table class="data"><thead><tr><th>{{ __('MATRICULE') }}</th><th>{{ __('ENSEIGNANT') }}</th><th>{{ __('MODE') }}</th><th>{{ __('VALEUR') }}</th><th>{{ __('HEURES/SEMAINE') }}</th><th>{{ __('TOTAL PAYÉ') }}</th></tr></thead>
<tbody>
@foreach($teachers as $t)
<tr>
<td>{{ $t->matricule }}</td><td>{{ $t->full_name }}</td><td>{{ $t->mode_paiement }}</td><td>{{ number_format($t->valeur_dh,2,',',' ') }}</td>
<td>{{ $t->subjects->sum('heures_semaine') }}</td>
<td>{{ number_format($t->payrolls()->where('statut','Payé')->sum('net'),2,',',' ') }}</td>
</tr>
@endforeach
</tbody></table></div>
@endif
@endsection
