@extends('layouts.app')
@section('title','État des absences')
@section('content')
<div class="page-head"><div><h1>État des absences</h1></div><a class="btn btn-ghost" href="{{ route('attendance.index') }}">Retour</a></div>
<div class="card table-wrap"><table class="data">
<thead><tr><th>DATE</th><th>ÉLÈVE</th><th>CLASSE</th><th>MATIÈRE</th><th>JUSTIFIÉ</th><th>MOTIF</th></tr></thead>
<tbody>
@foreach($absences as $a)
<tr><td>{{ $a->date->format('d/m/Y') }}</td><td>{{ $a->student?->full_name }}</td><td>{{ $a->student?->schoolClass?->nom }}</td><td>{{ $a->subject?->nom }}</td><td>{{ $a->justifie?'Oui':'Non' }}</td><td>{{ $a->motif }}</td></tr>
@endforeach
</tbody></table></div>{{ $absences->links() }}
@endsection
