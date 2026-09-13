<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $role ? ucfirst($role) . ' Login' : 'Login' }}</title>
</head>
<body>
    <main>
        <h1>{{ $role ? ucfirst($role) . ' Login' : 'Login' }}</h1>
        <form method="POST" action="{{ route('login.store') }}">
            @csrf
            @if ($role)
                <input type="hidden" name="role" value="{{ $role }}">
            @endif
            <label for="login">Email or username</label>
            <input id="login" name="login" type="text" value="{{ old('login') }}" required autofocus>
            @error('login')<p>{{ $message }}</p>@enderror
            <label for="password">Password</label>
            <input id="password" name="password" type="password" required>
            @error('password')<p>{{ $message }}</p>@enderror
            <label><input name="remember" type="checkbox" value="1"> Remember me</label>
            <button type="submit">Log in</button>
        </form>
    </main>
</body>
</html>