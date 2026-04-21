<?php

namespace App\Livewire\Holdings\Resto\Procurement\GoodsReceipt;

use App\Models\Holdings\Resto\Procurement\Rst_PurchaseOrder;
use App\Services\Resto\GoodsReceiptService;
use Livewire\Component;
use Livewire\WithFileUploads;

class GoodsReceiptCreate extends Component
{
    use WithFileUploads;

    public array $breadcrumbs = [];

    public array $toast = ['show' => false, 'type' => 'success', 'message' => ''];

    public ?int $selectedPOId = null;

    public ?int $receiptId = null;

    public array $poItems = [];

    public array $itemsData = [];

    public string $notes = '';

    public array $documentationFiles = [];

    public bool $showPOSelector = true;

    public function mount(?int $poId = null): void
    {
        $this->breadcrumbs = [
            ['label' => 'Main Dashboard', 'route' => 'dashboard', 'color' => 'text-gray-800'],
            ['label' => 'Resto', 'route' => 'dashboard.resto', 'color' => 'text-gray-800'],
            ['label' => 'Procurement', 'route' => 'dashboard.resto.procurement', 'color' => 'text-gray-800'],
            ['label' => 'Goods Receipt', 'route' => 'dashboard.resto.goods-receipt', 'color' => 'text-gray-800'],
            ['label' => 'Create', 'color' => 'text-gray-900 font-semibold'],
        ];

        if ($poId) {
            $this->selectedPOId = $poId;
            $this->loadPOItems();
        }
    }

    public function getAvailablePOsProperty(): array
    {
        return Rst_PurchaseOrder::where('status', 'approved')
            ->where('is_closed', false)
            ->with(['vendor', 'location'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn ($po) => [
                'id' => $po->id,
                'text' => $po->po_number.' - '.$po->vendor_name.' ('.$po->location->name.')',
            ])
            ->toArray();
    }

    public function loadPOItems(): void
    {
        if (! $this->selectedPOId) {
            return;
        }

        try {
            $receipt = GoodsReceiptService::createReceipt($this->selectedPOId);
            $this->receiptId = $receipt->id;
            $this->showPOSelector = false;

            $this->poItems = $receipt->items()->with(['item', 'purchaseOrderItem.uom'])->get()->toArray();

            $this->itemsData = [];
            foreach ($this->poItems as $item) {
                $this->itemsData[$item['id']] = [
                    'id' => $item['id'],
                    'item_name' => $item['item']['name'] ?? '-',
                    'uom' => $item['purchase_order_item']['uom']['name'] ?? '-',
                    'ordered_qty' => $item['ordered_qty'],
                    'received_qty' => 0,
                    'damaged_qty' => 0,
                    'expired_qty' => 0,
                    'condition_notes' => '',
                ];
            }
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function submitReceipt(): void
    {
        if (! $this->receiptId) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Pilih PO terlebih dahulu.'];

            return;
        }

        try {
            $itemsData = array_values($this->itemsData);

            $files = [];
            foreach ($this->itemsData as $key => $item) {
                if (isset($this->documentationFiles[$key]) && $this->documentationFiles[$key]) {
                    $files[$key] = $this->documentationFiles[$key];
                }
            }

            GoodsReceiptService::receiveItems(
                $this->receiptId,
                $itemsData,
                $this->notes,
                $files
            );

            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Penerimaan barang berhasil disimpan.'];

            $this->redirectRoute('dashboard.resto.goods-receipt.detail', $this->receiptId);
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function render()
    {
        return view('livewire.holdings.resto.procurement.goods-receipt.goods-receipt-create', [
            'availablePOs' => $this->availablePOs,
        ])->layout('components.sccr-layout');
    }
}
