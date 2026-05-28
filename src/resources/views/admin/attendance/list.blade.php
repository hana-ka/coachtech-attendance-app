@extends('layouts.default')

@section('title', '勤怠一覧')

@push('css')
<link rel="stylesheet" href="{{ asset('css/list.css') }}">
<link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
/>
@endpush

@section('content')
<div class="container">


    <h1 class="page-title">
        {{ $currentDate->format('Y年n月j日') }}の勤怠
    </h1>

    <div class="list__header">
        <a
            href="{{ route('admin.attendance.list', [
                'date' => $currentDate->copy()->subDay()->format('Y-m-d')
            ]) }}"
            class="list__nav-btn">
            <i class="fa-solid fa-arrow-left"></i>
            前日
        </a>
        <h2 class="list__month">
            <i class="fa-solid fa-calendar-days"></i>
            {{ $currentDate->format('Y/m/d') }}
        </h2>
        <a
            href="{{ route('admin.attendance.list', [
                'date' => $currentDate->copy()->addDay()->format('Y-m-d')
            ]) }}"
            class="list__nav-btn">
            翌日
            <i class="fa-solid fa-arrow-right"></i>
        </a>
    </div>

    <div class="list__table-wrapper">
        <table class="list__table">
            <thead>
                <tr>
                    <th class="list__th list__th--name">名前</th>
                    <th class="list__th">出勤</th>
                    <th class="list__th">退勤</th>
                    <th class="list__th">休憩</th>
                    <th class="list__th">合計</th>
                    <th class="list__th">詳細</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($attendances as $attendance)
                <tr>
                    <td class="list__td">
                        {{ $attendance->user->name }}
                    </td>
                    <td class="list__td">
                        {{ optional($attendance->clock_in)->format('H:i') }}
                    </td>
                    <td class="list__td">
                        {{ optional($attendance->clock_out)->format('H:i') }}
                    </td>
                    <td class="list__td">

                        @php

                            $breakMinutes = 0;

                            foreach ($attendance->breakTimes as $breakTime) {

                                if ($breakTime->break_end) {

                                    $breakMinutes +=
                                        $breakTime->break_start
                                            ->diffInMinutes($breakTime->break_end);

                                }
                            }

                            $breakHours = floor($breakMinutes / 60);

                            $breakRemainMinutes = $breakMinutes % 60;

                        @endphp

                        {{ sprintf('%02d:%02d', $breakHours, $breakRemainMinutes) }}

                    </td>
                    <td class="list__td">

                        @if ($attendance->clock_in && $attendance->clock_out)

                            @php

                                $workMinutes =
                                    $attendance->clock_in
                                        ->diffInMinutes($attendance->clock_out);

                                $totalMinutes = $workMinutes - $breakMinutes;

                                $workHours = floor($totalMinutes / 60);

                                $workRemainMinutes = $totalMinutes % 60;

                            @endphp

                            {{ sprintf('%02d:%02d', $workHours, $workRemainMinutes) }}

                        @endif

                    <td class="list__td">
                        <a
                            class="list__link"
                            href="{{ route('admin.attendance.detail', $attendance->user_id) }}
                                ?date={{ $currentDate->format('Y-m-d') }}"
                        >
                            詳細
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</div>
@endsection