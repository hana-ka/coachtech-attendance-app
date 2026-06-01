<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Attendance')</title>

    <link rel="stylesheet" href="{{ asset('css/reset.css') }}">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">

    @stack('css')

</head>
<body class="body @yield('body-class')">

    <header class="header">
        <div class="header-inner">
            <img class="header-logo" src="{{ asset('images/logo.png') }}" alt="logo">

            <nav class="header-nav">
                @if(auth()->check() && !request()->routeIs('verification.notice'))
                    <div class="header-nav__links">
                        @if(auth()->user()->role === 'admin')

                        <a href="/admin/attendance/list" class="header-nav__link">勤怠一覧</a>

                        <a href="/admin/staff/list" class="header-nav__link">スタッフ一覧</a>

                        <a href="/stamp_correction_request/list" class="header-nav__link">申請一覧</a>

                        @else

                        <a href="/attendance" class="header-nav__link">勤怠</a>

                        <a href="/attendance/list" class="header-nav__link">勤怠一覧</a>

                        <a href="/stamp_correction_request/list" class="header-nav__link">申請</a>
                        @endif
                    </div>

                    <form method="POST" action="/logout" class="header-nav__logout">
                        @csrf
                        <button type="submit" class="header-nav__logout-btn">ログアウト</button>
                    </form>
                @endif
            </nav>
        </div>
    </header>
    <main class="main">

        @yield('content')

    </main>

</body>

</html>