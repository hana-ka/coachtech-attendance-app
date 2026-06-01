@extends('layouts.default')

@section('title', 'ログイン')

@section('body-class', 'auth-page')

@push('css')
<link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endpush

@section('content')
<div class="auth">
    <h1 class="auth-title">ログイン</h1>

    <form method="POST" action="/login" class="auth-form">
        @csrf

        <div class="auth-group">
            <label for="email" class="auth-label">メールアドレス</label>
            <input
                type="email"
                id="email"
                name="email"
                class="auth-input"
                value="{{ old('email') }}"
                >

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

        <button type="submit" class="auth-button">
            ログインする
        </button>
    </form>

    <div class="auth-link">
        <a href="/register" class="auth-link-text">会員登録はこちら</a>
    </div>
</div>
@endsection