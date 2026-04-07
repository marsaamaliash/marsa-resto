<?php

namespace App\Traits;

use App\Models\Holdings\Campus\LMS\LmsRoom;

trait ResolvesRoom
{
    public function resolveRoom(?LmsRoom $room = null): ?LmsRoom
    {
        if ($room) {
            return $room;
        }

        $nip = auth()->user()->nip ?? null;

        return $nip
            ? LmsRoom::where('lecturer_nip', $nip)->latest()->first()
            : null;
    }
}
