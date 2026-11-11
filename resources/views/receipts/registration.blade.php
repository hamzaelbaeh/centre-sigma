@php
    $M = fn ($n) => '<span dir="ltr">'.e(\App\Services\RegistrationReceipt::money($n)).'</span>';
    $mn = fn ($n) => \App\Services\RegistrationReceipt::money($n);
    $stu = $student;
    $level = $level ?? 0;
    $logoH = [20, 17, 15, 13][$level];
    $rulesFull = [
        'مسؤولية المركز تقتصر على داخل مقره وخلال أوقات الحصص المسجّل فيها التلميذ فقط.',
        'يتحمل ولي الأمر إيصال التلميذ إلى المركز واستلامه عند نهاية الحصة في الوقت المحدد.',
        'أداء الواجب الشهري داخل أجل أقصاه 7 أيام من تاريخ استحقاقه حسب الأثمنة المتفق عليها عند التسجيل.',
        'الحضور والانضباط: إخبار المركز بكل غياب أو تأخر وتبريره، واحترام النظام الداخلي والأساتذة والزملاء.',
        'في حالة عدم انضباط التلميذ يحق لإدارة المركز توقيفه عن الدراسة بالمركز.',
        'يتحمل ولي الأمر تكلفة إصلاح أي ضرر يلحقه التلميذ عمداً بتجهيزات المركز، ولا يتحمل المركز مسؤولية ضياع الأغراض الشخصية.',
        'التصريح بأي حالة صحية خاصة، وإبقاء رقم هاتف ولي الأمر محيّناً.',
    ];
    $rulesShort = [
        'أداء الواجب الشهري داخل أجل أقصاه 7 أيام من تاريخ استحقاقه.',
        'مسؤولية المركز تقتصر على داخل مقره وخلال أوقات الحصص.',
        'احترام النظام الداخلي والانضباط؛ للإدارة حق توقيف التلميذ عند الإخلال.',
        'إبقاء رقم هاتف ولي الأمر محيّناً وإخبار المركز بالغياب.',
    ];
