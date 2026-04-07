<?php

namespace App\Http\Controllers\SSO;

use App\Http\Controllers\Controller;
use App\Services\AuthNavigationService;
use Illuminate\Http\Request;

class GoController extends Controller
{
    public function go(Request $request, AuthNavigationService $nav)
    {
        $user = auth()->user();
        abort_unless($user, 401);

        $code = (string) $request->query('code', '');
        $routeName = $nav->resolveRouteByCode($user, $code);

        abort_unless($routeName, 404, 'Module/Menu code tidak ditemukan atau tidak punya akses.');

        return redirect()->route($routeName);
    }
}
