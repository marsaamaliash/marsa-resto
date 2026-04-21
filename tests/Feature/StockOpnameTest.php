<?php

use App\Livewire\Holdings\Resto\CoreStock\StockOpname\StockOpnameDetail;
use App\Livewire\Holdings\Resto\CoreStock\StockOpname\StockOpnameTable;
use App\Models\Auth\AuthUser;
use App\Models\Holdings\Resto\CoreStock\Rst_StockBalance;
use App\Models\Holdings\Resto\CoreStock\Rst_StockOpname;
use App\Models\Holdings\Resto\CoreStock\Rst_StockOpnameItem;
use App\Models\Holdings\Resto\Master\Rst_MasterItem;
use App\Models\Holdings\Resto\Master\Rst_MasterLokasi;
use App\Models\Holdings\Resto\Master\Rst_MasterSatuan;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = AuthUser::factory()->create([
        'is_super_admin' => true,
        'must_change_password' => 0,
    ]);

    $this->actingAs($this->user);

    $this->gudang = Rst_MasterLokasi::create([
        'name' => 'Test Gudang Utama',
        'code' => 'WH-TEST-01',
        'type' => 'warehouse',
        'pic_name' => 'Budi',
        'is_active' => true,
    ]);

    $this->dapur = Rst_MasterLokasi::create([
        'name' => 'Test Kitchen A',
        'code' => 'KIT-TEST-01',
        'type' => 'kitchen',
        'pic_name' => 'Siti',
        'is_active' => true,
    ]);

    $this->satuan = Rst_MasterSatuan::create([
        'name' => 'Kilogram',
        'symbols' => 'kg',
    ]);

    $this->item = Rst_MasterItem::create([
        'name' => 'Test Item A',
        'sku' => 'ITEM-TEST-01',
        'uom_id' => $this->satuan->id,
        'category_id' => null,
        'is_active' => true,
    ]);

    Rst_StockBalance::create([
        'item_id' => $this->item->id,
        'location_id' => $this->gudang->id,
        'uom_id' => $this->satuan->id,
        'qty_available' => 100,
        'qty_reserved' => 0,
        'qty_in_transit' => 0,
        'qty_waste' => 0,
    ]);
});

afterEach(function () {
    Rst_StockOpnameItem::whereNotNull('id')->delete();
    Rst_StockOpname::withTrashed()->whereNotNull('id')->forceDelete();
    Rst_StockBalance::whereNotNull('id')->delete();
    Rst_MasterItem::withTrashed()->where('sku', 'like', 'ITEM-TEST-%')->forceDelete();
    Rst_MasterSatuan::withTrashed()->where('name', 'like', 'Test%')->forceDelete();
    Rst_MasterLokasi::withTrashed()->where('code', 'like', 'WH-TEST-%')->forceDelete();
    Rst_MasterLokasi::withTrashed()->where('code', 'like', 'KIT-TEST-%')->forceDelete();
    AuthUser::where('is_super_admin', true)->where('must_change_password', 0)->forceDelete();
});

function createTestOpname(array $overrides = []): Rst_StockOpname
{
    return Rst_StockOpname::create(array_merge([
        'reference_number' => 'SO-TEST-001',
        'location_id' => 1,
        'checker_name' => 'Test Checker',
        'checker_role' => 'Sous Chef',
        'witness_name' => 'Test Witness',
        'witness_role' => 'Store Keeper',
        'opname_date' => now()->format('Y-m-d'),
        'status' => 'draft',
        'remark' => 'Test remark',
        'is_frozen' => false,
    ], $overrides));
}

function createTestOpnameItem(int $opnameId, array $overrides = []): Rst_StockOpnameItem
{
    return Rst_StockOpnameItem::create(array_merge([
        'stock_opname_id' => $opnameId,
        'item_id' => 1,
        'location_id' => 1,
        'uom_id' => 1,
        'system_qty' => 100,
        'physical_qty' => 100,
        'difference' => 0,
        'status' => 'match',
        'remark' => null,
    ], $overrides));
}

test('user can see stock opname table', function () {
    Livewire::test(StockOpnameTable::class)
        ->assertSee('Stock Opname')
        ->assertSee('Pengecekan stok fisik vs sistem');
});

