<?php

namespace App\Livewire\Holdings\Resto\Procurement\PurchaseRequest;

use App\Models\Holdings\Resto\CoreStock\Rst_StockBalance;
use App\Models\Holdings\Resto\Master\Rst_MasterItem;
use App\Models\Holdings\Resto\Master\Rst_MasterLokasi;
use App\Models\Holdings\Resto\Procurement\Rst_PurchaseRequest;
use App\Services\Resto\PurchaseRequestService;
use Livewire\Component;

class PurchaseRequestCreate extends Component
{
    public array $breadcrumbs = [];

    public array $toast = ['show' => false, 'type' => 'success', 'message' => ''];

    public int $selectedLocationId = 0;

    public array $criticalItems = [];

    public array $selectedCriticalItems = [];

    public array $additionalItems = [];

    public string $notes = '';

    public string $requiredDate = '';

    public bool $showCriticalTab = true;

    public ?int $editingPrId = null;

    public ?Rst_PurchaseRequest $existingPR = null;

    public bool $isEditMode = false;

    public string $sortField = 'selisih';

    public string $sortDirection = 'desc';

    protected array $allowedSortFields = ['item_name', 'actual_stock', 'min_stock', 'selisih'];

    public function mount(?int $id = null): void
    {
        $this->breadcrumbs = [
            ['label' => 'Main Dashboard', 'route' => 'dashboard', 'color' => 'text-gray-800'],
            ['label' => 'Resto', 'route' => 'dashboard.resto', 'color' => 'text-gray-800'],
            ['label' => 'Procurement', 'route' => 'dashboard.resto.procurement', 'color' => 'text-gray-800'],
            ['label' => 'Purchase Request', 'route' => 'dashboard.resto.purchase-request', 'color' => 'text-gray-800'],
            ['label' => $id ? 'Edit PR' : 'Buat PR Baru', 'color' => 'text-gray-900 font-semibold'],
        ];

        $this->requiredDate = now()->addDays(7)->format('Y-m-d');

        if ($id) {
            $this->loadExistingPR($id);
        }
    }

    private function loadExistingPR(int $id): void
    {
        $pr = Rst_PurchaseRequest::with(['items.item', 'items.uom', 'requesterLocation'])->find($id);
        if (! $pr || ! $pr->canBeEdited()) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'PR tidak ditemukan atau tidak dapat diedit.'];
            $this->redirectRoute('dashboard.resto.purchase-request');

