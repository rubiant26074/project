<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function createRegister(): View
    {
        return view('auth.register');
    }

    public function storeRegister(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => null,
            'password' => $validated['password'],
            'is_active' => false,
            'approved_at' => null,
            'approved_by' => null,
        ]);

        return redirect()
            ->route('login')
            ->with('status', 'Pendaftaran berhasil dikirim. Tunggu persetujuan admin untuk penentuan role dan aktivasi akun.');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withErrors(['email' => 'Email atau password tidak sesuai.'])
                ->onlyInput('email');
        }

        if (! $request->user()?->isApproved()) {
            Auth::logout();

            return back()
                ->withErrors(['email' => 'Akun Anda belum aktif. Tunggu persetujuan admin untuk penentuan role dan aktivasi akun.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
