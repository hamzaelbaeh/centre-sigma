<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Centre Sigma') — {{ $appSettings->nom_etablissement ?? 'NOOR ACADEMY' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <style>
        :root {
            --gold: {{ $appSettings->couleur_principale ?? '#f7be1d' }};
            --sidebar: {{ $appSettings->couleur_sidebar ?? '#081f52' }};
        }
    </style>
    @stack('styles')
</head>
<body>
@php
    $yearLabel = $activeYear->nom ?? '2026/2027';
    $nav = [
        ['group' => null, 'items' => [
            ['route'=>'dashboard','label'=>'Tableau de bord','match'=>'dashboard'],
        ]],
        ['group' => 'SCOLAIRE', 'items' => [
            ['route'=>'students.index','label'=>'Élèves','match'=>'students.*'],
            ['route'=>'parents.index','label'=>'Parents / Tuteurs','match'=>'parents.*'],
            ['route'=>'classes.index','label'=>'Classes','match'=>'classes.*'],
            ['route'=>'teachers.index','label'=>'Enseignants','match'=>'teachers.*'],
            ['route'=>'subjects.index','label'=>'Matières','match'=>'subjects.*'],
            ['route'=>'timetable.index','label'=>'Emploi du temps','match'=>'timetable.*'],
            ['route'=>'attendance.index','label'=>'Présences','match'=>'attendance.*'],
        ]],
        ['group' => 'FINANCES', 'items' => [
            ['route'=>'payments.index','label'=>'Paiements élèves','match'=>'payments.*'],
            ['route'=>'fees.index','label'=>'Config. frais','match'=>'fees.*'],
            ['route'=>'payroll_teachers.index','label'=>'Paie enseignants','match'=>'payroll_teachers.*'],
            ['route'=>'staff.index','label'=>'Employés admin.','match'=>'staff.*'],
            ['route'=>'staff_payroll.index','label'=>'Paie employés','match'=>'staff_payroll.*'],
            ['route'=>'expenses.index','label'=>'Dépenses','match'=>'expenses.*'],
        ]],
        ['group' => 'GESTION', 'items' => [
            ['route'=>'departures.index','label'=>'Élèves sortants','match'=>'departures.*'],
            ['route'=>'trainings.index','label'=>'Formations','match'=>'trainings.*'],
            ['route'=>'years.index','label'=>'Années scolaires','match'=>'years.*'],
        ]],
        ['group' => 'SYSTÈME', 'items' => [
            ['route'=>'documents.index','label'=>'Documents','match'=>'documents.*'],
            ['route'=>'reports.index','label'=>'Rapports','match'=>'reports.*'],
            ['route'=>'users.index','label'=>'Utilisateurs','match'=>'users.*'],
            ['route'=>'settings.index','label'=>'Paramètres','match'=>'settings.*'],
        ]],
    ];
@endphp
<div class="app">
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <div class="logo">
                @if(!empty($appSettings?->logo))
                    <img src="{{ asset('storage/'.$appSettings->logo) }}" alt="" style="width:100%;height:100%;object-fit:cover;border-radius:10px">
                @else
                    N
                @endif
            </div>
            <div class="titles">
                <strong>{{ $appSettings->nom_etablissement ?? 'NOOR ACADEMY' }}</strong>
                <span>{{ $appSettings->sous_titre ?? 'SOUTIEN SCOLAIRE' }}</span>
            </div>
        </div>
        <nav style="padding:8px 0 20px;flex:1">
            @foreach($nav as $section)
                <div class="nav-group">
                    @if($section['group'])
                        <div class="nav-group-title">{{ $section['group'] }}</div>
                    @endif
                    @foreach($section['items'] as $item)
                        <a href="{{ route($item['route']) }}" class="nav-link {{ request()->routeIs($item['match']) ? 'active' : '' }}">
                            <span class="ico">•</span> {{ $item['label'] }}
                        </a>
                    @endforeach
                </div>
            @endforeach
        </nav>
        <div class="sidebar-footer">Année scolaire {{ $yearLabel }}</div>
    </aside>
    <div class="main">
        <header class="topbar">
            <span class="pill gold">{{ $yearLabel }}</span>
            <div class="lang-toggle">
                <button type="button" class="lang-btn active" title="Français">FR</button>
                <button type="button" class="lang-btn" title="العربية">AR</button>
            </div>
            <div class="user-chip">
                <div class="avatar">{{ strtoupper(substr(auth()->user()->name ?? 'A',0,1)) }}</div>
                <div style="font-size:12.5px;line-height:1.2">
                    <strong>{{ auth()->user()->name ?? 'Administrateur' }}</strong><br>
                    <span class="muted">{{ auth()->user()->role ?? '' }}</span>
                </div>
                <form method="POST" action="{{ route('logout') }}" style="margin:0 0 0 6px">
                    @csrf
                    <button class="btn btn-sm btn-ghost" type="submit">Quitter</button>
                </form>
            </div>
        </header>
        <div class="content">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert-error">
                    <ul style="margin:0;padding-left:18px">
                        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                    </ul>
                </div>
            @endif
            @yield('content')
        </div>
    </div>
</div>
@stack('scripts')
</body>
</html>
