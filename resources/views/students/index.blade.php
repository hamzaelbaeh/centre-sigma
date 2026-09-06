@extends('layouts.app')
@section('title',__('Élèves'))
@section('content')
<div class="page-head">
 <div><h1>{{ __('Élèves') }}</h1><div class="sub">{{ __('Gestion des élèves') }}</div></div>
 <div class="actions">
  <button type="button" class="btn btn-ghost" onclick="window.print()">{{ __('Imprimer') }}</button>
  <a class="btn btn-ghost" href="{{ route('students.export', request()->query()) }}">{{ __('Excel') }}</a>
  <a class="btn btn-ghost" href="{{ route('students.import') }}">{{ __('Importer') }}</a>
  <a class="btn btn-gold" href="{{ route('students.create') }}">{{ __('Nouvel élève') }}</a>
 </div>
</div>
<form class="filters card" method="GET">
 <input type="text" name="q" value="{{ request('q') }}" placeholder="{{ __('Recherche...') }}">
 <select name="class_id"><option value="">{{ __('Toutes') }}</option>@foreach($classes as $c)<option value="{{ $c->id }}" @selected(request('class_id')==$c->id)>{{ $c->nom }}</option>@endforeach</select>
 <select name="statut"><option value="">{{ __('Tous') }}</option>@foreach(['Actif','Suspendu','Transféré','Abandonné','Diplômé','Exclu'] as $s)<option value="{{ $s }}" @selected(request('statut')==$s)>{{ __($s) }}</option>@endforeach</select>
 <button class="btn btn-gold" type="submit">{{ __('Filtrer') }}</button>
</form>
<div class="card table-wrap">
<table class="data">
<thead><tr><th>{{ __('MATRICULE') }}</th><th>{{ __('ÉLÈVE') }}</th><th>{{ __('DATE DE NAISSANCE') }}</th><th>{{ __('CLASSE') }}</th><th>{{ __('TÉLÉPHONE') }}</th><th>{{ __("DATE D'INSCRIPTION") }}</th><th>{{ __('STATUT') }}</th><th>{{ __('ACTIONS') }}</th></tr></thead>
<tbody>
@forelse($students as $st)
<tr>
 <td>{{ $st->matricule }}</td>
 <td><strong>{{ $st->full_name }}</strong></td>
 <td>{{ optional($st->date_naissance)->format('d/m/Y') }}</td>
 <td>{{ $st->schoolClass?->nom }}</td>
 <td>{{ $st->telephone }}</td>
 <td>{{ optional($st->date_inscription)->format('d/m/Y') }}</td>
 <td><span class="badge {{ $st->statut==='Actif'?'badge-green':'badge-gray' }}">{{ __($st->statut) }}</span></td>
 <td>
  <a class="btn btn-sm btn-ghost" href="{{ route('students.show',$st) }}">{{ __('Fiche') }}</a>
  <a class="btn btn-sm btn-ghost" href="{{ route('students.edit',$st) }}">{{ __('Modifier') }}</a>
  <form style="display:inline" method="POST" action="{{ route('students.destroy',$st) }}" onsubmit="return confirm(@json(__('Supprimer ?')))">@csrf @method('DELETE')<button class="btn btn-sm btn-danger" type="submit">{{ __('Supprimer') }}</button></form>
 </td>
</tr>
@empty
<tr><td colspan="8" class="empty">{{ __('Aucun élève') }}</td></tr>
@endforelse
</tbody></table>
</div>
{{ $students->links() }}
@endsection
