@extends('layouts.default')

@section('title', '勤怠')

@push('css')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endpush

@section('content')
<div class="attendance">

    <div class="attendance-inner">

        <div class="attendance-status">
            <span class="attendance-status__label">{{ $statusLabel }}</span>
        </div>

        <div class="attendance-date">
            <p class="attendance-date__day">{{ $now->format('Y年n月j日') }}({{ ['日', '月', '火', '水', '木', '金', '土'][$now->dayOfWeek] }})</p>
            <h1 class="attendance-date__time">{{ $now->format('H:i') }}</h1>
        </div>

        <div class="attendance-actions">

            @if ($status === 'off')

                <form
                    action="/attendance/clock-in"
                    method="POST"
                    class="attendance-actions__form"
                >
                    @csrf

                    <button
                        type="submit"
                        class="attendance-button attendance-button--primary"
                    >
                        出勤
                    </button>
                </form>

            @elseif ($status === 'working')

                <form
                    action="/attendance/clock-out"
                    method="POST"
                    class="attendance-actions__form"
                >
                    @csrf

                    <button
                        type="submit"
                        class="attendance-button attendance-button--primary"
                    >
                        退勤
                    </button>
                </form>

                <form
                    action="/break/start"
                    method="POST"
                    class="attendance-actions__form"
                >
                    @csrf

                    <button
                        type="submit"
                        class="attendance-button attendance-button--secondary"
                    >
                        休憩入
                    </button>
                </form>

            @elseif ($status === 'break')

                <form
                    action="/break/end"
                    method="POST"
                    class="attendance-actions__form"
                >
                    @csrf

                    <button
                        type="submit"
                        class="attendance-button attendance-button--secondary"
                    >
                        休憩戻
                    </button>
                </form>

            @elseif ($status === 'done')

                <p class="attendance-actions__message">
                    お疲れ様でした。
                </p>

            @endif

        </div>

    </div>
</div>
@endsection