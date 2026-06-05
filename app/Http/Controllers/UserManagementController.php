<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function index(): View
    {
        $filter = request()->string('status')->toString() ?: 'all';

        $usersQuery = User::query()->with('roleDefinition');

        if ($filter === 'pending') {
            $usersQuery->where('is_active', false);
        } elseif ($filter === 'active') {
            $usersQuery->where('is_active', true);
        }

        return view('users.index', [
            'users' => $usersQuery->orderByDesc('is_active')->orderBy('name')->get(),
            'roles' => Role::query()->where('is_active', true)->orderBy('name')->get(),
            'filter' => $filter,
            'counts' => [
                'all' => User::query()->count(),
                'active' => User::query()->where('is_active', true)->count(),
                'pending' => User::query()->where('is_active', false)->count(),
            ],
        ]);
    }

    public function create(): View
    {
        return view('users.create', [
            'roles' => Role::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', Rule::exists('roles', 'code')->where(fn ($query) => $query->where('is_active', true))],
            'is_active' => ['nullable', 'boolean'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        User::create([
            ...$validated,
            'is_active' => (bool) ($validated['is_active'] ?? false),
            'approved_at' => ($validated['is_active'] ?? false) ? now() : null,
            'approved_by' => ($validated['is_active'] ?? false) ? $request->user()?->id : null,
        ]);

        return redirect()
            ->route('users.index')
            ->with('status', 'User baru berhasil ditambahkan.');
    }

    public function edit(User $user): View
    {
        return view('users.edit', [
            'userModel' => $user,
            'roles' => Role::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function approve(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'role' => ['required', Rule::exists('roles', 'code')->where(fn ($query) => $query->where('is_active', true))],
        ]);

        $user->update([
            'role' => $validated['role'],
            'is_active' => true,
            'approved_at' => now(),
            'approved_by' => $request->user()?->id,
        ]);

        return redirect()
            ->route('users.index', ['status' => 'pending'])
            ->with('status', 'User berhasil disetujui dan diaktifkan.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'role' => ['nullable', Rule::exists('roles', 'code')->where(fn ($query) => $query->where('is_active', true))],
            'is_active' => ['nullable', 'boolean'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $isActive = (bool) ($validated['is_active'] ?? false);

        if ($isActive && blank($validated['role'] ?? null)) {
            return redirect()
                ->route('users.edit', $user)
                ->withErrors(['role' => 'Role wajib dipilih saat mengaktifkan user.']);
        }

        if (($request->user()?->id === $user->id) && (($validated['role'] ?? null) !== 'admin')) {
            return redirect()
                ->route('users.edit', $user)
                ->withErrors(['role' => 'Akun admin yang sedang login tidak bisa menurunkan rolenya sendiri.']);
        }

        if (blank($validated['password'] ?? null)) {
            unset($validated['password']);
        }

        $user->update([
            ...$validated,
            'is_active' => $isActive,
            'approved_at' => $isActive ? ($user->approved_at ?? now()) : null,
            'approved_by' => $isActive ? ($user->approved_by ?? $request->user()?->id) : null,
        ]);

        return redirect()
            ->route('users.index')
            ->with('status', 'Data user berhasil diperbarui.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($request->user()?->id === $user->id) {
            return redirect()
                ->route('users.index')
                ->with('status', 'Akun yang sedang login tidak bisa dihapus.');
        }

        $user->delete();

        return redirect()
            ->route('users.index')
            ->with('status', 'User berhasil dihapus.');
    }
}
