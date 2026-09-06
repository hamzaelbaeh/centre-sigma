@extends('layouts.app')
@section('title',__('Formation'))
@section('content')
<div class="page-head"><div><h1>{{ $training->nom }}</h1><div class="sub">{{ $training->formateur }} · {{ number_format($training->prix,2,',',' ') }} DH</div></div>
<a class="btn btn-ghost" href="{{ route('trainings.edit',$training) }}">{{ __('Modifier') }}</a></div>
<div class="grid grid-2">
<div class="card">
<h3>{{ __('Inscrire') }}</h3>
<form method="POST" action="{{ route('trainings.enroll',$training) }}">@csrf
<div class="form-group"><label>{{ __('Élève') }}</label><select class="form-select" style="width:100%" name="student_id"><option value="">{{ __('— Externe —') }}</option>@foreach($students as $s)<option value="{{ $s->id }}">{{ $s->full_name }}</option>@endforeach</select></div>
<div class="form-group"><label>{{ __('Nom si externe') }}</label><input class="form-input" style="width:100%" name="nom_externe"></div>
<div class="form-group"><label>{{ __('Téléphone') }}</label><input class="form-input" style="width:100%" name="telephone"></div>
<div class="form-group"><label>{{ __('Montant payé') }}</label><input class="form-input" style="width:100%" type="number" step="0.01" name="montant_paye" value="0"></div>
<button class="btn btn-gold" type="submit">{{ __('Inscrire') }}</button>
</form>
</div>
<div class="card">
<h3>{{ __('Participants') }}</h3>
<table class="data"><thead><tr><th>{{ __('NOM') }}</th><th>{{ __('TÉL') }}</th><th>{{ __('PAYÉ') }}</th><th>{{ __('RÉSULTAT') }}</th><th>{{ __('CERTIFICAT') }}</th><th></th></tr></thead>
<tbody>
@foreach($training->participants as $p)
<tr>
<form method="POST" action="{{ route('trainings.participant',$p) }}">@csrf @method('PUT')
<td>{{ $p->display_name }}</td><td>{{ $p->telephone }}</td>
<td><input class="form-input" style="width:90px" type="number" step="0.01" name="montant_paye" value="{{ $p->montant_paye }}"></td>
<td><input class="form-input" style="width:100px" name="resultat" value="{{ $p->resultat }}"></td>
<td><input type="checkbox" name="certificat" value="1" @checked($p->certificat)> Certificat délivré</td>
<td><button class="btn btn-sm btn-gold">OK</button></td>
</form>
</tr>
@endforeach
</tbody></table>
</div>
</div>
@endsection
