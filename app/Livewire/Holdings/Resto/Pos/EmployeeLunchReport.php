<?php

namespace App\Livewire\Holdings\Resto\Pos;

use App\Models\Holdings\Resto\Pos\Rst_EmployeeLunchTransaction;
use Livewire\Component;
use Livewire\WithPagination;

class EmployeeLunchReport extends Component
{
    use WithPagination;

    public array $breadcrumbs = [];

    public string $search = '';

    public string $dateFilter = '';

    public string $paymentFilter = '';

    public bool $showDetailModal = false;

    public ?int $selectedTransactionId = null;

    public function mount(): void
    {
        $this->breadcrumbs = [
            ['label' => 'Main Dashboard', 'route' => 'dashboard', 'color' => 'text-gray-800'],
            ['label' => 'Resto', 'route' => 'dashboard.resto', 'color' => 'text-gray-800'],
            ['label' => 'Riwayat Makan Siang', 'color' => 'text-gray-900 font-semibold'],
        ];
    }

    public function openDetail(int $id): void
    {
        $this->selectedTransactionId = $id;
        $this->showDetailModal = true;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingDateFilter(): void
    {
        $this->resetPage();
    }

    public function updatingPaymentFilter(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->dateFilter = '';
        $this->paymentFilter = '';
        $this->resetPage();
    }

    public function render()
    {
        $query = Rst_EmployeeLunchTransaction::query()
            ->when($this->search, function ($q) {
                $q->where('employee_number', 'like', '%'.$this->search.'%');
            })
            ->when($this->dateFilter, function ($q) {
                $q->whereDate('paid_at', $this->dateFilter);
            })
            ->when($this->paymentFilter, function ($q) {
                $q->where('payment_method', $this->paymentFilter);
            })
            ->orderBy('paid_at', 'desc');

        $transactions = $query->paginate(20);

        $todayTotal = Rst_EmployeeLunchTransaction::whereDate('paid_at', today())
            ->sum('total_amount');

        $todayCount = Rst_EmployeeLunchTransaction::whereDate('paid_at', today())
            ->count();

        $todayAllowanceUsed = Rst_EmployeeLunchTransaction::whereDate('paid_at', today())
            ->sum('allowance_used');

        $todayExcess = Rst_EmployeeLunchTransaction::whereDate('paid_at', today())
            ->sum('excess_amount');

        return view('livewire.holdings.resto.pos.employee-lunch-report', [
            'transactions' => $transactions,
            'todayTotal' => $todayTotal,
            'todayCount' => $todayCount,
            'todayAllowanceUsed' => $todayAllowanceUsed,
            'todayExcess' => $todayExcess,
        ])->layout('components.sccr-layout');
    }
}
