@extends('layouts.app')
@section('title', 'Masuk Admin · MAUMAIN')
@section('content')
<section class="login-wrap"><form class="panel login-panel" action="{{ route('login') }}" method="POST">@csrf<h2>Login Admin</h2><p class="muted">Masuk menggunakan akun pengelola Anda.</p><label for="email">Email</label><input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="admin@lapangan.test"><label for="password">Kata sandi</label><input id="password" name="password" type="password" required autocomplete="current-password" placeholder="Masukkan kata sandi"><button class="button full">Masuk dashboard →</button><a class="back-link" href="{{ route('customer.index') }}">← Kembali ke lapangan</a></form></section>
@endsection
