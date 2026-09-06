@extends('layouts.app')
@section('title',__('Employés administratifs'))
@section('content')
<div class="page-head"><div><h1>{{ __('Employés administratifs') }}</h1></div>
<div class="actions"><a class="btn btn-outline" href="{{ route('staff_payroll.index') }}">{{ __('Paie employés') }}</a><a class="btn btn-gold" href="{{ route('staff.create') }}">{{ __('Nouvel employé') }}</a></div></div>
<div class="card table-wrap"><table class="data">
<thead><tr><th>{{ __('MATRICULE') }}</th><th>{{ __('EMPLOYÉS ADMINISTRATIFS') }}</th><th>{{ __('POSTE') }}</th><th>{{ __('TÉLÉPHONE') }}</th><th>{{ __("DATE D'EMBAUCHE") }}</th><th>{{ __('MONTANT') }}</th><th>{{ __('STATUT') }}</th><th>{{ __('ACTIONS') }}</th></tr></thead>
<tbody>
@foreach($staff as $s)
<tr>
<td>{{ $s->matricule }}</td><td><strong>{{ $s->full_name }}</strong></td><td>{{ __($s->poste) }}</td><td>{{ $s->telephone }}</td>
<td>{{ optional($s->date_embauche)->format('d/m/Y') }}</td><td>{{ number_format($s->salaire,2,',',' ') }} DH</td>
<td><span class="badge {{ $s->statut==='Actif'?'badge-green':'badge-gray' }}">{{ $s->statut }}</span></td>
<td><a class="btn btn-sm btn-ghost" href="{{ route('staff.edit',$s) }}">{{ __('Modifier') }}</a>
<form style="display:inline" method="POST" action="{{ route('staff.destroy',$s) }}" onsubmit="return confirm(@json(__('Supprimer ?')))">@csrf @method('DELETE')<button class="btn btn-sm btn-danger">{{ __('Supprimer') }}</button></form></td>
</tr>
@endforeach
</tbody></table></div>{{ $staff->links() }}
@endsection
