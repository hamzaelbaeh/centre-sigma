@php
    $M = fn ($n) => '<span dir="ltr">'.e(\App\Services\RegistrationReceipt::money($n)).'</span>';
@endphp
<html dir="rtl" lang="ar">
<head>
<meta charset="utf-8">
<style>
  body { font-family: xbriyaz; font-size: 8.5pt; color: #000; direction: rtl; }
  table { border-collapse: collapse; width: 100%; }
  th { border: 0.25mm solid #000; background-color: #d9d9d9; padding: 1.3mm 1mm; font-size: 8.5pt; text-align: center; }
  td { border: 0.2mm solid #000; padding: 1mm 1.2mm; vertical-align: middle; line-height: 1.2; }
  td.c { text-align: center; }
  tr.g0 td { background-color: #ffffff; }
  tr.g1 td { background-color: #efefef; }
  tr.first td { border-top: 0.6mm solid #000; }
  tr.tot td { border-top: 0.6mm solid #000; background-color: #d9d9d9; font-weight: bold; font-size: 10.5pt; }
  .title { font-size: 18pt; font-weight: bold; }
  .small { font-size: 8pt; }
</style>
</head>
<body>
<table style="margin-bottom:3mm"><tr>
  <td style="border:0; width:24mm"><img src="{{ $logo }}" style="height:20mm"></td>
  <td style="border:0; text-align:center">
    <div class="title">تقرير التلاميذ الشهري — {{ $label }}</div>
    <div>مركز سيكما – Sigma Académie</div>
    <div class="small">الهاتف: <span dir="ltr">{{ $phone }}</span></div>
  </td>
  <td style="border:0; width:60mm; text-align:left" class="small">
    عدد التلاميذ: <b>{{ $count }}</b> &nbsp; عدد الأولياء: <b>{{ $parents }}</b><br>
    تاريخ الإصدار: <span dir="ltr">{{ $generated }}</span>
  </td>
</tr></table>

<table>
  <thead>
    <tr>
      <th style="width:18mm">رقم التلميذ</th>
      <th style="width:34mm">التلميذ</th>
      <th style="width:36mm">الأب</th>
      <th style="width:23mm">المستوى</th>
      <th>المواد</th>
      <th style="width:19mm">تاريخ التسجيل</th>
      <th style="width:16mm">الأقدمية</th>
      <th style="width:24mm">المبلغ المستحق هذا الشهر</th>
      <th style="width:23mm">الأداء هذا الشهر</th>
      <th style="width:28mm">حالة الأداء</th>
    </tr>
  </thead>
  <tbody>
  @forelse($groups as $gi => $g)
    @foreach($g as $ri => $r)
    @php $s = $r['student']; $p = $r['parent']; @endphp
    <tr class="g{{ $gi % 2 }} {{ $ri === 0 ? 'first' : '' }}" style="page-break-inside: avoid">
      <td class="c"><b dir="ltr">{{ $s->matricule }}</b></td>
      <td>{{ $s->full_name }}</td>
      <td>@if($p)<span dir="ltr">{{ $p->numero_pad }}</span> · {{ $p->full_name }}<br><span class="small" dir="ltr">{{ $p->telephone }}</span>@else — @endif</td>
      <td class="c">{{ $r['level'] }}</td>
      <td>{{ $r['subjects'] }}</td>
      <td class="c"><span dir="ltr">{{ optional($s->date_inscription)->format('d/m/Y') ?: '—' }}</span></td>
      <td class="c">{{ $r['seniority'] }}</td>
      <td class="c">@if($r['status'] === 'بدون فاتورة') — @else <b>{!! $M($r['due']) !!}</b>@if($r['estimated'])<b>*</b>@endif @if($r['adjusted'])<b>†</b>@endif @if($r['credit_in'] > 0)<br><span class="small">منه رصيد {!! $M($r['credit_in']) !!}</span>@endif @endif</td>
      <td class="c">{!! $M($r['cash']) !!}</td>
      <td class="c">{!! nl2br(e($r['status'])) !!}@if($r['credit_out'] > 0)<br><span class="small">رصيد مرحّل {!! $M($r['credit_out']) !!}</span>@endif</td>
    </tr>
    @endforeach
  @empty
    <tr><td colspan="10" class="c">لا توجد معطيات لهذا الشهر</td></tr>
  @endforelse
    <tr class="tot">
      <td colspan="7" style="text-align:center">المجموع</td>
      <td class="c">{!! $M($total_due) !!}</td>
      <td class="c">{!! $M($total_cash) !!}</td>
      <td class="c">—</td>
    </tr>
  </tbody>
</table>
<div class="small" style="margin-top:2mm">المبلغ المستحق = ما يخص هذا الشهر فقط (المستهلك في شهر التسجيل / واجب الشهر)، أما «الأداء هذا الشهر» فهو المبلغ المقبوض فعلياً خلال الشهر.<br>* مبلغ تقديري لتلميذ سُجّل خلال الشهر: يُعاد احتسابه نهائياً عند إقفال الشهر حسب الحصص المنجزة فعلياً.<br>† تم تصحيح عدد الحصص يدوياً لهذا التلميذ هذا الشهر.</div>
</body>
</html>
