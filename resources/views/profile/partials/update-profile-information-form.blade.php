<section>
    <header>
        <h2 class="text-lg font-semibold text-gray-900">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("Update your account's profile information with email address.") }}
        </p>
    </header>

    <form id="send-verification" method="POST" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="POST" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <!-- NIP -->
        <div>
            <x-sccr-input-label for="nip" :value="__('NIP')" />
            <x-sccr-text-input id="nip" type="text"
                class="mt-1 block w-full text-white bg-gray-100 dark:bg-gray-800" :value="Auth::user()->employee_nip" disabled />
        </div>

        <!-- Nama Karyawan -->
        <div class="mt-4">
            <x-sccr-input-label for="nama" :value="__('Nama')" />
            <x-sccr-text-input id="nama" type="text"
                class="mt-1 block w-full text-white bg-gray-100 dark:bg-gray-800" :value="Auth::user()->employees->nama ?? '-'" disabled />
        </div>

        <!-- Email -->
        <div>
            <x-sccr-input-label for="email" :value="__('Email')" />
            <x-sccr-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)"
                required autocomplete="username" />
            <x-sccr-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && !$user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification"
                            class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4">
            <x-sccr-primary-button>{{ __('Save') }}</x-sccr-primary-button>

            @if (session('status') === 'profile-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600">
                    {{ __('Saved.') }}
                </p>
            @endif
        </div>
    </form>
</section>
