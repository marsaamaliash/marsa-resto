<?php

namespace App\Http\Middleware;

use App\Models\Holdings\Resto\CoreStock\Rst_StockOpnameFreeze;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckStockOpnameFreeze
{
    public function handle(Request $request, Closure $next): Response
    {
        $frozenLocations = Rst_StockOpnameFreeze::where('status', 'frozen')
            ->pluck('location_id')
            ->toArray();

        if (! empty($frozenLocations)) {
            $locationNames = Rst_StockOpnameFreeze::whereIn('location_id', $frozenLocations)
                ->with('location')
                ->get()
                ->map(fn ($f) => $f->location?->name ?? "Location #{$f->location_id}")
                ->implode(', ');

            abort(403, "Akses ditolak. Stock Opname sedang berlangsung dan lokasi berikut dalam status freeze: {$locationNames}.");
        }

        return $next($request);
    }
}
