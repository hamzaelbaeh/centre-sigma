@extends('layouts.app')
@section('title',__('Classes'))
@section('content')
<div class="page-head"><div><h1>{{ __('Classes') }}</h1><div class="sub">{{ __('Année 2026/2027') }}</div></div><a class="btn btn-gold" href="{{ route('classes.create') }}">{{ __('Nouvelle classe') }}</a></div>
<div class="grid grid-2">
@foreach($classes as $c)
<div class="card class-card">
 <h2>{{ $c->nom }}</h2>
 <p class="muted">{{ $c->niveau }} · {{ __('Salle') }} {{ $c->room?->nom ?? ($c->salle ?: '—') }}</p>
 <p><strong>{{ $c->occupancy }}</strong> / {{ $c->capacite ?? '—' }} {{ __('places') }}</p>
 <p>{{ __('Enseignant principal :') }} {{ $c->teacher?->full_name ?: '—' }}</p>
 <div class="actions" style="margin-top:10px">
  <a class="btn btn-sm btn-ghost" href="{{ route('classes.show',$c) }}">{{ __('Détails') }}</a>
  <a class="btn btn-sm btn-ghost" href="{{ route('timetable.index',['class_id'=>$c->id]) }}">{{ __('Emploi du temps') }}</a>
  <a class="btn btn-sm btn-ghost" href="{{ route('classes.edit',$c) }}">{{ __('Modifier') }}</a>
  <form style="display:inline" method="POST" action="{{ route('classes.destroy',$c) }}" onsubmit="return confirm(@json(__('Supprimer ?')))">@csrf @method('DELETE')<button class="btn btn-sm btn-danger">{{ __('Supprimer') }}</button></form>
 </div>
</div>
@endforeach
</div>
@endsection