            return;
        }

        $this->editingPrId = $id;
        $this->existingPR = $pr;
        $this->isEditMode = true;
        $this->selectedLocationId = $pr->requester_location_id;
        $this->notes = $pr->notes ?? '';
        $this->requiredDate = $pr->required_date?->format('Y-m-d') ?? now()->addDays(7)->format('Y-m-d');

        foreach ($pr->items as $item) {
            if ($item->is_critical) {
                $this->selectedCriticalItems[$item->item_id] = [
                    'id' => $item->item_id,
                    'qty' => $item->requested_qty,
                    'notes' => $item->notes ?? '',
                    'actual_stock' => $item->actual_stock,
                    'min_stock' => $item->min_stock,
                    'pr_item_id' => $item->id,
                ];
            } else {
                $minStock = $item->item?->min_stock ?? 0;
                $actualStock = $item->actual_stock ?? 0;

                $this->additionalItems[] = [
                    'id' => $item->item_id,
                    'name' => $item->item?->name ?? 'Unknown',
                    'sku' => $item->item?->sku ?? '-',
                    'uom' => $item->uom?->name ?? 'Pcs',
                    'actual_stock' => $actualStock,
                    'min_stock' => $minStock,
                    'selisih' => $minStock - $actualStock,
                    'qty' => $item->requested_qty,
                    'notes' => $item->notes ?? '',
                    'pr_item_id' => $item->id,
                ];
            }
        }

        $this->loadCriticalItems();
    }

    public function updatedSelectedLocationId(): void
    {
        $this->loadCriticalItems();
        $this->selectedCriticalItems = [];
    }

    public function sortBy(string $field): void
    {
        if (! in_array($field, $this->allowedSortFields, true)) {
            return;
        }

        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';

            return;
        }

        $this->sortField = $field;
        $this->sortDirection = 'desc';
    }

    public function loadCriticalItems(): void
    {
        if ($this->selectedLocationId === 0) {
            $this->criticalItems = [];

            return;
        }

        $criticalData = PurchaseRequestService::getCriticalStockItems($this->selectedLocationId);

        $this->criticalItems = [];
        foreach ($criticalData as $data) {
            $item = $data['item'];

            $this->criticalItems[] = [
                'id' => $item->id,
                'name' => $item->name,
                'sku' => $item->sku ?? '-',
                'uom_id' => $item->uom_id,
                'uom' => $item->uom?->name ?? 'Pcs',
                'actual_stock' => $data['actual_stock'],
                'min_stock' => $data['min_stock'],
                'deficit' => $data['deficit'],
                'status' => $data['status'],
            ];
        }

        $this->criticalItems = $this->applySorting($this->criticalItems, 'deficit');
    }

    public function getLocationsProperty(): array
    {
        return Rst_MasterLokasi::where('type', 'warehouse')
            ->orderBy('name')
            ->get()
            ->map(fn ($loc) => [
                'id' => $loc->id,
                'name' => $loc->name,
            ])
            ->toArray();
    }

    public function getAvailableItemsProperty(): array
    {
        $criticalItemIds = collect($this->criticalItems)->pluck('id')->toArray();

        $items = Rst_MasterItem::whereNotIn('id', $criticalItemIds)
            ->orderBy('name')
            ->get();

        $result = $items->map(function ($item) {
            $stokBalance = null;
            $qtyAvailable = 0;
            $minStock = $item->min_stock ?? 0;

            if ($this->selectedLocationId > 0) {
                $stokBalance = Rst_StockBalance::where('item_id', $item->id)
                    ->where('location_id', $this->selectedLocationId)
                    ->first();

                $qtyAvailable = $stokBalance?->qty_available ?? 0;
            }

            return [
                'id' => $item->id,
                'name' => $item->name,
                'item_name' => $item->name,
                'sku' => $item->sku ?? '-',
                'uom_id' => $item->uom_id,
                'uom' => $item->uom?->name ?? 'Pcs',
                'actual_stock' => $qtyAvailable,
                'min_stock' => $minStock,
                'selisih' => $minStock - $qtyAvailable,
            ];
        })->toArray();

        return $this->applySorting($result);
    }

    private function applySorting(array $items, ?string $defaultSortField = null): array
    {
        $sortField = $defaultSortField ?? $this->sortField;
        $sortDirection = $defaultSortField ? 'asc' : $this->sortDirection;

        $collection = collect($items);
        $sorted = $collection->sortBy(function ($item) use ($sortField) {
            return match ($sortField) {
                'actual_stock' => (float) $item['actual_stock'],
                'min_stock' => (float) $item['min_stock'],
                'selisih', 'deficit' => abs((float) ($item['selisih'] ?? $item['deficit'] ?? 0)),
                'item_name', 'name' => $item['item_name'] ?? $item['name'] ?? '',
                default => $item['id'],
            };
        }, SORT_REGULAR, $sortDirection === 'asc');

        return $sorted->values()->toArray();
    }

    public function toggleCriticalItem(int $itemId): void
    {
        if (isset($this->selectedCriticalItems[$itemId])) {
            unset($this->selectedCriticalItems[$itemId]);
        } else {
            $item = collect($this->criticalItems)->firstWhere('id', $itemId);
            if ($item) {
                $suggestedQty = ceil($item['deficit'] * 1.5);
                $this->selectedCriticalItems[$itemId] = [
                    'id' => $itemId,
                    'qty' => $suggestedQty,
                    'notes' => '',
                    'actual_stock' => $item['actual_stock'],
                    'min_stock' => $item['min_stock'],
                ];
            }
        }
    }

    public function toggleAdditionalItem(int $itemId): void
    {
        $existingIndex = collect($this->additionalItems)->search(fn ($item) => $item['id'] === $itemId);

        if ($existingIndex !== false) {
            array_splice($this->additionalItems, $existingIndex, 1);
        } else {
            $item = Rst_MasterItem::with('uom')->find($itemId);
            if ($item) {
                $stokBalance = null;
                $qtyAvailable = 0;

                if ($this->selectedLocationId > 0) {
                    $stokBalance = Rst_StockBalance::where('item_id', $itemId)
                        ->where('location_id', $this->selectedLocationId)
                        ->first();

                    $qtyAvailable = $stokBalance?->qty_available ?? 0;
                }

                $minStock = $item->min_stock ?? 0;

                $this->additionalItems[] = [
                    'id' => $item->id,
                    'name' => $item->name,
                    'sku' => $item->sku ?? '-',
                    'uom' => $item->uom?->name ?? 'Pcs',
                    'actual_stock' => $qtyAvailable,
                    'min_stock' => $minStock,
                    'selisih' => $minStock - $qtyAvailable,
                    'qty' => 1,
                    'notes' => '',
                ];
            }
        }
    }

    public function addAdditionalItem(int $itemId): void
    {
        $item = Rst_MasterItem::with('uom')->find($itemId);
        if (! $item) {
            return;
        }

        $this->additionalItems[] = [
            'id' => $item->id,
            'name' => $item->name,
            'uom' => $item->uom?->name ?? 'Pcs',
            'qty' => 1,
            'notes' => '',
        ];
    }

    public function removeAdditionalItem(int $index): void
    {
        if (isset($this->additionalItems[$index])) {
            array_splice($this->additionalItems, $index, 1);
        }
    }

    public function updateAdditionalQty(int $index, float $qty): void
    {
        if (isset($this->additionalItems[$index])) {
            $this->additionalItems[$index]['qty'] = max(0.01, $qty);
        }
    }

    public function updateCriticalQty(int $itemId, float $qty): void
    {
        if (isset($this->selectedCriticalItems[$itemId])) {
            $this->selectedCriticalItems[$itemId]['qty'] = max(0.01, $qty);
        }
    }

    public function saveDraft(): void
    {
        try {
            $this->validateData();

            if ($this->isEditMode && $this->editingPrId) {
                $pr = $this->updateDraft();
                $message = 'Purchase Request berhasil diperbarui.';
            } else {
                $pr = $this->createDraft();
                $message = 'Purchase Request berhasil disimpan sebagai draft.';
            }

            $this->toast = ['show' => true, 'type' => 'success', 'message' => $message];
            $this->redirectRoute('dashboard.resto.purchase-request');
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function submitToRM(): void
    {
        try {
            $this->validateData();

            $user = auth()->user()?->username ?? 'SYSTEM';

            if ($this->isEditMode && $this->editingPrId) {
                $pr = $this->updateDraft();
            } else {
                $pr = $this->createDraft();
            }

            PurchaseRequestService::submitToRM($pr->id, $this->notes, $user);

            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Purchase Request berhasil disubmit ke RM untuk approval.'];
            $this->redirectRoute('dashboard.resto.purchase-request');
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => $e->getMessage()];
        }
    }

    private function validateData(): void
    {
        if ($this->selectedLocationId === 0) {
            throw new \Exception('Pilih lokasi terlebih dahulu.');
        }

        $totalItems = count($this->selectedCriticalItems) + count($this->additionalItems);
        if ($totalItems === 0) {
            throw new \Exception('Pilih minimal 1 item untuk Purchase Request.');
        }
    }

    private function createDraft(): Rst_PurchaseRequest
    {
        $allItems = [];

        foreach ($this->selectedCriticalItems as $itemId => $data) {
            $allItems[] = [
                'item_id' => $itemId,
                'qty' => $data['qty'],
                'notes' => $data['notes'] ?? null,
            ];
        }

        foreach ($this->additionalItems as $item) {
            $allItems[] = [
                'item_id' => $item['id'],
                'qty' => $item['qty'],
                'notes' => $item['notes'] ?? null,
            ];
        }

        $user = auth()->user()?->username ?? 'SYSTEM';

        return PurchaseRequestService::createFromCritical(
            $this->selectedLocationId,
            $allItems,
            $this->notes,
            $user,
            $this->requiredDate
        );
    }

    private function updateDraft(): Rst_PurchaseRequest
    {
        $allItems = [];

        foreach ($this->selectedCriticalItems as $itemId => $data) {
            $allItems[] = [
                'item_id' => $itemId,
                'qty' => $data['qty'],
                'notes' => $data['notes'] ?? null,
            ];
        }

        foreach ($this->additionalItems as $item) {
            $allItems[] = [
                'item_id' => $item['id'],
                'qty' => $item['qty'],
                'notes' => $item['notes'] ?? null,
            ];
        }

        return PurchaseRequestService::revisePR($this->editingPrId, $allItems, $this->notes, $this->requiredDate);
    }

    public function cancel(): void
    {
        $this->redirectRoute('dashboard.resto.purchase-request');
    }

    public function render()
    {
        return view('livewire.holdings.resto.procurement.purchase-request.purchase-request-create');
    }
}
