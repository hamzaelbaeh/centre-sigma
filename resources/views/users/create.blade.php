@extends('layouts.app')
@section('title',__('Nouvel utilisateur'))
@section('content')
@php
    $student = $student ?? null;
    $parent = $parent ?? null;
    $class = $class ?? null;
    $teacher = $teacher ?? null;
    $subject = $subject ?? null;
    $staff = $staff ?? null;
    $expense = $expense ?? null;
    $training = $training ?? null;
    $user = $user ?? null;
@endphp
<div class="page-head"><div><h1>{{ __('Nouvel utilisateur') }}</h1></div></div>
<form class="card" method="POST" action="{{ route('users.store') }}">@csrf

<div class="form-group"><label>{{ __("Nom d'utilisateur") }} <span class="req">*</span></label><input class="form-input" style="width:100%" name="username" value="{{ old('username',$user->username ?? '') }}" required></div>
<div class="form-group"><label>{{ __('Nom complet') }} <span class="req">*</span></label><input class="form-input" style="width:100%" name="name" value="{{ old('name',$user->name ?? '') }}" required></div>
<div class="form-group"><label>{{ __('Rôle') }}</label><select class="form-select" style="width:100%" name="role">@foreach(['Administrateur','Direction','Comptable','Secrétaire','Enseignant','Surveillant'] as $r)<option value="{{ $r }}" @selected(old('role',$user->role ?? '')==$r)>{{ __($r) }}</option>@endforeach</select></div>
<div class="form-group"><label>{{ __('Mot de passe') }} @if(isset($user))<span class="muted">(laisser vide pour ne pas changer)</span>@else<span class="req">*</span>@endif</label><input class="form-input" style="width:100%" type="password" name="password" @if(!isset($user)) required @endif></div>
<label style="display:flex;gap:8px;align-items:center;margin-bottom:12px"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $user->is_active ?? true))> Compte actif</label>
<button class="btn btn-gold" type="submit">{{ __('Enregistrer') }}</button></form>
@endsection
