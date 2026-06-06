@extends('layouts.app')

@section('content')
    <main class="page" data-preserve-scroll-page>
        <section class="hero-banner">
            <div>
                <p class="eyebrow">Admin Panel</p>
                <h1>Role Management</h1>
                <p class="hero-copy">Kelola daftar role, status aktif role, dan deskripsi hak akses untuk user aplikasi.</p>
            </div>
        </section>

        <section class="panel-card">
            <div class="section-head">
                <div>
                    <p class="eyebrow">Role Baru</p>
                    <h2>Tambah Role</h2>
                </div>
            </div>

            <form method="POST" action="{{ route('roles.store') }}" class="form-stack">
                @csrf
                <div class="form-grid">
                    <div class="form-field">
                        <label for="code">Kode Role</label>
                        <input id="code" name="code" type="text" placeholder="viewer" required>
                    </div>
                    <div class="form-field">
                        <label for="name">Nama Role</label>
                        <input id="name" name="name" type="text" placeholder="Viewer" required>
                    </div>
                </div>
                <div class="form-field">
                    <label for="description">Deskripsi</label>
                    <textarea id="description" name="description" rows="4" placeholder="Akses baca dashboard dan detail project"></textarea>
                </div>
                <label class="checkbox-inline">
                    <input type="checkbox" name="is_active" value="1" checked>
                    <span>Role aktif dan bisa dipakai saat buat user</span>
                </label>
                <div class="toolbar-group">
                    <button class="toolbar-button toolbar-button-primary" type="submit">Simpan Role</button>
                </div>
            </form>
        </section>

        <section class="panel-card">
            <div class="section-head">
                <div>
                    <p class="eyebrow">Daftar Role</p>
                    <h2>Role Aktif dan Role Sistem</h2>
                </div>
            </div>

            <div class="table-shell">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Kode Role</th>
                            <th>Nama Role</th>
                            <th>Deskripsi</th>
                            <th>Status</th>
                            <th>Pemakai</th>
                            <th>Tipe</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($roles as $role)
                            <tr>
                                <td data-label="Kode Role">
                                    <form method="POST" action="{{ route('roles.update', $role) }}" class="table-form-stack">
                                        @csrf
                                        @method('PUT')
                                        <input name="code" type="text" value="{{ $role->code }}" @readonly($role->is_system) required>
                                </td>
                                <td data-label="Nama Role">
                                        <input name="name" type="text" value="{{ $role->name }}" required>
                                </td>
                                <td data-label="Deskripsi">
                                        <textarea name="description" rows="3">{{ $role->description }}</textarea>
                                </td>
                                <td data-label="Status">
                                        <label class="checkbox-inline checkbox-inline-compact">
                                            <input type="checkbox" name="is_active" value="1" @checked($role->is_active)>
                                            <span>{{ $role->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                                        </label>
                                </td>
                                <td data-label="Pemakai">
                                        <span class="table-chip">{{ $role->users_count }} user</span>
                                </td>
                                <td data-label="Tipe">
                                        <span class="table-chip">{{ $role->is_system ? 'Sistem' : 'Custom' }}</span>
                                </td>
                                <td data-label="Aksi">
                                        <div class="toolbar-group">
                                            <button class="toolbar-button toolbar-button-small" type="submit">Update</button>
                                        </div>
                                    </form>

                                    <form method="POST" action="{{ route('roles.destroy', $role) }}" onsubmit="return confirm('Hapus role ini?')" class="toolbar-group toolbar-group-top">
                                        @csrf
                                        @method('DELETE')
                                        <button class="toolbar-button toolbar-button-danger toolbar-button-small" type="submit" @disabled($role->is_system || $role->users_count > 0)>Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <section class="panel-card">
            <div class="section-head">
                <div>
                    <p class="eyebrow">Matrik Hak Akses</p>
                    <h2>Detail Akses Per Role</h2>
                </div>
                <div class="matrix-toolbar">
                    <form method="GET" action="{{ route('roles.index') }}" class="matrix-role-filter">
                        <label for="matrix-role">Pilih Role</label>
                        <select id="matrix-role" name="matrix_role" onchange="this.form.submit()">
                            @foreach ($matrixRoles as $role)
                                <option value="{{ $role->code }}" @selected($selectedMatrixRole?->code === $role->code)>
                                    {{ $role->name }} ({{ strtoupper($role->code) }})
                                </option>
                            @endforeach
                        </select>
                    </form>
                </div>
            </div>

            <form method="POST" action="{{ route('roles.permissions.update') }}" id="permission-matrix-form">
                @csrf
                @method('PUT')
                <input type="hidden" name="matrix_role" value="{{ $selectedMatrixRole?->code }}">

                <div class="matrix-save-row matrix-save-row-top">
                    <button class="toolbar-button toolbar-button-primary toolbar-button-small" type="submit">Simpan Matrix</button>
                </div>

                @if ($selectedMatrixRole)
                    @foreach ($permissionGroups as $groupName => $permissions)
                        <div class="subsection">
                            <h3>{{ $groupName }}</h3>
                            <div class="table-shell">
                                <table class="admin-table permission-matrix-table">
                                    <thead>
                                        <tr>
                                            <th>Hak Akses</th>
                                            <th>{{ $selectedMatrixRole->name }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($permissions as $permission)
                                            @php
                                                $rolePermission = $selectedMatrixRole->permissions->firstWhere('permission_key', $permission['key']);
                                                $isAdministrator = $selectedMatrixRole->code === 'admin';
                                                $isAllowed = $isAdministrator || ($rolePermission
                                                    ? $rolePermission->is_allowed
                                                    : in_array($selectedMatrixRole->code, $permission['roles'], true));
                                            @endphp
                                            <tr>
                                                <td data-label="Hak Akses">
                                                    <strong>{{ $permission['label'] }}</strong>
                                                </td>
                                                <td data-label="{{ $selectedMatrixRole->name }}">
                                                    <select
                                                        class="permission-select permission-select-wide {{ $isAllowed ? 'is-allowed' : 'is-denied' }}"
                                                        name="permissions[{{ $permission['key'] }}]"
                                                        @disabled($isAdministrator)
                                                    >
                                                        <option value="1" @selected($isAllowed)>Diizinkan</option>
                                                        <option value="0" @selected(! $isAllowed)>Tidak diizinkan</option>
                                                    </select>
                                                    @if ($isAdministrator)
                                                        <input type="hidden" name="permissions[{{ $permission['key'] }}]" value="1">
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="empty-state">Belum ada role aktif untuk diatur pada matrix hak akses.</div>
                @endif

                <div class="matrix-save-row">
                    <button class="toolbar-button toolbar-button-primary toolbar-button-small" type="submit">Simpan Matrix</button>
                </div>
            </form>
        </section>
    </main>
@endsection
