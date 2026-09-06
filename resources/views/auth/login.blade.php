<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" @if(app()->getLocale()==='ar') dir="rtl" @endif>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Connexion') }} — Centre Sigma</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ @filemtime(public_path('css/app.css')) ?: time() }}">
</head>
<body>
<div class="login-wrap">
    <div class="login-card">
        <div style="display:flex;gap:10px;align-items:center;justify-content:space-between;margin-bottom:16px">
            <div style="display:flex;gap:10px;align-items:center">
                <div class="logo" style="width:44px;height:44px;border-radius:12px;background:#f7be1d;color:#081f52;display:flex;align-items:center;justify-content:center;font-weight:800">N</div>
                <div>
                    <strong>NOOR ACADEMY</strong><br>
                    <span class="muted">Centre Sigma / CenterFlow</span>
                </div>
            </div>
            <div class="lang-toggle">
                <a href="{{ route('locale.switch', 'fr') }}" class="lang-btn {{ app()->getLocale()==='fr' ? 'active' : '' }}">FR</a>
                <a href="{{ route('locale.switch', 'ar') }}" class="lang-btn {{ app()->getLocale()==='ar' ? 'active' : '' }}">AR</a>
            </div>
        </div>
        <h1>{{ __('Connexion') }}</h1>
        <p>{{ __('Année scolaire') }} 2026/2027</p>
        @if($errors->any())
            <div class="alert alert-error">{{ $errors->first() }}</div>
        @endif
        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="form-group">
                <label>{{ __("Nom d'utilisateur") }}</label>
                <input class="form-input" style="width:100%" type="text" name="username" value="{{ old('username') }}" required autofocus>
            </div>
            <div class="form-group">
                <label>{{ __('Mot de passe') }}</label>
                <input class="form-input" style="width:100%" type="password" name="password" autocomplete="current-password" required>
            </div>
            <label style="display:flex;gap:8px;align-items:center;font-size:13px;margin-bottom:14px">
                <input type="checkbox" name="remember"> {{ __('Se souvenir de moi') }}
            </label>
            <button class="btn btn-gold" style="width:100%;justify-content:center" type="submit">{{ __('Se connecter') }}</button>
        </form>
    </div>
</div>
</body>
</html>
