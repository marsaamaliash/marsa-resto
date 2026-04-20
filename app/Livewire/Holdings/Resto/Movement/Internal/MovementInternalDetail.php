<?php

namespace App\Livewire\Holdings\Resto\Movement\Internal;

use App\Models\Holdings\Resto\CoreStock\Rst_RequestActivity;
use App\Models\Holdings\Resto\CoreStock\Rst_StockMutation;
use App\Models\Holdings\Resto\Movement\Rst_Movement;
use App\Services\Resto\StockMovementService;
use Illuminate\Support\Collection;
use Livewire\Component;

class MovementInternalDetail extends Component
{
    public array $breadcrumbs = [];

    public array $toast = ['show' => false, 'type' => 'success', 'message' => ''];

    public bool $canWrite = false;

    public bool $canCreate = false;

    public bool $canUpdate = false;

    public bool $canDelete = false;

    public bool $canApprove = false;

    public bool $canApproveExcChef = false;

    public bool $canApproveRM = false;

    public bool $canApproveSPV = false;

    public bool $canInTransit = false;

    public ?string $rejectOverlayMode = null;

    public ?string $rejectOverlayId = null;

    public string $rejectNotes = '';

    public int $id;

    private function syncCaps(): void
    {
        $u = auth()->user();

        $this->canCreate = (bool) ($u?->hasPermission('MOVEMENT_INTERNAL_CREATE') ?? false);
        $this->canUpdate = (bool) ($u?->hasPermission('MOVEMENT_INTERNAL_UPDATE') ?? false);
        $this->canDelete = (bool) ($u?->hasPermission('MOVEMENT_INTERNAL_DELETE') ?? false);
        $this->canApprove = (bool) ($u?->hasPermission('MOVEMENT_INTERNAL_APPROVE') ?? false);
        $this->canApproveExcChef = (bool) ($u?->hasPermission('MOVEMENT_INTERNAL_APPROVE_EXC_CHEF') ?? false);
        $this->canApproveRM = (bool) ($u?->hasPermission('MOVEMENT_INTERNAL_APPROVE_RM') ?? false);
        $this->canApproveSPV = (bool) ($u?->hasPermission('MOVEMENT_INTERNAL_APPROVE_SPV') ?? false);
        $this->canInTransit = (bool) ($u?->hasPermission('MOVEMENT_INTERNAL_IN_TRANSIT') ?? false);

        $this->canWrite = $this->canCreate || $this->canUpdate;
    }

    public function mount(int $id): void
    {
        $this->id = $id;

        $this->breadcrumbs = [
            ['label' => 'Main Dashboard', 'route' => 'dashboard', 'color' => 'text-gray-800'],
            ['label' => 'Resto', 'route' => 'dashboard.resto', 'color' => 'text-gray-800'],
            ['label' => 'Master Movement', 'route' => 'dashboard.resto.master-movement', 'color' => 'text-gray-900 font-semibold'],
            ['label' => 'Stock Movement', 'route' => 'dashboard.resto.movement-internal-2', 'color' => 'text-gray-800'],
            ['label' => 'Detail', 'color' => 'text-gray-900 font-semibold'],
        ];

        $this->syncCaps();
    }

    public function hydrate(): void
    {
        $this->syncCaps();
    }

    public function getDetailData(): ?Rst_Movement
    {
        if (! $this->id) {
            return null;
        }

        return Rst_Movement::with(['items.item', 'items.uom', 'fromLocation', 'toLocation'])
            ->find($this->id);
    }

    public function getStockMutations(): Collection
    {
        $movement = $this->getDetailData();
        if (! $movement || ! $movement->reference_number) {
            return collect();
        }

        return Rst_StockMutation::where('reference_number', $movement->reference_number)
            ->with(['item', 'uom', 'location', 'fromLocation', 'toLocation'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getRequestActivities(): Collection
    {
        $movement = $this->getDetailData();
        if (! $movement) {
            return collect();
        }

        return Rst_RequestActivity::where('movement_id', $movement->id)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function openRejectOverlay(string $id): void
    {
        $movement = Rst_Movement::find($id);
        if (! $movement) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Data tidak ditemukan.'];

            return;
        }

        if (! in_array($movement->status, ['requested', 'approved'])) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Hanya bisa reject pada status Requested atau Approved.'];

            return;
        }

        $this->rejectOverlayMode = 'reject';
        $this->rejectOverlayId = $id;
        $this->rejectNotes = '';
    }

    public function closeRejectOverlay(): void
    {
        $this->rejectOverlayMode = null;
        $this->rejectOverlayId = null;
        $this->rejectNotes = '';
    }

    public function excChefCanApprove(string $id): void
    {
        $movement = Rst_Movement::find($id);
        if (! $movement) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Data tidak ditemukan.'];

            return;
        }

        if ($movement->status !== 'requested') {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Hanya bisa approve pada status Requested.'];

            return;
        }

        if (($movement->approval_level ?? 0) !== 0) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Exc Chef sudah approve. Hubungi RM.'];

            return;
        }

        try {
            $approverName = auth()->user()?->name ?? 'Exc Chef';
            StockMovementService::approveMovement((int) $id, 1, $approverName, 'Approved by Exc Chef');
            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Approved by Exc Chef.'];
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function rmCanApprove(string $id): void
    {
        $movement = Rst_Movement::find($id);
        if (! $movement) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Data tidak ditemukan.'];

            return;
        }

        if ($movement->status !== 'requested') {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Hanya bisa approve pada status Requested.'];

            return;
        }

        if (($movement->approval_level ?? 0) !== 1) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Belum di-approve oleh Exc Chef.'];

            return;
        }

        try {
            $approverName = auth()->user()?->name ?? 'RM';
            StockMovementService::approveMovement((int) $id, 2, $approverName, 'Approved by RM');
            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Approved by RM.'];
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function spvCanApprove(string $id): void
    {
        $movement = Rst_Movement::find($id);
        if (! $movement) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Data tidak ditemukan.'];

            return;
        }

        if ($movement->status !== 'requested') {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Hanya bisa approve pada status Requested.'];

            return;
        }

        if (($movement->approval_level ?? 0) !== 2) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Belum di-approve oleh RM.'];

            return;
        }

        try {
            $approverName = auth()->user()?->name ?? 'Supervisor';
            StockMovementService::approveMovement((int) $id, 3, $approverName, 'Approved by SPV');
            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Approved by SPV. Movement fully approved.'];
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function excChefCanReject(string $id): void
    {
        $movement = Rst_Movement::find($id);
        if (! $movement) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Data tidak ditemukan.'];

            return;
        }

        if (! in_array($movement->status, ['requested', 'approved'])) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Hanya bisa reject pada status Requested atau Approved.'];

            return;
        }

        try {
            $rejecterName = auth()->user()?->name ?? 'Exc Chef';
            $reason = $this->rejectNotes ?: 'Rejected by Exc Chef';
            StockMovementService::rejectMovement((int) $id, $rejecterName, $reason);
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Movement rejected, stock dikembalikan.'];
            $this->closeRejectOverlay();
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function render()
    {
        $detail = $this->getDetailData();
        $stockMutations = $this->getStockMutations();
        $requestActivities = $this->getRequestActivities();

        return view('livewire.holdings.resto.movement.internal.movement-internal-detail', [
            'detail' => $detail,
            'stockMutations' => $stockMutations,
            'requestActivities' => $requestActivities,
            'breadcrumbs' => $this->breadcrumbs,
        ])->layout('components.sccr-layout');
    }
}
