# AGENTS.md - SCCR Project

Laravel 12 + Livewire 3 + Alpine.js + TailwindCSS 4 HR/attendance management system. Pest for testing, Pint for formatting, Vite for frontend.

## Commands

```bash
composer run dev          # server + queue + logs(pail) + Vite (concurrently)
composer run test         # config:clear + artisan test
vendor/bin/pint           # format (run before committing)
vendor/bin/pint --test    # dry-run
php artisan test tests/Feature/File.php   # single file
php artisan test --filter=name            # filtered
```

## Architecture

### Route Structure

All routes loaded from `routes/web.php`. Module route files are `require`d inside an auth middleware group:

```
routes/
  web.php                  # root, requires all sub-files
  auth.php                 # Livewire auth routes (login/logout only)
  dashboard.php            # /dashboard and /dashboard/{holding}
  sso/sso.php              # SSO admin (roles, users, nav-items, approvals)
  holdings/hq/sdm.php      # HR (module 01001) + Inventaris (module 01005)
  holdings/hq/finance.php  # Finance (module 01003, mostly commented out)
  holdings/campus/campus.php  # LMS + Siakad
  holdings/resto/resto.php    # Resto (prefix: dashboard/resto, NO module auth)
```

Note: `holdings/clinic/`, `holdings/farm/`, `holdings/hospital/`, `holdings/resort/` directories may exist but are NOT loaded in `web.php`.

### Middleware Stack

Global module routes (`routes/web.php` line 36): `['auth', 'force.password.change', 'auth.membership']`

- `force.password.change` — redirects to `/sso/change-password` if `must_change_password=1` AND password is default (`password123`/`Password123`/`PASSWORD123`). Super admin bypasses.
- `auth.membership` → `SetAuthMembership` — sets user's auth membership context

Two-tier authorization on module routes:

- `authorize.module:XXXX` — checks `auth_modules` table (cached 12h), user module assignment, lock/inactive status. Super admin bypasses. Also accepts second param `full` for access level.
- `authorize.permission:XXX` — fine-grained permission check, also verifies module membership first

SSO routes use `authorize.module:00000`. LMS routes use `role:dosen` instead of module auth. Resto and Siakad routes have NO module-level authorization.

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
    Layout/                    # SccrSidebar
    GlobalToast.php, SccrToolbar.php
  Models/                      # Eloquent models
    Auth/                      # AuthUser, AuthIdentity, AuthApproval, AuthNavItem, AuthPersonalAccessToken
    Holdings/Hq/Sdm/Hr/        # Emp_Employee, Emp_Position, Emp_JobTitle
    Holdings/Hq/Sdm/Rt/Inventaris/
    Holdings/Hq/Finance/       # Fin_Account, Fin_Account_List
    Holdings/Campus/LMS/       # LmsRoom, Quiz*, Participant, etc.
    Holdings/Campus/Siakads/Students/
    Holdings/Resto/            # Rst_* prefixed models
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
- Vite: uses `@defstudio/vite-livewire-plugin`. Default Blade refresh is disabled; livewire plugin triggers Tailwind CSS refresh.

### Database

- Default: SQLite (`database/database.sqlite`). Testing: SQLite `:memory:`
- Queue/session/cache: `database` driver
- Legacy `absensi` table: model `AbsensiPWA`, `primaryKey = 'id_absensi'`, no migration
- Tables without models: use `\DB::table()`
- Auth tables: `auth_modules`, `auth_permissions`, `auth_users` (separate from `users` table)

### Models

- Use `$fillable`, not `$guarded`
- Typed properties and return types everywhere
- Models mirror route structure: `app/Models/Holdings/Hq/`, `app/Models/Holdings/Campus/`, etc.
- Resto models use `Rst_` prefix convention (e.g., `Rst_Menu`, `Rst_Order`)
- Auth models live in `app/Models/Auth/`

### Testing

- Pest with `RefreshDatabase` for Feature tests (configured in `tests/Pest.php`)
- SQLite in-memory, sync queue, array cache/mail/session (configured in `phpunit.xml`)
- `php artisan test tests/Feature/File.php` for single file
- `php artisan test --filter=name` for filtered
- No Unit tests currently exist

## Key Rules

1. NO comments unless requested
2. NO emojis unless requested
3. NO guessing URLs
4. Follow existing patterns from neighboring files
5. Commit only when asked
6. Run `vendor/bin/pint` after PHP changes
