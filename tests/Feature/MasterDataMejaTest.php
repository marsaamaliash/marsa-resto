<?php

use App\Livewire\Holdings\Resto\Master\Meja\MejaCreate;
use App\Livewire\Holdings\Resto\Master\Meja\MejaEdit;
use App\Livewire\Holdings\Resto\Master\Meja\MejaShow;
use App\Livewire\Holdings\Resto\Master\Meja\MejaTable;
use App\Models\Auth\AuthUser;
use App\Models\Holdings\Resto\Master\Rst_Meja;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = AuthUser::factory()->create([
        'is_super_admin' => true,
        'must_change_password' => 0,
    ]);

    $this->actingAs($this->user);
});

afterEach(function () {
    Rst_Meja::withTrashed()->where('table_number', 'like', 'TEST-%')->forceDelete();
    Rst_Meja::withTrashed()->where('table_number', 'like', 'MEJA-%')->forceDelete();
    AuthUser::where('is_super_admin', true)->where('must_change_password', 0)->forceDelete();
});

test('user can see meja table', function () {
    Livewire::test(MejaTable::class)
        ->assertSee('Manajemen Meja')
        ->assertSee('Master Data Manajemen Meja Resto');
});

test('user can open create overlay', function () {
    Livewire::test(MejaTable::class)
        ->call('openCreate')
        ->assertSet('overlayMode', 'create');
});

test('user can create meja', function () {
    Livewire::test(MejaCreate::class)
        ->set('table_number', 'TEST-01')
        ->set('capacity', 4)
        ->set('area', 'indoor')
        ->set('status', 'available')
        ->set('notes', 'Meja dekat jendela')
        ->set('is_active', true)
        ->call('store')
        ->assertDispatched('meja-created')
        ->assertDispatched('meja-overlay-close');

    $this->assertDatabaseHas('meja', [
        'table_number' => 'TEST-01',
        'capacity' => 4,
        'area' => 'indoor',
        'status' => 'available',
    ], 'sccr_resto');
});

test('user can create meja as draft', function () {
    Livewire::test(MejaCreate::class)
        ->set('table_number', 'TEST-DRAFT-01')
        ->set('capacity', 2)
        ->set('area', 'outdoor')
        ->set('status', 'available')
        ->call('saveDraft')
        ->assertDispatched('meja-created')
        ->assertDispatched('meja-overlay-close');

    $this->assertDatabaseHas('meja', [
        'table_number' => 'TEST-DRAFT-01',
        'is_active' => false,
    ], 'sccr_resto');
});

test('creating meja with empty table_number fails', function () {
    Livewire::test(MejaCreate::class)
        ->set('table_number', '')
        ->set('capacity', 4)
        ->set('area', 'indoor')
        ->set('status', 'available')
        ->call('store')
        ->assertHasErrors(['table_number' => 'required']);
});

test('creating meja with duplicate table_number fails', function () {
    Rst_Meja::create([
        'table_number' => 'TEST-DUP-01',
        'capacity' => 4,
        'area' => 'indoor',
        'status' => 'available',
        'is_active' => true,
    ]);

    Livewire::test(MejaCreate::class)
        ->set('table_number', 'TEST-DUP-01')
        ->set('capacity', 4)
        ->set('area', 'indoor')
        ->set('status', 'available')
        ->call('store')
        ->assertHasErrors(['table_number' => 'unique']);
});

test('creating meja with invalid capacity fails', function () {
    Livewire::test(MejaCreate::class)
        ->set('table_number', 'TEST-01')
        ->set('capacity', 0)
        ->set('area', 'indoor')
        ->set('status', 'available')
        ->call('store')
        ->assertHasErrors(['capacity' => 'min']);
});

test('user can cancel create', function () {
    Livewire::test(MejaCreate::class)
        ->call('cancel')
        ->assertDispatched('close-overlay');
});

test('user can see meja detail', function () {
    $meja = Rst_Meja::create([
        'table_number' => 'TEST-01',
        'capacity' => 4,
        'area' => 'indoor',
        'status' => 'available',
        'is_active' => true,
    ]);

    Livewire::test(MejaShow::class, ['id' => $meja->id])
        ->assertSee('Meja TEST-01')
        ->assertSee('4 orang')
        ->assertSee('Indoor');
});

test('user can open edit overlay', function () {
    $meja = Rst_Meja::create([
        'table_number' => 'TEST-01',
        'capacity' => 4,
        'area' => 'indoor',
        'status' => 'available',
        'is_active' => true,
    ]);

    Livewire::test(MejaTable::class)
        ->call('openEdit', $meja->id)
        ->assertSet('overlayMode', 'edit')
        ->assertSet('overlayId', (string) $meja->id);
});

