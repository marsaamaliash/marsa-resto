<?php

namespace App\Livewire\Holdings\Resto\Procurement\Invoice;

use App\Models\Holdings\Resto\Procurement\Rst_PurchaseOrder;
use App\Services\Resto\GoodsReceiptService;
use Livewire\Component;
use Livewire\WithFileUploads;

class InvoiceDetail extends Component
{
    use WithFileUploads;

    public array $breadcrumbs = [];

    public array $toast = ['show' => false, 'type' => 'success', 'message' => ''];

    public ?int $poId = null;

    public ?Rst_PurchaseOrder $po = null;

    public ?string $invoiceNumber = '';

    public ?string $invoiceDate = '';

    public $invoiceFile = null;

    public bool $showUploadModal = false;

    public function mount(int $id): void
    {
        $this->poId = $id;
        $this->loadPO();

        $this->breadcrumbs = [
            ['label' => 'Main Dashboard', 'route' => 'dashboard', 'color' => 'text-gray-800'],
            ['label' => 'Resto', 'route' => 'dashboard.resto', 'color' => 'text-gray-800'],
            ['label' => 'Procurement', 'route' => 'dashboard.resto.procurement', 'color' => 'text-gray-800'],
            ['label' => 'Invoice', 'route' => 'dashboard.resto.invoice', 'color' => 'text-gray-800'],
            ['label' => $this->po?->po_number ?? 'Detail', 'color' => 'text-gray-900 font-semibold'],
        ];
    }

    public function loadPO(): void
    {
        $this->po = Rst_PurchaseOrder::with([
            'vendor',
            'location',
            'goodsReceipts.items.item',
        ])->find($this->poId);

        if ($this->po) {
            $this->invoiceNumber = $this->po->invoice_number;
            $this->invoiceDate = $this->po->invoice_date?->format('Y-m-d') ?? '';
        }
    }

    public function uploadInvoice(): void
    {
        try {
            if (! $this->po->isFullyReceived()) {
                $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Purchase Order harus fully received sebelum upload invoice.'];

                return;
            }

            GoodsReceiptService::updateInvoice(
                $this->poId,
                $this->invoiceNumber,
                $this->invoiceDate,
                $this->invoiceFile
            );

            $this->loadPO();
            $this->showUploadModal = false;
            $this->invoiceFile = null;
            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Invoice berhasil diupload.'];
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function markAsPaid(): void
    {
        try {
            GoodsReceiptService::markAsPaid($this->poId);
            $this->loadPO();
            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Invoice berhasil ditandai sebagai lunas.'];
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function render()
    {
        return view('livewire.holdings.resto.procurement.invoice.invoice-detail')
            ->layout('components.sccr-layout');
    }
}
