@extends('layouts.app')
@section('title',__('Détails classe'))
@section('content')
<div class="page-head"><div><h1>{{ $class->nom }}</h1><div class="sub">{{ $class->niveau }} · {{ __('Salle') }} {{ $class->room?->nom ?? ($class->salle ?: '—') }}</div></div>
<div class="actions">
  <a class="btn btn-ghost" href="{{ route('classes.edit',$class) }}">{{ __('Modifier') }}</a>
  <a class="btn btn-ghost" href="{{ route('classes.index') }}">{{ __('Retour') }}</a>
</div></div>
<div class="grid grid-2">
<div class="card">
  <h3>{{ __('Élèves') }} ({{ $class->students->count() }})</h3>
  <ul>@forelse($class->students as $s)<li>{{ $s->full_name }}@if($s->niveau_scolaire) <span class="muted">· {{ __($s->niveau_scolaire) }}</span>@endif</li>@empty<li class="muted">{{ __('Aucun élève') }}</li>@endforelse</ul>

  <h3 style="margin-top:16px">{{ __('Affecter des élèves') }}</h3>
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
<div class="card"><h3>{{ __('Enseignant') }}</h3><p>{{ $class->teacher?->full_name ?: '—' }}</p>
<a class="btn btn-gold" href="{{ route('timetable.index',['class_id'=>$class->id]) }}">{{ __('Emploi du temps') }}</a></div>
</div>
@endsection
