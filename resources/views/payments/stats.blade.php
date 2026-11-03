@extends('layouts.app')
@section('title','إحصائيات الأداء')
@section('content')
<div class="page-head"><div><h1>إحصائيات الأداء</h1><div class="sub">حالات الأداء ديال التلاميذ حسب الحضور</div></div><a class="btn btn-ghost" href="{{ route('payments.index') }}">{{ __('Retour') }}</a></div>
@php
$cards = [
 ['جداد ما خلّصوش (هاد الشهر)', $groups['new']->count(), '#2563eb'],
 ['حضروا حصة 1 بلا أداء', $groups['one']->count(), '#d97706'],
 ['حضروا حصتين بلا أداء (موقوفين مؤقتاً)', $groups['two']->count(), '#dc2626'],
 ['مسجّلين وما خلّصوش', $groups['all']->count(), '#7c3aed'],
 ['حضر وما كمّلش (منسحب)', $withdrawn->count(), '#475569'],
];
@endphp
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px;margin-bottom:16px">
@foreach($cards as [$l,$n,$c])
 <div class="card" style="border-top:4px solid {{ $c }}"><div class="muted" style="font-size:13px">{{ $l }}</div><div style="font-size:28px;font-weight:700;color:{{ $c }}">{{ $n }}</div></div>
@endforeach
</div>

<div class="card" id="alerts" style="margin-bottom:16px"><h3>🔔 الإشعارات ({{ $alerts->count() }})</h3>
@forelse($alerts as $a)
 <div style="display:flex;justify-content:space-between;align-items:center;gap:8px;padding:8px 0;border-bottom:1px solid #eee">
  <div><a href="{{ route('students.show', $a->student_id) }}">{{ $a->message }}</a> <span class="muted">· {{ $a->student?->schoolClass?->nom }} · {{ $a->created_at->format('d/m/Y H:i') }}</span></div>
  <div style="display:flex;gap:6px">
   <form method="POST" action="{{ route('students.withdraw', $a->student_id) }}" onsubmit="return confirm('تأكيد: ما مهتمش / غادي يوقف؟')">@csrf<button class="btn btn-sm btn-outline">ما مهتمش / غادي يوقف</button></form>
   <form method="POST" action="{{ route('alerts.read', $a) }}">@csrf<button class="btn btn-sm btn-ghost">تمت القراءة</button></form>
  </div>
 </div>
@empty <div class="muted">ما كاين حتى إشعار.</div> @endforelse
</div>

@php $sections = [['two','حضروا حصتين بلا أداء — موقوفين مؤقتاً'],['one','حضروا حصة 1 بلا أداء'],['new','جداد ما خلّصوش'],['all','جميع المسجّلين اللي ما خلّصوش']]; @endphp
@foreach($sections as [$k,$title])
<div class="card table-wrap" style="margin-bottom:16px"><h3>{{ $title }} ({{ $groups[$k]->count() }})</h3>
<table class="data"><thead><tr><th>التلميذ</th><th>القسم</th><th>الحالة</th><th>حصص بلا أداء</th><th>التسجيل</th></tr></thead><tbody>
@forelse($groups[$k] as $r)
<tr><td><a href="{{ route('students.show', $r['student']) }}">{{ $r['student']->full_name }}</a></td><td>{{ $r['student']->schoolClass?->nom }}</td><td>{{ __($r['student']->statut) }}</td><td>{{ $r['attended'] }}</td><td>{{ optional($r['student']->date_inscription)->format('d/m/Y') }}</td></tr>
@empty <tr><td colspan="5" class="muted">—</td></tr> @endforelse
</tbody></table></div>
@endforeach

<div class="card table-wrap"><h3>حضر وما كمّلش — منسحب ({{ $withdrawn->count() }})</h3>
<div class="sub" style="margin-bottom:8px">مؤشر على إقناع الأستاذ: تلاميذ حضروا وما كمّلوش.</div>
<table class="data"><thead><tr><th>التلميذ</th><th>القسم</th><th>حصص حضرها</th><th>تاريخ الانسحاب</th></tr></thead><tbody>
@forelse($withdrawn as $r)
<tr><td><a href="{{ route('students.show', $r['student']) }}">{{ $r['student']->full_name }}</a></td><td>{{ $r['student']->schoolClass?->nom }}</td><td>{{ $r['attended'] }}</td><td>{{ optional($r['student']->retire_at)->format('d/m/Y') }}</td></tr>
@empty <tr><td colspan="4" class="muted">—</td></tr> @endforelse
</tbody></table></div>
@endsection