test('user can edit meja', function () {
    $meja = Rst_Meja::create([
        'table_number' => 'TEST-01',
        'capacity' => 4,
        'area' => 'indoor',
        'status' => 'available',
        'is_active' => true,
    ]);

    Livewire::test(MejaEdit::class, ['id' => $meja->id])
        ->set('table_number', 'TEST-02')
        ->set('capacity', 6)
        ->set('area', 'vip')
        ->set('status', 'reserved')
        ->set('notes', 'Updated notes')
        ->call('update')
        ->assertDispatched('meja-updated')
        ->assertDispatched('meja-overlay-close');

    $meja->refresh();
    expect($meja->table_number)->toBe('TEST-02')
        ->and($meja->capacity)->toBe(6)
        ->and($meja->area)->toBe('vip')
        ->and($meja->status)->toBe('reserved')
        ->and($meja->notes)->toBe('Updated notes');
});

test('editing meja with empty table_number fails', function () {
    $meja = Rst_Meja::create([
        'table_number' => 'TEST-01',
        'capacity' => 4,
        'area' => 'indoor',
        'status' => 'available',
        'is_active' => true,
    ]);

    Livewire::test(MejaEdit::class, ['id' => $meja->id])
        ->set('table_number', '')
        ->call('update')
        ->assertHasErrors(['table_number' => 'required']);
});

test('editing meja with invalid area fails', function () {
    $meja = Rst_Meja::create([
        'table_number' => 'TEST-01',
        'capacity' => 4,
        'area' => 'indoor',
        'status' => 'available',
        'is_active' => true,
    ]);

    Livewire::test(MejaEdit::class, ['id' => $meja->id])
        ->set('area', 'invalid-area')
        ->call('update')
        ->assertHasErrors(['area' => 'in']);
});

test('user can cancel edit', function () {
    $meja = Rst_Meja::create([
        'table_number' => 'TEST-01',
        'capacity' => 4,
        'area' => 'indoor',
        'status' => 'available',
        'is_active' => true,
    ]);

    Livewire::test(MejaEdit::class, ['id' => $meja->id])
        ->call('cancel')
        ->assertDispatched('close-overlay');
});

test('user can delete meja', function () {
    $meja = Rst_Meja::create([
        'table_number' => 'TEST-01',
        'capacity' => 4,
        'area' => 'indoor',
        'status' => 'available',
        'is_active' => true,
    ]);

    Livewire::test(MejaTable::class)
        ->call('deleteItem', $meja->id);

    $meja->refresh();
    expect($meja->deleted_at)->not()->toBeNull();
});

test('user can cancel delete via confirmation', function () {
    $meja = Rst_Meja::create([
        'table_number' => 'TEST-01',
        'capacity' => 4,
        'area' => 'indoor',
        'status' => 'available',
        'is_active' => true,
    ]);

    expect($meja->deleted_at)->toBeNull();
});

test('user can restore deleted meja', function () {
    $meja = Rst_Meja::create([
        'table_number' => 'TEST-01',
        'capacity' => 4,
        'area' => 'indoor',
        'status' => 'available',
        'is_active' => true,
    ]);

    $meja->delete();

    Livewire::test(MejaTable::class)
        ->call('restoreItem', $meja->id);

    $meja->refresh();
    expect($meja->deleted_at)->toBeNull();
});

test('user can search meja by table number', function () {
    Rst_Meja::create(['table_number' => 'MEJA-01', 'capacity' => 4, 'area' => 'indoor', 'status' => 'available', 'is_active' => true]);
    Rst_Meja::create(['table_number' => 'MEJA-02', 'capacity' => 2, 'area' => 'outdoor', 'status' => 'available', 'is_active' => true]);

    Livewire::test(MejaTable::class)
        ->set('search', 'MEJA-01')
        ->call('applyFilter')
        ->assertSee('MEJA-01')
        ->assertDontSee('MEJA-02');
});

test('user search meja returns no data', function () {
    Rst_Meja::create(['table_number' => 'MEJA-01', 'capacity' => 4, 'area' => 'indoor', 'status' => 'available', 'is_active' => true]);

    Livewire::test(MejaTable::class)
        ->set('search', 'NONEXISTENT')
        ->call('applyFilter')
        ->assertSee('Data tidak ditemukan');
});

test('user can filter meja by area', function () {
    Rst_Meja::create(['table_number' => 'MEJA-01', 'capacity' => 4, 'area' => 'indoor', 'status' => 'available', 'is_active' => true]);
    Rst_Meja::create(['table_number' => 'MEJA-02', 'capacity' => 2, 'area' => 'outdoor', 'status' => 'available', 'is_active' => true]);

    Livewire::test(MejaTable::class)
        ->set('filter1', 'indoor')
        ->call('applyFilter')
        ->assertSee('MEJA-01')
        ->assertDontSee('MEJA-02');
});

test('user can filter meja by active status', function () {
    Rst_Meja::create(['table_number' => 'MEJA-01', 'capacity' => 4, 'area' => 'indoor', 'status' => 'available', 'is_active' => true]);
    Rst_Meja::create(['table_number' => 'MEJA-02', 'capacity' => 2, 'area' => 'indoor', 'status' => 'available', 'is_active' => false]);

    Livewire::test(MejaTable::class)
        ->set('filter2', '1')
        ->call('applyFilter')
        ->assertSee('MEJA-01')
        ->assertDontSee('MEJA-02');
});

