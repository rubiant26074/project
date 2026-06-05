@extends('layouts.app')

@section('content')
    <main class="auth-page">
        <section class="auth-card">
            <div class="auth-branding">
                <p class="eyebrow">Secure Access</p>
                <h1>{{ config('app.name') }}</h1>
                <p class="auth-version">Versi {{ config('app.version') }}</p>
            </div>

            <form method="POST" action="{{ route('login.store') }}" class="form-stack">
                @csrf
                <div class="form-field">
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus>
                </div>

                <div class="form-field">
                    <label for="password">Password</label>
                    <input id="password" name="password" type="password" required>
                </div>

                <label class="checkbox-inline">
                    <input type="checkbox" name="remember" value="1">
                    <span>Ingat sesi login saya</span>
                </label>

                <button class="toolbar-button toolbar-button-primary" type="submit">Masuk</button>

                <div class="auth-helper">
                    <span>Belum punya akun?</span>
                    <a class="inline-link" href="{{ route('register') }}">Daftar user baru</a>
                </div>
            </form>
        </section>
    </main>
@endsection
