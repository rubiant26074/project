@extends('layouts.app')

@section('content')
    <main class="page">
        <section class="hero-banner">
            <div>
                <p class="eyebrow">Admin Panel</p>
                <h1>User Management</h1>
                <p class="hero-copy">Kelola akun user, role yang dipakai, approval pendaftaran baru, dan akses administrasi aplikasi.</p>
            </div>
            <div class="hero-actions">
                <a class="toolbar-button" href="{{ route('roles.index') }}">Kelola Role</a>
            </div>
        </section>

        <section class="panel-card">
            <div class="section-head">
                <div>
                    <p class="eyebrow">User Baru</p>
                    <h2>Tambah User</h2>
                </div>
            </div>

            <form method="POST" action="{{ route('users.store') }}" class="form-stack">
                @csrf
                <div class="form-grid">
                    <div class="form-field">
                        <label for="name">Nama User</label>
                        <input id="name" name="name" type="text" value="{{ old('name') }}" required>
                    </div>
                    <div class="form-field">
                        <label for="email">Email</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" required>
                    </div>
                </div>
                <div class="form-grid form-grid-3">
                    <div class="form-field">
                        <label for="role">Role</label>
                        <select id="role" name="role" required>
                            <option value="">Pilih role</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role->code }}" @selected(old('role') === $role->code)>{{ $role->name }} ({{ $role->code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-field">
                        <label for="password">Password</label>
                        <input id="password" name="password" type="password" required>
                    </div>
                    <div class="form-field">
                        <label for="password_confirmation">Konfirmasi Password</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" required>
                    </div>
                </div>
                <label class="checkbox-inline">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', true))>
                    <span>User aktif dan boleh login</span>
                </label>
                <div class="toolbar-group">
                    <button class="toolbar-button toolbar-button-primary" type="submit">Simpan User</button>
                </div>
            </form>
        </section>

        <section class="panel-card">
            <div class="section-head">
                <div>
                    <p class="eyebrow">Daftar User</p>
                    <h2>Semua Akun Aplikasi</h2>
                </div>
                <div class="toolbar-group">
                    <a class="toolbar-button toolbar-button-small {{ $filter === 'all' ? 'toolbar-button-primary' : '' }}" href="{{ route('users.index', ['status' => 'all']) }}">Semua ({{ $counts['all'] }})</a>
                    <a class="toolbar-button toolbar-button-small {{ $filter === 'active' ? 'toolbar-button-primary' : '' }}" href="{{ route('users.index', ['status' => 'active']) }}">Aktif ({{ $counts['active'] }})</a>
                    <a class="toolbar-button toolbar-button-small {{ $filter === 'pending' ? 'toolbar-button-primary' : '' }}" href="{{ route('users.index', ['status' => 'pending']) }}">Pending Approval ({{ $counts['pending'] }})</a>
                </div>
            </div>

            <div class="table-shell">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Nama User</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Approval</th>
                            <th>Password Baru</th>
                            <th>Konfirmasi</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr>
                                <td data-label="Nama User">
                                    <form method="POST" action="{{ route('users.update', $user) }}" class="table-form-stack">
                                        @csrf
                                        @method('PUT')
                                        <input name="name" type="text" value="{{ $user->name }}" required>
                                </td>
                                <td data-label="Email">
                                        <input name="email" type="email" value="{{ $user->email }}" required>
                                </td>
                                <td data-label="Role">
                                        <select name="role">
                                            <option value="">Pilih role</option>
                                            @foreach ($roles as $role)
                                                <option value="{{ $role->code }}" @selected($user->role === $role->code)>{{ $role->name }}</option>
                                            @endforeach
                                        </select>
                                </td>
                                <td data-label="Status">
                                        <label class="checkbox-inline checkbox-inline-compact">
                                            <input type="checkbox" name="is_active" value="1" @checked($user->is_active)>
                                            <span>{{ $user->is_active ? 'Aktif' : 'Pending' }}</span>
                                        </label>
                                </td>
                                <td data-label="Approval">
                                        <div class="inline-meta inline-meta-compact">
                                            <span>{{ $user->roleDefinition?->name ?? ($user->role ? strtoupper($user->role) : 'Belum ditentukan') }}</span>
                                            <span>{{ $user->approved_at ? $user->approved_at->format('d M Y H:i') : 'Belum disetujui' }}</span>
                                        </div>
                                        @if (! $user->is_active)
                                            <div class="inline-meta inline-meta-compact toolbar-group-top">
                                                <span>Untuk approve: pilih role, centang aktif, lalu klik Update.</span>
                                            </div>
                                        @endif
                                </td>
                                <td data-label="Password Baru">
                                        <input name="password" type="password" placeholder="Kosongkan jika tetap">
                                </td>
                                <td data-label="Konfirmasi">
                                        <input name="password_confirmation" type="password" placeholder="Ulangi password">
                                </td>
                                <td data-label="Aksi">
                                        <div class="toolbar-group">
                                            <button class="toolbar-button toolbar-button-small" type="submit">Update</button>
                                        </div>
                                    </form>

                                    <form method="POST" action="{{ route('users.destroy', $user) }}" onsubmit="return confirm('Hapus user ini?')" class="toolbar-group toolbar-group-top">
                                        @csrf
                                        @method('DELETE')
                                        <button class="toolbar-button toolbar-button-danger toolbar-button-small" type="submit">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8">
                                    <div class="empty-state">
                                        Tidak ada data user pada filter ini.
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </main>
@endsection
