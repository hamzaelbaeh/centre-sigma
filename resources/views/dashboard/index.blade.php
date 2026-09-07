@extends('layouts.app')
@section('title',__('Tableau de bord'))
@section('content')
@php $fmt = fn($n) => number_format((float)$n, 2, ',', ' ').' DH'; @endphp
<div class="page-head">
 <div><h1>{{ __('Tableau de bord') }}</h1><div class="sub">{{ now()->translatedFormat('l d/m/Y') }} · {{ now()->translatedFormat('F Y') }}</div></div>
 <div class="actions">
  <a class="btn btn-outline" href="{{ route('payments.index') }}">{{ __('Paiements élèves') }}</a>
  <a class="btn btn-gold" href="{{ route('attendance.index') }}">{{ __('Présences') }}</a>
 </div>
</div>
<div class="grid grid-6" style="margin-bottom:14px">
@foreach([[__('Nouvel élève'), route('students.create')],[__('Paiement'), route('payments.index')],[__('Présences'), route('attendance.index')],[__('Nouvelle dépense'), route('expenses.create')],[__('Formation'), route('trainings.create')],[__('Rapports'), route('reports.index')]] as $qa)
<a class="card quick-card" href="{{ $qa[1] }}"><div class="qi">*</div><div style="font-size:12.5px;font-weight:600">{{ $qa[0] }}</div></a>
@endforeach
</div>
<div class="grid grid-4" style="margin-bottom:14px">
<a class="card" href="{{ route('students.index') }}"><div class="kpi-icon kpi-blue">E</div><div class="stat-num">{{ $eleves }}</div><div class="stat-label">{{ __('Élèves actifs') }}</div></a>
<a class="card" href="{{ route('teachers.index') }}"><div class="kpi-icon kpi-teal">T</div><div class="stat-num">{{ $enseignants }}</div><div class="stat-label">{{ __('Enseignants') }}</div></a>
<a class="card" href="{{ route('classes.index') }}"><div class="kpi-icon kpi-orange">C</div><div class="stat-num">{{ $classes }}</div><div class="stat-label">{{ __('Classes actives') }}</div></a>
<a class="card" href="{{ route('staff.index') }}"><div class="kpi-icon kpi-gray">A</div><div class="stat-num">{{ $employes }}</div><div class="stat-label">{{ __('Employés admin.') }}</div></a>
</div>
<div class="grid grid-4" style="margin-bottom:14px">
<div class="card"><div class="stat-num" style="font-size:22px">{{ $fmt($encaisseMois) }}</div><div class="stat-label">{{ __('Encaissé ce mois') }}</div></div>
<div class="card"><div class="stat-num" style="font-size:22px">{{ $fmt($restant) }}</div><div class="stat-label">{{ __('Restant') }}</div></div>
<div class="card"><div class="stat-num" style="font-size:22px">{{ $fmt($salairesAPayer) }}</div><div class="stat-label">{{ __('Salaires à payer') }}</div></div>
<div class="card"><div class="stat-num" style="font-size:22px">{{ $fmt($depenses) }}</div><div class="stat-label">{{ __('Dépenses') }}</div></div>
</div>
<div class="grid grid-3" style="margin-bottom:14px">
<div class="card"><div class="stat-label">{{ __('Résultat financier du mois') }}</div><div class="stat-num" style="font-size:24px;color:{{ $resultat < 0 ? '#dc2626' : '#16a34a' }}">{{ $fmt($resultat) }}</div><div class="muted">{{ __('Recettes - dépenses - salaires payés') }}</div></div>
<div class="card"><div class="stat-num" style="font-size:24px">{{ $taux }}%</div><div class="stat-label">{{ __('Taux de collecte') }}</div><div class="progress"><span style="width:{{ min(100,$taux) }}%"></span></div><div class="muted" style="margin-top:8px">{{ __('Encaissé:') }} {{ $fmt($encaisseMois) }} | {{ __('Attendu:') }} {{ $fmt($attendu) }}</div></div>
<div class="card"><div class="stat-num" style="font-size:24px">{{ $absencesToday }}</div><div class="stat-label">{{ __("Absences aujourd'hui") }}</div><a class="btn btn-sm btn-outline" style="margin-top:10px" href="{{ route('attendance.index') }}">{{ __('Voir les présences') }}</a></div>
</div>
<div class="grid grid-2" style="margin-bottom:14px">
<div class="card"><h3>{{ __('Évolution financière — 6 derniers mois') }}</h3>
<table class="data"><thead><tr><th>{{ __('Mois') }}</th><th>{{ __('Recettes') }}</th><th>{{ __('Dépenses') }}</th><th>{{ __('Salaires') }}</th></tr></thead><tbody>
@foreach($chartMonths as $m)
<tr><td>{{ $m['label'] }}</td><td>{{ number_format($m['recettes'],2,',',' ') }}</td><td>{{ number_format($m['depenses'],2,',',' ') }}</td><td>{{ number_format($m['salaires'],2,',',' ') }}</td></tr>
@endforeach
</tbody></table></div>
<div class="card"><h3>{{ __('Répartition du mois') }}</h3><div class="empty"><div class="muted">{{ __('Recettes') }} {{ $fmt($encaisseMois) }} · {{ __('Dépenses') }} {{ $fmt($depenses) }}</div></div></div>
</div>
<div class="grid grid-2">
<div class="card"><h3>{{ __('Paiements en retard') }} ({{ $overdue->count() }})</h3>
@if($overdue->isEmpty())<div class="empty"><div class="ok">OK</div>{{ __('Aucun impayé') }}</div>
@else<div class="table-wrap"><table class="data"><thead><tr><th>{{ __('ÉLÈVE') }}</th><th>{{ __('CLASSE') }}</th><th>{{ __('SOLDE DÛ') }}</th></tr></thead><tbody>
@foreach($overdue as $p)<tr><td>{{ $p->student?->full_name }}</td><td>{{ $p->student?->schoolClass?->nom }}</td><td><span class="money-red">{{ number_format($p->restant,2,',',' ') }} DH</span></td></tr>@endforeach
</tbody></table></div>@endif</div>
<div>
<div class="card" style="margin-bottom:14px"><h3>{{ __('Enseignants à payer') }} — {{ now()->translatedFormat('F Y') }}</h3><div class="empty"><div class="ok">OK</div>{{ __('Aucune paie en attente.') }} <a href="{{ route('payroll_teachers.index') }}">{{ __('Générer la paie') }}</a></div></div>
<div class="card" style="margin-bottom:14px"><h3>{{ __('Documents expirants (30 j.)') }}</h3><div class="empty"><div class="ok">OK</div>{{ __('Aucun document expirant') }}</div></div>
<div class="card"><h3>{{ __("Élèves ayant quitté l'établissement") }}</h3><div class="empty">{{ __('Aucune sortie enregistrée —') }} <a href="{{ route('departures.index') }}">{{ __('voir') }}</a></div></div>
</div></div>

