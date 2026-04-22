<?php

namespace App\Models\Auth;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\HasApiTokens;

class AuthUser extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $connection = 'mysql';

    protected $table = 'auth_users';

    protected $fillable = [
        'identity_id',
        'username',
        'email',
        'password',
        'is_locked',
        'is_super_admin',
        'is_super_scope',
        'must_change_password',
        'password_changed_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'is_super_admin' => 'boolean',
        'is_super_scope' => 'boolean',
        'must_change_password' => 'boolean',
        'password_changed_at' => 'datetime',
    ];

    /* =========================
     | SUPER PRIVILEGE
     ========================= */

    public function isSuperAdmin(): bool
    {
        return $this->is_super_admin === true;
    }

    public function isSuperScope(): bool
    {
        return $this->is_super_scope === true;
    }

    /* =========================
     | RELATION
     | auth_users.identity_id -> auth_identities.id
     ========================= */

    public function identity()
    {
        return $this->belongsTo(AuthIdentity::class, 'identity_id');
    }

    public function identities()
    {
        return $this->hasMany(AuthIdentity::class, 'auth_user_id');
    }

    /* =========================
     | STATUS
     ========================= */

    public function isActive(): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return (int) ($this->identity?->is_active ?? 0) === 1;
    }

    /* =========================
     | CACHE VERSION
     ========================= */

    protected function cacheVersionKey(): string
    {
        return "auth:user:{$this->id}:cache_ver";
    }

    protected function cacheVersion(): int
    {
        $v = Cache::get($this->cacheVersionKey(), 1);
        $v = is_numeric($v) ? (int) $v : 1;

        return max(1, $v);
    }

    protected function bumpCacheVersion(): void
    {
        $key = $this->cacheVersionKey();
        $cur = $this->cacheVersion();
        Cache::put($key, $cur + 1, now()->addYears(10));
    }

    public function clearAuthCache(): void
    {
        $this->bumpCacheVersion();
    }

    /* =========================
     | SCOPE SNAPSHOT
     ========================= */

    protected function scopeSnapshot(): array
    {
        $i = $this->identity;

        return [
            'holding_id' => (int) ($i?->holding_id ?? 0),
            'department_id' => (int) ($i?->department_id ?? 0),
            'division_id' => (int) ($i?->division_id ?? 0),
            'identity_active' => (int) ($i?->is_active ?? 0),
        ];
    }

    /**
     * Helper: build tuples + bindings for:
     * (scope_type, scope_target_id) IN (('global',0),('holding',?),('department',?),('division',?))
     */
    protected function scopeTupleSqlAndBindings(int $holdingId, int $depId, int $divId): array
    {
        $pairs = [['global', 0]];

        if ($holdingId > 0) {
            $pairs[] = ['holding', $holdingId];
        }
        if ($depId > 0) {
            $pairs[] = ['department', $depId];
        }
        if ($divId > 0) {
            $pairs[] = ['division', $divId];
        }

        $placeholders = implode(',', array_fill(0, count($pairs), '(?,?)'));

        $bindings = [];
        foreach ($pairs as $p) {
            $bindings[] = $p[0];
            $bindings[] = (int) $p[1];
        }

        return [
            'sql' => "(o.scope_type, o.scope_target_id) IN ({$placeholders})",
            'bindings' => $bindings,
        ];
    }

    /* =========================
     | MODULE ROUTE (helper)
     ========================= */

    public function moduleRoute(string $moduleCode): ?string
    {
        $moduleCode = trim($moduleCode);
        if ($moduleCode === '') {
            return null;
        }

        // optional: kalau user tidak punya module ini, kembalikan null
        if (! $this->isSuperAdmin() && ! $this->hasModule($moduleCode)) {
            return null;
        }

        $key = "auth:module:route:{$moduleCode}";

        return Cache::remember($key, now()->addMinutes(30), function () use ($moduleCode) {
            return DB::table('auth_modules')
                ->where('code', $moduleCode)
                ->where('is_active', 1)
                ->value('route');
        });
    }

    /* =========================
     | MODULE (ROLE_MODULES + OVERRIDE)
     ========================= */

    public function hasModule(string $moduleCode): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return array_key_exists($moduleCode, $this->modulesWithAccessLevel());
    }

    public function modules(): array
    {
        return array_keys($this->modulesWithAccessLevel());
    }

    public function moduleAccessLevel(string $moduleCode): ?string
    {
        if ($this->isSuperAdmin()) {
            return 'full';
        }
        $map = $this->modulesWithAccessLevel();

        return $map[$moduleCode]['access_level'] ?? null;
    }

    /**
     * [
     *   '01001' => ['access_level' => 'view'|'full'],
     * ]
     */
    public function modulesWithAccessLevel(): array
    {
        $v = $this->cacheVersion();
        $s = $this->scopeSnapshot();

        $cacheKey = "auth:user:{$this->id}:v{$v}:mods:"
            ."h{$s['holding_id']}:d{$s['department_id']}:dv{$s['division_id']}";

        return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($s) {
            return $this->resolveModulesWithAccessLevel($s);
        });
    }

    protected function resolveModulesWithAccessLevel(array $scope): array
    {
        // super admin => semua module aktif full
        if ($this->isSuperAdmin()) {
            $rows = DB::table('auth_modules')
                ->where('is_active', 1)
                ->orderBy('code')
                ->get(['code']);

            $map = [];
            foreach ($rows as $m) {
                $map[(string) $m->code] = ['access_level' => 'full'];
            }

            return $map;
        }

        if ((int) ($scope['identity_active'] ?? 0) !== 1) {
            return [];
        }

        $holdingId = (int) $scope['holding_id'];
        $depId = (int) $scope['department_id'];
        $divId = (int) $scope['division_id'];

        // 1) BASE dari role_modules (scope-aware) + hanya module aktif
        $baseRows = DB::table('auth_user_roles as ur')
            ->join('auth_role_modules as rm', 'rm.role_id', '=', 'ur.role_id')
            ->join('auth_modules as m', function ($j) {
                $j->on('m.code', '=', 'rm.module_code')
                    ->where('m.is_active', '=', 1);
            })
            ->where('ur.auth_user_id', (int) $this->id)
            ->where('rm.is_active', 1)
            ->where(function ($q) use ($holdingId, $depId, $divId) {
                $q->whereNull('rm.scope_type')
                    ->orWhere('rm.scope_type', 'global');

                $q->orWhere(function ($qq) use ($holdingId) {
                    $qq->where('rm.scope_type', 'holding')
                        ->where('rm.scope_holding_id', $holdingId);
                });

                $q->orWhere(function ($qq) use ($depId) {
                    $qq->where('rm.scope_type', 'department')
                        ->where('rm.scope_department_id', $depId);
                });

                $q->orWhere(function ($qq) use ($divId) {
                    $qq->where('rm.scope_type', 'division')
                        ->where('rm.scope_division_id', $divId);
                });
            })
            ->groupBy('rm.module_code')
            ->get([
                'rm.module_code',
                DB::raw("MAX(CASE WHEN rm.access_level='full' THEN 2 ELSE 1 END) as access_rank"),
            ]);

        $map = [];
        foreach ($baseRows as $r) {
            $code = (string) $r->module_code;
            $map[$code] = ['access_level' => ((int) $r->access_rank >= 2) ? 'full' : 'view'];
        }

        // === OVERRIDES (pakai scope_target_id) ===
        $tuple = $this->scopeTupleSqlAndBindings($holdingId, $depId, $divId);

        // 2) DENY override (menang)
        $deny = DB::table('auth_user_module_overrides as o')
            ->where('o.auth_user_id', (int) $this->id)
            ->where('o.is_active', 1)
            ->where('o.effect', 'deny')
            ->whereRaw($tuple['sql'], $tuple['bindings'])
            ->pluck('o.module_code')
            ->map(fn ($x) => (string) $x)
            ->unique()
            ->values()
            ->toArray();

        foreach ($deny as $code) {
            unset($map[$code]);
        }

        // 3) ALLOW override (hanya module aktif; add/upgrade)
        $allowRows = DB::table('auth_user_module_overrides as o')
            ->join('auth_modules as m', function ($j) {
                $j->on('m.code', '=', 'o.module_code')
                    ->where('m.is_active', '=', 1);
            })
            ->where('o.auth_user_id', (int) $this->id)
            ->where('o.is_active', 1)
            ->where('o.effect', 'allow')
            ->whereRaw($tuple['sql'], $tuple['bindings'])
            ->get(['o.module_code', 'o.access_level']);

        foreach ($allowRows as $r) {
            $code = (string) $r->module_code;
            $new = ((string) $r->access_level === 'full') ? 'full' : 'view';

            if (! isset($map[$code])) {
                $map[$code] = ['access_level' => $new];

                continue;
            }

            $cur = $map[$code]['access_level'] ?? 'view';
            if ($cur !== 'full' && $new === 'full') {
                $map[$code]['access_level'] = 'full';
            }
        }

        ksort($map);

        return $map;
    }

    /* =========================
     | PERMISSIONS (MODULE-GATED + OVERRIDE)
     ========================= */

    public function hasPermission(string $code): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return in_array($code, $this->permissions(), true);
    }

    public function permissions(): array
    {
        $v = $this->cacheVersion();
        $s = $this->scopeSnapshot();

        $cacheKey = "auth:user:{$this->id}:v{$v}:perm:"
            ."h{$s['holding_id']}:d{$s['department_id']}:dv{$s['division_id']}";

        return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($s) {
            return $this->resolvePermissions($s);
        });
    }

    protected function resolvePermissions(array $scope): array
    {
        // super admin => semua permission aktif
        if ($this->isSuperAdmin()) {
            return DB::table('auth_permissions')
                ->where('is_active', 1)
                ->orderBy('module_code')
                ->orderBy('code')
                ->pluck('code')
                ->map(fn ($x) => (string) $x)
                ->unique()
                ->values()
                ->toArray();
        }

        if ((int) ($scope['identity_active'] ?? 0) !== 1) {
            return [];
        }

        // MODULE GATE: hanya permission dari module yang effective
        // (pakai cached modulesWithAccessLevel agar hemat)
        $moduleMap = $this->modulesWithAccessLevel();
        $allowedModules = array_keys($moduleMap);
        if (empty($allowedModules)) {
            return [];
        }

        $holdingId = (int) $scope['holding_id'];
        $depId = (int) $scope['department_id'];
        $divId = (int) $scope['division_id'];

        $tuple = $this->scopeTupleSqlAndBindings($holdingId, $depId, $divId);

        // 1) BASE dari role_permissions (difilter module_code IN allowedModules)
        $base = DB::table('auth_user_roles as ur')
            ->join('auth_role_permissions as rp', 'rp.role_id', '=', 'ur.role_id')
            ->join('auth_permissions as p', 'p.id', '=', 'rp.permission_id')
            ->where('ur.auth_user_id', (int) $this->id)
            ->where('p.is_active', 1)
            ->whereIn('p.module_code', $allowedModules)
            ->pluck('p.code')
            ->map(fn ($x) => (string) $x)
            ->unique()
            ->values()
            ->toArray();

        $set = array_fill_keys($base, true);

        // 2) OVERRIDE DENY (tetap module-gated)  ✅ pakai scope_target_id
        $deny = DB::table('auth_user_permission_overrides as o')
            ->join('auth_permissions as p', function ($j) use ($allowedModules) {
                $j->on('p.code', '=', 'o.permission_code')
                    ->where('p.is_active', '=', 1)
                    ->whereIn('p.module_code', $allowedModules);
            })
            ->where('o.auth_user_id', (int) $this->id)
            ->where('o.is_active', 1)
            ->where('o.effect', 'deny')
            ->whereRaw($tuple['sql'], $tuple['bindings'])
            ->pluck('o.permission_code')
            ->map(fn ($x) => (string) $x)
            ->unique()
            ->values()
            ->toArray();

        foreach ($deny as $code) {
            unset($set[$code]);
        }

        // 3) OVERRIDE ALLOW (hanya permission aktif & module-nya allowed) ✅ scope_target_id
        $allow = DB::table('auth_user_permission_overrides as o')
            ->join('auth_permissions as p', function ($j) use ($allowedModules) {
                $j->on('p.code', '=', 'o.permission_code')
                    ->where('p.is_active', '=', 1)
                    ->whereIn('p.module_code', $allowedModules);
            })
            ->where('o.auth_user_id', (int) $this->id)
            ->where('o.is_active', 1)
            ->where('o.effect', 'allow')
            ->whereRaw($tuple['sql'], $tuple['bindings'])
            ->pluck('o.permission_code')
            ->map(fn ($x) => (string) $x)
            ->unique()
            ->values()
            ->toArray();

        foreach ($allow as $code) {
            $set[$code] = true;
        }

        $out = array_keys($set);
        sort($out);

        return $out;
    }
}
