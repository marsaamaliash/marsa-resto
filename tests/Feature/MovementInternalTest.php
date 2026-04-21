<?php

use App\Livewire\Holdings\Resto\Movement\Internal\MovementInternalTable;
use App\Models\Auth\AuthUser;
use App\Models\Holdings\Resto\CoreStock\Rst_StockBalance;
use App\Models\Holdings\Resto\Master\Rst_MasterItem;
use App\Models\Holdings\Resto\Master\Rst_MasterLokasi;
use App\Models\Holdings\Resto\Master\Rst_MasterSatuan;
use App\Models\Holdings\Resto\Movement\Rst_Movement;
use App\Models\Holdings\Resto\Movement\Rst_MovementItem;
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
    Rst_MovementItem::whereNotNull('id')->delete();
    Rst_Movement::withTrashed()->whereNotNull('id')->forceDelete();
    Rst_StockBalance::whereNotNull('id')->delete();
    Rst_MasterItem::withTrashed()->where('sku', 'like', 'ITEM-TEST-%')->forceDelete();
    Rst_MasterSatuan::withTrashed()->where('name', 'like', 'Test%')->forceDelete();
    Rst_MasterLokasi::withTrashed()->where('code', 'like', 'WH-TEST-%')->forceDelete();
    Rst_MasterLokasi::withTrashed()->where('code', 'like', 'KIT-TEST-%')->forceDelete();
    AuthUser::where('is_super_admin', true)->where('must_change_password', 0)->forceDelete();
});

function createTestMovement(array $overrides = []): Rst_Movement
{
    return Rst_Movement::create(array_merge([
        'reference_number' => 'SM-TEST-001',
        'from_location_id' => 1,
        'to_location_id' => 2,
        'type' => 'internal_transfer',
        'status' => 'requested',
        'remark' => 'Test remark',
    ], $overrides));
}

function createTestMovementItem(int $movementId, array $overrides = []): Rst_MovementItem
{
    return Rst_MovementItem::create(array_merge([
        'movement_id' => $movementId,
        'item_id' => 1,
        'qty' => 10,
        'uom_id' => 1,
        'remark' => 'Test item remark',
    ], $overrides));
}

test('user can see movement table', function () {
    Livewire::test(MovementInternalTable::class)
        ->assertSee('Stock Movement')
        ->assertSee('Transfer barang antar lokasi internal');
});

test('user can create movement gudang to dapur', function () {
    Livewire::test(MovementInternalTable::class)
        ->call('openCreateOverlay')
        ->assertSet('overlayMode', 'create')
        ->set('createFromLocationId', $this->gudang->id)
        ->set('createToLocationId', $this->dapur->id)
        ->set('createItems', [
            ['item_id' => $this->item->id, 'qty' => 5, 'remark' => 'Test item'],
        ])
        ->call('processCreate')
        ->assertSet('toast.type', 'success');

    $this->assertDatabaseHas('movements', [
        'from_location_id' => $this->gudang->id,
        'to_location_id' => $this->dapur->id,
        'type' => 'internal_transfer',
    ], 'sccr_resto');
});

test('user can see movement detail', function () {
    $movement = createTestMovement([
        'from_location_id' => $this->gudang->id,
        'to_location_id' => $this->dapur->id,
    ]);

    createTestMovementItem($movement->id, [
        'item_id' => $this->item->id,
        'uom_id' => $this->satuan->id,
    ]);

    Livewire::test(MovementInternalTable::class)
        ->call('openShow', $movement->id)
        ->assertSet('overlayMode', 'show')
        ->assertSet('overlayId', (string) $movement->id);
});

test('user can search movement', function () {
    createTestMovement([
        'reference_number' => 'SM-SEARCH-001',
        'from_location_id' => $this->gudang->id,
        'to_location_id' => $this->dapur->id,
    ]);

    createTestMovement([
        'reference_number' => 'SM-OTHER-002',
        'from_location_id' => $this->gudang->id,
        'to_location_id' => $this->dapur->id,
    ]);

    Livewire::test(MovementInternalTable::class)
        ->set('search', 'SEARCH')
        ->call('applyFilter')
        ->assertSee('SM-SEARCH-001')
        ->assertDontSee('SM-OTHER-002');
});

