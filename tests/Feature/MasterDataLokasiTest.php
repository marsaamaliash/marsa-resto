<?php

use App\Livewire\Holdings\Resto\Master\Lokasi\LokasiCreate;
use App\Livewire\Holdings\Resto\Master\Lokasi\LokasiEdit;
use App\Livewire\Holdings\Resto\Master\Lokasi\LokasiShow;
use App\Livewire\Holdings\Resto\Master\Lokasi\LokasiTable;
use App\Models\Auth\AuthUser;
use App\Models\Holdings\Resto\Master\Rst_MasterLokasi;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = AuthUser::factory()->create([
        'is_super_admin' => true,
        'must_change_password' => 0,
    ]);

    $this->actingAs($this->user);
});

afterEach(function () {
    Rst_MasterLokasi::withTrashed()->where('name', 'like', '%Test%')->forceDelete();
    Rst_MasterLokasi::withTrashed()->where('name', 'like', '%Gudang%')->forceDelete();
    Rst_MasterLokasi::withTrashed()->where('name', 'like', '%Kitchen%')->forceDelete();
    Rst_MasterLokasi::withTrashed()->where('name', 'like', '%Zebra%')->forceDelete();
    Rst_MasterLokasi::withTrashed()->where('name', 'like', '%Alpha%')->forceDelete();
    Rst_MasterLokasi::withTrashed()->where('code', 'like', 'WH-0%')->forceDelete();
    Rst_MasterLokasi::withTrashed()->where('code', 'like', 'KIT-0%')->forceDelete();
    Rst_MasterLokasi::withTrashed()->where('code', 'like', 'A')->forceDelete();
    Rst_MasterLokasi::withTrashed()->where('code', 'like', 'B')->forceDelete();
    AuthUser::where('is_super_admin', true)->where('must_change_password', 0)->forceDelete();
});

test('user can see lokasi table', function () {
    Livewire::test(LokasiTable::class)
        ->assertSee('Lokasi')
        ->assertSee('Master Data Lokasi Resto');
});

test('user can open create overlay', function () {
    Livewire::test(LokasiTable::class)
        ->call('openCreate')
        ->assertSet('overlayMode', 'create');
});

test('user can create lokasi', function () {
    Livewire::test(LokasiCreate::class)
        ->set('name', 'Test Gudang Utama')
        ->set('code', 'WH-TEST-01')
        ->set('type', 'warehouse')
        ->set('pic_name', 'Budi Santoso')
        ->set('notes', 'Gudang pusat')
        ->set('is_active', true)
        ->call('store')
        ->assertDispatched('lokasi-created')
        ->assertDispatched('lokasi-overlay-close');

    $this->assertDatabaseHas('locations', [
        'name' => 'Test Gudang Utama',
        'code' => 'WH-TEST-01',
        'type' => 'warehouse',
        'pic_name' => 'Budi Santoso',
    ], 'sccr_resto');
});

test('creating lokasi with empty name fails', function () {
    Livewire::test(LokasiCreate::class)
        ->set('name', '')
        ->set('type', 'warehouse')
        ->set('pic_name', 'Budi')
        ->call('store')
        ->assertHasErrors(['name' => 'required']);
});

test('creating lokasi with empty type fails', function () {
    Livewire::test(LokasiCreate::class)
        ->set('name', 'Test Gudang')
        ->set('type', '')
        ->set('pic_name', 'Budi')
        ->call('store')
        ->assertHasErrors(['type' => 'required']);
});

test('creating lokasi with empty pic_name fails', function () {
    Livewire::test(LokasiCreate::class)
        ->set('name', 'Test Gudang')
        ->set('type', 'warehouse')
        ->set('pic_name', '')
        ->call('store')
        ->assertHasErrors(['pic_name' => 'required']);
});

test('user can cancel create', function () {
    Livewire::test(LokasiCreate::class)
        ->call('cancel')
        ->assertDispatched('close-overlay');
});

test('user can see lokasi detail', function () {
    $lokasi = Rst_MasterLokasi::create([
        'name' => 'Test Kitchen A',
        'code' => 'KIT-TEST-01',
        'type' => 'kitchen',
        'pic_name' => 'Siti Aminah',
        'is_active' => true,
    ]);

    Livewire::test(LokasiShow::class, ['id' => $lokasi->id])
        ->assertSee('Test Kitchen A')
        ->assertSee('KIT-TEST-01')
        ->assertSee('Siti Aminah');
});

