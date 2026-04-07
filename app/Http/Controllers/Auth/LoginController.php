<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function create()
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->only('login', 'password');

        // Tentukan apakah login pakai email atau nip
        $field = filter_var($credentials['login'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        if (Auth::attempt([$field => $credentials['login'], 'password' => $credentials['password'], 'is_locked' => 0], $request->boolean('remember'))) {
            $request->session()->regenerate();

            // Pastikan identity aktif (kalau tidak, logout)
            $user = auth()->user();
            if (! $user->isSuperAdmin() && ! $user->isActive()) {
                Auth::logout();
                throw ValidationException::withMessages(['login' => 'Akun tidak aktif/terkunci.']);
            }

            return redirect()->intended(RouteServiceProvider::HOME);
        }

        throw ValidationException::withMessages([
            'login' => __('Kredensial tidak cocok dengan data kami.'),
        ]);
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
