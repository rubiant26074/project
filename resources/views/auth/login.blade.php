@extends('layouts.app')

@section('content')
    <main class="page page-narrow">
        <section class="login-panel">
            <div class="login-panel-copy">
                <p class="eyebrow">Secure Access</p>
                <h1>Masuk ke Project Control Manager</h1>
                <p class="hero-copy">
                    Login sebagai admin atau user untuk membuka dashboard project, flow proses, checklist, dan histori progress.
                </p>
            </div>

            <form method="POST" action="{{ route('login.store') }}" class="panel-card form-stack">
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

                <div class="info-box">
                    <strong>Akun demo</strong>
                    <p>Admin: <code>admin@project-control.local</code> / <code>admin12345</code></p>
                    <p>User: <code>user@project-control.local</code> / <code>user12345</code></p>
                </div>
            </form>
        </section>
    </main>
@endsection
