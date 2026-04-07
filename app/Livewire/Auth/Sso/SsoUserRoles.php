<?php

namespace App\Livewire\Auth\Sso;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class SsoUserRoles extends Component
{
    public int $userId;

    public array $userInfo = [
        'id' => 0,
        'username' => '',
        'email' => '',
        'identity_type' => '',
        'identity_key' => '',
    ];

    public string $searchRole = '';

    /** @var array<int, bool> */
    public array $selectedRoleIds = []; // [role_id => true]

    public bool $canRoleAssign = false;

    public function mount(int $userId): void
    {
        $this->userId = (int) $userId;

        $u = auth()->user();
        $this->canRoleAssign = (bool) ($u?->hasPermission('SSO_USER_ROLE_ASSIGN') ?? false);

        abort_unless($this->canRoleAssign, 403, 'Tidak punya izin assign role.');

        $row = DB::table('auth_users as u')
            ->join('auth_identities as i', 'i.id', '=', 'u.identity_id')
            ->where('u.id', $this->userId)
            ->first([
                'u.id', 'u.username', 'u.email',
                'i.identity_type', 'i.identity_key',
            ]);

        abort_unless($row, 404, 'User tidak ditemukan.');

        $this->userInfo = [
            'id' => (int) $row->id,
            'username' => (string) $row->username,
            'email' => (string) ($row->email ?? ''),
            'identity_type' => (string) $row->identity_type,
            'identity_key' => (string) $row->identity_key,
        ];

        // preload selected roles
        $selected = DB::table('auth_user_roles')
            ->where('auth_user_id', $this->userId)
            ->pluck('role_id')
            ->map(fn ($x) => (int) $x)
            ->toArray();

        $map = [];
        foreach ($selected as $rid) {
            $map[$rid] = true;
        }
        $this->selectedRoleIds = $map;
    }

    public function close(): void
    {
        // parent SsoUserTable sudah punya listener ini
        $this->dispatch('sso-user-overlay-close');
    }

    public function save(): void
    {
        abort_unless($this->canRoleAssign, 403, 'Tidak punya izin assign role.');

        // normalize selected ids
        $ids = array_keys(array_filter($this->selectedRoleIds ?? [], fn ($v) => (bool) $v));
        $ids = array_values(array_unique(array_map('intval', $ids)));

        // validasi: semua role_id harus ada
        if (! empty($ids)) {
            $existsCount = DB::table('auth_roles')->whereIn('id', $ids)->count();
            if ($existsCount !== count($ids)) {
                abort(422, 'Ada role yang tidak valid.');
            }
        }

        $now = now();
        $actorId = (int) auth()->id();

        DB::transaction(function () use ($ids, $now, $actorId) {
            // lock existing user_roles rows
            DB::table('auth_user_roles')
                ->where('auth_user_id', $this->userId)
                ->lockForUpdate()
                ->get();

            DB::table('auth_user_roles')
                ->where('auth_user_id', $this->userId)
                ->delete();

            if (! empty($ids)) {
                $rows = [];
                foreach ($ids as $rid) {
                    $rows[] = [
                        'auth_user_id' => $this->userId,
                        'role_id' => (int) $rid,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
                DB::table('auth_user_roles')->insert($rows);
            }

            // audit
            DB::table('auth_audit_logs')->insert([
                'user_id' => $actorId,
                'module_code' => '00000',
                'action' => 'SSO_USER_ROLE_ASSIGN',
                'payload' => json_encode([
                    'target_user_id' => $this->userId,
                    'role_ids' => $ids,
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'ip' => request()->ip(),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        });

        // clear cache target user
        Cache::forget("auth:user:{$this->userId}:permissions");
        Cache::forget("auth:user:{$this->userId}:modules");

        // notify parent table (parent akan close overlay + toast + refresh)
        $this->dispatch('sso-user-roles-updated');
    }

    public function rolesList(): array
    {
        $q = trim($this->searchRole);

        $rows = DB::table('auth_roles')
            ->when($q !== '', function ($qq) use ($q) {
                $qq->where(function ($w) use ($q) {
                    $w->where('code', 'like', "%{$q}%")
                        ->orWhere('name', 'like', "%{$q}%");
                });
            })
            ->orderBy('code', 'asc')
            ->get(['id', 'code', 'name']);

        return $rows->map(fn ($r) => [
            'id' => (int) $r->id,
            'code' => (string) $r->code,
            'name' => (string) $r->name,
        ])->toArray();
    }

    public function render()
    {
        return view('livewire.auth.sso.sso-user-roles', [
            'userInfo' => $this->userInfo,
            'roles' => $this->rolesList(),
        ]);
    }
}
