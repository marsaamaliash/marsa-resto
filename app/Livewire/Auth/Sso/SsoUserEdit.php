<?php

namespace App\Livewire\Auth\Sso;

use Illuminate\Support\Facades\DB;
use Livewire\Component;

class SsoUserEdit extends Component
{
    public int $userId;

    public array $toast = ['show' => false, 'type' => 'success', 'message' => ''];

    public bool $canUpdate = false;

    public bool $canIdentityUpdate = false;

    public bool $canLock = false;

    public string $username = '';

    public ?string $email = null;

    public bool $is_locked = false;

    public string $identity_type = '';

    public string $identity_key = '';

    public bool $is_active = true;

    public ?int $holding_id = null;

    public ?int $department_id = null;

    public ?int $division_id = null;

    public function mount(int $userId): void
    {
        $this->userId = $userId;

        $u = auth()->user();
        $this->canUpdate = (bool) ($u?->hasPermission('SSO_USER_UPDATE') ?? false);
        $this->canIdentityUpdate = (bool) ($u?->hasPermission('SSO_IDENTITY_UPDATE') ?? false);
        $this->canLock = (bool) ($u?->hasPermission('SSO_USER_LOCK') ?? false);

        if (! $this->canUpdate && ! $this->canIdentityUpdate && ! $this->canLock) {
            abort(403, 'Forbidden');
        }

        $this->load();
    }

    protected function load(): void
    {
        $row = DB::table('auth_users as u')
            ->join('auth_identities as i', 'i.id', '=', 'u.identity_id')
            ->where('u.id', $this->userId)
            ->select([
                'u.username', 'u.email', 'u.is_locked',
                'i.identity_type', 'i.identity_key', 'i.is_active',
                'i.holding_id', 'i.department_id', 'i.division_id',
            ])
            ->first();

        if (! $row) {
            abort(404);
        }

        $this->username = (string) $row->username;
        $this->email = $row->email ? (string) $row->email : null;
        $this->is_locked = ((int) $row->is_locked) === 1;

        $this->identity_type = (string) $row->identity_type;
        $this->identity_key = (string) $row->identity_key;
        $this->is_active = ((int) $row->is_active) === 1;

        $this->holding_id = $row->holding_id ? (int) $row->holding_id : null;
        $this->department_id = $row->department_id ? (int) $row->department_id : null;
        $this->division_id = $row->division_id ? (int) $row->division_id : null;
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
            ->mapWithKeys(fn ($d) => [(string) $d->id => $d->name])->toArray();
    }

    protected function divisionOptions(): array
    {
        return DB::table('divisions')->orderBy('name')->get(['id', 'name'])
            ->mapWithKeys(fn ($d) => [(string) $d->id => $d->name])->toArray();
    }

    public function save(): void
    {
        // user update
        if (! $this->canUpdate && ! $this->canIdentityUpdate && ! $this->canLock) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Tidak punya izin.'];

            return;
        }

        $username = trim($this->username);
        if ($username === '' || mb_strlen($username) > 50) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Username wajib (maks 50).'];

            return;
        }

        if ($this->email !== null && $this->email !== '' && ! filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Format email tidak valid.'];

            return;
        }

        DB::beginTransaction();
        try {
            // uniqueness username/email
            $uExists = DB::table('auth_users')
                ->where('username', $username)
                ->where('id', '!=', $this->userId)
                ->exists();
            if ($uExists) {
                throw new \RuntimeException("Username sudah dipakai: {$username}");
            }

            if ($this->email) {
                $eExists = DB::table('auth_users')
                    ->where('email', $this->email)
                    ->where('id', '!=', $this->userId)
                    ->exists();
                if ($eExists) {
                    throw new \RuntimeException("Email sudah dipakai: {$this->email}");
                }
            }

            // get identity_id
            $identityId = DB::table('auth_users')->where('id', $this->userId)->value('identity_id');
            if (! $identityId) {
                throw new \RuntimeException('Identity link tidak valid.');
            }

            // update auth_users
            $uPayload = [];
            if ($this->canUpdate) {
                $uPayload['username'] = $username;
                $uPayload['email'] = $this->email ?: null;
            }
            if ($this->canLock) {
                $uPayload['is_locked'] = $this->is_locked ? 1 : 0;
            }
            if (! empty($uPayload)) {
                $uPayload['updated_at'] = now();
                DB::table('auth_users')->where('id', $this->userId)->update($uPayload);
            }

            // update auth_identities
            if ($this->canIdentityUpdate) {
                DB::table('auth_identities')->where('id', (int) $identityId)->update([
                    'holding_id' => $this->holding_id,
                    'department_id' => $this->department_id,
                    'division_id' => $this->division_id,
                    'is_active' => $this->is_active ? 1 : 0,
                    'updated_at' => now(),
                ]);
            }

            DB::commit();

            $this->dispatch('sso-user-updated');
            $this->dispatch('sso-user-overlay-close');
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => $e->getMessage()];
        }
    }

    public function render()
    {
        return view('livewire.auth.sso.sso-user-edit', [
            'holdingOptions' => ['' => '-'] + $this->holdingOptions(),
            'departmentOptions' => ['' => '-'] + $this->departmentOptions(),
            'divisionOptions' => ['' => '-'] + $this->divisionOptions(),
        ]);
    }
}
