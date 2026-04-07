# AGENTS.md - SCCR Project

Laravel 12 + Livewire 3 + Alpine.js + TailwindCSS 4 HR/attendance management system. Pest for testing, Pint for formatting, Vite for frontend.

## Commands

```bash
composer run dev          # server + queue + logs + Vite (concurrently)
composer run test         # config:clear + artisan test
vendor/bin/pint           # format (run before committing)
vendor/bin/pint --test    # dry-run
```

## Architecture

### Route Structure

All routes loaded from `routes/web.php`. Module route files are `require`d inside an auth middleware group:

```
routes/
  web.php                  # root, requires all sub-files
  auth.php                 # Livewire auth routes
  dashboard.php            # /dashboard and /dashboard/{holding}
  sso/sso.php              # SSO admin (roles, users, nav-items, approvals)
  holdings/hq/sdm.php      # HR (module 01001) + Inventaris (module 01005)
  holdings/hq/finance.php  # Finance
  holdings/campus/campus.php  # LMS + Siakad
  holdings/resto/resto.php    # Resto
  holdings/clinic/         # exists, check if active
  holdings/farm/           # exists, check if active
  holdings/hospital/       # exists, check if active
  holdings/resort/         # exists but empty (commented out)
```

### Authorization

Two-tier middleware on module routes:

- `authorize.module:XXXX` — checks `auth_modules` table (cached 12h), user module assignment, lock/inactive status. Super admin bypasses.
- `authorize.permission:XXX` — fine-grained permission check

SSO routes use `authorize.module:00000`. LMS routes use `role:dosen` instead.

### Directory Layout

```
app/
  Livewire/                    # flat, not Http/Livewire
    Holdings/Hq/Sdm/Hr/        # HR: Employee, Absensi
    Holdings/Hq/Sdm/Rt/Inventaris/  # GA/Inventaris
    Holdings/Hq/Finance/       # Finance
    Holdings/Campus/Siakad/    # Academic
    Holdings/Campus/LMS/       # Learning management
    Holdings/Resto/            # Restaurant
    Bod/                       # Board of Director dashboards
    Dashboard/                 # Main dashboards per holding
    Auth/                      # Auth components
    Sso/                       # SSO-specific (approvals)
  Models/                      # Eloquent models
  Http/Middleware/             # AuthorizeModule, AuthorizePermission, ForcePasswordChange, SetAuthMembership, SSOVerified, ApiTokenGuard
```

## Conventions

### Livewire Components

- Namespace: `App\Livewire\Holdings\Hq\Sdm\Hr`
- Views: `resources/views/livewire/holdings/hq/sdm/hr/` (kebab-case)
- Always: `->layout('components.sccr-layout')`
- Permissions: check in `mount()` via `$user?->hasPermission('CODE')`
- Events: `$this->dispatch('event-name')` / `#[On('event-name')]`
- Toast: `$this->toast = ['show' => false, 'type' => 'success', 'message' => '']`
- Vite: uses `@defstudio/vite-livewire-plugin`

### Database

- Default: SQLite (`.env.example`). Testing: SQLite `:memory:`
- Queue/session/cache: `database` driver
- Legacy `absensi` table: model `AbsensiPWA`, `primaryKey = 'id_absensi'`, no migration
- Tables without models: use `\DB::table()`

### Models

- Use `$fillable`, not `$guarded`
- Typed properties and return types everywhere
- Models mirror route structure: `app/Models/Holdings/Hq/`, `app/Models/Holdings/Campus/`, etc.

### Testing

- Pest with `RefreshDatabase` for Feature tests
- SQLite in-memory, sync queue, array cache/mail/session
- `php artisan test tests/Feature/File.php` for single file
- `php artisan test --filter=name` for filtered

## Key Rules

1. NO comments unless requested
2. NO emojis unless requested
3. NO guessing URLs
4. Follow existing patterns from neighboring files
5. Commit only when asked
6. Run `vendor/bin/pint` after PHP changes