test('user can create stock opname', function () {
    Livewire::test(StockOpnameTable::class)
        ->call('openCreateOverlay')
        ->assertSet('overlayMode', 'create')
        ->set('createLocationId', $this->gudang->id)
        ->set('createCheckerName', 'Sous Chef Test')
        ->set('createCheckerRole', 'Sous Chef')
        ->set('createWitnessName', 'Store Keeper Test')
        ->set('createWitnessRole', 'Store Keeper')
        ->set('createOpnameDate', now()->format('Y-m-d'))
        ->set('createItems', [
            ['item_id' => $this->item->id, 'physical_qty' => 95, 'remark' => 'Kurang 5'],
        ])
        ->call('processCreate')
        ->assertSet('toast.type', 'success');

    $this->assertDatabaseHas('stock_opnames', [
        'location_id' => $this->gudang->id,
        'checker_name' => 'Sous Chef Test',
        'witness_name' => 'Store Keeper Test',
        'status' => 'draft',
    ], 'sccr_resto');
});

test('user can see stock opname detail', function () {
    $opname = createTestOpname([
        'location_id' => $this->gudang->id,
    ]);

    createTestOpnameItem($opname->id, [
        'item_id' => $this->item->id,
        'location_id' => $this->gudang->id,
        'uom_id' => $this->satuan->id,
    ]);

    Livewire::test(StockOpnameDetail::class, ['id' => $opname->id])
        ->assertSee('Stock Opname - Detail')
        ->assertSee('Test Checker');
});

test('user can search stock opname', function () {
    createTestOpname([
        'reference_number' => 'SO-SEARCH-001',
        'location_id' => $this->gudang->id,
    ]);

    createTestOpname([
        'reference_number' => 'SO-OTHER-002',
        'location_id' => $this->gudang->id,
    ]);

    Livewire::test(StockOpnameTable::class)
        ->set('search', 'SEARCH')
        ->call('applyFilter')
        ->assertSee('SO-SEARCH-001')
        ->assertDontSee('SO-OTHER-002');
});

test('search stock opname returns no data', function () {
    createTestOpname([
        'reference_number' => 'SO-EXIST-001',
        'location_id' => $this->gudang->id,
    ]);

    Livewire::test(StockOpnameTable::class)
        ->set('search', 'NONEXISTENT')
        ->call('applyFilter')
        ->assertSee('Data tidak ditemukan');
});

test('user can filter by status', function () {
    createTestOpname([
        'reference_number' => 'SO-DRAFT-001',
        'location_id' => $this->gudang->id,
        'status' => 'draft',
    ]);

    createTestOpname([
        'reference_number' => 'SO-REQ-002',
        'location_id' => $this->gudang->id,
        'status' => 'requested',
    ]);

    Livewire::test(StockOpnameTable::class)
        ->set('filter1', 'draft')
        ->call('applyFilter')
        ->assertSee('SO-DRAFT-001')
        ->assertDontSee('SO-REQ-002');
});

test('user can export filtered data', function () {
    createTestOpname([
        'reference_number' => 'SO-EXP-001',
        'location_id' => $this->gudang->id,
    ]);

    Livewire::test(StockOpnameTable::class)
        ->call('exportFiltered');
});

test('user can export selected data', function () {
    $opname = createTestOpname([
        'reference_number' => 'SO-EXP-001',
        'location_id' => $this->gudang->id,
    ]);

    Livewire::test(StockOpnameTable::class)
        ->set('selectedItems', [(string) $opname->id])
        ->call('exportSelected');
});

test('export selected shows warning when no items selected', function () {
    Livewire::test(StockOpnameTable::class)
        ->call('exportSelected')
        ->assertSet('toast.message', 'Pilih data terlebih dahulu');
});

test('user can delete stock opname', function () {
    $opname = createTestOpname([
        'location_id' => $this->gudang->id,
    ]);

    Livewire::test(StockOpnameTable::class)
        ->call('deleteItem', (string) $opname->id)
        ->assertSet('toast.type', 'success');

    $opname->refresh();
    expect($opname->deleted_at)->not()->toBeNull();
});

