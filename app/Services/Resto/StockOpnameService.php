<?php

namespace App\Services\Resto;

use App\Models\Holdings\Resto\CoreStock\Rst_StockBalance;
use App\Models\Holdings\Resto\CoreStock\Rst_StockMutation;
use App\Models\Holdings\Resto\CoreStock\Rst_StockOpname;
use App\Models\Holdings\Resto\CoreStock\Rst_StockOpnameFreeze;
use App\Models\Holdings\Resto\CoreStock\Rst_StockOpnameItem;
use App\Models\Holdings\Resto\Master\Rst_MasterItem;
use Illuminate\Support\Facades\DB;

class StockOpnameService
{
    public static function createOpname(
        int $locationId,
        string $checkerName,
        string $checkerRole,
        string $witnessName,
        string $witnessRole,
        string $opnameDate,
        array $items,
        ?string $remark = null
    ): Rst_StockOpname {
        return DB::transaction(function () use ($locationId, $checkerName, $checkerRole, $witnessName, $witnessRole, $opnameDate, $items, $remark) {
            $hasFreeze = Rst_StockOpnameFreeze::where('status', 'frozen')->exists();

            if (! $hasFreeze) {
                throw new \Exception('Lokasi harus di-freeze terlebih dahulu sebelum membuat Stock Opname.');
            }

            $opname = Rst_StockOpname::create([
                'reference_number' => ReferenceNumberService::generateOpnameNumber(),
                'location_id' => $locationId,
                'checker_name' => $checkerName,
                'checker_role' => $checkerRole,
                'witness_name' => $witnessName,
                'witness_role' => $witnessRole,
                'opname_date' => $opnameDate,
                'status' => 'draft',
                'remark' => $remark,
                'is_frozen' => true,
            ]);

            foreach ($items as $itemData) {
                $itemId = $itemData['item_id'];
                $physicalQty = $itemData['physical_qty'];

                $item = Rst_MasterItem::findOrFail($itemId);

                $systemQty = Rst_StockBalance::where('item_id', $itemId)
                    ->where('location_id', $locationId)
                    ->value('qty_available') ?? 0;

                $difference = $physicalQty - $systemQty;
                $status = abs($difference) < 0.001 ? 'match' : ($difference > 0 ? 'surplus' : 'deficit');

                Rst_StockOpnameItem::create([
                    'stock_opname_id' => $opname->id,
                    'item_id' => $itemId,
                    'location_id' => $locationId,
                    'uom_id' => $item->uom_id,
                    'system_qty' => $systemQty,
                    'physical_qty' => $physicalQty,
                    'difference' => $difference,
                    'status' => $status,
                    'remark' => $itemData['remark'] ?? null,
                ]);
            }

            return $opname;
        });
    }

    public static function reviseOpname(
        int $opnameId,
        int $itemId,
        float $newPhysicalQty,
        ?string $notes = null
    ): Rst_StockOpnameItem {
        return DB::transaction(function () use ($opnameId, $itemId, $newPhysicalQty, $notes) {
            $opname = Rst_StockOpname::findOrFail($opnameId);

            if ($opname->status !== 'draft') {
                throw new \Exception('Hanya bisa merevisi pada status Draft.');
            }

            $opnameItem = Rst_StockOpnameItem::where('stock_opname_id', $opnameId)
                ->where('item_id', $itemId)
                ->firstOrFail();

            $systemQty = $opnameItem->system_qty;
            $difference = $newPhysicalQty - $systemQty;
            $status = abs($difference) < 0.001 ? 'match' : ($difference > 0 ? 'surplus' : 'deficit');

            $opnameItem->physical_qty = $newPhysicalQty;
            $opnameItem->difference = $difference;
            $opnameItem->status = $status;
            if ($notes !== null) {
                $opnameItem->remark = $notes;
            }
            $opnameItem->save();

            return $opnameItem;
        });
    }

    public static function submitOpname(int $opnameId): Rst_StockOpname
    {
        return DB::transaction(function () use ($opnameId) {
            $opname = Rst_StockOpname::findOrFail($opnameId);

            if ($opname->status !== 'draft') {
                throw new \Exception('Hanya bisa submit pada status Draft.');
            }

            $opname->status = 'requested';
            $opname->save();

            return $opname;
        });
    }

    public static function approveOpname(
        int $opnameId,
        int $level,
        string $approverName,
        ?string $notes = null
    ): Rst_StockOpname {
        return DB::transaction(function () use ($opnameId, $level, $approverName) {
            $opname = Rst_StockOpname::findOrFail($opnameId);

            if ($opname->status !== 'requested') {
                throw new \Exception('Hanya bisa approve pada status Requested.');
            }

            $currentLevel = $opname->approval_level ?? 0;

            if ($level !== $currentLevel + 1) {
                throw new \Exception('Approval level tidak valid. Selesaikan approval sebelumnya.');
            }

            $now = now();

            match ($level) {
                1 => $opname->fill([
                    'approval_level' => 1,
                    'exc_chef_approved_by' => $approverName,
                    'exc_chef_approved_at' => $now,
                ]),
                2 => $opname->fill([
                    'approval_level' => 2,
                    'rm_approved_by' => $approverName,
                    'rm_approved_at' => $now,
                ]),
                3 => $opname->fill([
                    'approval_level' => 3,
                    'spv_approved_by' => $approverName,
                    'spv_approved_at' => $now,
                ]),
                default => throw new \Exception('Level approval tidak valid.'),
            };

            $opname->save();

            return $opname;
        });
    }

