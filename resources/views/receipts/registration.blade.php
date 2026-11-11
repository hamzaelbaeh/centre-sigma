@php
    $M = fn ($n) => '<span dir="ltr">'.e(\App\Services\RegistrationReceipt::money($n)).'</span>';
    $stu = $student;
    $level = $level ?? 0;
    $logoH = [21, 19, 16][$level];
    $rules = [
        'مسؤولية المركز تقتصر على داخل مقره وخلال أوقات الحصص المسجّل فيها التلميذ فقط.',
        'يتحمل ولي الأمر إيصال التلميذ إلى المركز واستلامه عند نهاية الحصة في الوقت المحدد.',
        'أداء الواجب الشهري داخل أجل أقصاه 7 أيام من تاريخ استحقاقه حسب الأثمنة المتفق عليها عند التسجيل.',
        'الحضور والانضباط: إخبار المركز بكل غياب أو تأخر وتبريره، واحترام النظام الداخلي والأساتذة والزملاء.',
        'في حالة عدم انضباط التلميذ يحق لإدارة المركز توقيفه عن الدراسة بالمركز.',
        'يتحمل ولي الأمر تكلفة إصلاح أي ضرر يلحقه التلميذ عمداً بتجهيزات المركز، ولا يتحمل المركز مسؤولية ضياع الأغراض الشخصية.',
        'التصريح بأي حالة صحية خاصة، وإبقاء رقم هاتف ولي الأمر محيّناً.',
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
  .kv td { border: 0.2mm solid #000; padding: 1.4mm 2mm; font-size: 10pt; }
  .kv td.l { background-color: #ececec; font-weight: bold; width: 17%; }
  .kv td.v { width: 33%; }
  .bill th { border: 0.2mm solid #000; background-color: #ececec; padding: 1.2mm 1.5mm; font-size: 9.5pt; }
  .bill td { border: 0.2mm solid #000; padding: 1.3mm 1.5mm; text-align: center; font-size: 10pt; }
  .sum td { border: 0.3mm solid #000; padding: 1.6mm 2mm; text-align: center; font-size: 10pt; }
  .sum td.h { background-color: #ececec; font-weight: bold; font-size: 9.5pt; }
  .big { font-size: 15pt; font-weight: bold; }
  .rules { font-size: 9pt; line-height: 1.35; }
  .cut { height: 4.5mm; border-bottom: 0.4mm dashed #000; text-align: center; font-size: 7.5pt; line-height: 1; }
  .sig td { border: 0.2mm solid #000; height: 17mm; vertical-align: top; text-align: center; font-size: 8.5pt; font-weight: bold; padding-top: 1mm; }
  .h4 { font-weight: bold; font-size: 10.5pt; border-bottom: 0.3mm solid #000; margin: 2.5mm 0 1.2mm 0; }
  .notes { border: 0.2mm solid #000; height: 14mm; padding: 1mm 2mm; font-size: 9pt; margin-top: 2.5mm; }
  .lvl1 .kv td { padding: 0.8mm 2mm; font-size: 9.5pt; }
  .lvl1 .bill td { padding: 0.5mm 1.5mm; font-size: 9.5pt; }
  .lvl1 .bill th { padding: 0.7mm 1.5mm; font-size: 9pt; }
  .lvl1 .sum td { padding: 1mm 2mm; font-size: 9.5pt; }
  .lvl1 .h4 { margin: 1.5mm 0 0.8mm 0; }
  .lvl1 .rules { font-size: 8.2pt; }
  .lvl2 .kv td { padding: 0.4mm 2mm; font-size: 9pt; }
  .lvl2 .bill td { padding: 0.25mm 1.2mm; font-size: 9pt; }
  .lvl2 .bill th { padding: 0.4mm 1.2mm; font-size: 8.5pt; }
  .lvl2 .sum td { padding: 0.6mm 2mm; font-size: 9pt; }
  .lvl2 .sum td.h { font-size: 8.5pt; }
  .lvl2 .h4 { margin: 1mm 0 0.5mm 0; font-size: 9.5pt; }
  .lvl2 .rules { font-size: 7.8pt; line-height: 1.2; }
  .lvl2 .sig td { height: 10mm; }
  .lvl2 .title { font-size: 13pt; }
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

  <table class="kv" style="margin-top:1.5mm">
    <tr><td class="l">رقم التلميذ</td><td class="v"><b dir="ltr">{{ $stu->matricule }}</b></td><td class="l">تاريخ التسجيل</td><td class="v"><span dir="ltr">{{ $reg_date }}</span></td></tr>
    <tr><td class="l">الاسم الكامل</td><td class="v"><b>{{ $stu->full_name }}</b></td><td class="l">المستوى</td><td class="v">{{ $niveau }}</td></tr>
    <tr><td class="l">القسم</td><td class="v">{{ $classe }}</td><td class="l">تاريخ الازدياد</td><td class="v"><span dir="ltr">{{ optional($stu->date_naissance)->format('d/m/Y') ?: '—' }}</span></td></tr>
    <tr><td class="l">هاتف التلميذ</td><td class="v"><span dir="ltr">{{ $stu->telephone ?: '—' }}</span></td><td class="l">العنوان</td><td class="v">{{ $stu->adresse ?: '—' }}</td></tr>
    <tr><td class="l">رقم الولي</td><td class="v"><b dir="ltr">{{ $parent_id }}</b></td><td class="l">اسم الولي</td><td class="v">{{ $parent?->full_name ?: '—' }}</td></tr>
    <tr><td class="l">هاتف الولي</td><td class="v"><span dir="ltr">{{ $parent?->telephone ?: '—' }}</span></td><td class="l">رقم الوصل</td><td class="v"><span dir="ltr">{{ $receipt_no }}</span></td></tr>
  </table>

  <div class="h4">تفصيل الفوترة (الشهر الأول)</div>
  <table class="bill">
    <tr><th style="text-align:right">المادة</th><th>الواجب الشهري</th><th>حصص الشهر</th><th>ثمن الحصة</th><th>الحصص المتبقية</th><th>المستهلك</th></tr>
    @forelse($lines as $l)
    <tr>
      <td style="text-align:right">{{ $l['subject'] }}</td>
      <td>{!! $M($l['monthly']) !!}</td>
      <td>{{ $l['sessions_month'] }}</td>
      <td>{!! $M($l['session_price']) !!}</td>
      <td><span dir="ltr">{{ $l['sessions'] }} × {{ \App\Services\RegistrationReceipt::money($l['session_price']) }}</span></td>
      <td><b>{!! $M($l['amount']) !!}</b></td>
    </tr>
    @empty
    <tr><td colspan="6">—</td></tr>
    @endforelse
  </table>

  <table class="sum" style="margin-top:2mm">
    <tr><td class="h">الشهر الكامل المؤدى</td><td class="h">المستهلك هذا الشهر</td><td class="h">الرصيد المرحّل للشهر الثاني</td><td class="h">المطلوب في الشهر الثاني</td></tr>
    <tr><td><b>{!! $M($cash) !!}</b> <span class="sub">من أصل {!! $M($full) !!}</span></td><td><b>{!! $M($consumed) !!}</b></td><td><b>{!! $M($credit) !!}</b></td><td><b>{!! $M($next_due) !!}</b></td></tr>
  </table>
  @if($level === 0 && count($lines) <= 3)<div class="notes">ملاحظات الإدارة:</div>@endif
</div>

<div class="cut">قصّ هنا</div>

{{-- ===== BOTTOM HALF: PARENT PAYMENT RECEIPT ===== --}}
<div class="half lvl{{ $level }}">
  <table><tr>
    <td style="width:24mm"><img src="{{ $logo }}" style="height:{{ $logoH }}mm"></td>
    <td style="text-align:center">
      <div class="title">مركز سيكما – Sigma Académie</div>
      <div class="sub"><b>وصل أداء</b> — نسخة ولي الأمر</div>
      <div class="sub">الهاتف: <span dir="ltr">{{ $phone }}</span> &nbsp;|&nbsp; {{ $address }}</div>
    </td>
    <td style="width:46mm; text-align:left">
      <div class="tag">نسخة ولي الأمر</div>
      <span class="sub">رقم الوصل: <span dir="ltr">{{ $receipt_no }}</span></span><br>
      <span class="sub">التاريخ: <span dir="ltr">{{ $date }}</span></span>
    </td>
  </tr></table>

  <table class="kv" style="margin-top:1.5mm">
    <tr><td class="l">التلميذ(ة)</td><td class="v"><b>{{ $stu->full_name }}</b></td><td class="l">رقم التلميذ</td><td class="v"><b dir="ltr">{{ $stu->matricule }}</b></td></tr>
    <tr><td class="l">الولي</td><td class="v">{{ $parent?->full_name ?: '—' }}</td><td class="l">رقم الولي</td><td class="v"><b dir="ltr">{{ $parent_id }}</b></td></tr>
    <tr><td class="l">المواد</td><td class="v" colspan="3">{{ $subjects ?: '—' }}</td></tr>
  </table>

  <table class="sum" style="margin-top:1.5mm">
    <tr><td class="h" style="width:34%">المبلغ المؤدى</td><td class="h">المستهلك هذا الشهر</td><td class="h">الرصيد المرحّل</td><td class="h">المطلوب في الشهر الثاني</td></tr>
    <tr><td><span class="big">{!! $M($cash) !!}</span></td><td>{!! $M($consumed) !!}</td><td>{!! $M($credit) !!}</td><td><b>{!! $M($next_due) !!}</b></td></tr>
  </table>

  <div class="h4">النظام الداخلي — التزامات ولي الأمر والتلميذ</div>
  <div class="rules">
    @foreach($rules as $i => $r)<div>{{ $i + 1 }}. {{ $r }}</div>@endforeach
  </div>

  <table class="sig" style="margin-top:2mm">
    <tr><td style="width:50%">توقيع ولي الأمر (قرأتُ ووافقتُ)</td><td>توقيع وختم إدارة المركز</td></tr>
  </table>
</div>

</body>
</html>
