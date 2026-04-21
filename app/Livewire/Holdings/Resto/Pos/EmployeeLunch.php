<?php

namespace App\Livewire\Holdings\Resto\Pos;

use App\Models\Holdings\Resto\Pos\Rst_Employee;
use App\Models\Holdings\Resto\Pos\Rst_EmployeeLunchTransaction;
use App\Models\Holdings\Resto\Pos\Rst_Menu;
use Livewire\Component;
use Livewire\WithPagination;

class EmployeeLunch extends Component
{
    use WithPagination;

    public array $breadcrumbs = [];

    public string $search = '';

    public string $categoryFilter = '';

    public string $employeeNumber = '';

    public function mount(): void
    {
        $this->breadcrumbs = [
            ['label' => 'Main Dashboard', 'route' => 'dashboard', 'color' => 'text-gray-800'],
            ['label' => 'Resto', 'route' => 'dashboard.resto', 'color' => 'text-gray-800'],
            ['label' => 'Makan Siang Karyawan', 'color' => 'text-gray-900 font-semibold'],
        ];
    }

    public function searchEmployee(): void
    {
        if (empty($this->employeeNumber)) {
            $this->dispatch('employee-search-result',
                found: false,
                message: 'Nomor induk karyawan wajib diisi'
            );

            return;
        }

        $employee = Rst_Employee::where('employee_number', $this->employeeNumber)
            ->where('is_active', true)
            ->first();

        if (! $employee) {
            $this->dispatch('employee-search-result',
                found: false,
                message: 'Karyawan tidak ditemukan atau tidak aktif'
            );

            return;
        }

        $this->dispatch('employee-search-result',
            found: true,
            message: 'Karyawan ditemukan: '.$employee->name,
            employee: [
                'employee_number' => $employee->employee_number,
                'name' => $employee->name,
                'department' => $employee->department,
                'position' => $employee->position,
                'daily_allowance' => (float) $employee->daily_allowance,
            ],
            todayUsage: $employee->getTodayUsage(),
            allowanceRemaining: $employee->getTodayRemaining()
        );
    }

    public function checkout(array $cartItems, string $paymentMethod = 'allowance'): void
    {
        // Data $cartItems dan $paymentMethod sekarang dikirim langsung dari Alpine.js
        $result = $this->processTransaction($cartItems, $this->employeeNumber, $paymentMethod);

        if ($result['success']) {
            $this->dispatch('transaction-complete',
                success: true,
                message: $result['message'],
                newRemaining: $result['newRemaining'] ?? 0
            );
        } else {
            $this->dispatch('transaction-complete',
                success: false,
                message: $result['message']
            );
        }
    }

    public function processTransaction(array $cartItems, string $employeeNumber, string $paymentMethod): array
    {
        if (empty($cartItems) || empty($employeeNumber)) {
            return [
                'success' => false,
                'message' => 'Data tidak lengkap',
            ];
        }

        $employee = Rst_Employee::where('employee_number', $employeeNumber)
            ->where('is_active', true)
            ->first();

        if (! $employee) {
            return [
                'success' => false,
                'message' => 'Karyawan tidak ditemukan',
            ];
        }

        $totalAmount = 0;
        $items = [];
        foreach ($cartItems as $item) {
            $subtotal = $item['price'] * $item['qty'];
            $totalAmount += $subtotal;
            $items[] = [
                'menu_id' => $item['id'],
                'name' => $item['name'],
                'qty' => $item['qty'],
                'price' => $item['price'],
                'subtotal' => $subtotal,
                'note' => $item['note'] ?? null,
            ];
        }

        $allowanceRemaining = $employee->getTodayRemaining();
        $allowanceUsed = min($allowanceRemaining, $totalAmount);
        $excessAmount = max(0, $totalAmount - $allowanceRemaining);

        $resolvedPaymentMethod = 'allowance';
        if ($excessAmount > 0) {
            $resolvedPaymentMethod = $paymentMethod === 'salary' ? 'salary_deduction' : 'QRIS';
        }

        Rst_EmployeeLunchTransaction::create([
            'employee_number' => $employee->employee_number,
            'items' => $items,
            'total_amount' => $totalAmount,
            'allowance_used' => $allowanceUsed,
            'excess_amount' => $excessAmount,
            'payment_method' => $resolvedPaymentMethod,
            'paid_at' => now(),
        ]);

        $newRemaining = (float) $employee->daily_allowance - $employee->getTodayUsage();

        $message = 'Transaksi berhasil';
        if ($excessAmount > 0 && $resolvedPaymentMethod === 'salary_deduction') {
            $message = 'Transaksi berhasil - potong gaji Rp '.number_format($excessAmount, 0, ',', '.');
        } elseif ($excessAmount > 0) {
            $message = 'Transaksi berhasil - QRIS Rp '.number_format($excessAmount, 0, ',', '.');
        } else {
            $message = 'Transaksi berhasil - menggunakan jatah harian Rp '.number_format($allowanceUsed, 0, ',', '.');
        }

        return [
            'success' => true,
            'message' => $message,
            'newRemaining' => $newRemaining,
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingCategoryFilter(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Rst_Menu::where('is_active', true);

        if ($this->search) {
            $query->where('name', 'like', '%'.$this->search.'%');
        }

        if ($this->categoryFilter) {
            $query->where('category', $this->categoryFilter);
        }

        $menus = $query->orderBy('category')->orderBy('name')->paginate(12);

        $categories = Rst_Menu::where('is_active', true)
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category');

        return view('livewire.holdings.resto.pos.employee-lunch', [
            'menus' => $menus,
            'categories' => $categories,
        ])->layout('components.sccr-layout');
    }
}
