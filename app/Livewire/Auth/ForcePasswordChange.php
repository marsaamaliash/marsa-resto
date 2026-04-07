<?php

namespace App\Livewire\Auth;

use App\Models\Auth\AuthUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class ForcePasswordChange extends Component
{
    public string $current_password = '';

    public string $password = '';

    public string $password_confirmation = '';

    /** true => password lama tidak diperlukan (forced default password) */
    public bool $withoutOld = false;

    private function isDefaultPasswordHash(?string $hashed): bool
    {
        $hashed = (string) $hashed;

        return Hash::check('password123', $hashed)
            || Hash::check('Password123', $hashed)
            || Hash::check('PASSWORD123', $hashed);
    }

    /**
     * Authoritative forced mode:
     * hanya jika must_change_password=1 dan password masih default.
     */
    private function isForced(AuthUser $u): bool
    {
        if ((int) ($u->must_change_password ?? 0) !== 1) {
            return false;
        }

        return $this->isDefaultPasswordHash($u->password);
    }

    public function mount(): void
    {
        /** @var AuthUser|null $u */
        $u = auth()->user();
        abort_unless($u, 401);

        $forced = $this->isForced($u);

        // forced => disable old
        $this->withoutOld = $forced;

        if ($forced) {
            session(['pw.force_without_old' => 1]);
            // intended diset oleh middleware saat redirect dari halaman lain
        } else {
            // mandiri => bersihkan semua flag forced agar tidak nyangkut
            session()->forget('pw.force_without_old');
            session()->forget('pw.intended');
        }
    }

    public function save()
    {
        /** @var AuthUser|null $actor */
        $actor = auth()->user();
        abort_unless($actor, 401);

        // ✅ re-check authoritative sebelum validasi (anti session nyasar)
        $forced = $this->isForced($actor);
        $this->withoutOld = $forced;

        $rules = [
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];

        if (! $forced) {
            $rules['current_password'] = ['required', 'string'];
        }

        $this->validate($rules, [], [
            'current_password' => 'Password Lama',
            'password' => 'Password Baru',
            'password_confirmation' => 'Konfirmasi Password Baru',
        ]);

        // policy: jangan boleh mengandung password123
        if (stripos($this->password, 'password123') !== false) {
            $this->addError('password', 'Password baru tidak boleh mengandung "password123".');

            return;
        }

        if (! $forced) {
            if (! Hash::check($this->current_password, (string) $actor->password)) {
                $this->addError('current_password', 'Password lama tidak sesuai.');

                return;
            }
        }

        DB::transaction(function () use ($actor) {
            /** @var AuthUser $u */
            $u = AuthUser::query()
                ->whereKey((int) $actor->id)
                ->lockForUpdate()
                ->firstOrFail();

            $u->password = Hash::make($this->password);
            $u->must_change_password = 0;
            $u->password_changed_at = now();
            $u->save();

            // refresh auth user agar state session tidak stale
            Auth::setUser($u->fresh());

            if (method_exists($u, 'clearAuthCache')) {
                $u->clearAuthCache();
            }
        });

        // selesai: bersihkan flag forced
        session()->forget('pw.force_without_old');

        // ✅ target redirect:
        // - forced => ke pw.intended kalau valid & bukan halaman change-password
        // - mandiri => selalu ke dashboard
        if ($forced) {
            $intended = session()->pull('pw.intended');

            if (is_string($intended) && $intended !== '' && ! str_contains($intended, '/sso/change-password')) {
                session()->regenerate();

                return $this->redirect($intended, navigate: true);
            }

            session()->regenerate();

            return $this->redirectRoute('dashboard', navigate: true);
        }

        // mandiri
        session()->forget('pw.intended');
        session()->regenerate();

        return $this->redirectRoute('dashboard', navigate: true);
    }

    public function cancel()
    {
        session()->forget('pw.force_without_old');
        session()->forget('pw.intended');

        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();

        return redirect()->route('login');
    }

    public function render()
    {
        return view('livewire.auth.force-password-change')
            ->layout('components.ui.sccr-auth-layout');
    }
}