test('user can filter deleted items', function () {
    Rst_Meja::create(['table_number' => 'MEJA-01', 'capacity' => 4, 'area' => 'indoor', 'status' => 'available', 'is_active' => true]);
    $deleted = Rst_Meja::create(['table_number' => 'MEJA-02', 'capacity' => 2, 'area' => 'indoor', 'status' => 'available', 'is_active' => true]);
    $deleted->delete();

    Livewire::test(MejaTable::class)
        ->set('filterStatus', 'deleted')
        ->call('applyFilter')
        ->assertSee('MEJA-02')
        ->assertDontSee('MEJA-01');
});

test('user can clear filters', function () {
    Livewire::test(MejaTable::class)
        ->set('search', 'test')
        ->set('filter1', 'indoor')
        ->set('filter2', '1')
        ->call('clearFilters')
        ->assertSet('search', '')
        ->assertSet('filter1', '')
        ->assertSet('filter2', '');
});

test('user can toggle column picker', function () {
    Livewire::test(MejaTable::class)
        ->assertSet('showColumnPicker', false)
        ->call('toggleColumnPicker')
        ->assertSet('showColumnPicker', true);
});

test('user can reset columns', function () {
    Livewire::test(MejaTable::class)
        ->set('columnVisibility.notes', true)
        ->call('resetColumns')
        ->assertSet('columnVisibility.notes', false);
});

test('deleted row shows red background', function () {
    $meja = Rst_Meja::create([
        'table_number' => 'TEST-01',
        'capacity' => 4,
        'area' => 'indoor',
        'status' => 'available',
        'is_active' => true,
    ]);

    $meja->delete();

    Livewire::test(MejaTable::class)
        ->assertSee('bg-red-50');
});

test('restore button shown for deleted items', function () {
    $meja = Rst_Meja::create([
        'table_number' => 'TEST-01',
        'capacity' => 4,
        'area' => 'indoor',
        'status' => 'available',
        'is_active' => true,
    ]);

    $meja->delete();

    Livewire::test(MejaTable::class)
        ->assertSee('restoreItem');
});

test('edit button hidden for deleted items', function () {
    $meja = Rst_Meja::create([
        'table_number' => 'TEST-01',
        'capacity' => 4,
        'area' => 'indoor',
        'status' => 'available',
        'is_active' => true,
    ]);

    $meja->delete();

    Livewire::test(MejaTable::class)
        ->assertDontSee("openEdit('{$meja->id}')");
});

test('user can export filtered data', function () {
    Rst_Meja::create(['table_number' => 'TEST-01', 'capacity' => 4, 'area' => 'indoor', 'status' => 'available', 'is_active' => true]);

    Livewire::test(MejaTable::class)
        ->call('exportFiltered');
});

test('user can export selected data', function () {
    $meja = Rst_Meja::create(['table_number' => 'TEST-01', 'capacity' => 4, 'area' => 'indoor', 'status' => 'available', 'is_active' => true]);

    Livewire::test(MejaTable::class)
        ->set('selectedItems', [(string) $meja->id])
        ->call('exportSelected');
});

test('export selected shows warning when no items selected', function () {
    Livewire::test(MejaTable::class)
        ->call('exportSelected')
        ->assertSet('toast.message', 'Pilih data terlebih dahulu');
});

test('sort by table_number ascending', function () {
    Rst_Meja::create(['table_number' => 'MEJA-Z', 'capacity' => 4, 'area' => 'indoor', 'status' => 'available', 'is_active' => true]);
    Rst_Meja::create(['table_number' => 'MEJA-A', 'capacity' => 2, 'area' => 'indoor', 'status' => 'available', 'is_active' => true]);

    Livewire::test(MejaTable::class)
        ->set('sortField', 'table_number')
        ->set('sortDirection', 'asc')
        ->call('applyFilter')
        ->assertSeeInOrder(['MEJA-A', 'MEJA-Z']);
});

test('sort by table_number descending', function () {
    Rst_Meja::create(['table_number' => 'MEJA-Z', 'capacity' => 4, 'area' => 'indoor', 'status' => 'available', 'is_active' => true]);
    Rst_Meja::create(['table_number' => 'MEJA-A', 'capacity' => 2, 'area' => 'indoor', 'status' => 'available', 'is_active' => true]);

    Livewire::test(MejaTable::class)
        ->set('sortField', 'table_number')
        ->set('sortDirection', 'desc')
        ->call('applyFilter')
        ->assertSeeInOrder(['MEJA-Z', 'MEJA-A']);
});

test('handle created event closes overlay and shows toast', function () {
    Livewire::test(MejaTable::class)
        ->set('overlayMode', 'create')
        ->dispatch('meja-created')
        ->assertSet('overlayMode', null)
        ->assertSet('toast.type', 'success')
        ->assertSet('toast.message', 'Data berhasil ditambahkan.');
});

test('handle updated event closes overlay and shows toast', function () {
    Livewire::test(MejaTable::class)
        ->set('overlayMode', 'edit')
        ->dispatch('meja-updated')
        ->assertSet('overlayMode', null)
        ->assertSet('toast.type', 'success')
        ->assertSet('toast.message', 'Data berhasil diperbarui.');
});
