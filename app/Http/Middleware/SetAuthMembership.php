<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SetAuthMembership
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (! $user) {
            return $next($request);
        }

        // 1) sumber membership
        $mid = null;

        // API: prioritas header (atau query)
        if ($request->is('api/*')) {
            $mid = (int) ($request->header('X-Membership-Id') ?? $request->query('membership_id') ?? 0);
            if ($mid <= 0) {
                $mid = null;
            }
        } else {
            // WEB: ambil dari session
            $mid = (int) ($request->session()->get('auth.membership_id', 0));
            if ($mid <= 0) {
                $mid = null;
            }
        }

        // 2) kalau belum ada, auto pick jika user cuma punya 1 membership aktif
        if ($mid === null) {
            $ids = DB::table('auth_memberships')
                ->where('auth_user_id', (int) $user->id)
                ->where('is_active', 1)
                ->orderBy('id')
                ->pluck('id')
                ->map(fn ($x) => (int) $x)
                ->toArray();

            if (count($ids) === 1) {
                $mid = $ids[0];
            }
        }

        // 3) validasi membership milik user
        if ($mid !== null) {
            $ok = DB::table('auth_memberships')
                ->where('id', $mid)
                ->where('auth_user_id', (int) $user->id)
                ->where('is_active', 1)
                ->exists();

            if (! $ok) {
                // invalid / tidak punya akses → hapus
                $mid = null;
                $request->session()->forget('auth.membership_id');
            }
        }

        // 4) simpan untuk request ini
        $request->attributes->set('membership_id', $mid);

        // WEB: persist
        if (! $request->is('api/*') && $mid !== null) {
            $request->session()->put('auth.membership_id', $mid);
        }

        return $next($request);
    }
}