test('user can open edit overlay from show', function () {
    $lokasi = Rst_MasterLokasi::create([
        'name' => 'Test Kitchen A',
        'code' => 'KIT-TEST-01',
        'type' => 'kitchen',
        'pic_name' => 'Siti Aminah',
        'is_active' => true,
    ]);

    Livewire::test(LokasiTable::class)
        ->call('openEdit', $lokasi->id)
        ->assertSet('overlayMode', 'edit')
        ->assertSet('overlayId', (string) $lokasi->id);
});

test('user can edit lokasi', function () {
    $lokasi = Rst_MasterLokasi::create([
        'name' => 'Test Kitchen A',
        'code' => 'KIT-TEST-01',
        'type' => 'kitchen',
        'pic_name' => 'Siti Aminah',
        'is_active' => true,
    ]);

    Livewire::test(LokasiEdit::class, ['id' => $lokasi->id])
        ->set('name', 'Test Kitchen B')
        ->set('code', 'KIT-TEST-02')
        ->set('type', 'outlet')
        ->set('pic_name', 'Ahmad Fauzi')
        ->set('notes', 'Updated notes')
        ->call('update')
        ->assertDispatched('lokasi-updated')
        ->assertDispatched('lokasi-overlay-close');

    $lokasi->refresh();
    expect($lokasi->name)->toBe('Test Kitchen B')
        ->and($lokasi->code)->toBe('KIT-TEST-02')
        ->and($lokasi->type)->toBe('outlet')
        ->and($lokasi->pic_name)->toBe('Ahmad Fauzi')
        ->and($lokasi->notes)->toBe('Updated notes');
});

test('editing lokasi with empty name fails', function () {
    $lokasi = Rst_MasterLokasi::create([
        'name' => 'Test Kitchen A',
        'code' => 'KIT-TEST-01',
        'type' => 'kitchen',
        'pic_name' => 'Siti',
        'is_active' => true,
    ]);

    Livewire::test(LokasiEdit::class, ['id' => $lokasi->id])
        ->set('name', '')
        ->call('update')
        ->assertHasErrors(['name' => 'required']);
});

test('editing lokasi with empty type fails', function () {
    $lokasi = Rst_MasterLokasi::create([
        'name' => 'Test Kitchen A',
        'code' => 'KIT-TEST-01',
        'type' => 'kitchen',
        'pic_name' => 'Siti',
        'is_active' => true,
    ]);

    Livewire::test(LokasiEdit::class, ['id' => $lokasi->id])
        ->set('type', '')
        ->call('update')
        ->assertHasErrors(['type' => 'required']);
});

test('user can cancel edit', function () {
    $lokasi = Rst_MasterLokasi::create([
        'name' => 'Test Kitchen A',
        'code' => 'KIT-TEST-01',
        'type' => 'kitchen',
        'pic_name' => 'Siti',
        'is_active' => true,
    ]);

    Livewire::test(LokasiEdit::class, ['id' => $lokasi->id])
        ->call('cancel')
        ->assertDispatched('close-overlay');
});

test('user can delete lokasi', function () {
    $lokasi = Rst_MasterLokasi::create([
        'name' => 'Test Gudang Utama',
        'code' => 'WH-TEST-01',
        'type' => 'warehouse',
        'pic_name' => 'Budi',
        'is_active' => true,
    ]);

    Livewire::test(LokasiTable::class)
        ->call('deleteItem', $lokasi->id);

    $lokasi->refresh();
    expect($lokasi->deleted_at)->not()->toBeNull();
});

test('deleted lokasi still exists in database', function () {
    $lokasi = Rst_MasterLokasi::create([
        'name' => 'Test Gudang Utama',
        'code' => 'WH-TEST-01',
        'type' => 'warehouse',
        'pic_name' => 'Budi',
        'is_active' => true,
    ]);

    $id = $lokasi->id;

    Livewire::test(LokasiTable::class)
        ->call('deleteItem', $id);

    expect(Rst_MasterLokasi::withTrashed()->find($id))->not->toBeNull();
});

test('user can restore deleted lokasi', function () {
    $lokasi = Rst_MasterLokasi::create([
        'name' => 'Test Gudang Utama',
        'code' => 'WH-TEST-01',
        'type' => 'warehouse',
        'pic_name' => 'Budi',
        'is_active' => true,
    ]);

    $lokasi->delete();

    Livewire::test(LokasiTable::class)
        ->call('restoreItem', $lokasi->id);

    $lokasi->refresh();
    expect($lokasi->deleted_at)->toBeNull();
});