<div class="card" style="margin-top:14px;margin-bottom:14px">
 <h3>{{ __('Statistiques des créneaux') }}</h3>
 <p class="muted" style="margin:4px 0 12px">{{ __('Basé sur l\'emploi du temps (créneaux hebdomadaires récurrents).') }}</p>
 <div class="grid grid-3" style="margin-bottom:14px">
  <div class="card" style="box-shadow:none;border:1px solid var(--border)">
   <div class="stat-num" style="font-size:22px">{{ number_format($weeklyHours, 2, ',', ' ') }} h</div>
   <div class="stat-label">{{ __('Heures hebdomadaires') }}</div>
  </div>
  <div class="card" style="box-shadow:none;border:1px solid var(--border)">
   <div class="stat-num" style="font-size:22px">{{ $weeklySessions }}</div>
   <div class="stat-label">{{ __('Séances hebdomadaires') }}</div>
  </div>
  <div class="card" style="box-shadow:none;border:1px solid var(--border)">
   <div class="stat-num" style="font-size:22px">{{ number_format($monthlyHoursEstimate, 2, ',', ' ') }} h</div>
   <div class="stat-label">{{ __('تقدير شهري') }} (×4)</div>
   <div class="muted" style="margin-top:6px;font-size:12px">{{ __('Estimation mensuelle basée sur l\'emploi du temps (heures × 4).') }}</div>
  </div>
 </div>
 <div class="tabs" style="margin-bottom:10px">
  <button type="button" class="tab active" data-slot-tab="subjects" onclick="switchSlotTab(this,'subjects')">{{ __('Par matière') }}</button>
  <button type="button" class="tab" data-slot-tab="teachers" onclick="switchSlotTab(this,'teachers')">{{ __('Par enseignant') }}</button>
 </div>
 <div id="slot-tab-subjects" class="table-wrap">
  <table class="data">
   <thead><tr>
    <th>{{ __('Matière') }}</th>
    <th>{{ __('Séances / semaine') }}</th>
    <th>{{ __('Heures / semaine') }}</th>
    <th>{{ __('تقدير شهري') }} — {{ __('séances') }} (×4)</th>
    <th>{{ __('تقدير شهري') }} — {{ __('heures') }} (×4)</th>
   </tr></thead>
   <tbody>
   @forelse($bySubject as $row)
   <tr>
    <td>{{ $row['name'] }}</td>
    <td>{{ $row['sessions'] }}</td>
    <td>{{ number_format($row['hours'], 2, ',', ' ') }}</td>
    <td>{{ $row['monthly_sessions'] }}</td>
    <td>{{ number_format($row['monthly_hours'], 2, ',', ' ') }}</td>
   </tr>
   @empty
   <tr><td colspan="5" class="muted">{{ __('Aucun créneau dans l\'emploi du temps.') }}</td></tr>
   @endforelse
   </tbody>
  </table>
 </div>
 <div id="slot-tab-teachers" class="table-wrap" style="display:none">
  <table class="data">
   <thead><tr>
    <th>{{ __('Enseignant') }}</th>
    <th>{{ __('Séances / semaine') }}</th>
    <th>{{ __('Heures / semaine') }}</th>
    <th>{{ __('تقدير شهري') }} — {{ __('séances') }} (×4)</th>
    <th>{{ __('تقدير شهري') }} — {{ __('heures') }} (×4)</th>
   </tr></thead>
   <tbody>
   @forelse($byTeacher as $row)
   <tr>
    <td>{{ $row['name'] }}</td>
    <td>{{ $row['sessions'] }}</td>
    <td>{{ number_format($row['hours'], 2, ',', ' ') }}</td>
    <td>{{ $row['monthly_sessions'] }}</td>
    <td>{{ number_format($row['monthly_hours'], 2, ',', ' ') }}</td>
   </tr>
   @empty
   <tr><td colspan="5" class="muted">{{ __('Aucun créneau dans l\'emploi du temps.') }}</td></tr>
   @endforelse
   </tbody>
  </table>
 </div>
</div>
<script>
function switchSlotTab(btn, key) {
  document.querySelectorAll('[data-slot-tab]').forEach(el => el.classList.remove('active'));
  btn.classList.add('active');
  document.getElementById('slot-tab-subjects').style.display = key === 'subjects' ? '' : 'none';
  document.getElementById('slot-tab-teachers').style.display = key === 'teachers' ? '' : 'none';
}
</script>

@endsection