    public static function finalizeOpname(int $opnameId): Rst_StockOpname
    {
        return DB::transaction(function () use ($opnameId) {
            $opname = Rst_StockOpname::findOrFail($opnameId);

            if ($opname->status !== 'requested' || $opname->approval_level !== 3) {
                throw new \Exception('Semua approval harus diselesaikan terlebih dahulu.');
            }

            $opnameItems = Rst_StockOpnameItem::where('stock_opname_id', $opnameId)->get();

            foreach ($opnameItems as $opnameItem) {
                if (abs($opnameItem->difference) < 0.001) {
                    continue;
                }

                $balance = Rst_StockBalance::where('item_id', $opnameItem->item_id)
                    ->where('location_id', $opnameItem->location_id)
                    ->first();

                if (! $balance) {
                    continue;
                }

                $beforeQty = $balance->qty_available;
                $balance->qty_available = $opnameItem->physical_qty;
                $balance->save();

                $afterQty = $balance->qty_available;

                Rst_StockMutation::create([
                    'item_id' => $opnameItem->item_id,
                    'location_id' => $opnameItem->location_id,
                    'uom_id' => $opnameItem->uom_id,
                    'type' => $opnameItem->difference > 0 ? 'in' : 'out',
                    'reference_number' => $opname->reference_number,
                    'qty' => abs($opnameItem->difference),
                    'qty_before' => $beforeQty,
                    'qty_after' => $afterQty,
                    'from_location_id' => $opnameItem->location_id,
                    'to_location_id' => $opnameItem->location_id,
                    'user_id' => 'SYSTEM',
                    'notes' => "Stock adjustment from opname #{$opname->id}",
                ]);
            }

            $opname->status = 'completed';
            $opname->is_frozen = false;
            $opname->save();

            Rst_StockOpnameFreeze::where('status', 'frozen')
                ->update(['status' => 'released']);

            return $opname;
        });
    }

    public static function rejectOpname(
        int $opnameId,
        string $rejecterName,
        ?string $notes = null
    ): Rst_StockOpname {
        return DB::transaction(function () use ($opnameId) {
            $opname = Rst_StockOpname::findOrFail($opnameId);

            if ($opname->status !== 'requested') {
                throw new \Exception('Hanya bisa reject pada status Requested.');
            }

            $opname->status = 'rejected';
            $opname->is_frozen = false;
            $opname->save();

            Rst_StockOpnameFreeze::where('status', 'frozen')
                ->update(['status' => 'released']);

            return $opname;
        });
    }

    public static function cancelOpname(int $opnameId): Rst_StockOpname
    {
        return DB::transaction(function () use ($opnameId) {
            $opname = Rst_StockOpname::findOrFail($opnameId);

            if (! in_array($opname->status, ['draft', 'requested'])) {
                throw new \Exception('Hanya bisa cancel pada status Draft atau Requested.');
            }

            $opname->status = 'cancelled';
            $opname->is_frozen = false;
            $opname->save();

            Rst_StockOpnameFreeze::where('status', 'frozen')
                ->update(['status' => 'released']);

            return $opname;
        });
    }

    public static function cloneOpname(int $opnameId): Rst_StockOpname
    {
        return DB::transaction(function () use ($opnameId) {
            $original = Rst_StockOpname::with('items')->findOrFail($opnameId);

            $clone = $original->replicate();
            $clone->reference_number = ReferenceNumberService::generateOpnameNumber();
            $clone->status = 'draft';
            $clone->approval_level = 0;
            $clone->exc_chef_approved_by = null;
            $clone->exc_chef_approved_at = null;
            $clone->rm_approved_by = null;
            $clone->rm_approved_at = null;
            $clone->spv_approved_by = null;
            $clone->spv_approved_at = null;
            $clone->is_frozen = false;
            $clone->save();

            foreach ($original->items as $item) {
                $cloneItem = $item->replicate();
                $cloneItem->stock_opname_id = $clone->id;
                $cloneItem->save();
            }

            return $clone;
        });
    }

    public static function holdLocation(int $locationId, string $referenceNumber): void
    {
        $existing = Rst_StockOpnameFreeze::where('location_id', $locationId)
            ->where('status', 'frozen')
            ->first();

        if ($existing) {
            throw new \Exception("Lokasi ini sedang dalam status freeze oleh {$existing->reference_number}.");
        }

        Rst_StockOpnameFreeze::create([
            'location_id' => $locationId,
            'reference_number' => $referenceNumber,
            'frozen_by' => auth()->user()?->name ?? 'SYSTEM',
            'frozen_at' => now(),
            'status' => 'frozen',
        ]);
    }

    public static function releaseLocation(int $locationId, string $referenceNumber): void
    {
        Rst_StockOpnameFreeze::where('location_id', $locationId)
            ->where('reference_number', $referenceNumber)
            ->where('status', 'frozen')
            ->update(['status' => 'released']);
    }
}
