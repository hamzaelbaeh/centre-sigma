@extends('layouts.app')
@section('title','Fiche élève')
@section('content')
<div class="page-head">
 <div><h1>Fiche — {{ $student->full_name }}</h1><div class="sub">{{ $student->matricule }} · {{ $student->statut }}</div></div>
 <div class="actions"><a class="btn btn-ghost" href="{{ route('students.edit',$student) }}">Modifier</a><a class="btn btn-ghost" href="{{ route('students.index') }}">Retour</a></div>
</div>
<div class="grid grid-2">
<div class="card">
 <p><strong>Classe:</strong> {{ $student->schoolClass?->nom }}</p>
 <p><strong>Naissance:</strong> {{ optional($student->date_naissance)->format('d/m/Y') }} — {{ $student->lieu_naissance }}</p>
 <p><strong>Sexe:</strong> {{ $student->sexe }} · <strong>CIN:</strong> {{ $student->cin }}</p>
 <p><strong>Tél:</strong> {{ $student->telephone }} · <strong>Email:</strong> {{ $student->email }}</p>
 <p><strong>Adresse:</strong> {{ $student->adresse }}</p>
 <p><strong>Inscription:</strong> {{ optional($student->date_inscription)->format('d/m/Y') }}</p>
 <p><strong>Massar:</strong> {{ $student->code_massar }}</p>
</div>
<div class="card">
 <h3>Parents / Tuteurs</h3>
 <ul>@forelse($student->parents as $p)<li>{{ $p->full_name }} — {{ $p->telephone }}</li>@empty<li class="muted">Aucun</li>@endforelse</ul>
 <h3>Paiements</h3>
 <table class="data"><thead><tr><th>TYPE</th><th>MONTANT</th><th>PAYÉ</th><th>STATUT</th></tr></thead><tbody>
 @foreach($student->payments as $pay)<tr><td>{{ $pay->type }}</td><td>{{ number_format($pay->montant,2,',',' ') }}</td><td>{{ number_format($pay->paye,2,',',' ') }}</td><td>{{ $pay->statut }}</td></tr>@endforeach
 </tbody></table>
</div>
</div>
@endsection
