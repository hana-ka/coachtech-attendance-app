@extends('layouts.default')

@section('title', '会員登録')

@section('body-class', 'auth-page')

@push('css')
<link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endpush

@section('content')
<div class="auth">
    <h1 class="auth-title">会員登録</h1>

    <form method="POST" action="/register" class="auth-form">
        @csrf

        <div class="auth-group">
            <label for="name" class="auth-label">名前</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" class="auth-input">

            @error('name')
                <p class="error">{{ $message }}</p>
            @enderror
        </div>

        <div class="login-group">
            <label for="email" class="auth-label">メールアドレス</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" class="auth-input">

            @error('email')
                <p class="error">{{ $message }}</p>
            @enderror
        </div>

        <div class="auth-group">
            <label for="password" class="auth-label">パスワード</label>
            <input type="password" id="password" name="password" class="auth-input">

            @error('password')
                <p class="error">{{ $message }}</p>
            @enderror
        </div>

        <div class="auth-group">
            <label for="password_confirmation" class="auth-label">パスワード確認</label>
            <input type="password" id="password_confirmation" name="password_confirmation" class="auth-input">
        </div>

        <button type="submit" class="auth-button">
            登録する
        </button>
    </form>

    <div class="auth-link">
        <a href="/login" class="auth-link-text">ログインはこちら</a>
    </div>
</div>
@endsection