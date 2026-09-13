<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', $settings['school_name'] ?? 'Student Management System')</title>
    <style>
        :root{--ink:#172033;--muted:#5c6678;--accent:#b45309;--line:#d9dee8;--soft:#f7f4ef}*{box-sizing:border-box}body{margin:0;color:var(--ink);font-family:Georgia,serif;background:var(--soft)}a{color:var(--accent);text-decoration:none}a:hover{text-decoration:underline}.shell{max-width:1160px;margin:auto;padding:0 1.25rem}header{background:#fff;border-bottom:1px solid var(--line)}.nav{display:flex;align-items:center;gap:1rem;min-height:76px;flex-wrap:wrap}.brand{font-size:1.25rem;font-weight:700;color:var(--ink);margin-right:auto}.nav a{font-family:system-ui,sans-serif;font-size:.92rem}.portal-links{display:flex;gap:.5rem;border-left:1px solid var(--line);padding-left:1rem}.hero{padding:5rem 0 4rem;background:linear-gradient(135deg,#fff 0%,#f3eadc 100%)}.hero h1{font-size:clamp(2.5rem,7vw,5.5rem);line-height:.95;max-width:760px;margin:0 0 1rem}.hero p{font-size:1.25rem;line-height:1.5;max-width:650px;color:var(--muted)}main{min-height:70vh}.section{padding:3rem 0}.section h2{font-size:2rem;margin-top:0}.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:1rem}.card{background:#fff;border:1px solid var(--line);padding:1.25rem;border-radius:6px}.card h3{margin-top:0}.meta{color:var(--muted);font-family:system-ui,sans-serif;font-size:.9rem}.prose{max-width:760px;font-size:1.1rem;line-height:1.7}.form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:1rem}.field{display:flex;flex-direction:column;gap:.35rem}.field.full{grid-column:1/-1}label{font-family:system-ui,sans-serif;font-weight:600}input,textarea,button{font:inherit;padding:.7rem;border:1px solid #b8c0cc;border-radius:4px}textarea{min-height:130px}button{background:var(--accent);color:#fff;border:0;cursor:pointer}.notice{padding:.75rem;background:#ecfdf5;color:#166534;border:1px solid #86efac}.error{color:#b91c1c;font-family:system-ui,sans-serif}footer{background:#172033;color:#fff;padding:2rem 0;font-family:system-ui,sans-serif}@media(max-width:800px){.nav{padding:.75rem 0}.portal-links{border-left:0;padding-left:0;width:100%}.form-grid{grid-template-columns:1fr}}
    </style>
</head>
<body>
<header><div class="shell nav"><a class="brand" href="{{ route('public.home') }}">{{ $settings['school_name'] ?? 'Oakbridge School' }}</a><a href="{{ route('public.about') }}">About</a><a href="{{ route('public.academics') }}">Academics</a><a href="{{ route('public.admissions') }}">Admissions</a><a href="{{ route('public.faculty') }}">Faculty</a><a href="{{ route('public.events') }}">Events</a><a href="{{ route('public.news') }}">News</a><a href="{{ route('public.gallery') }}">Gallery</a><a href="{{ route('public.contact') }}">Contact</a>
<div class="portal-links">
    <a href="{{ url('/login') }}">Student</a>
    <a href="{{ url('/login') }}">Teacher</a>
    <a href="{{ url('/admin') }}">Admin</a></div>

</div></header>
<main>@if(session('status'))<div class="shell section"><p class="notice">{{ session('status') }}</p></div>@endif @yield('content')</main>
<footer><div class="shell"><strong>{{ $settings['school_name'] ?? 'Oakbridge School' }}</strong><p>Learning with purpose. Growing with confidence.</p><a href="{{ route('public.faq') }}" style="color:#fbbf24">FAQ</a> · <a href="{{ route('public.notices') }}" style="color:#fbbf24">Notices</a></div></footer>
</body>
</html>
