<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ForcePasswordChange
{
    private function isDefaultPasswordHash(?string $hashed): bool
    {
        $hashed = (string) $hashed;

        return Hash::check('password123', $hashed)
            || Hash::check('Password123', $hashed)
            || Hash::check('PASSWORD123', $hashed);
    }

    private function isForced($user): bool
    {
        if ((int) ($user->must_change_password ?? 0) !== 1) {
            return false;
        }

        return $this->isDefaultPasswordHash($user->password);
    }

    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();
        if (! $user) {
            return $next($request);
        }

        if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            $request->session()->forget('pw.force_without_old');
            $request->session()->forget('pw.intended');

            return $next($request);
        }

        $routeName = (string) optional($request->route())->getName();

        // halaman change-password & logout boleh, tapi tetap bersihin flag kalau bukan forced
        if ($routeName === 'sso.password.change' || $routeName === 'logout') {

            if ($this->isForced($user)) {
                // forced: pastikan flag ada
                $request->session()->put('pw.force_without_old', 1);
            } else {
                // mandiri: bersihkan supaya tidak nyangkut
                $request->session()->forget('pw.force_without_old');
                $request->session()->forget('pw.intended');
            }

            return $next($request);
        }

        // selain page change-password:
        if ($this->isForced($user)) {
            $request->session()->put('pw.force_without_old', 1);

            if (! $request->session()->has('pw.intended')) {
                $request->session()->put('pw.intended', $request->fullUrl());
            }

            return redirect()->route('sso.password.change');
        }

        // auto-heal kalau must_change_password nyangkut tapi password sudah bukan default
        if ((int) ($user->must_change_password ?? 0) === 1 && ! $this->isDefaultPasswordHash($user->password)) {
            $request->session()->forget('pw.force_without_old');
            $request->session()->forget('pw.intended');

            $user->must_change_password = 0;
            $user->save();

            return $next($request);
        }

        // normal
        $request->session()->forget('pw.force_without_old');
        $request->session()->forget('pw.intended');

        return $next($request);
    }
}
