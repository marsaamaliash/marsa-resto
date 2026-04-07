<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SCCR SSO</title>
    @vite('resources/css/app.css')
</head>

<body class="bg-gray-100 flex items-center justify-center min-h-screen">

    <div class="w-full max-w-md bg-white p-8 rounded-2xl shadow-lg">
        <!-- Logo + Nama Perusahaan -->
        <div class="flex flex-col items-center mb-6">
            <img src="{{ asset('images/logoSCCR.png') }}" alt="Logo SCCR" class="h-24 w-auto mb-3">
            {{-- <h1 class="text-xl font-bold text-gray-700">SCCR SSO</h1> --}}
        </div>

        <h2 class="text-2xl font-semibold text-center text-gray-800 mb-6">Login</h2>

        <!-- Error Handling -->
        @if ($errors->any())
            <div class="mb-4 p-3 bg-red-100 border border-red-300 text-red-700 rounded-lg text-sm">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Form Login -->
        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf

            <!-- NIK / Email -->
            <div>
                <label for="login" class="block text-sm font-medium text-gray-700">NIP atau Email</label>
                <input id="login" name="login" type="text" value="{{ old('login') }}" required autofocus
                    class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            </div>

            <!-- Password -->
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                <input id="password" name="password" type="password" required
                    class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            </div>

            <!-- Remember Me -->
            <div class="flex items-center">
                <input id="remember_me" type="checkbox" name="remember"
                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                <label for="remember_me" class="ml-2 text-sm text-gray-600">Ingat saya</label>
            </div>

            <!-- Button -->
            <div>
                <button type="submit"
                    class="w-full px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                    Login
                </button>
            </div>
        </form>
    </div>
</body>

</html>
