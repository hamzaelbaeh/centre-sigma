@extends('layouts.app')
@section('title','Employés administratifs')
@section('content')
<div class="page-head"><div><h1>Employés administratifs</h1></div>
<div class="actions"><a class="btn btn-outline" href="{{ route('staff_payroll.index') }}">Paie employés</a><a class="btn btn-gold" href="{{ route('staff.create') }}">Nouvel employé</a></div></div>
<div class="card table-wrap"><table class="data">
<thead><tr><th>MATRICULE</th><th>EMPLOYÉS ADMINISTRATIFS</th><th>POSTE</th><th>TÉLÉPHONE</th><th>DATE D'EMBAUCHE</th><th>MONTANT</th><th>STATUT</th><th>ACTIONS</th></tr></thead>
<tbody>
@foreach($staff as $s)
<tr>
<td>{{ $s->matricule }}</td><td><strong>{{ $s->full_name }}</strong></td><td>{{ $s->poste }}</td><td>{{ $s->telephone }}</td>
<td>{{ optional($s->date_embauche)->format('d/m/Y') }}</td><td>{{ number_format($s->salaire,2,',',' ') }} DH</td>
<td><span class="badge {{ $s->statut==='Actif'?'badge-green':'badge-gray' }}">{{ $s->statut }}</span></td>
<td><a class="btn btn-sm btn-ghost" href="{{ route('staff.edit',$s) }}">Modifier</a>
<form style="display:inline" method="POST" action="{{ route('staff.destroy',$s) }}" onsubmit="return confirm('Supprimer ?')">@csrf @method('DELETE')<button class="btn btn-sm btn-danger">Supprimer</button></form></td>
</tr>
@endforeach
</tbody></table></div>{{ $staff->links() }}
@endsection
