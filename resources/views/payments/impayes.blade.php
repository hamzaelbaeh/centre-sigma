@extends('layouts.app')
@section('title','État des impayés')
@section('content')
<div class="page-head"><div><h1>État des impayés</h1></div><a class="btn btn-ghost" href="{{ route('payments.index') }}">Retour</a></div>
<div class="card table-wrap"><table class="data">
<thead><tr><th>ÉLÈVE</th><th>CLASSE</th><th>TYPE</th><th>RESTANT</th><th>STATUT</th></tr></thead>
<tbody>@foreach($payments as $p)<tr><td>{{ $p->student?->full_name }}</td><td>{{ $p->student?->schoolClass?->nom }}</td><td>{{ $p->type }}</td><td><span class="money-red">{{ number_format($p->restant,2,',',' ') }} DH</span></td><td>{{ $p->statut }}</td></tr>@endforeach</tbody>
</table></div>
@endsection
