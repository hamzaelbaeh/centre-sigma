<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Connexion — Centre Sigma</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
<div class="login-wrap">
    <div class="login-card">
        <div style="display:flex;gap:10px;align-items:center;margin-bottom:16px">
            <div class="logo" style="width:44px;height:44px;border-radius:12px;background:#f7be1d;color:#081f52;display:flex;align-items:center;justify-content:center;font-weight:800">N</div>
            <div>
                <strong>NOOR ACADEMY</strong><br>
                <span class="muted">Centre Sigma / CenterFlow</span>
            </div>
        </div>
        <h1>Connexion</h1>
        <p>Année scolaire 2026/2027</p>
        @if($errors->any())
            <div class="alert alert-error">{{ $errors->first() }}</div>
        @endif
        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="form-group">
                <label>Nom d'utilisateur</label>
                <input class="form-input" style="width:100%" type="text" name="username" value="{{ old('username','admin') }}" required autofocus>
            </div>
            <div class="form-group">
                <label>Mot de passe</label>
                <input class="form-input" style="width:100%" type="password" name="password" value="admin123" required>
            </div>
            <label style="display:flex;gap:8px;align-items:center;font-size:13px;margin-bottom:14px">
                <input type="checkbox" name="remember"> Se souvenir de moi
            </label>
            <button class="btn btn-gold" style="width:100%;justify-content:center" type="submit">Se connecter</button>
        </form>
    </div>
</div>
</body>
</html>
