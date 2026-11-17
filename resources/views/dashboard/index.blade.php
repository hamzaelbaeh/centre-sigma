@extends('layouts.app')
@section('title', 'لوحة القيادة')
@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
<style>
.sd{--navy:#081f52;--navy2:#123a8a;--g:var(--gold,#f7be1d);direction:rtl;text-align:right;font-family:'Cairo','Tajawal',Inter,system-ui,sans-serif}
.sd *{box-sizing:border-box}
.sd .hero{background:linear-gradient(135deg,var(--navy) 0%,var(--navy2) 100%);color:#fff;border-radius:18px;padding:18px 20px;margin-bottom:16px;display:flex;flex-wrap:wrap;gap:14px;align-items:center;justify-content:space-between;box-shadow:0 8px 24px rgba(8,31,82,.18)}
.sd .hero h1{margin:0;font-size:22px;font-weight:800}
.sd .hero .sub{opacity:.85;font-size:13px;margin-top:4px}
.sd .hero .yr{display:inline-block;background:var(--g);color:var(--navy);border-radius:999px;padding:2px 10px;font-weight:700;font-size:12px;margin-inline-start:6px}
.sd .qa{display:flex;gap:8px;flex-wrap:wrap}
.sd .qa a{background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.25);color:#fff;border-radius:12px;padding:9px 14px;font-weight:700;font-size:13px;transition:.15s}
.sd .qa a:hover{background:var(--g);color:var(--navy)}
.sd .qa a.pri{background:var(--g);color:var(--navy);border-color:transparent}
.sd .period{display:flex;flex-wrap:wrap;gap:8px;align-items:center;background:#fff;border:1px solid var(--border);border-radius:14px;padding:10px 12px;margin-bottom:16px}
.sd .period .lbl{font-weight:800;color:var(--navy);margin-inline-end:6px}
.sd .chip{border:1px solid var(--border);background:#f8fafc;border-radius:999px;padding:7px 14px;font-size:13px;font-weight:700;color:#334155}
.sd .chip.on{background:var(--navy);color:#fff;border-color:var(--navy)}
.sd .period select{border:1px solid var(--border);border-radius:999px;padding:7px 12px;font-family:inherit;font-weight:700;background:#f8fafc}
.sd .period .cur{margin-inline-start:auto;font-size:13px;color:var(--muted)}
.sd .period .cur b{color:var(--navy)}
.sd .kpis{display:grid;grid-template-columns:repeat(6,1fr);gap:12px;margin-bottom:16px}
.sd .kpi{background:#fff;border:1px solid var(--border);border-radius:16px;padding:14px;position:relative;overflow:hidden}
.sd .kpi:before{content:"";position:absolute;inset-inline-start:0;top:0;bottom:0;width:4px;background:var(--c,#081f52)}
.sd .kpi .t{font-size:12.5px;color:var(--muted);font-weight:700}
.sd .kpi .v{font-size:24px;font-weight:800;margin-top:6px;color:#0f172a;white-space:nowrap}
.sd .kpi .v small{font-size:13px;color:var(--muted);font-weight:700}
.sd .kpi .d{font-size:12px;margin-top:6px;color:var(--muted)}
.sd .up{color:#16a34a;font-weight:800}.sd .down{color:#dc2626;font-weight:800}.sd .eq{color:#64748b;font-weight:700}
.sd .bar{height:7px;background:#e2e8f0;border-radius:99px;overflow:hidden;margin-top:8px}.sd .bar span{display:block;height:100%;background:var(--g)}
.sd .sec{font-size:15px;font-weight:800;color:var(--navy);margin:6px 0 10px;display:flex;align-items:center;gap:8px}
.sd .sec:before{content:"";width:6px;height:18px;border-radius:3px;background:var(--g)}
.sd .grid3{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:16px}
.sd .grid2{display:grid;grid-template-columns:3fr 2fr;gap:12px;margin-bottom:16px}
.sd .grid2e{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px}
.sd .box{background:#fff;border:1px solid var(--border);border-radius:16px;padding:16px}
.sd .box h3{margin:0 0 12px;font-size:14.5px;font-weight:800;color:var(--navy);display:flex;justify-content:space-between;align-items:center;gap:8px}
.sd .box h3 .n{background:#eef2ff;color:var(--navy);border-radius:999px;padding:2px 10px;font-size:12px}
.sd .cyc{border-radius:16px;padding:16px;color:#fff;background:var(--cc)}
.sd .cyc .hd{display:flex;justify-content:space-between;align-items:center}
.sd .cyc .hd b{font-size:17px}.sd .cyc .cnt{font-size:30px;font-weight:800;line-height:1}
.sd .cyc .cnt small{font-size:12px;font-weight:600;opacity:.85}
.sd .cyc .money{display:grid;grid-template-columns:repeat(3,1fr);gap:6px;margin:12px 0;background:rgba(255,255,255,.12);border-radius:12px;padding:8px}
.sd .cyc .money div{text-align:center}.sd .cyc .money span{display:block;font-size:11px;opacity:.85}.sd .cyc .money strong{font-size:14px;white-space:nowrap}
.sd .cyc .lv{display:flex;flex-wrap:wrap;gap:6px}
.sd .cyc .lv span{background:rgba(255,255,255,.16);border-radius:999px;padding:3px 10px;font-size:12px}
.sd .cyc .lv span.z{opacity:.5}
.sd .cyc .bar{background:rgba(255,255,255,.25)}
.sd table{width:100%;border-collapse:collapse;font-size:13px}
.sd th{background:#f1f5f9;color:#334155;font-weight:800;padding:9px 8px;text-align:right;white-space:nowrap}
.sd td{padding:9px 8px;border-bottom:1px solid #f1f5f9;vertical-align:middle}
.sd tr:hover td{background:#fafcff}
.sd .tw{overflow:auto;max-height:420px}
.sd .num{direction:ltr;unicode-bidi:isolate;white-space:nowrap;font-variant-numeric:tabular-nums}
.sd .red{color:#b91c1c;font-weight:800}
.sd .btnx{display:inline-flex;align-items:center;gap:4px;border-radius:9px;padding:5px 9px;font-size:12px;font-weight:700;border:1px solid var(--border);background:#fff;white-space:nowrap}
.sd .btnx.wa{background:#dcfce7;border-color:#bbf7d0;color:#166534}
.sd .btnx.pay{background:var(--g);border-color:transparent;color:var(--navy)}
.sd .tag{display:inline-block;border-radius:999px;padding:2px 9px;font-size:11.5px;font-weight:700;background:#eef2ff;color:#1e3a8a}
.sd .tag.old{background:#fee2e2;color:#991b1b}
.sd .sess{display:flex;gap:10px;align-items:center;padding:10px;border:1px solid #eef2f7;border-radius:12px;margin-bottom:8px}
.sd .sess .tm{background:var(--navy);color:#fff;border-radius:10px;padding:6px 8px;font-weight:800;font-size:12px;text-align:center;min-width:86px}
.sd .sess .in b{display:block;font-size:13.5px}.sd .sess .in span{font-size:12px;color:var(--muted)}
.sd .alert-i{display:flex;gap:10px;padding:10px 12px;border-radius:12px;margin-bottom:8px;border:1px solid}
.sd .alert-i.red{background:#fef2f2;border-color:#fecaca}.sd .alert-i.amber{background:#fffbeb;border-color:#fde68a}.sd .alert-i.blue{background:#eff6ff;border-color:#bfdbfe}
.sd .alert-i .ic{font-size:18px}.sd .alert-i b{display:block;font-size:13.5px}.sd .alert-i span{font-size:12px;color:#475569}
.sd .empty2{text-align:center;color:var(--muted);padding:22px 8px;font-size:13px}
.sd .note{font-size:12px;color:var(--muted);margin-top:8px}
.sd .chartbox{position:relative;height:260px}
@media(max-width:1200px){.sd .kpis{grid-template-columns:repeat(3,1fr)}}
@media(max-width:900px){.sd .grid3,.sd .grid2,.sd .grid2e{grid-template-columns:1fr}}
@media(max-width:560px){.sd .kpis{grid-template-columns:repeat(2,1fr)}.sd .kpi .v{font-size:19px}.sd .hero h1{font-size:19px}.sd .period .cur{margin-inline-start:0;width:100%}}
</style>
@endpush
@section('content')
@php
  $f = fn($n) => number_format((float)$n, 0, ',', ' ');
  $delta = function ($cur, $prev, $goodUp = true, $pct = false) use ($f) {
      $diff = $cur - $prev;
      if (abs($diff) < 0.5) return '<span class="eq">= بحال</span>';
      $up = $diff > 0; $good = $goodUp ? $up : !$up;
      $txt = $pct ? (($up ? '+' : '−').abs(round($diff)).' نقطة') : (($up ? '+' : '−').$f(abs($diff)));
      return '<span class="'.($good ? 'up' : 'down').'">'.($up ? '▲' : '▼').' <span class="num">'.$txt.'</span></span>';
  };
@endphp
<div class="sd">
  <div class="hero">
    <div>
      <h1>لوحة القيادة</h1>
      <div class="sub">{{ $todayLabel }} @if($year)<span class="yr">السنة الدراسية {{ $year->nom }}</span>@endif</div>
    </div>
    <div class="qa">
      <a class="pri" href="{{ route('students.create') }}">＋ تسجيل تلميذ</a>
      <a href="{{ route('payments.index') }}">💰 تسجيل أداء</a>
      <a href="{{ route('payments.stats') }}#alerts">🔔 الإشعارات</a>
      <a href="{{ route('attendance.index') }}">✔ الحضور</a>
    </div>
  </div>

  <form class="period" method="GET" action="{{ route('dashboard') }}" id="periodForm">
    <span class="lbl">المدة:</span>
    <a class="chip {{ $p==='current' ? 'on' : '' }}" href="{{ route('dashboard') }}">الشهر الحالي</a>
    <a class="chip {{ $p==='prev' ? 'on' : '' }}" href="{{ route('dashboard', ['p'=>'prev']) }}">الشهر الماضي</a>
    <input type="hidden" name="p" value="month">
    <select name="m" class="{{ $p==='month' ? 'on' : '' }}" onchange="this.form.submit()" aria-label="اختيار شهر">
      <option value="">اختيار شهر…</option>
      @foreach($months as $mo)<option value="{{ $mo['v'] }}" @selected($p==='month' && $selectedMonth===$mo['v'])>{{ $mo['l'] }}</option>@endforeach
    </select>
    @if($year)<a class="chip {{ $p==='year' ? 'on' : '' }}" href="{{ route('dashboard', ['p'=>'year']) }}">السنة الدراسية</a>@endif
    <span class="cur">كتشوف: <b>{{ $label }}</b> · مقارنة مع {{ $prevLabel }}</span>
  </form>

  {{-- KPIs --}}
  <div class="kpis">
    <div class="kpi" style="--c:#081f52"><div class="t">الواجب</div><div class="v"><span class="num">{{ $f($kpi['due']) }}</span> <small>درهم</small></div>
      <div class="d">{!! $delta($kpi['due'], $kpiPrev['due']) !!}</div>
      @if($kpi['estimated'] > 0)<div class="d">منها <span class="num">{{ $f($kpi['estimated']) }}</span> تقديري (الفاتورة كتخرج فآخر الشهر)</div>@endif
    </div>
    <div class="kpi" style="--c:#16a34a"><div class="t">المحصل</div><div class="v"><span class="num">{{ $f($kpi['paid']) }}</span> <small>درهم</small></div>
      <div class="d">{!! $delta($kpi['paid'], $kpiPrev['paid']) !!}</div>
      <div class="d">النقد اللي دخل فهاد المدة: <b class="num">{{ $f($kpi['cash']) }}</b></div>
    </div>
    <div class="kpi" style="--c:#dc2626"><div class="t">الباقي · نسبة التحصيل <b class="num">{{ $kpi['rate'] }}%</b></div><div class="v"><span class="num">{{ $f($kpi['rest']) }}</span> <small>درهم</small></div>
      <div class="bar"><span style="width:{{ min(100,$kpi['rate']) }}%"></span></div>
      <div class="d">{!! $delta($kpi['rest'], $kpiPrev['rest'], false) !!}</div>
    </div>
    <div class="kpi" style="--c:#2563eb"><div class="t">التلاميذ النشيطون</div><div class="v"><span class="num">{{ $kpi['active'] }}</span></div>
      <div class="d">{!! $delta($kpi['active'], $kpiPrev['active']) !!}</div>
      @if($kpi['suspended'])<div class="d">منهم <b class="num">{{ $kpi['suspended'] }}</b> موقوف مؤقتا</div>@endif
    </div>
    <div class="kpi" style="--c:#f7be1d"><div class="t">التسجيلات الجديدة</div><div class="v"><span class="num">{{ $kpi['new'] }}</span></div>
      <div class="d">{!! $delta($kpi['new'], $kpiPrev['new']) !!}</div>
    </div>
    <div class="kpi" style="--c:#64748b"><div class="t">المنقطعون</div><div class="v"><span class="num">{{ $kpi['left'] }}</span></div>
      <div class="d">{!! $delta($kpi['left'], $kpiPrev['left'], false) !!}</div>
    </div>
  </div>

  {{-- Cycles --}}
  <div class="sec">الأسلاك</div>
  <div class="grid3" @if(count($cycles) > 3) style="grid-template-columns:repeat(auto-fit,minmax(240px,1fr))" @endif>
    @foreach($cycles as $key => $c)
    <div class="cyc" style="--cc:linear-gradient(135deg,{{ $c['color'] }},#081f52)">
      <div class="hd"><b>{{ $c['label'] }}</b><div class="cnt num">{{ $c['students'] }} <small>تلميذ</small></div></div>
      <div class="money">
        <div><span>الواجب</span><strong class="num">{{ $f($c['due']) }}</strong></div>
        <div><span>المحصل</span><strong class="num">{{ $f($c['paid']) }}</strong></div>
        <div><span>الباقي</span><strong class="num">{{ $f($c['rest']) }}</strong></div>
      </div>
      <div class="lv">@foreach($c['levelRows'] as $lv)<span class="{{ $lv['n'] ? '' : 'z' }}">{{ $lv['label'] }}: <b class="num">{{ $lv['n'] }}</b></span>@endforeach</div>
      <div class="bar"><span style="width:{{ min(100,$c['rate']) }}%"></span></div>
      <div style="font-size:11.5px;opacity:.85;margin-top:4px">نسبة التحصيل <span class="num">{{ $c['rate'] }}%</span></div>
    </div>
    @endforeach
  </div>

  {{-- Revenue --}}
  <div class="sec">المداخيل</div>
  <div class="grid2">
    <div class="box"><h3>آخر 6 شهور: الواجب مقابل المحصل</h3><div class="chartbox"><canvas id="revChart"></canvas></div></div>
    <div class="box"><h3>توزيع الواجب على المواد <span class="n">{{ $label }}</span></h3>
      @if($donut->isEmpty())<div class="empty2">ماكاين حتى مبلغ فهاد المدة</div>@else<div class="chartbox"><canvas id="subChart"></canvas></div>@endif
    </div>
  </div>

  {{-- Daily follow-up --}}
  <div class="sec">المتابعة اليومية</div>
  <div class="grid2">
    <div class="box"><h3>اللي ماخلصوش (من الأقدم) <span class="n num">{{ count($unpaid) }}</span></h3>
      @if(empty($unpaid))<div class="empty2">✅ كلشي خالص</div>@else
      <div class="tw"><table><thead><tr><th>التلميذ</th><th>المستوى</th><th>من شهر</th><th>الباقي</th><th>هاتف الولي</th><th></th></tr></thead><tbody>
      @foreach($unpaid as $u)
        <tr>
          <td><a href="{{ route('students.show', $u['student']) }}"><b>{{ $u['student']->full_name }}</b></a></td>
          <td>{{ $u['level'] }}</td>
          <td><span class="tag {{ $u['days'] > 10 ? 'old' : '' }}">{{ $u['sinceLabel'] }}</span>@if($u['months']>1) <span class="muted">({{ $u['months'] }} شهور)</span>@endif</td>
          <td class="red num">{{ $f($u['rest']) }}</td>
          <td class="num">{{ $u['phone'] ?: '—' }}</td>
          <td style="white-space:nowrap">
            @if($u['wa'])<a class="btnx wa" target="_blank" rel="noopener" href="{{ $u['wa'] }}">واتساب</a>@endif
            <a class="btnx pay" href="{{ $u['payUrl'] }}">تسجيل الأداء</a>
          </td>
        </tr>
      @endforeach
      </tbody></table></div>@endif
    </div>
    <div>
      <div class="box" style="margin-bottom:12px"><h3>حصص اليوم <span class="n">{{ $todayLabel }}</span></h3>
        @forelse($todaySlots as $sl)
          <div class="sess"><div class="tm num">{{ substr($sl->debut,0,5) }} - {{ substr($sl->fin,0,5) }}</div>
            <div class="in"><b>{{ $sl->subject?->nom ?? '—' }}</b><span>{{ $sl->teacher?->full_name ?? 'بلا أستاذ' }} · {{ $sl->salle ?: ($sl->schoolClass?->room?->nom ?? '—') }} · {{ $sl->schoolClass?->nom }}</span></div></div>
        @empty
          <div class="empty2">ماكايناش حصص اليوم</div>
          @if($nextDay)
            <div class="note" style="margin-bottom:8px">الحصص الجاية: <b>{{ $nextDay['label'] }}</b></div>
            @foreach($nextDay['slots'] as $sl)
              <div class="sess"><div class="tm num">{{ substr($sl->debut,0,5) }} - {{ substr($sl->fin,0,5) }}</div>
                <div class="in"><b>{{ $sl->subject?->nom ?? '—' }}</b><span>{{ $sl->teacher?->full_name ?? 'بلا أستاذ' }} · {{ $sl->salle ?: ($sl->schoolClass?->room?->nom ?? '—') }} · {{ $sl->schoolClass?->nom }}</span></div></div>
            @endforeach
          @endif
        @endforelse
      </div>
      <div class="box"><h3>التسجيلات الجديدة <span class="n num">{{ $newStudents->count() }}</span></h3>
        @if($newStudents->isEmpty())<div class="empty2">ماكاين حتى تسجيل جديد فهاد المدة</div>@else
        <div class="tw" style="max-height:260px"><table><tbody>
        @foreach($newStudents as $s)
          <tr><td><a href="{{ route('students.show', $s) }}"><b>{{ $s->full_name }}</b></a><div class="muted" style="font-size:11.5px">{{ $s->subjects->pluck('nom')->implode('، ') ?: 'بلا مواد' }}</div></td>
          <td>{{ \App\Http\Controllers\DashboardController::LEVELS_AR[$s->niveau_scolaire] ?? ($s->niveau_scolaire ?: '—') }}</td>
          <td class="num">{{ $s->date_inscription?->format('d/m') }}</td></tr>
        @endforeach
        </tbody></table></div>@endif
      </div>
    </div>
  </div>

  {{-- Subjects & alerts --}}
  <div class="grid2e">
    <div class="box"><h3>المواد والأساتذة <span class="n">{{ $label }}</span></h3>
      <div class="tw"><table><thead><tr><th>المادة</th><th>الأستاذ</th><th>التلاميذ</th><th>الواجب (درهم)</th></tr></thead><tbody>
      @forelse($subjectRows as $r)
        <tr><td><b>{{ $r['name'] }}</b></td><td>{{ $r['teachers'] ?: '—' }}</td><td class="num">{{ $r['students'] }}</td><td class="num">{{ $f($r['due']) }}</td></tr>
      @empty<tr><td colspan="4" class="empty2">ماكايناش مواد</td></tr>@endforelse
      </tbody></table></div>
    </div>
    <div class="box"><h3>تنبيهات <span class="n num">{{ count($alerts) }}</span></h3>
      @forelse($alerts as $a)
        <a class="alert-i {{ $a['level'] }}" href="{{ $a['url'] }}"><div class="ic">{{ $a['level']==='red' ? '⛔' : ($a['level']==='amber' ? '⚠️' : 'ℹ️') }}</div><div><b>{{ $a['title'] }}</b><span>{{ $a['text'] }}</span></div></a>
      @empty<div class="empty2">✅ ماكاين حتى تنبيه</div>@endforelse
    </div>
  </div>
</div>
@endsection
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function(){
  if (!window.Chart) return;
  Chart.defaults.font.family = "Cairo, Tajawal, Inter, system-ui, sans-serif";
  var chart = @json($chart);
  new Chart(document.getElementById('revChart'), {
    type: 'bar',
    data: { labels: chart.map(function(x){return x.label}), datasets: [
      { label: 'الواجب', data: chart.map(function(x){return x.due}), backgroundColor: '#081f52', borderRadius: 6 },
      { label: 'المحصل', data: chart.map(function(x){return x.paid}), backgroundColor: '#f7be1d', borderRadius: 6 }
    ]},
    options: { maintainAspectRatio: false, plugins: { legend: { position: 'bottom', rtl: true } }, scales: { y: { beginAtZero: true } } }
  });
  var el = document.getElementById('subChart');
  if (el) {
    var d = @json($donut);
    new Chart(el, {
      type: 'doughnut',
      data: { labels: d.map(function(x){return x.name}), datasets: [{ data: d.map(function(x){return Math.round(x.due)}),
        backgroundColor: ['#081f52','#f7be1d','#2563eb','#0f766e','#b45309','#7c3aed','#db2777','#64748b'] }]},
      options: { maintainAspectRatio: false, cutout: '62%', plugins: { legend: { position: 'bottom', rtl: true } } }
    });
  }
})();
</script>
@endpush
