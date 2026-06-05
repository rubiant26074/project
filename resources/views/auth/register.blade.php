@extends('layouts.app')

@section('content')
    <main class="auth-page">
        <section class="auth-card">
            <div class="auth-branding">
                <p class="eyebrow">User Registration</p>
                <h1>{{ config('app.name') }}</h1>
                <p class="auth-version">Versi {{ config('app.version') }}</p>
                <p class="hero-copy">
                    Isi seluruh data pendaftaran. Setelah dikirim, akun akan menunggu persetujuan admin. Role dan aktivasi akun ditentukan oleh admin.
                </p>
            </div>

            <form method="POST" action="{{ route('register.store') }}" class="form-stack">
                @csrf
                <div class="form-field">
                    <label for="name">Nama Lengkap</label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus>
                </div>

                <div class="form-field">
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required>
                </div>

                <div class="form-field">
                    <label for="password">Password</label>
                    <input id="password" name="password" type="password" required>
                </div>

                <div class="form-field">
                    <label for="password_confirmation">Konfirmasi Password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required>
                </div>

                <button class="toolbar-button toolbar-button-primary" type="submit">Kirim Pendaftaran</button>

                <div class="auth-helper">
                    <span>Sudah punya akun?</span>
                    <a class="inline-link" href="{{ route('login') }}">Kembali ke login</a>
                </div>
            </form>
        </section>
    </main>
@endsection
