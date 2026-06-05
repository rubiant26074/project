@csrf

<div class="form-stack">
    <div class="form-grid">
        <div class="form-field">
            <label for="name">Nama User</label>
            <input id="name" name="name" type="text" value="{{ old('name', $userModel->name ?? '') }}" required>
        </div>
        <div class="form-field">
            <label for="email">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email', $userModel->email ?? '') }}" required>
        </div>
    </div>

    <div class="form-grid">
        <div class="form-field">
            <label for="role">Role</label>
            <select id="role" name="role" {{ isset($userModel) ? '' : 'required' }}>
                <option value="">Pilih role</option>
                @foreach ($roles as $role)
                    <option value="{{ $role->code }}" @selected(old('role', $userModel->role ?? '') === $role->code)>{{ $role->name }} ({{ $role->code }})</option>
                @endforeach
            </select>
        </div>
        <div class="form-field">
            <label for="password">{{ isset($userModel) ? 'Password Baru' : 'Password' }}</label>
            <input id="password" name="password" type="password" {{ isset($userModel) ? '' : 'required' }}>
        </div>
    </div>

    <div class="form-field">
        <label for="password_confirmation">Konfirmasi Password</label>
        <input id="password_confirmation" name="password_confirmation" type="password" {{ isset($userModel) ? '' : 'required' }}>
    </div>

    <label class="checkbox-inline">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $userModel->is_active ?? true))>
        <span>User aktif dan boleh login</span>
    </label>

    <div class="toolbar-group">
        <button class="toolbar-button toolbar-button-primary" type="submit">{{ $submitLabel }}</button>
        <a class="toolbar-button" href="{{ $cancelUrl }}">Batal</a>
    </div>
</div>
