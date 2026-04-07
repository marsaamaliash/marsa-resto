<?php

namespace App\Livewire\Auth;

use App\Services\AuthService;
use Livewire\Component;

class LoginForm extends Component
{
    public $login = '';

    public $password = '';

    public $remember = false;

    public function authenticate(AuthService $auth)
    {
        $raw = trim((string) $this->password);
        $isDefaultInput = stripos($raw, 'password123') !== false;

        try {
            $auth->login($this->login, $this->password, (bool) $this->remember);

            $u = auth()->user();
            if (! $u) {
                throw new \RuntimeException('Login gagal. Silakan coba lagi.');
            }

            // kalau user mengetik default → force change tanpa password lama
            if ($isDefaultInput) {
                session(['pw.force_without_old' => 1]);

                return redirect()->route('sso.password.change');
            }

            // kalau flag must_change_password → force change normal (pakai password lama)
            if ((int) ($u->must_change_password ?? 0) === 1) {
                session()->forget('pw.force_without_old');

                return redirect()->route('sso.password.change');
            }

            session()->forget('pw.force_without_old');

            return redirect()->route('dashboard');

        } catch (\Throwable $e) {
            $msg = trim((string) $e->getMessage());
            if ($msg === '' || str_contains($msg, 'Call to undefined method') || str_contains($msg, 'SQLSTATE')) {
                $msg = 'Login gagal. Periksa NIP/Email dan password.';
            }
            $this->addError('login', $msg);
        }
    }

    public function render()
    {
        return view('livewire.auth.login-form')
            ->layout('components.ui.sccr-auth-layout');
    }
}
