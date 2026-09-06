@extends('layouts.app')
@section('title',__('Présences'))
@section('content')
<div class="page-head"><div><h1>{{ __('Présences') }}</h1></div>
<div class="actions"><a class="btn btn-outline" href="{{ route('attendance.absences') }}">{{ __('État des absences') }}</a></div></div>
<form class="filters card" method="GET">
<select name="class_id">@foreach($classes as $c)<option value="{{ $c->id }}" @selected($classId==$c->id)>{{ $c->nom }}</option>@endforeach</select>
<input type="date" name="date" value="{{ $date }}">
<select name="subject_id"><option value="">{{ __('Matière…') }}</option>@foreach($subjects as $s)<option value="{{ $s->id }}" @selected($subjectId==$s->id)>{{ $s->nom }}</option>@endforeach</select>
<button class="btn btn-gold">{{ __('Charger') }}</button>
</form>
<div class="grid grid-2">
<div class="card">
<form method="POST" action="{{ route('attendance.store') }}">@csrf
<input type="hidden" name="class_id" value="{{ $classId }}"><input type="hidden" name="date" value="{{ $date }}"><input type="hidden" name="subject_id" value="{{ $subjectId }}">
<div class="table-wrap"><table class="data">
<thead><tr><th>{{ __('ÉLÈVE') }}</th><th>{{ __('PRÉSENT') }}</th><th>{{ __('ABSENT') }}</th><th>{{ __('RETARD') }}</th><th>{{ __('JUSTIFIÉ') }}</th><th>{{ __('MOTIF') }}</th></tr></thead>
<tbody>
@foreach($students as $st)
@php $ex=$existing->get($st->id); $stt=$ex->status ?? 'present'; @endphp
<tr>
<td>{{ $st->full_name }}</td>
<td><input type="radio" name="records[{{ $st->id }}][status]" value="present" @checked($stt==='present')></td>
<td><input type="radio" name="records[{{ $st->id }}][status]" value="absent" @checked($stt==='absent')></td>
<td><input type="radio" name="records[{{ $st->id }}][status]" value="retard" @checked($stt==='retard')></td>
<td><input type="checkbox" name="records[{{ $st->id }}][justifie]" value="1" @checked($ex->justifie ?? false)></td>
<td><input class="form-input" name="records[{{ $st->id }}][motif]" value="{{ $ex->motif ?? '' }}"></td>
</tr>
@endforeach
</tbody></table></div>
<button class="btn btn-gold" style="margin-top:12px" type="submit">{{ __('Enregistrer les présences') }}</button>
</form>
</div>
<div class="card"><h3>{{ __('Statistiques du mois') }}</h3>
<p>{{ __("Présents :") }} <strong>{{ $monthStats['present'] }}</strong></p>
<p>{{ __("Absents :") }} <strong>{{ $monthStats['absent'] }}</strong></p>
<p>{{ __("Retards :") }} <strong>{{ $monthStats['retard'] }}</strong></p>
</div>
</div>
@endsection
