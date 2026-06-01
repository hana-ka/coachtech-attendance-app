@extends('layouts.default')

@section('title', '申請一覧')

@push('css')
<link rel="stylesheet" href="{{ asset('css/list.css') }}">
@endpush

@section('content')
<div class="container">

    <h1 class="page-title">申請一覧</h1>

    <div class="list__tabs">
        <a
            href="{{ route('request.list', ['status' => 'pending']) }}"
            class="list__tab
            {{ $status === 'pending' ? 'list__tab--active' : '' }}"
        >承認待ち</a>
        <a
            href="{{ route('request.list', ['status' => 'approved']) }}"
            class="list__tab
            {{ $status === 'approved' ? 'list__tab--active' : '' }}"
        >
            承認済み
        </a>
    </div>

    <div class="list__table-wrapper">
        <table class="list__table">
            <thead>
                <tr>
                    <th class="list__th">状態</th>
                    <th class="list__th">名前</th>
                    <th class="list__th">対象日時</th>
                    <th class="list__th">申請理由</th>
                    <th class="list__th">申請日時</th>
                    <th class="list__th list__th--detail">詳細</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($requests as $request)
                <tr>
                    <td class="list__td">
                        {{ $request->status === 'pending' ? '承認待ち' : '承認済み' }}
                    </td>
                    <td class="list__td">
                        {{ $request->user->name }}
                    </td>
                    <td class="list__td">
                        {{ $request->attendance->work_date->format('Y/m/d') }}
                    </td>
                    <td class="list__td">
                        {{ $request->note }}
                    </td>
                    <td class="list__td">
                        {{ $request->created_at->format('Y/m/d') }}
                    </td>
                    <td class="list__td list__td--detail">
                        <a
                            href="{{ route('attendance.detail', $request->attendance->work_date->format('Y-m-d')) }}"
                            class="list__link"
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