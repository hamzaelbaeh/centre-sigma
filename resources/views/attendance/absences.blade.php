@extends('layouts.app')
@section('title',__('État des absences'))
@section('content')
<div class="page-head"><div><h1>{{ __('État des absences') }}</h1></div><a class="btn btn-ghost" href="{{ route('attendance.index') }}">{{ __('Retour') }}</a></div>
<div class="card table-wrap"><table class="data">
<thead><tr><th>{{ __('DATE') }}</th><th>{{ __('ÉLÈVE') }}</th><th>{{ __('CLASSE') }}</th><th>{{ __('MATIÈRE') }}</th><th>{{ __('JUSTIFIÉ') }}</th><th>{{ __('MOTIF') }}</th></tr></thead>
<tbody>
@foreach($absences as $a)
<tr><td>{{ $a->date->format('d/m/Y') }}</td><td>{{ $a->student?->full_name }}</td><td>{{ $a->student?->schoolClass?->nom }}</td><td>{{ $a->subject?->nom }}</td><td>{{ $a->justifie?__('Oui'):__('Non') }}</td><td>{{ $a->motif }}</td></tr>
@endforeach
</tbody></table></div>{{ $absences->links() }}
@endsection
