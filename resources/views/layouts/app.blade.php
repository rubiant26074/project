<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ $title ?? 'Project Control Manager' }}</title>
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body data-theme="industrial-clean">
        <div class="app-shell">
            <header class="topbar">
                <div class="topbar-brand-block">
                    <a class="brand-link" href="{{ auth()->check() ? route('dashboard') : route('login') }}">Project Control Manager</a>
                    <span class="brand-subtitle">Operational dashboard for industrial project delivery</span>
                </div>
                <div class="topbar-tools">
                    @auth
                        <nav class="topbar-nav">
                            <a href="{{ route('dashboard') }}">Dashboard</a>
                            @if (auth()->user()->isAdmin())
                                <a href="{{ route('projects.create') }}">Project Baru</a>
                                <a href="{{ route('master-flows.index') }}">Master Flow</a>
                            @endif
                        </nav>
                    @endauth
                    <div class="theme-switcher">
                        <label class="theme-switcher-label" for="theme-switcher">Theme</label>
                        <select id="theme-switcher" class="theme-select" data-theme-switcher>
                            <option value="industrial-clean">Industrial Clean</option>
                            <option value="dark-steel">Dark Steel</option>
                            <option value="control-room">Control Room</option>
                            <option value="green-schneider">Green Schneider</option>
                        </select>
                    </div>
                    @auth
                        <div class="topbar-user">
                            <div class="user-badge">
                                <strong>{{ auth()->user()->name }}</strong>
                                <span>{{ strtoupper(auth()->user()->role) }}</span>
                            </div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button class="toolbar-button toolbar-button-small" type="submit">Logout</button>
                            </form>
                        </div>
                    @endauth
                </div>
            </header>

            @if (session('status'))
                <div class="flash-message">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="flash-message flash-message-error">
                    {{ $errors->first() }}
                </div>
            @endif

            @yield('content')
        </div>
    </body>
</html>
