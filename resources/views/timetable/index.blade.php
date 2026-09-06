@extends('layouts.app')
@section('title','Emploi du temps')
@section('content')
<div class="page-head"><div><h1>Emploi du temps</h1></div>
<div class="actions"><button class="btn btn-ghost" onclick="window.print()">Imprimer</button></div></div>
<form class="filters card" method="GET">
<select name="vue" onchange="this.form.submit()"><option value="classe" @selected($view==='classe')>Vue par classe</option><option value="enseignant" @selected($view==='enseignant')>Vue par enseignant</option></select>
@if($view==='enseignant')
<select name="teacher_id"><option value="">Enseignant…</option>@foreach($teachers as $t)<option value="{{ $t->id }}" @selected($teacherId==$t->id)>{{ $t->full_name }}</option>@endforeach</select>
@else
<select name="class_id"><option value="">Classe…</option>@foreach($classes as $c)<option value="{{ $c->id }}" @selected($classId==$c->id)>{{ $c->nom }}</option>@endforeach</select>
@endif
<button class="btn btn-gold">Afficher</button>
</form>
<div class="grid grid-2">
<div class="card">
<h3>Grille</h3>
<table class="tt-grid"><thead><tr><th>HEURE</th>@foreach($jours as $j)<th>{{ $j }}</th>@endforeach</tr></thead>
<tbody>
@php $hours = ['13:30','14:30','15:30','16:30','17:45']; @endphp
@foreach($hours as $h)
<tr><th>{{ $h }}</th>
@foreach($jours as $j)
<td>@foreach($slots->where('jour',$j) as $slot)
@if(substr((string)$slot->debut,0,5)===$h || ($slot->debut <= $h && $slot->fin > $h))
<div class="slot"><strong>{{ $slot->subject?->nom ?: 'Cours' }}</strong>{{ $slot->teacher?->full_name }}<br>{{ substr($slot->debut,0,5) }}-{{ substr($slot->fin,0,5) }}
<form method="POST" action="{{ route('timetable.destroy',$slot) }}">@csrf @method('DELETE')<button class="btn btn-sm btn-danger" type="submit">Supprimer</button></form>
</div>
@endif
@endforeach</td>
@endforeach
</tr>
@endforeach
</tbody></table>
</div>
<div class="card">
<h3>Ajouter un créneau</h3>
<form method="POST" action="{{ route('timetable.store') }}">@csrf
<div class="form-group"><label>Classe <span class="req">*</span></label><select class="form-select" style="width:100%" name="class_id" required>@foreach($classes as $c)<option value="{{ $c->id }}">{{ $c->nom }}</option>@endforeach</select></div>
<div class="form-group"><label>Matières</label><select class="form-select" style="width:100%" name="subject_id"><option value="">—</option>@foreach($subjects as $s)<option value="{{ $s->id }}">{{ $s->nom }}</option>@endforeach</select></div>
<div class="form-group"><label>Enseignant</label><select class="form-select" style="width:100%" name="teacher_id"><option value="">—</option>@foreach($teachers as $t)<option value="{{ $t->id }}">{{ $t->full_name }}</option>@endforeach</select></div>
<div class="form-group"><label>Jour</label><select class="form-select" style="width:100%" name="jour">@foreach($jours as $j)<option>{{ $j }}</option>@endforeach</select></div>
<div class="form-row"><div class="form-group"><label>Début <span class="req">*</span></label><input class="form-input" style="width:100%" type="time" name="debut" value="13:30" required></div>
<div class="form-group"><label>Fin <span class="req">*</span></label><input class="form-input" style="width:100%" type="time" name="fin" value="14:30" required></div></div>
<div class="form-group"><label>Salle</label><input class="form-input" style="width:100%" name="salle"></div>
<button class="btn btn-gold" type="submit">Ajouter un créneau</button>
</form>
</div>
</div>
@endsection
