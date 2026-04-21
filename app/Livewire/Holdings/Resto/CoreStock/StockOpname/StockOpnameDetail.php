<?php

namespace App\Livewire\Holdings\Resto\CoreStock\StockOpname;

use App\Models\Holdings\Resto\CoreStock\Rst_StockOpname;
use App\Services\Resto\StockOpnameService;
use Livewire\Component;

class StockOpnameDetail extends Component
{
    public array $breadcrumbs = [];

    public array $toast = ['show' => false, 'type' => 'success', 'message' => ''];

    public int $id;

    public bool $showAdjustmentForm = false;

    public bool $adjustmentLocked = false;

    public array $adjustmentItems = [];

    public function mount(int $id): void
    {
        $this->id = $id;

        $this->breadcrumbs = [
            ['label' => 'Main Dashboard', 'route' => 'dashboard', 'color' => 'text-gray-800'],
            ['label' => 'Resto', 'route' => 'dashboard.resto', 'color' => 'text-gray-800'],
            ['label' => 'Core Stock', 'route' => 'dashboard.resto.core-stock', 'color' => 'text-gray-900 font-semibold'],
            ['label' => 'Stock Opname', 'route' => 'dashboard.resto.stock-opname', 'color' => 'text-gray-800'],
            ['label' => 'Detail', 'color' => 'text-gray-900 font-semibold'],
        ];

        $detail = $this->getDetailData();
        if ($detail) {
            $this->adjustmentLocked = StockOpnameService::hasAdjustments($detail->id);

            if ($this->adjustmentLocked) {
                $this->adjustmentItems = $detail->adjustments->map(fn ($adj) => [
                    'item_id' => $adj->item_id,
                    'item_name' => $adj->item?->name ?? '-',
                    'system_qty' => $adj->system_qty,
                    'physical_qty' => $adj->physical_qty,
                    'difference' => $adj->difference,
                    'status' => $adj->status,
                    'uom' => $adj->uom?->symbols ?? '',
                    'remark' => $adj->remark ?? '',
                ])->toArray();
            }
        }
    }

    public function getDetailData(): ?Rst_StockOpname
    {
        if (! $this->id) {
            return null;
        }

        return Rst_StockOpname::with(['location', 'items.item', 'items.uom', 'adjustments.item', 'adjustments.uom'])
            ->find($this->id);
    }

    public function toggleAdjustmentForm(): void
    {
        $this->showAdjustmentForm = ! $this->showAdjustmentForm;

        if ($this->showAdjustmentForm && ! $this->adjustmentLocked) {
            $detail = $this->getDetailData();
            if ($detail) {
                $this->adjustmentItems = $detail->items->map(fn ($item) => [
                    'item_id' => $item->item_id,
                    'item_name' => $item->item?->name ?? '-',
                    'system_qty' => $item->system_qty,
                    'physical_qty' => $item->physical_qty,
                    'difference' => $item->difference,
                    'status' => $item->status,
                    'uom' => $item->uom?->symbols ?? '',
                    'remark' => $item->remark ?? '',
                ])->toArray();
            }
        }
    }

    public function saveAdjustments(): void
    {
        if ($this->adjustmentLocked) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Adjustment sudah dilakukan dan tidak bisa diubah.'];

            return;
        }

        $detail = $this->getDetailData();
        if (! $detail) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Data tidak ditemukan.'];

            return;
        }

        $itemsToSave = [];
        foreach ($this->adjustmentItems as $adjItem) {
            if ($adjItem['item_id'] > 0) {
                $itemsToSave[] = [
                    'item_id' => $adjItem['item_id'],
                    'physical_qty' => (float) $adjItem['physical_qty'],
                    'remark' => $adjItem['remark'] ?? null,
                ];
            }
        }

        try {
            StockOpnameService::saveAdjustments((int) $detail->id, $itemsToSave);

            $this->adjustmentLocked = true;
            $this->showAdjustmentForm = false;

            $detail->refresh();
            $this->adjustmentItems = $detail->adjustments->map(fn ($adj) => [
                'item_id' => $adj->item_id,
                'item_name' => $adj->item?->name ?? '-',
                'system_qty' => $adj->system_qty,
                'physical_qty' => $adj->physical_qty,
                'difference' => $adj->difference,
                'status' => $adj->status,
                'uom' => $adj->uom?->symbols ?? '',
                'remark' => $adj->remark ?? '',
            ])->toArray();

            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Adjustment berhasil disimpan.'];
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function excChefCanApprove(string $id): void
    {
        $opname = Rst_StockOpname::find($id);
        if (! $opname) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Data tidak ditemukan.'];

            return;
        }

        if ($opname->status !== 'requested') {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Hanya bisa approve pada status Requested.'];

            return;
        }

        if (($opname->approval_level ?? 0) !== 0) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Exc Chef sudah approve.'];

            return;
        }

        try {
            $approverName = auth()->user()?->name ?? 'Exc Chef';
            StockOpnameService::approveOpname((int) $id, 1, $approverName, 'Approved by Exc Chef');
            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Approved by Exc Chef.'];
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function rmCanApprove(string $id): void
    {
        $opname = Rst_StockOpname::find($id);
        if (! $opname) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Data tidak ditemukan.'];

            return;
        }

        if ($opname->status !== 'requested') {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Hanya bisa approve pada status Requested.'];

            return;
        }

        if (($opname->approval_level ?? 0) !== 1) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Belum di-approve oleh Exc Chef.'];

            return;
        }

        try {
            $approverName = auth()->user()?->name ?? 'RM';
            StockOpnameService::approveOpname((int) $id, 2, $approverName, 'Approved by RM');
            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Approved by RM.'];
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function spvCanApprove(string $id): void
    {
        $opname = Rst_StockOpname::find($id);
        if (! $opname) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Data tidak ditemukan.'];

            return;
        }

        if ($opname->status !== 'requested') {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Hanya bisa approve pada status Requested.'];

            return;
        }

        if (($opname->approval_level ?? 0) !== 2) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Belum di-approve oleh RM.'];

            return;
        }

        try {
            $approverName = auth()->user()?->name ?? 'Supervisor';
            StockOpnameService::approveOpname((int) $id, 3, $approverName, 'Approved by SPV');
            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Approved by SPV.'];
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function finalizeOpname(string $id): void
    {
        try {
            StockOpnameService::finalizeOpname((int) $id);
            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Stock adjustment berhasil dilakukan.'];
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function submitOpname(string $id): void
    {
        $opname = Rst_StockOpname::find($id);
        if (! $opname) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Data tidak ditemukan.'];

            return;
        }

        if ($opname->status !== 'draft') {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Hanya bisa submit pada status Draft.'];

            return;
        }

        try {
            StockOpnameService::submitOpname((int) $id);
            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Stock Opname disubmit untuk approval.'];
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function rejectOpname(string $id): void
    {
        try {
            $opname = Rst_StockOpname::find($id);
            if (! $opname) {
                $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Data tidak ditemukan.'];

                return;
            }

            StockOpnameService::rejectOpname((int) $id, auth()->user()?->name ?? 'SYSTEM', 'Rejected');
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Stock Opname ditolak.'];
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function render()
    {
        $detail = $this->getDetailData();

        return view('livewire.holdings.resto.core-stock.stock-opname.stock-opname-detail', [
            'detail' => $detail,
            'breadcrumbs' => $this->breadcrumbs,
        ])->layout('components.sccr-layout');
    }
}
