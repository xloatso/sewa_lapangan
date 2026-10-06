<?php

use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('admin:create {email} {--name=Admin Lapangan}', function () {
    $email = $this->argument('email');
    if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $this->error('Email tidak valid.');

        return 1;
    }
    if (User::where('email', $email)->exists()) {
        $this->error('Email sudah terdaftar. Akun yang ada tidak diubah.');

        return 1;
    }
    $password = $this->secret('Kata sandi admin (minimal 12 karakter)');
    if (! is_string($password) || strlen($password) < 12) {
        $this->error('Kata sandi minimal 12 karakter.');

        return 1;
    }
    $user = new User;
    $user->name = $this->option('name');
    $user->email = $email;
    $user->password = $password;
    $user->is_admin = true;
    $user->save();
    $this->info('Akun admin berhasil dibuat. Silakan masuk melalui /login.');
})->purpose('Membuat akun pengelola lapangan tanpa registrasi publik');