test('deleted stock opname still exists in database', function () {
    $opname = createTestOpname([
        'location_id' => $this->gudang->id,
    ]);

    $id = $opname->id;

    Livewire::test(StockOpnameTable::class)
        ->call('deleteItem', (string) $id);

    expect(Rst_StockOpname::withTrashed()->find($id))->not->toBeNull();
});

test('user can restore deleted stock opname', function () {
    $opname = createTestOpname([
        'location_id' => $this->gudang->id,
    ]);

    $opname->delete();

    Livewire::test(StockOpnameTable::class)
        ->call('restoreItem', (string) $opname->id)
        ->assertSet('toast.type', 'success');

    $opname->refresh();
    expect($opname->deleted_at)->toBeNull();
});

test('user can clone stock opname', function () {
    $opname = createTestOpname([
        'reference_number' => 'SO-CLONE-001',
        'location_id' => $this->gudang->id,
    ]);

    createTestOpnameItem($opname->id, [
        'item_id' => $this->item->id,
        'location_id' => $this->gudang->id,
        'uom_id' => $this->satuan->id,
    ]);

    Livewire::test(StockOpnameTable::class)
        ->call('cloneItem', (string) $opname->id)
        ->assertSet('toast.type', 'success');

    $this->assertDatabaseCount('stock_opnames', 2, 'sccr_resto');
});

test('user can toggle column picker', function () {
    Livewire::test(StockOpnameTable::class)
        ->assertSet('showColumnPicker', false)
        ->call('toggleColumnPicker')
        ->assertSet('showColumnPicker', true);
});

test('user can reset columns', function () {
    Livewire::test(StockOpnameTable::class)
        ->set('columnVisibility.remark', true)
        ->call('resetColumns')
        ->assertSet('columnVisibility.remark', false);
});

test('deleted row shows red background', function () {
    $opname = createTestOpname([
        'location_id' => $this->gudang->id,
    ]);

    $opname->delete();

    Livewire::test(StockOpnameTable::class)
        ->assertSee('bg-red-50');
});

test('restore button shown for deleted items', function () {
    $opname = createTestOpname([
        'location_id' => $this->gudang->id,
    ]);

    $opname->delete();

    Livewire::test(StockOpnameTable::class)
        ->assertSee('restoreItem');
});

test('user can filter deleted items', function () {
    createTestOpname([
        'reference_number' => 'SO-ACTIVE-001',
        'location_id' => $this->gudang->id,
    ]);

    $deleted = createTestOpname([
        'reference_number' => 'SO-DELETED-002',
        'location_id' => $this->gudang->id,
    ]);
    $deleted->delete();

    Livewire::test(StockOpnameTable::class)
        ->set('filterStatus', 'deleted')
        ->call('applyFilter')
        ->assertSee('SO-DELETED-002')
        ->assertDontSee('SO-ACTIVE-001');
});

test('user can clear filters', function () {
    Livewire::test(StockOpnameTable::class)
        ->set('search', 'test')
        ->set('filter1', 'draft')
        ->set('filterStatus', 'active')
        ->call('clearFilters')
        ->assertSet('search', '')
        ->assertSet('filter1', '')
        ->assertSet('filterStatus', '');
});

test('user can select all items', function () {
    createTestOpname([
        'reference_number' => 'SO-A-001',
        'location_id' => $this->gudang->id,
    ]);

    createTestOpname([
        'reference_number' => 'SO-B-002',
        'location_id' => $this->gudang->id,
    ]);

    Livewire::test(StockOpnameTable::class)
        ->set('selectAll', true)
        ->assertSet('selectAll', true);
});

test('sort by reference_number ascending', function () {
    createTestOpname([
        'reference_number' => 'SO-ZZZ-001',
        'location_id' => $this->gudang->id,
    ]);

    createTestOpname([
        'reference_number' => 'SO-AAA-002',
        'location_id' => $this->gudang->id,
    ]);

    Livewire::test(StockOpnameTable::class)
        ->set('sortField', 'reference_number')
        ->set('sortDirection', 'asc')
        ->call('applyFilter')
        ->assertSeeInOrder(['SO-AAA-002', 'SO-ZZZ-001']);
});

