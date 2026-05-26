@extends('layouts.default')

@section('title', '勤怠詳細')

@push('css')
<link rel="stylesheet" href="{{ asset('css/detail.css') }}">
@endpush

@section('content')

@php

    $workDate = \Carbon\Carbon::parse($id);

    $isPending =
        $latestRequest &&
        $latestRequest->status === 'pending';

    $breaks = $isPending
        ? $latestRequest->correctionRequestBreaks
        : ($attendance
            ? $attendance->breakTimes
            : collect());

    if ($breaks->isEmpty()) {
        $breaks = collect([
            (object) [
                'break_start' => null,
                'break_end' => null,
            ]
        ]);
    }

@endphp

<div class="container">

    <h1 class="page-title">勤怠詳細</h1>

    <form action="{{ route('correction.store', $id) }}" method="POST" class="detail__form">
    @csrf

        <div class="detail__card">

            <div class="detail__row">
                <p class="detail__label">名前</p>
                <div class="detail__value">
                    <p class="detail__text">{{ auth()->user()->name }}</p>
                </div>
            </div>

            <div class="detail__row">
                <p class="detail__label">日付</p>
                <div class="detail__value detail__value--date">
                    <p class="detail__year">{{ $workDate->format('Y年') }}</p>
                    <p class="detail__date">{{ $workDate->format('n月j日') }}</p>
                </div>
            </div>

            <div class="detail__row">
                <p class="detail__label detail__label--narrow">出勤・退勤</p>
                <div class="detail__value detail__value--time">
                    @if ($isPending)
                        <span class="detail__time detail__time--start">
                            {{ optional($attendance?->clock_in)->format('H:i') }}
                        </span>
                    @else
                        <input
                            type="time"
                            name="clock_in"
                            class="detail__input-time"
                            value="{{ optional($attendance?->clock_in)->format('H:i') }}">

                        @error('clock_in')
                        <p class="detail__error">
                            {{ $message }}
                        </p>
                        @enderror

                    @endif
                    <span class="detail__separator">〜</span>
                    @if ($isPending)
                        <span class="detail__time detail__time--end">
                            {{ optional($attendance?->clock_out)->format('H:i') }}
                        </span>
                    @else
                        <input
                            type="time"
                            name="clock_out"
                            class="detail__input-time"
                            value="{{ optional($attendance?->clock_out)->format('H:i') }}">

                        @error('clock_out')
                        <p class="detail__error">
                            {{ $message }}
                        </p>
                        @enderror

                    @endif
                </div>
            </div>

            @foreach ($breaks as $index => $break)

            <div class="detail__row">

                <p class="detail__label detail__label--narrow">
                    {{ $index === 0 ? '休憩' : '休憩' . ($index + 1) }}
                </p>

                <div class="detail__value detail__value--time">

                    @if ($isPending)
                        <span class="detail__time detail__time--start">
                            {{ optional($break->break_start)->format('H:i') }}
                        </span>
                    @else
                        <input
                            type="time"
                            name="break_start[]"
                            class="detail__input-time"
                            value="{{ optional($break->break_start)->format('H:i') }}">

                            @error("break_start.$index")
                            <p class="detail__error">
                                {{ $message }}
                            </p>
                            @enderror

                    @endif
                    <span class="detail__separator">〜</span>
                    @if ($isPending)

                        <span class="detail__time detail__time--end">
                            {{ optional($break->break_end)->format('H:i') }}
                        </span>
                    @else
                        <input
                            type="time"
                            name="break_end[]"
                            class="detail__input-time"
                            value="{{ optional($break->break_end)->format('H:i') }}">

                        @error("break_end.$index")
                        <p class="detail__error">
                            {{ $message }}
                        </p>
                        @enderror

                    @endif
                </div>

            </div>

            @endforeach

            <div class="detail__row">
                <p class="detail__label detail__label--narrow">備考</p>
                <div class="detail__value">
                    @if ($isPending)
                        <p class="detail__text">
                            {{ $latestRequest->note }}
                        </p>
                    @else
                        <textarea
                            name="note"
                            class="detail__textarea"
                        ></textarea>

                        @error('note')
                        <p class="detail__error">
                            {{ $message }}
                        </p>
                        @enderror

                    @endif
                </div>
            </div>
        </div>

        @if ($isPending)
        <p class="detail__note">
            ※承認待ちのため修正はできません。
        </p>
        @endif

        @if (!$isPending)
        <div class="detail__button-wrapper">
            <button
                type="submit"
                class="detail__button"
            >
                修正
            </button>
        </div>
        @endif

    </form>
</div>
@endsection