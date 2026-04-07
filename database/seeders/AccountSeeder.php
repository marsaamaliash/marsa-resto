<?php

// database/seeders/AccountSeeder.php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\AccountSubcategory;
use App\Models\Holding;
use Illuminate\Database\Seeder;

class AccountSeeder extends Seeder
{
    public function run(): void
    {
        $holdings = Holding::all();
        $subcategories = AccountSubcategory::all()->keyBy('name');

        $accounts = [
            ['type' => 'Asset', 'subcategory' => 'Kas', 'name' => 'Kas Besar'],
            ['type' => 'Asset', 'subcategory' => 'Bank', 'name' => 'Bank BCA'],
            ['type' => 'Liability', 'subcategory' => 'Hutang Usaha', 'name' => 'Hutang Supplier'],
            ['type' => 'Revenue', 'subcategory' => 'Penjualan', 'name' => 'Pendapatan Makanan'],
            ['type' => 'Expense', 'subcategory' => 'Gaji', 'name' => 'Beban Gaji'],
        ];

        foreach ($holdings as $holding) {
            foreach ($accounts as $index => $data) {
                $subcategory = $subcategories[$data['subcategory']] ?? null;

                Account::create([
                    'holding_id' => $holding->id,
                    'code' => "{$holding->code}-{$this->typeCode($data['type'])}-{$subcategory?->code}-".str_pad($index + 1, 4, '0', STR_PAD_LEFT),
                    'name' => "{$data['name']} {$holding->name}",
                    'type' => $data['type'],
                    'subcategory_id' => $subcategory?->id,
                    'is_active' => true,
                ]);
            }
        }
    }

    private function typeCode(string $type): string
    {
        return match ($type) {
            'Asset' => '1',
            'Liability' => '2',
            'Equity' => '3',
            'Revenue' => '4',
            'Expense' => '5',
            default => '0',
        };
    }
}
