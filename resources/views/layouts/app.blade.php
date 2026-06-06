<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ $title ?? config('app.name') }}</title>
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body data-theme="industrial-clean">
        @if (request()->routeIs('login') || request()->routeIs('register'))
            <div class="auth-shell">
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
        @else
            <div class="app-shell">
                <aside class="sidebar-shell">
                    <div class="sidebar-brand-block">
                        <a class="brand-link" href="{{ auth()->check() ? route('dashboard') : route('login') }}">{{ config('app.name') }}</a>
                        <span class="brand-subtitle">Operational dashboard for industrial project delivery</span>
                    </div>

                    @auth
                        <nav class="sidebar-nav">
                            <a class="@if (request()->routeIs('dashboard')) is-active @endif" href="{{ route('dashboard') }}">
                                <span class="sidebar-nav-icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24">
                                        <path d="M4 13.5 12 5l8 8.5"></path>
                                        <path d="M6.5 11.5V19h11v-7.5"></path>
                                    </svg>
                                </span>
                                <span class="sidebar-nav-label">Dashboard</span>
                            </a>
                            @if (auth()->user()->canAccess('process_view'))
                                <a class="@if (request()->routeIs('my-tasks.*')) is-active @endif" href="{{ route('my-tasks.index') }}">
                                    <span class="sidebar-nav-icon" aria-hidden="true">
                                        <svg viewBox="0 0 24 24">
                                            <path d="M8 6h11"></path>
                                            <path d="M8 12h11"></path>
                                            <path d="M8 18h11"></path>
                                            <path d="m3.8 6 1 1 2-2"></path>
                                            <path d="m3.8 12 1 1 2-2"></path>
                                            <path d="m3.8 18 1 1 2-2"></path>
                                        </svg>
                                    </span>
                                    <span class="sidebar-nav-label">Daftar Checklist</span>
                                </a>
                            @endif
                            @if (auth()->user()->canAccess('project_create'))
                                <a class="@if (request()->routeIs('projects.create')) is-active @endif" href="{{ route('projects.create') }}">
                                    <span class="sidebar-nav-icon" aria-hidden="true">
                                        <svg viewBox="0 0 24 24">
                                            <path d="M12 5v14"></path>
                                            <path d="M5 12h14"></path>
                                            <rect x="4" y="4" width="16" height="16" rx="3"></rect>
                                        </svg>
                                    </span>
                                    <span class="sidebar-nav-label">Project Baru</span>
                                </a>
                            @endif
                            @if (auth()->user()->canManageMasterFlows())
                                <a class="@if (request()->routeIs('master-flows.*')) is-active @endif" href="{{ route('master-flows.index') }}">
                                    <span class="sidebar-nav-icon" aria-hidden="true">
                                        <svg viewBox="0 0 24 24">
                                            <rect x="4" y="5" width="16" height="4" rx="1.5"></rect>
                                            <rect x="4" y="10" width="10" height="4" rx="1.5"></rect>
                                            <rect x="4" y="15" width="13" height="4" rx="1.5"></rect>
                                        </svg>
                                    </span>
                                    <span class="sidebar-nav-label">Master Flow</span>
                                </a>
                            @endif
                            @if (auth()->user()->canManageRoles())
                                <a class="@if (request()->routeIs('roles.*')) is-active @endif" href="{{ route('roles.index') }}">
                                    <span class="sidebar-nav-icon" aria-hidden="true">
                                        <svg viewBox="0 0 24 24">
                                            <path d="M12 5.5 14 7l2.5-.4 1 2.3 2 1.5-1 2.3 1 2.3-2 1.5-1 2.3L14 17l-2 1.5L10 17l-2.5.4-1-2.3-2-1.5 1-2.3-1-2.3 2-1.5 1-2.3L10 7z"></path>
                                            <circle cx="12" cy="12" r="2.5"></circle>
                                        </svg>
                                    </span>
                                    <span class="sidebar-nav-label">Role Management</span>
                                </a>
                            @endif
                            @if (auth()->user()->canManageUsers())
                                <a class="@if (request()->routeIs('users.*')) is-active @endif" href="{{ route('users.index') }}">
                                    <span class="sidebar-nav-icon" aria-hidden="true">
                                        <svg viewBox="0 0 24 24">
                                            <path d="M16.5 19.5v-1.2a3.8 3.8 0 0 0-3.8-3.8H8.8A3.8 3.8 0 0 0 5 18.3v1.2"></path>
                                            <circle cx="10.8" cy="8.5" r="3"></circle>
                                            <path d="M16.5 8.5a2.5 2.5 0 1 1 0 5"></path>
                                        </svg>
                                    </span>
                                    <span class="sidebar-nav-label">User Management</span>
                                </a>
                            @endif
                        </nav>
                    @endauth

                    <div class="sidebar-theme">
                        <label class="theme-switcher-label" for="theme-switcher">Theme</label>
                        <select id="theme-switcher" class="theme-select" data-theme-switcher>
                            <option value="industrial-clean">Industrial Clean</option>
                            <option value="dark-steel">Dark Steel</option>
                            <option value="control-room">Control Room</option>
                            <option value="green-schneider">Green Schneider</option>
                        </select>
                    </div>

                    @auth
                        <div class="sidebar-user">
                            <div class="user-badge">
                                <strong>{{ auth()->user()->name }}</strong>
                                <span>{{ strtoupper(auth()->user()->role) }}</span>
                            </div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button class="toolbar-button toolbar-button-small sidebar-logout" type="submit">Logout</button>
                            </form>
                        </div>
                    @endauth

                    <div class="sidebar-footnote">
                        <span>Industrial Clean Workspace</span>
                        <small>Project control dashboard for operational delivery</small>
                    </div>
                </aside>

                <div class="app-main">
                    <header class="page-topbar">
                        <div class="page-topbar-copy">
                            <strong>{{ config('app.name') }}</strong>
                            <span>Industrial project tracking workspace</span>
                        </div>
                        @auth
                            <div class="page-topbar-badge">{{ auth()->user()->name }}</div>
                        @endauth
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
            </div>
        @endif
    </body>
</html>
