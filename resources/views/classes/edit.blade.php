@extends('layouts.app')
@section('title',__('Modifier classe'))
@section('content')
<div class="page-head"><div><h1>{{ __('Modifier classe') }}</h1></div><a class="btn btn-ghost" href="{{ route('classes.index') }}">{{ __('Retour') }}</a></div>
<form class="card" method="POST" action="{{ route('classes.update',$class) }}">@csrf @method('PUT')

<div class="form-group"><label>{{ __('Classe') }} <span class="req">*</span></label><input class="form-input" style="width:100%" name="nom" value="{{ old('nom',$class->nom ?? '') }}" required></div>
<div class="form-row">
<div class="form-group"><label>{{ __('Niveau') }}</label><select class="form-select" style="width:100%" name="niveau">@foreach(['','Maternelle','Primaire','Collège','Lycée','Formation'] as $n)<option value="{{ $n }}" @selected(old('niveau',$class->niveau ?? '')==$n)>{{ $n ? __($n) : '—' }}</option>@endforeach</select></div>
<div class="form-group">
  <label>{{ __('Salle') }}</label>
  <select class="form-select" style="width:100%" name="room_id">
    <option value="">—</option>
    @foreach($rooms as $room)
      <option value="{{ $room->id }}" @selected(old('room_id', $class->room_id ?? '')==$room->id)>{{ $room->nom }}</option>
    @endforeach
  </select>
</div>
</div>
<div class="form-row">
<div class="form-group"><label>{{ __('Enseignant principal') }}</label><select class="form-select" style="width:100%" name="teacher_id"><option value="">—</option>@foreach($teachers as $t)<option value="{{ $t->id }}" @selected(old('teacher_id',$class->teacher_id ?? '')==$t->id)>{{ $t->full_name }}</option>@endforeach</select></div>
<div class="form-group"><label>{{ __('Capacité') }}</label><input class="form-input" style="width:100%" type="number" name="capacite" value="{{ old('capacite',$class->capacite ?? 30) }}"></div>
</div>
<button class="btn btn-gold" type="submit">{{ __('Enregistrer') }}</button></form>

<div class="card" style="margin-top:16px">
  <h3>{{ __('Affecter des élèves') }}</h3>
  <p class="sub" style="margin-bottom:10px">{{ __('Cochez les élèves à affecter à cette classe. Décochez pour retirer un élève (class_id vide).') }}</p>
  <form method="POST" action="{{ route('classes.students.sync', $class) }}">@csrf
    <div class="checkboxes" style="max-height:320px">
      @foreach($assignableStudents as $s)
        <label>
          <input type="checkbox" name="students[]" value="{{ $s->id }}" @checked(collect(old('students', $class->students->pluck('id')->all()))->contains($s->id))>
          {{ $s->full_name }}
          @if($s->class_id && $s->class_id != $class->id)
            <span class="muted">({{ $s->schoolClass?->nom }})</span>
          @elseif(!$s->class_id)
            <span class="muted">({{ __('Non affecté') }})</span>
          @endif
        </label>
      @endforeach
    </div>
    <button class="btn btn-gold" type="submit" style="margin-top:12px">{{ __('Enregistrer l\'affectation') }}</button>
  </form>
</div>
@endsection