test('search movement returns no data', function () {
    createTestMovement([
        'reference_number' => 'SM-EXIST-001',
        'from_location_id' => $this->gudang->id,
        'to_location_id' => $this->dapur->id,
    ]);

    Livewire::test(MovementInternalTable::class)
        ->set('search', 'NONEXISTENT')
        ->call('applyFilter')
        ->assertSee('Data tidak ditemukan');
});

test('user can filter by status', function () {
    createTestMovement([
        'reference_number' => 'SM-REQ-001',
        'from_location_id' => $this->gudang->id,
        'to_location_id' => $this->dapur->id,
        'status' => 'requested',
    ]);

    createTestMovement([
        'reference_number' => 'SM-APP-002',
        'from_location_id' => $this->gudang->id,
        'to_location_id' => $this->dapur->id,
        'status' => 'approved',
    ]);

    Livewire::test(MovementInternalTable::class)
        ->set('filter1', 'requested')
        ->call('applyFilter')
        ->assertSee('SM-REQ-001')
        ->assertDontSee('SM-APP-002');
});

test('user can export filtered data', function () {
    createTestMovement([
        'reference_number' => 'SM-EXP-001',
        'from_location_id' => $this->gudang->id,
        'to_location_id' => $this->dapur->id,
    ]);

    Livewire::test(MovementInternalTable::class)
        ->call('exportFiltered');
});

test('user can export selected data', function () {
    $movement = createTestMovement([
        'reference_number' => 'SM-EXP-001',
        'from_location_id' => $this->gudang->id,
        'to_location_id' => $this->dapur->id,
    ]);

    Livewire::test(MovementInternalTable::class)
        ->set('selectedItems', [(string) $movement->id])
        ->call('exportSelected');
});

test('export selected shows warning when no items selected', function () {
    Livewire::test(MovementInternalTable::class)
        ->call('exportSelected')
        ->assertSet('toast.message', 'Pilih data terlebih dahulu');
});

test('user can delete movement', function () {
    $movement = createTestMovement([
        'from_location_id' => $this->gudang->id,
        'to_location_id' => $this->dapur->id,
    ]);

    Livewire::test(MovementInternalTable::class)
        ->call('deleteItem', (string) $movement->id)
        ->assertSet('toast.type', 'success');

    $movement->refresh();
    expect($movement->deleted_at)->not()->toBeNull();
});

test('deleted movement still exists in database', function () {
    $movement = createTestMovement([
        'from_location_id' => $this->gudang->id,
        'to_location_id' => $this->dapur->id,
    ]);

    $id = $movement->id;

    Livewire::test(MovementInternalTable::class)
        ->call('deleteItem', (string) $id);

    expect(Rst_Movement::withTrashed()->find($id))->not->toBeNull();
});

test('user can restore deleted movement', function () {
    $movement = createTestMovement([
        'from_location_id' => $this->gudang->id,
        'to_location_id' => $this->dapur->id,
    ]);

    $movement->delete();

    Livewire::test(MovementInternalTable::class)
        ->call('restoreItem', (string) $movement->id)
        ->assertSet('toast.type', 'success');

    $movement->refresh();
    expect($movement->deleted_at)->toBeNull();
});

test('user can clone movement', function () {
    $movement = createTestMovement([
        'reference_number' => 'SM-CLONE-001',
        'from_location_id' => $this->gudang->id,
        'to_location_id' => $this->dapur->id,
    ]);

    createTestMovementItem($movement->id, [
        'item_id' => $this->item->id,
        'uom_id' => $this->satuan->id,
    ]);

    Livewire::test(MovementInternalTable::class)
        ->call('cloneItem', (string) $movement->id)
        ->assertSet('toast.type', 'success');

    $this->assertDatabaseCount('movements', 2, 'sccr_resto');
});

