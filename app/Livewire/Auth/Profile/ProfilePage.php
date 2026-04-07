<?php

namespace App\Livewire\Auth\Profile;

use App\Models\Auth\AuthUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class ProfilePage extends Component
{
    public array $breadcrumbs = [];

    public array $toast = ['show' => false, 'type' => 'success', 'message' => ''];

    public string $username = '';

    public string $email = '';

    public string $current_password = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function mount(): void
    {
        /** @var AuthUser|null $u */
        $u = auth()->user();
        abort_unless($u, 401);

        $this->breadcrumbs = [
            ['label' => 'Main Dashboard', 'route' => 'dashboard', 'color' => 'text-white'],
            ['label' => 'Profile', 'color' => 'text-white font-semibold'],
        ];

        $this->username = (string) ($u->username ?? '');
        $this->email = (string) ($u->email ?? '');
    }

    private function toast(string $type, string $message): void
    {
        $this->toast = ['show' => true, 'type' => $type, 'message' => $message];
    }

    public function saveProfile(): void
    {
        /** @var AuthUser|null $u */
        $u = auth()->user();
        abort_unless($u, 401);

        $this->resetErrorBag();

        $email = trim($this->email);

        if ($email !== '' && ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->addError('email', 'Format email tidak valid.');
            $this->toast('warning', 'Format email tidak valid.');

            return;
        }

        if ($email !== '') {
            $exists = DB::table('auth_users')
                ->where('email', $email)
                ->where('id', '<>', (int) $u->id)
                ->exists();

            if ($exists) {
                $this->addError('email', 'Email sudah digunakan user lain.');
                $this->toast('warning', 'Email sudah digunakan user lain.');

                return;
            }
        }

        DB::table('auth_users')->where('id', (int) $u->id)->update([
            'email' => ($email === '' ? null : $email),
            'updated_at' => now(),
        ]);

        $u->refresh();
        $this->email = (string) ($u->email ?? '');

        $this->toast('success', 'Profile berhasil diperbarui.');
    }

    private function containsForbiddenSequence(string $plain): bool
    {
        return preg_match('/password123/i', $plain) === 1;
    }

    /** ✅ tombol Batal: reset form lalu kembali dashboard */
    public function cancelPassword()
    {
        $this->resetErrorBag();
        $this->reset(['current_password', 'password', 'password_confirmation']);

        // Livewire v3 + navigate mode
        return $this->redirectRoute('dashboard', navigate: true);
    }

    /** ✅ ganti password mandiri: sukses -> langsung dashboard */
    public function changePassword()
    {
        /** @var AuthUser|null $u */
        $u = auth()->user();
        abort_unless($u, 401);

        $this->resetErrorBag();

        $current = (string) $this->current_password;
        $new = (string) $this->password;
        $confirm = (string) $this->password_confirmation;

        if (trim($current) === '') {
            $this->addError('current_password', 'Password lama wajib diisi.');
            $this->toast('warning', 'Password lama wajib diisi.');

            return;
        }

        if (! Hash::check($current, (string) $u->password)) {
            $this->addError('current_password', 'Password lama tidak sesuai.');
            $this->toast('warning', 'Password lama tidak sesuai.');

            return;
        }

        if (trim($new) === '') {
            $this->addError('password', 'Password baru wajib diisi.');
            $this->toast('warning', 'Password baru wajib diisi.');

            return;
        }

        if (mb_strlen($new) < 8) {
            $this->addError('password', 'Password baru minimal 8 karakter.');
            $this->toast('warning', 'Password baru minimal 8 karakter.');

            return;
        }

        if ($confirm !== $new) {
            $this->addError('password_confirmation', 'Konfirmasi password tidak sama.');
            $this->toast('warning', 'Konfirmasi password tidak sama.');

            return;
        }

        if ($this->containsForbiddenSequence($new)) {
            $this->addError('password', 'Password baru tidak boleh mengandung "password123" (variasi huruf besar/kecil apapun).');
            $this->toast('warning', 'Password baru mengandung "password123".');

            return;
        }

        if (Hash::check($new, (string) $u->password)) {
            $this->addError('password', 'Password baru tidak boleh sama dengan password lama.');
            $this->toast('warning', 'Password baru tidak boleh sama dengan password lama.');

            return;
        }

        DB::transaction(function () use ($u, $new) {
            // lock user row (anti race)
            DB::table('auth_users')->where('id', (int) $u->id)->lockForUpdate()->first();

            DB::table('auth_users')->where('id', (int) $u->id)->update([
                'password' => Hash::make($new),
                'must_change_password' => 0,
                'password_changed_at' => now(),
                'remember_token' => null,
                'updated_at' => now(),
            ]);

            // revoke sanctum tokens (kalau kamu pakai)
            DB::table('auth_personal_access_tokens')
                ->where('tokenable_type', AuthUser::class)
                ->where('tokenable_id', (int) $u->id)
                ->delete();
        });

        // clear auth cache
        if (method_exists($u, 'clearAuthCache')) {
            $u->clearAuthCache();
        }

        // bersihkan input biar state rapi
        $this->resetErrorBag();
        $this->reset(['current_password', 'password', 'password_confirmation']);

        // kalau kamu mau toast tampil di dashboard, simpan di session flash:
        session()->flash('toast', ['type' => 'success', 'message' => 'Password berhasil diganti.']);

        // redirect ke dashboard (SPA-safe)
        return $this->redirectRoute('dashboard', navigate: true);
    }

    public function render()
    {
        return view('livewire.auth.profile.profile-page', [
            'breadcrumbs' => $this->breadcrumbs,
            'username' => $this->username,
        ])->layout('components.sccr-layout');
    }
}
