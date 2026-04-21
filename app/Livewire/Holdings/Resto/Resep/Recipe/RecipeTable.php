<?php

namespace App\Livewire\Holdings\Resto\Resep\Recipe;

use App\Models\Holdings\Resto\Resep\Rst_Recipe;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class RecipeTable extends Component
{
    use WithPagination;

    public array $breadcrumbs = [];

    public array $toast = ['show' => false, 'type' => 'success', 'message' => ''];

    public bool $canWrite = false;

    public bool $canCreate = false;

    public bool $canUpdate = false;

    public bool $canDelete = false;

    public string $search = '';

    public string $filter1 = '';

    public string $filter2 = '';

    public int $perPage = 10;

    public string $sortField = 'id';

    public string $sortDirection = 'desc';

    public int $totalAll = 0;

    protected array $allowedSortFields = [
        'id',
        'recipe_code',
        'recipe_name',
        'is_active',
        'created_at',
    ];

    public array $selectedItems = [];

    public bool $selectAll = false;

    public ?string $overlayMode = null;

    public ?string $overlayId = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'filter1' => ['except' => ''],
        'filter2' => ['except' => ''],
        'perPage' => ['except' => 10],
        'sortField' => ['except' => 'id'],
        'sortDirection' => ['except' => 'desc'],
    ];

    private function syncCaps(): void
    {
        $u = auth()->user();

        $this->canCreate = (bool) ($u?->hasPermission('RECIPE_CREATE') ?? false);
        $this->canUpdate = (bool) ($u?->hasPermission('RECIPE_UPDATE') ?? false);
        $this->canDelete = (bool) ($u?->hasPermission('RECIPE_DELETE') ?? false);

        $this->canWrite = $this->canCreate || $this->canUpdate;
    }

    public function mount(): void
    {
        $this->breadcrumbs = [
            ['label' => 'Main Dashboard', 'route' => 'dashboard', 'color' => 'text-gray-800'],
            ['label' => 'Resto', 'route' => 'dashboard.resto', 'color' => 'text-gray-800'],
            ['label' => 'Recipe', 'route' => 'dashboard.resto.resep', 'color' => 'text-gray-800'],
            ['label' => 'Recipe', 'color' => 'text-gray-900 font-semibold'],
        ];

        $this->syncCaps();

        $this->totalAll = Rst_Recipe::count();
    }

    public function hydrate(): void
    {
        $this->syncCaps();
    }

    protected function dataQuery(): Collection
    {
        $query = Rst_Recipe::with(['menu', 'activeVersion', 'versions']);

        if ($this->search !== '') {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('recipe_code', 'like', "%{$search}%")
                    ->orWhere('recipe_name', 'like', "%{$search}%")
                    ->orWhereHas('menu', function ($mq) use ($search) {
                        $mq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($this->filter1 !== '') {
            // Filter by menu category (if needed)
            $query->whereHas('menu', function ($q) {
                $q->where('category', $this->filter1);
            });
        }

        if ($this->filter2 !== '') {
            if ($this->filter2 === 'active') {
                $query->where('is_active', true);
            } elseif ($this->filter2 === 'inactive') {
                $query->where('is_active', false);
            }
        }

        if (in_array($this->sortField, $this->allowedSortFields, true)) {
            $query->orderBy($this->sortField, $this->sortDirection);
        }

        return $query->get();
    }

    protected function paginateCollection(Collection $collection, int $perPage): LengthAwarePaginator
    {
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $items = $collection->slice(($currentPage - 1) * $perPage, $perPage)->values();

        return new LengthAwarePaginator(
            $items,
            $collection->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }

    protected function visibleIds(): array
    {
        $p = $this->paginateCollection($this->dataQuery(), $this->perPage);

        return $p->getCollection()
            ->pluck('id')
            ->map(fn ($v) => (string) $v)
            ->toArray();
    }

    public function sortBy(string $field): void
    {
        if (! in_array($field, $this->allowedSortFields, true)) {
            return;
        }

        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';

            return;
        }

        $this->sortField = $field;
        $this->sortDirection = 'asc';
    }

    public function applyFilter(): void
    {
        $this->resetPage();
        $this->selectedItems = [];
        $this->selectAll = false;
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'filter1', 'filter2']);
        $this->applyFilter();
    }

    public function updated($property): void
    {
        if (in_array($property, ['search', 'perPage', 'sortField', 'sortDirection'], true)) {
            $this->resetPage();
        }
    }

    public function updatedSelectAll(bool $value): void
    {
        $visible = $this->visibleIds();

        if ($value) {
            $this->selectedItems = array_values(array_unique(array_merge($this->selectedItems, $visible)));

            return;
        }

        $this->selectedItems = array_values(array_diff($this->selectedItems, $visible));
    }

    public function updatedSelectedItems(): void
    {
        $visible = $this->visibleIds();
        $this->selectAll = count($visible) > 0 && empty(array_diff($visible, $this->selectedItems));
    }

    public function exportFiltered()
    {
        $data = $this->dataQuery();

        return $this->generateExcel($data, 'Filtered');
    }

    public function exportSelected()
    {
        if (empty($this->selectedItems)) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Please select data first'];

            return null;
        }

        $ids = array_values(array_unique(array_map('strval', $this->selectedItems)));
        $data = $this->dataQuery()->whereIn('id', $ids);

        return $this->generateExcel($data, 'Selected');
    }

    private function generateExcel($data, string $type)
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet;
        $ws = $spreadsheet->getActiveSheet();

        $ws->fromArray([['ID', 'Recipe Code', 'Recipe Name', 'Menu/Type', 'Active Version', 'Component Count', 'Active', 'Created']], null, 'A1');

        $row = 2;
        foreach ($data as $item) {
            $activeVersion = $item->activeVersion;
            $menuOrType = $item->menu_id
                ? ($item->menu?->name ?? '-')
                : 'Semi-Finished';
            $ws->fromArray([
                $item['id'] ?? '',
                $item['recipe_code'] ?? '',
                $item['recipe_name'] ?? '',
                $menuOrType,
                $activeVersion ? 'V'.$activeVersion->version_no : '-',
                $activeVersion ? $activeVersion->components->count() : 0,
                $item['is_active'] ? 'Yes' : 'No',
                $item['created_at'] ?? '',
            ], null, 'A'.$row++);
        }

        $filename = "Recipe_{$type}_".now()->format('Ymd_His').'.xlsx';

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $tmp = tempnam(sys_get_temp_dir(), 'recipe_');
        $writer->save($tmp);

        return response()->download($tmp, $filename)->deleteFileAfterSend(true);
    }

    public function openCreate(): void
    {
        if (! $this->canCreate) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'No permission to create.'];

            return;
        }

        $this->selectedItems = [];
        $this->selectAll = false;

        $this->overlayMode = 'create';
        $this->overlayId = null;
    }

    public function openCreateSemiFinished(): void
    {
        if (! $this->canCreate) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'No permission to create.'];

            return;
        }

        $this->selectedItems = [];
        $this->selectAll = false;

        $this->overlayMode = 'create-semi-finished';
        $this->overlayId = null;
    }

    public function openShow(string $id): void
    {
        $this->overlayMode = 'show';
        $this->overlayId = $id;
    }

    public function openEdit(string $id): void
    {
        if (! $this->canUpdate) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'No permission to update.'];

            return;
        }

        $this->overlayMode = 'edit';
        $this->overlayId = $id;
    }

    public function closeOverlay(): void
    {
        $this->reset(['overlayMode', 'overlayId']);
    }

    #[On('recipe-overlay-close')]
    public function handleOverlayClose(): void
    {
        $this->closeOverlay();
    }

    #[On('recipe-created')]
    public function handleCreated(?string $id = null): void
    {
        $this->closeOverlay();
        $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Recipe added successfully.'];
    }

    #[On('recipe-updated')]
    public function handleUpdated(?string $id = null): void
    {
        $this->closeOverlay();
        $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Recipe updated successfully.'];
    }

    #[On('recipe-open-edit')]
    public function handleOpenEditFromShow(string $id): void
    {
        $this->openEdit($id);
    }

    public function deleteRecipe(string $id): void
    {
        if (! $this->canDelete) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'No permission to delete.'];

            return;
        }

        try {
            $recipe = Rst_Recipe::findOrFail($id);
            $recipe->deleted_by = auth()->id();
            $recipe->save();
            $recipe->delete();
            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Recipe deleted successfully.'];
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function goToDetail(string $id): void
    {
        $this->redirect(route('dashboard.resto.resep.recipe.detail', $id));
    }

    protected function filter1Options(): array
    {
        // Get menu categories from existing menus with recipes
        $categories = \App\Models\Holdings\Resto\Pos\Rst_Menu::whereHas('recipe')
            ->distinct()
            ->pluck('category')
            ->filter()
            ->toArray();

        $options = ['' => '-- All Menu Categories --'];
        foreach ($categories as $category) {
            $options[$category] = $category;
        }

        return $options;
    }

    protected function filter2Options(): array
    {
        return [
            '' => '-- All Status --',
            'active' => 'Active',
            'inactive' => 'Inactive',
        ];
    }

    public function render()
    {
        $data = $this->paginateCollection($this->dataQuery(), $this->perPage);

        $visible = $data->getCollection()
            ->pluck('id')
            ->map(fn ($v) => (string) $v)
            ->toArray();

        $this->selectAll = count($visible) > 0 && empty(array_diff($visible, $this->selectedItems));

        return view('livewire.holdings.resto.resep.recipe.recipe-table', [
            'data' => $data,
            'breadcrumbs' => $this->breadcrumbs,
            'filter1Options' => $this->filter1Options(),
            'filter2Options' => $this->filter2Options(),
        ])->layout('components.sccr-layout');
    }
}