test('user can toggle column picker', function () {
    Livewire::test(MovementInternalTable::class)
        ->assertSet('showColumnPicker', false)
        ->call('toggleColumnPicker')
        ->assertSet('showColumnPicker', true);
});

test('user can reset columns', function () {
    Livewire::test(MovementInternalTable::class)
        ->set('columnVisibility.remark', true)
        ->call('resetColumns')
        ->assertSet('columnVisibility.remark', false);
});

test('deleted row shows red background', function () {
    $movement = createTestMovement([
        'from_location_id' => $this->gudang->id,
        'to_location_id' => $this->dapur->id,
    ]);

    $movement->delete();

    Livewire::test(MovementInternalTable::class)
        ->assertSee('bg-red-50');
});

test('restore button shown for deleted items', function () {
    $movement = createTestMovement([
        'from_location_id' => $this->gudang->id,
        'to_location_id' => $this->dapur->id,
    ]);

    $movement->delete();

    Livewire::test(MovementInternalTable::class)
        ->assertSee('restoreItem');
});

test('user can filter deleted items', function () {
    createTestMovement([
        'reference_number' => 'SM-ACTIVE-001',
        'from_location_id' => $this->gudang->id,
        'to_location_id' => $this->dapur->id,
    ]);

    $deleted = createTestMovement([
        'reference_number' => 'SM-DELETED-002',
        'from_location_id' => $this->gudang->id,
        'to_location_id' => $this->dapur->id,
    ]);
    $deleted->delete();

    Livewire::test(MovementInternalTable::class)
        ->set('filterStatus', 'deleted')
        ->call('applyFilter')
        ->assertSee('SM-DELETED-002')
        ->assertDontSee('SM-ACTIVE-001');
});

test('user can clear filters', function () {
    Livewire::test(MovementInternalTable::class)
        ->set('search', 'test')
        ->set('filter1', 'requested')
        ->set('filter2', 'internal_transfer')
        ->set('filterStatus', 'active')
        ->call('clearFilters')
        ->assertSet('search', '')
        ->assertSet('filter1', '')
        ->assertSet('filter2', '')
        ->assertSet('filterStatus', '');
});

test('user can select all items', function () {
    createTestMovement([
        'reference_number' => 'SM-A-001',
        'from_location_id' => $this->gudang->id,
        'to_location_id' => $this->dapur->id,
    ]);

    createTestMovement([
        'reference_number' => 'SM-B-002',
        'from_location_id' => $this->gudang->id,
        'to_location_id' => $this->dapur->id,
    ]);

    Livewire::test(MovementInternalTable::class)
        ->set('selectAll', true)
        ->assertSet('selectAll', true);
});

test('sort by reference_number ascending', function () {
    createTestMovement([
        'reference_number' => 'SM-ZZZ-001',
        'from_location_id' => $this->gudang->id,
        'to_location_id' => $this->dapur->id,
    ]);

    createTestMovement([
        'reference_number' => 'SM-AAA-002',
        'from_location_id' => $this->gudang->id,
        'to_location_id' => $this->dapur->id,
    ]);

    Livewire::test(MovementInternalTable::class)
        ->set('sortField', 'reference_number')
        ->set('sortDirection', 'asc')
        ->call('applyFilter')
        ->assertSeeInOrder(['SM-AAA-002', 'SM-ZZZ-001']);
});

test('sort by reference_number descending', function () {
    createTestMovement([
        'reference_number' => 'SM-ZZZ-001',
        'from_location_id' => $this->gudang->id,
        'to_location_id' => $this->dapur->id,
    ]);

    createTestMovement([
        'reference_number' => 'SM-AAA-002',
        'from_location_id' => $this->gudang->id,
        'to_location_id' => $this->dapur->id,
    ]);

    Livewire::test(MovementInternalTable::class)
        ->set('sortField', 'reference_number')
        ->set('sortDirection', 'desc')
        ->call('applyFilter')
        ->assertSeeInOrder(['SM-ZZZ-001', 'SM-AAA-002']);
});
