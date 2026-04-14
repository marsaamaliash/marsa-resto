<?php

namespace App\Services\Resto;

use App\Models\Holdings\Resto\Movement\Rst_Movement;

class ReferenceNumberService
{
    public static function generateMovementNumber(): string
    {
        $today = now()->format('dmY');
        $prefix = "MOVE-{$today}-";

        $lastMovement = Rst_Movement::where('reference_number', 'like', "{$prefix}%")
            ->orderBy('reference_number', 'desc')
            ->first();

        $nextSequence = 1;
        if ($lastMovement) {
            $lastSequence = (int) substr($lastMovement->reference_number, -3);
            $nextSequence = $lastSequence + 1;
        }

        return $prefix.str_pad((string) $nextSequence, 3, '0', STR_PAD_LEFT);
    }
}
