@extends('layouts.default')

@section('title', '修正申請承認')

@push('css')
<link rel="stylesheet" href="{{ asset('css/detail.css') }}">
@endpush

@section('content')
<div class="container">

    <h1 class="page-title">勤怠詳細</h1>
    <form action="{{ route('admin.request.approve.update', $request->id) }}" method="POST">
        @csrf

        <div class="detail__card">

            <div class="detail__row">
                <p class="detail__label">名前</p>
                <div class="detail__value">
                    <p class="detail__text">{{ $request->user->name }}</p>
                </div>
            </div>

            <div class="detail__row">
                <p class="detail__label">日付</p>
                <div class="detail__value detail__value--date">
                    <p class="detail__year">
                        {{ $request->attendance->work_date->format('Y年') }}
                    </p>
                    <p class="detail__date">
                        {{ $request->attendance->work_date->format('n月j日') }}
                    </p>
                </div>
            </div>

            <div class="detail__row">
                <p class="detail__label detail__label--narrow">出勤・退勤</p>
                <div class="detail__value detail__value--time">
                    <p class="detail__time detail__time--start"> {{ optional($request->requested_clock_in)->format('H:i') }}</p>
                    <p class="detail__separator">〜</p>
                    <p class="detail__time detail__time--end">{{ optional($request->requested_clock_out)->format('H:i') }}</p>
                </div>
            </div>

            @foreach ($request->correctionRequestBreaks as $index => $break)
            <div class="detail__row">
                <p class="detail__label detail__label--narrow">
                    {{ $index === 0 ? '休憩' : '休憩' . ($index + 1) }}
                </p>
                <div class="detail__value detail__value--time">
                    <p class="detail__time detail__time--start">{{ optional($break->break_start)->format('H:i') }}</p>
                    <p class="detail__separator">〜</p>
                    <p class="detail__time detail__time--end">{{ optional($break->break_end)->format('H:i') }}</p>
                </div>
            </div>
            @endforeach

            <div class="detail__row">
                <p class="detail__label detail__label--narrow">備考</p>
                <div class="detail__value">
                    <p class="detail__text">{{ $request->note }}</p>
                </div>
            </div>

        </div>

        <div class="detail__button-wrapper">
            @if ($request->status === 'pending')
                <button
                    type="submit"
                    class="detail__button"
                >
                    承認
                </button>
            @else
                <button
                    type="button"
                    class="detail__button detail__button--disabled"
                    disabled
                >
                    承認済み
                </button>
            @endif
        </div>

    </form>

</div>
@endsection