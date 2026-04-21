<?php

namespace App\Services\Resto;

use App\Models\Holdings\Resto\CoreStock\Rst_StockOpname;
use App\Models\Holdings\Resto\Movement\Rst_Movement;
use App\Models\Holdings\Resto\Procurement\Rst_PurchaseRequest;

class ReferenceNumberService
{
    public static function generateMovementNumber(): string
    {
        $today = now()->format('dmY');
        $prefix = "SM-{$today}-";

        $lastMovement = Rst_Movement::withTrashed()->where('reference_number', 'like', "{$prefix}%")
            ->orderBy('reference_number', 'desc')
            ->first();

        $nextSequence = 1;
        if ($lastMovement) {
            $lastSequence = (int) substr($lastMovement->reference_number, -3);
            $nextSequence = $lastSequence + 1;
        }

        return $prefix.str_pad((string) $nextSequence, 3, '0', STR_PAD_LEFT);
    }

    public static function generatePurchaseRequestNumber(): string
    {
        $today = now()->format('Ymd');
        $prefix = "PR-{$today}-";

        $lastPR = Rst_PurchaseRequest::where('pr_number', 'like', "{$prefix}%")
            ->orderBy('pr_number', 'desc')
            ->first();

        $nextSequence = 1;
        if ($lastPR) {
            $lastSequence = (int) substr($lastPR->pr_number, -3);
            $nextSequence = $lastSequence + 1;
        }

        return $prefix.str_pad((string) $nextSequence, 3, '0', STR_PAD_LEFT);
    }

    public static function generateOpnameNumber(): string
    {
        $today = now()->format('dmY');
        $prefix = "SO-{$today}-";

        $lastOpname = Rst_StockOpname::withTrashed()->where('reference_number', 'like', "{$prefix}%")
            ->orderBy('reference_number', 'desc')
            ->first();

        $nextSequence = 1;
        if ($lastOpname) {
            $lastSequence = (int) substr($lastOpname->reference_number, -3);
            $nextSequence = $lastSequence + 1;
        }

        return $prefix.str_pad((string) $nextSequence, 3, '0', STR_PAD_LEFT);
    }
}