@endphp
<html dir="rtl" lang="ar">
<head>
<meta charset="utf-8">
<style>
  body { font-family: xbriyaz; font-size: 10pt; color: #000; direction: rtl; }
  table { border-collapse: collapse; width: 100%; }
  td, th { vertical-align: middle; line-height: 1.15; }
  .half { width: 100%; height: 134mm; }
  .title { font-size: 15pt; font-weight: bold; }
  .sub { font-size: 9.5pt; }
  .tag { font-size: 10pt; font-weight: bold; border: 0.4mm solid #000; padding: 1mm; text-align: center; margin-bottom: 1mm; }
  .kv td { border: 0.2mm solid #000; padding: 1.1mm 2mm; font-size: 10pt; }
  .kv td.l { background-color: #ececec; font-weight: bold; width: 17%; }
  .kv td.v { width: 33%; }
  .bill th { border: 0.2mm solid #000; background-color: #ececec; padding: 1mm 1.5mm; font-size: 9pt; }
  .bill td { border: 0.2mm solid #000; padding: 0.9mm 1.5mm; text-align: center; font-size: 9.5pt; }
  .sum td { border: 0.3mm solid #000; padding: 1.3mm 2mm; text-align: center; font-size: 10pt; }
  .sum td.h { background-color: #ececec; font-weight: bold; font-size: 9pt; }
  .big { font-size: 17pt; font-weight: bold; }
  .rules { font-size: 8.4pt; line-height: 1.28; }
  .cut { height: 4.5mm; border-bottom: 0.4mm dashed #000; text-align: center; font-size: 7.5pt; line-height: 1; }
  .sig td { border: 0.2mm solid #000; height: 22mm; vertical-align: top; text-align: center; font-size: 10pt; font-weight: bold; padding-top: 1.5mm; }
  .h4 { font-weight: bold; font-size: 10pt; border-bottom: 0.3mm solid #000; margin: 1.8mm 0 1mm 0; }
  .note { border: 0.2mm solid #000; padding: 1.5mm 2.5mm; font-size: 10pt; margin-top: 2mm; }
  .lvl1 .kv td { padding: 0.8mm 2mm; font-size: 9.5pt; }
  .lvl1 .bill td { padding: 0.5mm 1.5mm; font-size: 9pt; }
  .lvl1 .bill th { padding: 0.7mm 1.5mm; font-size: 8.5pt; }
  .lvl1 .sum td { padding: 0.9mm 2mm; font-size: 9.5pt; }
  .lvl1 .h4 { margin: 1.2mm 0 0.7mm 0; }
  .lvl1 .rules { font-size: 8pt; line-height: 1.22; }
  .lvl2 .kv td { padding: 0.45mm 2mm; font-size: 9pt; }
  .lvl2 .bill td { padding: 0.3mm 1.2mm; font-size: 8.5pt; }
  .lvl2 .bill th { padding: 0.4mm 1.2mm; font-size: 8pt; }
  .lvl2 .sum td { padding: 0.6mm 2mm; font-size: 9pt; }
  .lvl2 .sum td.h { font-size: 8pt; }
  .lvl2 .h4 { margin: 0.8mm 0 0.5mm 0; font-size: 9pt; }
  .lvl2 .rules { font-size: 7.4pt; line-height: 1.15; }
  .lvl2 .title { font-size: 13pt; }
  .lvl3 .kv td { padding: 0.2mm 2mm; font-size: 8.5pt; }
  .lvl3 .bill td { padding: 0.15mm 1mm; font-size: 8pt; }
  .lvl3 .bill th { padding: 0.2mm 1mm; font-size: 7.5pt; }
  .lvl3 .sum td { padding: 0.3mm 2mm; font-size: 8.5pt; }
  .lvl3 .h4 { margin: 0.5mm 0 0.3mm 0; font-size: 8.5pt; }
  .lvl3 .rules { font-size: 7pt; line-height: 1.1; }
  .lvl3 .title { font-size: 12pt; }
</style>
</head>
<body>

{{-- ===== TOP HALF: CENTRE ARCHIVE COPY ===== --}}
<div class="half lvl{{ $level }}">
  <table><tr>
    <td style="width:24mm"><img src="{{ $logo }}" style="height:{{ $logoH }}mm"></td>
    <td style="text-align:center">
      <div class="title">مركز سيكما – Sigma Académie</div>
      <div class="sub">وصل التسجيل — <b>نسخة أرشيف المركز</b></div>
    </td>
    <td style="width:46mm; text-align:left">
      <div class="tag">أرشيف المركز</div>
      <span class="sub">رقم الوصل: <span dir="ltr">{{ $receipt_no }}</span></span><br>
      <span class="sub">التاريخ: <span dir="ltr">{{ $date }}</span></span>
    </td>
  </tr></table>

  <table class="kv" style="margin-top:1.2mm">
    <tr><td class="l">رقم التلميذ</td><td class="v"><b dir="ltr">{{ $stu->matricule }}</b></td><td class="l">تاريخ التسجيل</td><td class="v"><span dir="ltr">{{ $reg_date }}</span></td></tr>
    <tr><td class="l">الاسم الكامل</td><td class="v"><b>{{ $stu->full_name }}</b></td><td class="l">المستوى / القسم</td><td class="v">{{ $niveau }} / {{ $classe }}</td></tr>
    <tr><td class="l">تاريخ الازدياد</td><td class="v"><span dir="ltr">{{ optional($stu->date_naissance)->format('d/m/Y') ?: '—' }}</span></td><td class="l">هاتف / عنوان التلميذ</td><td class="v"><span dir="ltr">{{ $stu->telephone ?: '—' }}</span> / {{ $stu->adresse ?: '—' }}</td></tr>
    <tr><td class="l">رقم الولي</td><td class="v"><b dir="ltr">{{ $parent_id }}</b></td><td class="l">الولي / هاتفه</td><td class="v">{{ $parent?->full_name ?: '—' }} / <span dir="ltr">{{ $parent?->telephone ?: '—' }}</span></td></tr>
  </table>

  <div class="h4">تفصيل الفوترة (الشهر الأول) — {{ $estimated ? 'تقدير عند التسجيل؛ الاحتساب النهائي في نهاية الشهر حسب الحصص المنجزة فعلياً' : 'محتسب حسب الحصص المنجزة فعلياً' }}</div>
  <table class="bill">
    <tr><th style="text-align:right">المادة</th><th>الواجب الشهري</th><th>حصص الشهر</th><th>ثمن الحصة</th><th>الحصص من التسجيل</th><th>{{ $estimated ? 'المستهلك (تقدير)' : 'المستهلك' }}</th></tr>
    @forelse($lines as $l)
    <tr>
      <td style="text-align:right">{{ $l['subject'] }}</td>
      <td>{!! $M($l['monthly']) !!}</td>
      <td>{{ $l['sessions_month'] }}</td>
      <td>{!! $M($l['session_price']) !!}</td>
      <td><span dir="ltr">{{ $l['sessions'] }} × {{ $mn($l['session_price']) }}</span></td>
      <td><b>{!! $M($l['amount']) !!}</b></td>
    </tr>
    @empty
    <tr><td colspan="6">—</td></tr>
    @endforelse
  </table>

  <table class="sum" style="margin-top:1.5mm">
    <tr><td class="h">الشهر الكامل المؤدى</td><td class="h">{{ $estimated ? 'المستهلك هذا الشهر (تقدير)' : 'المستهلك هذا الشهر' }}</td><td class="h">{{ $estimated ? 'الرصيد المرحّل (تقدير)' : 'الرصيد المرحّل' }}</td><td class="h">واجب الشهر الموالي بعد خصم الرصيد{{ $estimated ? ' (تقدير)' : '' }}</td></tr>
    <tr><td><b>{!! $M($cash) !!}</b></td><td><b>{!! $M($consumed) !!}</b></td><td><b>{!! $M($credit) !!}</b></td><td><b>{!! $M($next_due) !!}</b></td></tr>
  </table>

  <div class="h4">النظام الداخلي — التزامات ولي الأمر والتلميذ</div>
  <div class="rules">
    @foreach($rulesFull as $i => $r)<div>{{ $i + 1 }}. {{ $r }}</div>@endforeach
  </div>
  <div style="margin-top:2mm; font-size:10pt; font-weight:bold;">توقيع ولي الأمر (قرأتُ ووافقتُ): <span style="font-weight:normal">.................................................</span></div>
</div>

<div class="cut">قصّ هنا</div>

{{-- ===== BOTTOM HALF: PARENT PAYMENT RECEIPT ===== --}}
<div class="half lvl0">
  <table><tr>
    <td style="width:24mm"><img src="{{ $logo }}" style="height:21mm"></td>
    <td style="text-align:center">
      <div class="title">مركز سيكما – Sigma Académie</div>
      <div class="sub"><b>وصل أداء</b> — نسخة ولي الأمر</div>
      <div class="sub">الهاتف: <span dir="ltr">{{ $phone }}</span> &nbsp;|&nbsp; {{ $address }}</div>
    </td>
    <td style="width:46mm; text-align:left">
      <div class="tag">نسخة ولي الأمر</div>
      <span class="sub">رقم الوصل: <span dir="ltr">{{ $receipt_no }}</span></span><br>
      <span class="sub">تاريخ الأداء: <span dir="ltr">{{ $date }}</span></span>
    </td>
  </tr></table>

  <table class="kv" style="margin-top:2mm">
    <tr><td class="l">التلميذ(ة)</td><td class="v"><b>{{ $stu->full_name }}</b></td><td class="l">رقم التلميذ</td><td class="v"><b dir="ltr">{{ $stu->matricule }}</b></td></tr>
    <tr><td class="l">الولي</td><td class="v">{{ $parent?->full_name ?: '—' }}</td><td class="l">رقم الولي</td><td class="v"><b dir="ltr">{{ $parent_id }}</b></td></tr>
    <tr><td class="l">المواد</td><td class="v" colspan="3">{{ $subjects ?: '—' }}</td></tr>
  </table>

  <table class="sum" style="margin-top:2mm">
    <tr><td class="h" style="width:50%">المبلغ المؤدى</td><td class="h">تاريخ الأداء</td></tr>
    <tr><td><span class="big">{!! $M($cash) !!}</span></td><td><b><span dir="ltr">{{ $date }}</span></b></td></tr>
  </table>

  <div class="note">المبلغ المؤدى سيُخصم منه ما استُهلك هذا الشهر، وفي الشهر الموالي تؤدون فقط الفرق لإكمال واجب الشهر.</div>

  <div class="h4">النظام الداخلي (ملخص)</div>
  <div class="rules" style="font-size:8.6pt">
    @foreach($rulesShort as $i => $r)<div>{{ $i + 1 }}. {{ $r }}</div>@endforeach
  </div>

  <table class="sig" style="width:48%; margin-top:3mm" align="left">
    <tr><td>توقيع وختم الإدارة</td></tr>
  </table>
</div>

</body>
</html>
