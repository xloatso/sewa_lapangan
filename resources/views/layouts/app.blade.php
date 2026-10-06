<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'MAUMAIN · Reservasi Lapangan')</title>
    <link rel="stylesheet" href="{{ asset('css/reservation.css') }}">
    <script src="{{ asset('js/reservation.js') }}" defer></script>
</head>
<body>
    <header class="topbar"><div class="nav-wrap">
        <a class="brand" href="{{ route('customer.index') }}">Reservasi Lapangan</a>
        <nav aria-label="Navigasi utama">
            <a class="{{ request()->routeIs('customer.*') ? 'active' : '' }}" href="{{ route('customer.index') }}">Cari lapangan</a>
            @auth
                @if(auth()->user()->is_admin)<a class="{{ request()->routeIs('admin.*', 'courts.*') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">Dashboard admin</a>@endif
                <form method="POST" action="{{ route('logout') }}">@csrf<button class="button small secondary">Keluar</button></form>
            @else
                <a class="button small secondary" href="{{ route('login') }}">Masuk admin <span aria-hidden="true">↗</span></a>
            @endauth
        </nav>
    </div></header>
    <main class="container">
        @if(session('success'))<div class="notice success" role="status">{{ session('success') }}</div>@endif
        @if($errors->any())<div class="notice error" role="alert"><strong>Periksa kembali data Anda.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        @yield('content')
    </main>

</body>
</html>
