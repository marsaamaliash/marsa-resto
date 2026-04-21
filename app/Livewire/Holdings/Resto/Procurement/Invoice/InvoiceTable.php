<?php

namespace App\Livewire\Holdings\Resto\Procurement\Invoice;

use App\Models\Holdings\Resto\Procurement\Rst_PurchaseOrder;
use App\Services\Resto\GoodsReceiptService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InvoiceTable extends Component
{
    use WithPagination;

    public array $breadcrumbs = [];

    public array $toast = ['show' => false, 'type' => 'success', 'message' => ''];

    public bool $canUpload = false;

    public bool $canMarkPaid = false;

    public bool $canExport = false;

    public string $search = '';

    public string $filterPaymentStatus = '';

    public string $filterPaymentBy = '';

    public int $perPage = 10;

    public string $sortField = 'updated_at';

    public string $sortDirection = 'desc';

    protected array $allowedSortFields = [
        'id',
        'po_number',
        'vendor_name',
        'total_amount',
        'payment_by',
        'payment_status',
        'invoice_number',
        'invoice_date',
        'updated_at',
    ];

    protected $queryString = [
        'search' => ['except' => ''],
        'filterPaymentStatus' => ['except' => ''],
        'filterPaymentBy' => ['except' => ''],
        'perPage' => ['except' => 10],
        'sortField' => ['except' => 'updated_at'],
        'sortDirection' => ['except' => 'desc'],
    ];

    private function syncCaps(): void
    {
        $u = auth()->user();

        $this->canUpload = (bool) ($u?->hasPermission('INVOICE_UPLOAD') ?? false);
        $this->canMarkPaid = (bool) ($u?->hasPermission('INVOICE_MARK_PAID') ?? false);
        $this->canExport = (bool) ($u?->hasPermission('INVOICE_EXPORT') ?? false);
    }

    public function mount(): void
    {
        $this->breadcrumbs = [
            ['label' => 'Main Dashboard', 'route' => 'dashboard', 'color' => 'text-gray-800'],
            ['label' => 'Resto', 'route' => 'dashboard.resto', 'color' => 'text-gray-800'],
            ['label' => 'Procurement', 'route' => 'dashboard.resto.procurement', 'color' => 'text-gray-800'],
            ['label' => 'Invoice', 'color' => 'text-gray-900 font-semibold'],
        ];

        $this->syncCaps();
    }

    public function hydrate(): void
    {
        $this->syncCaps();
    }

    protected function dataQuery(): Collection
    {
        $query = Rst_PurchaseOrder::with(['vendor', 'location', 'goodsReceipts'])
            ->where(function ($q) {
                $q->whereNotNull('invoice_number')
                    ->orWhereNotNull('invoice_path')
                    ->orWhere('payment_status', '!=', 'unpaid');
            });

        if ($this->search !== '') {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('po_number', 'like', "%{$search}%")
                    ->orWhere('invoice_number', 'like', "%{$search}%")
                    ->orWhere('vendor_name', 'like', "%{$search}%");
            });
        }

        if ($this->filterPaymentStatus !== '') {
            $query->where('payment_status', $this->filterPaymentStatus);
        }

        if ($this->filterPaymentBy !== '') {
            $query->where('payment_by', $this->filterPaymentBy);
        }

        $field = in_array($this->sortField, $this->allowedSortFields) ? $this->sortField : 'updated_at';
        $direction = in_array($this->sortDirection, ['asc', 'desc']) ? $this->sortDirection : 'desc';

        return $query->orderBy($field, $direction)->get();
    }

    public function getFilterPaymentStatusOptionsProperty(): array
    {
        return [
            '' => 'All Status',
            'unpaid' => 'Unpaid',
            'pending_finance' => 'Pending Finance',
            'paid' => 'Paid',
        ];
    }

    public function getFilterPaymentByOptionsProperty(): array
    {
        return [
            '' => 'All',
            'holding' => 'Holding',
            'resto' => 'Resto',
        ];
    }

    public function render()
    {
        $allData = $this->dataQuery();
        $paginated = new LengthAwarePaginator(
            $allData->forPage($this->getPage(), $this->perPage),
            $allData->count(),
            $this->perPage,
            $this->getPage()
        );

        return view('livewire.holdings.resto.procurement.invoice.invoice-table', [
            'data' => $paginated,
        ])->layout('components.sccr-layout');
    }

    public function applyFilter(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset('search', 'filterPaymentStatus', 'filterPaymentBy');
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function markAsPaid(int $poId): void
    {
        try {
            GoodsReceiptService::markAsPaid($poId);

            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Invoice berhasil ditandai sebagai lunas.'];
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function exportExcel(): StreamedResponse
    {
        $data = $this->dataQuery();
        $filename = 'invoices_'.now()->format('Ymd_His').'.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control' => 'no-cache',
        ];

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, [
                'PO Number',
                'Vendor',
                'Lokasi',
                'Total Amount',
                'Payment By',
                'Payment Status',
                'Invoice Number',
                'Invoice Date',
            ]);

            foreach ($data as $po) {
                fputcsv($file, [
                    $po->po_number,
                    $po->vendor_name,
                    $po->location?->name ?? '-',
                    number_format($po->total_amount ?? 0, 2),
                    $po->payment_by,
                    $po->payment_status,
                    $po->invoice_number ?? '-',
                    $po->invoice_date?->format('Y-m-d') ?? '-',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    #[\Livewire\Attributes\On('refresh-invoice-table')]
    public function refresh(): void
    {
        $this->resetPage();
    }
}