test('sort by reference_number descending', function () {
    createTestOpname([
        'reference_number' => 'SO-ZZZ-001',
        'location_id' => $this->gudang->id,
    ]);

    createTestOpname([
        'reference_number' => 'SO-AAA-002',
        'location_id' => $this->gudang->id,
    ]);

    Livewire::test(StockOpnameTable::class)
        ->set('sortField', 'reference_number')
        ->set('sortDirection', 'desc')
        ->call('applyFilter')
        ->assertSeeInOrder(['SO-ZZZ-001', 'SO-AAA-002']);
});

test('user can submit stock opname', function () {
    $opname = createTestOpname([
        'location_id' => $this->gudang->id,
        'status' => 'draft',
    ]);

    Livewire::test(StockOpnameTable::class)
        ->call('submitOpname', (string) $opname->id)
        ->assertSet('toast.type', 'success');

    $opname->refresh();
    expect($opname->status)->toBe('requested')
        ->and($opname->is_frozen)->toBeTrue();
});

test('user can approve stock opname as exc chef', function () {
    $opname = createTestOpname([
        'location_id' => $this->gudang->id,
        'status' => 'requested',
        'approval_level' => 0,
    ]);

    Livewire::test(StockOpnameTable::class)
        ->call('excChefCanApprove', (string) $opname->id)
        ->assertSet('toast.type', 'success');

    $opname->refresh();
    expect($opname->approval_level)->toBe(1)
        ->and($opname->exc_chef_approved_by)->toBe('Exc Chef');
});

test('user can approve stock opname as rm', function () {
    $opname = createTestOpname([
        'location_id' => $this->gudang->id,
        'status' => 'requested',
        'approval_level' => 1,
    ]);

    Livewire::test(StockOpnameTable::class)
        ->call('rmCanApprove', (string) $opname->id)
        ->assertSet('toast.type', 'success');

    $opname->refresh();
    expect($opname->approval_level)->toBe(2)
        ->and($opname->rm_approved_by)->toBe('RM');
});

test('user can approve stock opname as supervisor', function () {
    $opname = createTestOpname([
        'location_id' => $this->gudang->id,
        'status' => 'requested',
        'approval_level' => 2,
    ]);

    Livewire::test(StockOpnameTable::class)
        ->call('spvCanApprove', (string) $opname->id)
        ->assertSet('toast.type', 'success');

    $opname->refresh();
    expect($opname->approval_level)->toBe(3)
        ->and($opname->spv_approved_by)->toBe('Supervisor');
});

test('user can finalize stock opname with adjustment', function () {
    $opname = createTestOpname([
        'location_id' => $this->gudang->id,
        'status' => 'requested',
        'approval_level' => 3,
    ]);

    createTestOpnameItem($opname->id, [
        'item_id' => $this->item->id,
        'location_id' => $this->gudang->id,
        'uom_id' => $this->satuan->id,
        'system_qty' => 100,
        'physical_qty' => 95,
        'difference' => -5,
        'status' => 'deficit',
    ]);

    Livewire::test(StockOpnameDetail::class, ['id' => $opname->id])
        ->call('finalizeOpname', (string) $opname->id)
        ->assertSet('toast.type', 'success');

    $opname->refresh();
    expect($opname->status)->toBe('completed');
});

test('user can reject stock opname', function () {
    $opname = createTestOpname([
        'location_id' => $this->gudang->id,
        'status' => 'requested',
    ]);

    Livewire::test(StockOpnameTable::class)
        ->call('rejectOpname', (string) $opname->id)
        ->assertSet('toast.type', 'warning');

    $opname->refresh();
    expect($opname->status)->toBe('rejected')
        ->and($opname->is_frozen)->toBeFalse();
});

test('user can cancel stock opname', function () {
    $opname = createTestOpname([
        'location_id' => $this->gudang->id,
        'status' => 'draft',
    ]);

    Livewire::test(StockOpnameTable::class)
        ->call('cancelOpname', (string) $opname->id)
        ->assertSet('toast.type', 'warning');

    $opname->refresh();
    expect($opname->status)->toBe('cancelled');
});