test('user can filter by type', function () {
    Rst_MasterLokasi::create(['name' => 'Test Gudang A', 'code' => 'WH-TEST-A', 'type' => 'warehouse', 'pic_name' => 'A', 'is_active' => true]);
    Rst_MasterLokasi::create(['name' => 'Test Kitchen A', 'code' => 'KIT-TEST-A', 'type' => 'kitchen', 'pic_name' => 'B', 'is_active' => true]);

    Livewire::test(LokasiTable::class)
        ->set('filter1', 'warehouse')
        ->call('applyFilter')
        ->assertSee('Test Gudang A')
        ->assertDontSee('Test Kitchen A');
});

test('user can filter by active status', function () {
    Rst_MasterLokasi::create(['name' => 'Test Gudang A', 'code' => 'WH-TEST-A', 'type' => 'warehouse', 'pic_name' => 'A', 'is_active' => true]);
    Rst_MasterLokasi::create(['name' => 'Test Gudang B', 'code' => 'WH-TEST-B', 'type' => 'warehouse', 'pic_name' => 'B', 'is_active' => false]);

    Livewire::test(LokasiTable::class)
        ->set('filter2', '1')
        ->call('applyFilter')
        ->assertSee('Test Gudang A')
        ->assertDontSee('Test Gudang B');
});

test('user can filter deleted items', function () {
    Rst_MasterLokasi::create(['name' => 'Test Gudang A', 'code' => 'WH-TEST-A', 'type' => 'warehouse', 'pic_name' => 'A', 'is_active' => true]);
    $deleted = Rst_MasterLokasi::create(['name' => 'Test Gudang B', 'code' => 'WH-TEST-B', 'type' => 'warehouse', 'pic_name' => 'B', 'is_active' => true]);
    $deleted->delete();

    Livewire::test(LokasiTable::class)
        ->set('filterStatus', 'deleted')
        ->call('applyFilter')
        ->assertSee('Test Gudang B')
        ->assertDontSee('Test Gudang A');
});

test('user can search by name', function () {
    Rst_MasterLokasi::create(['name' => 'Test Gudang Utama', 'code' => 'WH-TEST-01', 'type' => 'warehouse', 'pic_name' => 'A', 'is_active' => true]);
    Rst_MasterLokasi::create(['name' => 'Test Kitchen A', 'code' => 'KIT-TEST-01', 'type' => 'kitchen', 'pic_name' => 'B', 'is_active' => true]);

    Livewire::test(LokasiTable::class)
        ->set('search', 'Gudang')
        ->call('applyFilter')
        ->assertSee('Test Gudang Utama')
        ->assertDontSee('Test Kitchen A');
});

test('user can search by code', function () {
    Rst_MasterLokasi::create(['name' => 'Test Gudang Utama', 'code' => 'WH-TEST-01', 'type' => 'warehouse', 'pic_name' => 'A', 'is_active' => true]);
    Rst_MasterLokasi::create(['name' => 'Test Kitchen A', 'code' => 'KIT-TEST-01', 'type' => 'kitchen', 'pic_name' => 'B', 'is_active' => true]);

    Livewire::test(LokasiTable::class)
        ->set('search', 'KIT-TEST-01')
        ->call('applyFilter')
        ->assertSee('Test Kitchen A')
        ->assertDontSee('Test Gudang Utama');
});

test('user can clear filters', function () {
    Livewire::test(LokasiTable::class)
        ->set('search', 'test')
        ->set('filter1', 'warehouse')
        ->set('filter2', '1')
        ->call('clearFilters')
        ->assertSet('search', '')
        ->assertSet('filter1', '')
        ->assertSet('filter2', '');
});

test('user can toggle column picker', function () {
    Livewire::test(LokasiTable::class)
        ->assertSet('showColumnPicker', false)
        ->call('toggleColumnPicker')
        ->assertSet('showColumnPicker', true);
});

test('user can reset columns', function () {
    Livewire::test(LokasiTable::class)
        ->set('columnVisibility.notes', true)
        ->call('resetColumns')
        ->assertSet('columnVisibility.notes', false);
});

test('deleted row shows red background', function () {
    $lokasi = Rst_MasterLokasi::create([
        'name' => 'Test Gudang Utama',
        'code' => 'WH-TEST-01',
        'type' => 'warehouse',
        'pic_name' => 'Budi',
        'is_active' => true,
    ]);

    $lokasi->delete();

    Livewire::test(LokasiTable::class)
        ->assertSee('bg-red-50');
});

