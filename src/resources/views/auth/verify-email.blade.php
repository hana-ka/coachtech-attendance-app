@extends('layouts.default')

@section('title', 'メール認証')

@push('css')
<link rel="stylesheet" href="{{ asset('css/auth/verify.css') }}">
@endpush

@section('content')

<div class="verify">

    <p class="verify__text">
        登録していただいたメールアドレスに
        認証メールを送付しました。<br>
        メール認証を完了してください。
    </p>

    <a
        href="http://localhost:8025"
        class="verify__button"
    >
        認証はこちらから
    </a>

    <form
        method="POST"
        action="{{ route('verification.send') }}"
    >
        @csrf

        <button
            type="submit"
            class="verify__resend"
        >
            認証メールを再送する
        </button>

    </form>

</div>

@endsection