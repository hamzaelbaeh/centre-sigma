@extends('layouts.app')
@section('title',__('Fiche élève'))
@section('content')
<div class="page-head">
 <div><h1>{{ __('Fiche —') }} {{ $student->full_name }}</h1><div class="sub">{{ $student->matricule }} · {{ __($student->statut) }}</div></div>
 <div class="actions"><a class="btn btn-ghost" href="{{ route('students.edit',$student) }}">{{ __('Modifier') }}</a><a class="btn btn-ghost" href="{{ route('students.index') }}">{{ __('Retour') }}</a></div>
</div>
<div class="grid grid-2">
<div class="card">
 <p><strong>{{ __('Classe:') }}</strong> {{ $student->schoolClass?->nom }}</p>
 <p><strong>{{ __('Naissance:') }}</strong> {{ optional($student->date_naissance)->format('d/m/Y') }} — {{ $student->lieu_naissance }}</p>
 <p><strong>{{ __('Sexe:') }}</strong> {{ $student->sexe }} · <strong>{{ __('CIN:') }}</strong> {{ $student->cin }}</p>
 <p><strong>{{ __('Tél:') }}</strong> {{ $student->telephone }} · <strong>{{ __('Email:') }}</strong> {{ $student->email }}</p>
 <p><strong>{{ __('Adresse:') }}</strong> {{ $student->adresse }}</p>
 <p><strong>{{ __('Inscription:') }}</strong> {{ optional($student->date_inscription)->format('d/m/Y') }}</p>
 <p><strong>{{ __('Massar:') }}</strong> {{ $student->code_massar }}</p>
</div>
<div class="card">
 <h3>{{ __('Matières inscrites') }}</h3>
 <table class="data"><thead><tr><th>{{ __('Matière') }}</th><th>{{ __('Prix facturé') }}</th></tr></thead><tbody>
 @forelse($student->subjects as $sub)
 @php
   $prix = $sub->pivot->prix !== null && $sub->pivot->prix !== '' ? (float)$sub->pivot->prix : (float)$sub->prix;
 @endphp
 <tr><td>{{ $sub->nom }}</td><td>{{ number_format($prix, 2, ',', ' ') }} DH</td></tr>
 @empty
 <tr><td colspan="2" class="muted">{{ __('Aucune matière') }}</td></tr>
 @endforelse
 </tbody></table>
 <p style="margin-top:8px"><strong>{{ __('Total mensuel estimé') }} :</strong> {{ number_format($student->monthlyFeeAmount(), 2, ',', ' ') }} DH</p>

 <h3>{{ __('Parents / Tuteurs') }}</h3>
 <ul>@forelse($student->parents as $p)<li>{{ $p->full_name }} — {{ $p->telephone }}</li>@empty<li class="muted">{{ __('Aucun') }}</li>@endforelse</ul>
 <h3>{{ __('Paiements') }}</h3>
 <table class="data"><thead><tr><th>{{ __('TYPE') }}</th><th>{{ __('MONTANT') }}</th><th>{{ __('PAYÉ') }}</th><th>{{ __('STATUT') }}</th></tr></thead><tbody>
 @foreach($student->payments as $pay)<tr><td>{{ $pay->type }}</td><td>{{ number_format($pay->montant,2,',',' ') }}</td><td>{{ number_format($pay->paye,2,',',' ') }}</td><td>{{ __($pay->statut) }}</td></tr>@endforeach
 </tbody></table>
</div>
</div>
@endsection
