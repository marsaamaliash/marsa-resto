<?php

namespace App\Livewire\Auth\Sso;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class SsoUserCreate extends Component
{
    public array $toast = ['show' => false, 'type' => 'success', 'message' => ''];

    public bool $canCreate = false;

    public bool $canRoleAssign = false;

    public string $identity_type = 'employee';

    public string $identity_key = '';

    // ✅ select selalu string → simpan string agar tidak “silent fail” karena type mismatch
    public string $holding_id = '';

    public string $department_id = '';

    public string $division_id = '';

    public bool $is_active = true;

    public string $username = '';

    public string $email = ''; // kosong = null

    public bool $is_locked = false;

    public array $role_ids = []; // string[] dari checkbox

    public function mount(): void
    {
        $u = auth()->user();
        $this->canCreate = (bool) ($u?->hasPermission('SSO_USER_CREATE') ?? false);
        $this->canRoleAssign = (bool) ($u?->hasPermission('SSO_USER_ROLE_ASSIGN') ?? false);

        if (! $this->canCreate) {
            abort(403, 'Forbidden');
        }
    }

    private function toast(string $type, string $message): void
    {
        $this->toast = ['show' => true, 'type' => $type, 'message' => $message];
    }

    protected function rules(): array
    {
        return [
            'identity_type' => 'required|in:employee,lecturer,student',
            'identity_key' => 'required|string|max:30',

            'holding_id' => 'nullable',
            'department_id' => 'nullable',
            'division_id' => 'nullable',

            'is_active' => 'boolean',

            'username' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'is_locked' => 'boolean',

            'role_ids' => 'array',
        ];
    }

    private function toNullableInt(string $v): ?int
    {
        $v = trim($v);
        if ($v === '') {
            return null;
        }
        if (! ctype_digit($v)) {
            return null;
        }

        return (int) $v;
    }

    protected function holdingOptions(): array
    {
        return DB::table('holdings')->orderBy('name')->get(['id', 'name', 'alias'])
            ->mapWithKeys(fn ($h) => [(string) $h->id => ($h->name.($h->alias ? ' - '.$h->alias : ''))])
            ->toArray();
    }

    protected function departmentOptions(): array
    {
        return DB::table('departments')->orderBy('name')->get(['id', 'name'])
            ->mapWithKeys(fn ($d) => [(string) $d->id => (string) $d->name])->toArray();
    }

    protected function divisionOptions(): array
    {
        return DB::table('divisions')->orderBy('name')->get(['id', 'name'])
            ->mapWithKeys(fn ($d) => [(string) $d->id => (string) $d->name])->toArray();
    }

    protected function roleOptions(): array
    {
        return DB::table('auth_roles')->orderBy('code')->get(['id', 'code', 'name'])
            ->mapWithKeys(fn ($r) => [(string) $r->id => ((string) $r->code.' - '.(string) $r->name)])->toArray();
    }

    public function save(): void
    {
        if (! $this->canCreate) {
            $this->toast('warning', 'Tidak punya izin create.');

            return;
        }

        $this->resetErrorBag();
        $this->validate();

        $identityType = trim($this->identity_type);
        $identityKey = trim($this->identity_key);

        $username = trim($this->username) !== '' ? trim($this->username) : $identityKey;
        $email = trim($this->email) !== '' ? trim($this->email) : null;

        $holdingId = $this->toNullableInt($this->holding_id);
        $depId = $this->toNullableInt($this->department_id);
        $divId = $this->toNullableInt($this->division_id);

        try {
            DB::transaction(function () use ($identityType, $identityKey, $holdingId, $depId, $divId, $username, $email) {

                // ✅ Ambil identity (kalau sudah ada dari import employee, kita LINK)
                $identity = DB::table('auth_identities')
                    ->where('identity_type', $identityType)
                    ->where('identity_key', $identityKey)
                    ->lockForUpdate()
                    ->first(['id', 'auth_user_id']);

                if ($identity) {
                    // kalau sudah ter-link → stop
                    if (! empty($identity->auth_user_id)) {
                        throw new \RuntimeException("Identity sudah ter-link ke user_id={$identity->auth_user_id}.");
                    }

                    $identityId = (int) $identity->id;

                    // optional tapi recommended: sinkronkan scope dari form
                    DB::table('auth_identities')->where('id', $identityId)->update([
                        'holding_id' => $holdingId,
                        'department_id' => $depId,
                        'division_id' => $divId,
                        'is_active' => $this->is_active ? 1 : 0,
                        'updated_at' => now(),
                    ]);
                } else {
                    // kalau belum ada → create identity
                    $identityId = DB::table('auth_identities')->insertGetId([
                        'auth_user_id' => null,
                        'identity_type' => $identityType,
                        'identity_key' => $identityKey,
                        'holding_id' => $holdingId,
                        'department_id' => $depId,
                        'division_id' => $divId,
                        'is_active' => $this->is_active ? 1 : 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                // ✅ create user login untuk identity tsb
                if (DB::table('auth_users')->where('username', $username)->exists()) {
                    throw new \RuntimeException("Username sudah dipakai: {$username}");
                }
                if ($email && DB::table('auth_users')->where('email', $email)->exists()) {
                    throw new \RuntimeException("Email sudah dipakai: {$email}");
                }

                $userId = DB::table('auth_users')->insertGetId([
                    'identity_id' => $identityId,
                    'username' => $username,
                    'email' => $email,
                    'password' => Hash::make('password123'),
                    'must_change_password' => 1,
                    'password_changed_at' => null,
                    'last_login_at' => null,
                    'is_locked' => $this->is_locked ? 1 : 0,
                    'remember_token' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                    'is_super_admin' => 0,
                    'is_super_scope' => 0,
                ]);

                // ✅ link balik
                DB::table('auth_identities')->where('id', $identityId)->update([
                    'auth_user_id' => $userId,
                    'updated_at' => now(),
                ]);

                // roles optional
                if ($this->canRoleAssign && ! empty($this->role_ids)) {
                    $roleIds = array_values(array_unique(array_map('intval', $this->role_ids)));
                    $validRoleIds = DB::table('auth_roles')
                        ->whereIn('id', $roleIds)
                        ->pluck('id')
                        ->map(fn ($x) => (int) $x)
                        ->toArray();

                    foreach ($validRoleIds as $rid) {
                        DB::table('auth_user_roles')->updateOrInsert(
                            ['auth_user_id' => $userId, 'role_id' => $rid],
                            ['created_at' => now(), 'updated_at' => now()]
                        );
                    }
                }
            });

            // ✅ sukses: tutup overlay + refresh table parent
            $this->dispatch('sso-user-created');
            $this->dispatch('sso-user-overlay-close');

        } catch (\Throwable $e) {
            $this->toast('warning', $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.auth.sso.sso-user-create', [
            'holdingOptions' => ['' => '-'] + $this->holdingOptions(),
            'departmentOptions' => ['' => '-'] + $this->departmentOptions(),
            'divisionOptions' => ['' => '-'] + $this->divisionOptions(),
            'roleOptions' => $this->roleOptions(),
        ]);
    }
}
