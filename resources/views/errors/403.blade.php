<x-sccr-layout>
    <x-slot name="header">
        <h1 class="text-2xl text-red-600">🔑 Access Denied ⚠️</h1>
    </x-slot>
    <div class="flex flex-col items-center justify-center h-[60vh] text-center">
        <h1 class="text-6xl font-bold text-red-600">🔒 FORBIDDEN</h1>
        <p class="mt-4 text-lg">
            You do not have access rights in this module.
        </p>

        <a href="{{ route('dashboard') }}" class="mt-6 px-6 py-2 bg-emerald-600 text-white rounded-lg">
            👉 Back to Dashboard
        </a>
    </div>
</x-sccr-layout>
