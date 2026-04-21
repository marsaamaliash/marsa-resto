<?php

namespace App\Livewire\Holdings\Resto\CoreStock\StockOpname;

use App\Models\Holdings\Resto\CoreStock\Rst_StockOpname;
use App\Models\Holdings\Resto\CoreStock\Rst_StockOpnameItem;
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
    }

    public function getDetailData(): ?Rst_StockOpname
    {
        if (! $this->id) {
            return null;
        }

        return Rst_StockOpname::with(['location', 'items.item', 'items.uom'])
            ->find($this->id);
    }

    public function toggleAdjustmentForm(): void
    {
        $this->showAdjustmentForm = ! $this->showAdjustmentForm;

        if ($this->showAdjustmentForm) {
            $detail = $this->getDetailData();
            if ($detail) {
                $this->adjustmentLocked = $detail['status'] !== 'draft';

                $this->adjustmentItems = $detail->items->map(fn ($item) => [
                    'id' => $item->id,
                    'item_name' => $item->item?->name ?? '-',
                    'system_qty' => $item->system_qty,
                    'physical_qty' => $item->physical_qty,
                    'difference' => $item->difference,
                    'status' => $item->status,
                    'uom' => $item->uom?->symbols ?? '',
                    'remark' => $item->remark ?? '',
                    'confirmed' => $item->status !== 'match',
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

        foreach ($this->adjustmentItems as $adjItem) {
            if (! $adjItem['confirmed']) {
                continue;
            }

            $opnameItem = Rst_StockOpnameItem::find($adjItem['id']);
            if (! $opnameItem) {
                continue;
            }

            $newPhysicalQty = (float) $adjItem['physical_qty'];
            $newDifference = $newPhysicalQty - $opnameItem->system_qty;
            $newStatus = abs($newDifference) < 0.001 ? 'match' : ($newDifference > 0 ? 'surplus' : 'deficit');

            $opnameItem->physical_qty = $newPhysicalQty;
            $opnameItem->difference = $newDifference;
            $opnameItem->status = $newStatus;
            if (isset($adjItem['remark'])) {
                $opnameItem->remark = $adjItem['remark'];
            }
            $opnameItem->save();
        }

        $this->adjustmentLocked = true;
        $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Adjustment berhasil disimpan.'];
        $this->showAdjustmentForm = false;
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
            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Stock Opname disubmit & lokasi di-freeze.'];
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
