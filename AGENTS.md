# AGENTS.md - SCCR Project

## Project Overview

Laravel 12 + Livewire 3 + Alpine.js + TailwindCSS attendance/HR management system. Uses Pest for testing, Pint for formatting, and Vite for frontend builds.

## Commands

### Development

```bash
composer run dev          # Full dev: server + queue + logs + Vite
php artisan serve          # PHP dev server only
npm run dev                # Vite dev server (HMR)
npm run build              # Production Vite build
```

### Testing (Pest)

```bash
composer run test          # Run all tests (config:clear + artisan test)
php artisan test           # Run all tests
php artisan test tests/Feature/ExampleTest.php   # Run single test file
php artisan test --filter=test_name              # Run tests matching name
php artisan test --parallel                       # Run tests in parallel
vendor/bin/pest tests/Unit/ExampleTest.php        # Direct Pest invocation
```

### Linting & Formatting (Pint)

```bash
vendor/bin/pint              # Auto-format all PHP files (Laravel preset)
vendor/bin/pint --test       # Dry-run, show violations only
vendor/bin/pint app/Http     # Format specific directory
```

### Artisan Utilities

```bash
php artisan livewire:make AbsensiManager          # Create Livewire component
php artisan livewire:make AbsensiManager --inline # Inline view component
php artisan make:model Absensi -m                 # Model + migration
php artisan make:migration create_absensi_table    # Migration only
php artisan migrate                               # Run migrations
php artisan migrate:rollback                      # Rollback last batch
php artisan route:list                            # List all routes
php artisan optimize:clear                        # Clear all caches
```

## Architecture

### Directory Structure

```
app/
  Livewire/                          # All Livewire components (flat, not Http/Livewire)
    Holdings/Hq/Sdm/Hr/              # HR module (Employee, Absensi)
    Holdings/Hq/Sdm/Rt/Inventaris/   # GA/Inventaris module
    Holdings/Hq/Finance/             # Finance module
    Holdings/Campus/Siakad/          # Academic module
    Holdings/Campus/LMS/             # LMS module
    Dashboard/                       # Dashboard widgets
    Auth/                            # Auth components
  Models/                            # Eloquent models
  Http/Controllers/                  # Traditional controllers (legacy, avoid)
  Http/Middleware/                   # Custom middleware
routes/
  web.php                            # Main entry, requires sub-route files
  holdings/hq/sdm.php                # SDM module routes (HR, Inventaris)
  holdings/hq/finance.php            # Finance routes
  holdings/hq/resort.php             # Resort routes (mostly commented)
  dashboard.php                      # Dashboard routes
resources/views/livewire/            # Livewire views (kebab-case naming)
resources/views/components/          # Reusable Blade components
```

### Routing Pattern

Routes are organized in `routes/holdings/hq/sdm.php` with module-level authorization:

```php
Route::prefix('holdings/hq/sdm/hr')
    ->name('holdings.hq.sdm.hr.')
    ->middleware('authorize.module:01001')
    ->group(function () {
        Route::get('/', EmployeeTable::class)
            ->name('employee-table')
            ->middleware('authorize.permission:EMP_VIEW');
    });
```

### Middleware

- `authorize.module:XXXX` - Module-level authorization (module codes like `01001`)
- `authorize.permission:XXX` - Permission-level (e.g., `EMP_VIEW`, `EMP_CREATE`)
- `sso.verified` - SSO verification
- `force.password.change` - Force password change on first login
- `auth.membership` - Set auth membership context

All authenticated routes use: `auth`, `force.password.change`, `auth.membership`

## Code Style & Conventions

### PHP/Backend

- **Formatting**: Laravel Pint (Laravel preset). Run `vendor/bin/pint` before committing.
- **Naming**: StudlyCase for classes, camelCase for methods/properties, snake_case for DB columns.
- **Imports**: Group imports (facades, then classes, then models). Alphabetical within groups.
- **Types**: Use typed properties and return types everywhere (`public string $search = ''`).
- **Models**: Use `$fillable`, not `$guarded`. Define relationships with proper foreign keys.

### Livewire Components (v3)

```php
namespace App\Livewire\Holdings\Hq\Sdm\Hr;

use Livewire\Component;
use Livewire\Attributes\On;

class AbsensiManager extends Component
{
    // Public properties for form/state
    public array $breadcrumbs = [];
    public array $toast = ['show' => false, 'type' => 'success', 'message' => ''];
    public bool $canCreate = false;

    // Query string for URL persistence
    protected $queryString = ['search' => ['except' => '']];

    public function mount(): void
    {
        $this->breadcrumbs = [...];
        $user = auth()->user();
        $this->canCreate = (bool) ($user?->hasPermission('ABS_UPLOAD') ?? false);
    }

    // Use #[On] attribute for event listeners (Livewire v3)
    #[On('absensi-created')]
    public function handleCreated(): void { ... }

    // Use $this->dispatch() (not emit) for events
    public function save(): void
    {
        $this->validate();
        // ...
        $this->dispatch('absensi-created');
    }

    public function render()
    {
        return view('livewire.holdings.hq.sdm.hr.absensi-manager', [...])
            ->layout('components.sccr-layout');
    }
}
```

### Livewire Conventions

- **Layout**: Always use `->layout('components.sccr-layout')`
- **View naming**: kebab-case matching component name (`AbsensiManager` → `absensi-manager.blade.php`)
- **Permissions**: Check in `mount()` via `$user?->hasPermission('PERM_CODE')`
- **Events**: `$this->dispatch('event-name')` (NOT `emit`)
- **Event listeners**: `#[On('event-name')]` attribute
- **File uploads**: Use `WithFileUploads` trait + `wire:model="file"`
- **Toast**: Use `$this->toast` array for inline notifications

### Blade Views

- Use `<x-app-layout>` or `<x-ui.sccr-card>` for page wrappers
- Alpine.js for client-side interactivity (tabs, modals, dropdowns)
- Livewire directives: `wire:model`, `wire:click`, `wire:submit`, `wire:model.file`
- Session flash: `@if (session('success'))` for notifications

### JavaScript/Frontend

- **Vite**: Use `@vite(['resources/js/file.js'])` in Blade views
- **Alpine.js**: `x-data`, `x-show`, `x-transition`, `@click` for client-side state
- **Livewire JS**: Access `$wire` from JS for calling Livewire methods

### Error Handling

- **Validation**: Use `$this->validate()` in Livewire or `$request->validate()` in controllers
- **Custom messages**: `$messages` array property for Indonesian validation messages
- **Exceptions**: Wrap risky operations in `try/catch (\Throwable $e)` with user-friendly messages
- **Database errors**: Use `\DB::table()` for legacy tables without models

### Database

- **Legacy tables**: Use `\DB::table('table_name')` when no model exists
- **Migrations**: Table `absensi` has no migration (external/legacy table)
- **Models**: `AbsensiPWA` maps to `absensi` table with `primaryKey = 'id_absensi'`

### Testing (Pest)

```php
// Feature tests use RefreshDatabase
pest()->extend(Tests\TestCase::class)
    ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Feature');

// Example test structure
test('example', function () {
    $response = $this->get('/');
    $response->assertStatus(200);
});
```

## Key Rules

1. **NO comments** unless explicitly requested
2. **NO emojis** in code unless explicitly requested
3. **NO undocumented URLs** - never generate or guess URLs
4. **Follow existing patterns** - mimic code style from neighboring files
5. **Commit only when asked** - never auto-commit changes
6. **Run lint/typecheck** after code changes: `vendor/bin/pint` for PHP
7. **Kept concise** - stop after completing the task, no summaries