test('restore button shown for deleted items', function () {
    $lokasi = Rst_MasterLokasi::create([
        'name' => 'Test Gudang Utama',
        'code' => 'WH-TEST-01',
        'type' => 'warehouse',
        'pic_name' => 'Budi',
        'is_active' => true,
    ]);

    $lokasi->delete();

    Livewire::test(LokasiTable::class)
        ->assertSee('restoreItem');
});

test('edit button hidden for deleted items', function () {
    $lokasi = Rst_MasterLokasi::create([
        'name' => 'Test Gudang Utama',
        'code' => 'WH-TEST-01',
        'type' => 'warehouse',
        'pic_name' => 'Budi',
        'is_active' => true,
    ]);

    $lokasi->delete();

    Livewire::test(LokasiTable::class)
        ->assertDontSee("openEdit('{$lokasi->id}')");
});

test('user can select all items', function () {
    Rst_MasterLokasi::create(['name' => 'Test A', 'code' => 'WH-TEST-A', 'type' => 'warehouse', 'pic_name' => 'A', 'is_active' => true]);
    Rst_MasterLokasi::create(['name' => 'Test B', 'code' => 'WH-TEST-B', 'type' => 'warehouse', 'pic_name' => 'B', 'is_active' => true]);

    Livewire::test(LokasiTable::class)
        ->set('selectAll', true)
        ->assertSet('selectAll', true);
});

test('user can export filtered data', function () {
    Rst_MasterLokasi::create(['name' => 'Test Gudang A', 'code' => 'WH-TEST-A', 'type' => 'warehouse', 'pic_name' => 'A', 'is_active' => true]);

    Livewire::test(LokasiTable::class)
        ->call('exportFiltered');
});

test('user can export selected data', function () {
    $lokasi = Rst_MasterLokasi::create(['name' => 'Test Gudang A', 'code' => 'WH-TEST-A', 'type' => 'warehouse', 'pic_name' => 'A', 'is_active' => true]);

    Livewire::test(LokasiTable::class)
        ->set('selectedItems', [(string) $lokasi->id])
        ->call('exportSelected');
});

test('export selected shows warning when no items selected', function () {
    Livewire::test(LokasiTable::class)
        ->call('exportSelected')
        ->assertSet('toast.message', 'Pilih data terlebih dahulu');
});

test('sort by name ascending', function () {
    Rst_MasterLokasi::create(['name' => 'Test Zebra', 'code' => 'WH-TEST-Z', 'type' => 'warehouse', 'pic_name' => 'A', 'is_active' => true]);
    Rst_MasterLokasi::create(['name' => 'Test Alpha', 'code' => 'WH-TEST-AL', 'type' => 'warehouse', 'pic_name' => 'B', 'is_active' => true]);

    Livewire::test(LokasiTable::class)
        ->set('sortField', 'name')
        ->set('sortDirection', 'asc')
        ->call('applyFilter')
        ->assertSeeInOrder(['Test Alpha', 'Test Zebra']);
});

test('sort by name descending', function () {
    Rst_MasterLokasi::create(['name' => 'Test Zebra', 'code' => 'WH-TEST-Z', 'type' => 'warehouse', 'pic_name' => 'A', 'is_active' => true]);
    Rst_MasterLokasi::create(['name' => 'Test Alpha', 'code' => 'WH-TEST-AL', 'type' => 'warehouse', 'pic_name' => 'B', 'is_active' => true]);

    Livewire::test(LokasiTable::class)
        ->set('sortField', 'name')
        ->set('sortDirection', 'desc')
        ->call('applyFilter')
        ->assertSeeInOrder(['Test Zebra', 'Test Alpha']);
});

test('handle created event closes overlay and shows toast', function () {
    Livewire::test(LokasiTable::class)
        ->set('overlayMode', 'create')
        ->dispatch('lokasi-created')
        ->assertSet('overlayMode', null)
        ->assertSet('toast.type', 'success')
        ->assertSet('toast.message', 'Data berhasil ditambahkan.');
});

test('handle updated event closes overlay and shows toast', function () {
    Livewire::test(LokasiTable::class)
        ->set('overlayMode', 'edit')
        ->dispatch('lokasi-updated')
        ->assertSet('overlayMode', null)
        ->assertSet('toast.type', 'success')
        ->assertSet('toast.message', 'Data berhasil diperbarui.');
});
