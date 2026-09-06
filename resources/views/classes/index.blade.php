@extends('layouts.app')
@section('title','Classes')
@section('content')
<div class="page-head"><div><h1>Classes</h1><div class="sub">Année 2026/2027</div></div><a class="btn btn-gold" href="{{ route('classes.create') }}">Nouvelle classe</a></div>
<div class="grid grid-2">
@foreach($classes as $c)
<div class="card class-card">
 <h2>{{ $c->nom }}</h2>
 <p class="muted">{{ $c->niveau }} · Salle {{ $c->salle ?: '—' }}</p>
 <p><strong>{{ $c->occupancy }}</strong> / {{ $c->capacite ?? '—' }} places</p>
 <p>Enseignant principal : {{ $c->teacher?->full_name ?: '—' }}</p>
 <div class="actions" style="margin-top:10px">
  <a class="btn btn-sm btn-ghost" href="{{ route('classes.show',$c) }}">Détails</a>
  <a class="btn btn-sm btn-ghost" href="{{ route('timetable.index',['class_id'=>$c->id]) }}">Emploi du temps</a>
  <a class="btn btn-sm btn-ghost" href="{{ route('classes.edit',$c) }}">Modifier</a>
  <form style="display:inline" method="POST" action="{{ route('classes.destroy',$c) }}" onsubmit="return confirm('Supprimer ?')">@csrf @method('DELETE')<button class="btn btn-sm btn-danger">Supprimer</button></form>
 </div>
</div>
@endforeach
</div>
@endsection
