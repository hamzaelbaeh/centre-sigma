@extends('layouts.app')
@section('title',__('Détails classe'))
@section('content')
<div class="page-head"><div><h1>{{ $class->nom }}</h1><div class="sub">{{ $class->niveau }} · {{ $class->salle }}</div></div>
<a class="btn btn-ghost" href="{{ route('classes.index') }}">{{ __('Retour') }}</a></div>
<div class="grid grid-2">
<div class="card"><h3>{{ __('Élèves') }} ({{ $class->students->count() }})</h3>
<ul>@foreach($class->students as $s)<li>{{ $s->full_name }}</li>@endforeach</ul></div>
<div class="card"><h3>{{ __('Enseignant') }}</h3><p>{{ $class->teacher?->full_name ?: '—' }}</p>
<a class="btn btn-gold" href="{{ route('timetable.index',['class_id'=>$class->id]) }}">{{ __('Emploi du temps') }}</a></div>
</div>
@endsection
