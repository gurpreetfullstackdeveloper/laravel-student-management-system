<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Student Portal')</title>
    <style>
        body { font-family: system-ui, sans-serif; max-width: 1100px; margin: 0 auto; padding: 1rem; color: #172033; }
        nav { display: flex; gap: 1rem; flex-wrap: wrap; align-items: center; padding-bottom: 1rem; border-bottom: 1px solid #d8dee9; }
        nav a { color: #1d4ed8; text-decoration: none; }
        nav form { margin-left: auto; }
        main { padding: 1.5rem 0; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; }
        .panel { border: 1px solid #d8dee9; border-radius: 6px; padding: 1rem; background: #fff; }
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        th, td { text-align: left; padding: .7rem; border-bottom: 1px solid #e5e7eb; }
        label { display: block; margin-top: 1rem; font-weight: 600; }
        input, textarea, button { font: inherit; padding: .55rem; margin-top: .35rem; }
        input, textarea { width: min(100%, 500px); box-sizing: border-box; }
        button { cursor: pointer; }
        .status { color: #166534; }
        .error { color: #b91c1c; }
    </style>
</head>
<body>
    <nav>
        <a href="{{ route('public.home') }}">Public site</a>
        <a href="{{ route('student.dashboard') }}">Dashboard</a>
        <a href="{{ route('student.profile.edit') }}">Profile</a>
        <a href="{{ route('student.enrollments.index') }}">Enrollment</a>
        <a href="{{ route('student.attendance.index') }}">Attendance</a>
        <a href="{{ route('student.results.index') }}">Results</a>
        <a href="{{ route('student.fees.index') }}">Fees</a>
        <a href="{{ route('student.notices.index') }}">Notices</a>
        <a href="{{ route('teacher.login') }}">Teacher portal</a>
        <a href="{{ url('/admin') }}">Super Admin</a>
        <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit">Log out</button></form>
    </nav>
    <main>
        @if (session('status'))<p class="status">{{ session('status') }}</p>@endif
        @if ($errors->any())<div class="error">{{ $errors->first() }}</div>@endif
        @yield('content')
    </main>
</body>
</html>
