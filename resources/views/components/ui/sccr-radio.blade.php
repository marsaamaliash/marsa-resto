@props([
    'name',
    'options' => [], // ['L' => 'Laki-laki', 'P' => 'Perempuan']
    'label' => null,
])

<div class="mb-4">
    @if ($label)
        <p class="text-sm font-medium text-gray-700 mb-2">{{ $label }}</p>
    @endif

    <div class="flex gap-4">
        @foreach ($options as $value => $text)
            <label class="flex items-center space-x-2 text-sm text-gray-700">
                <input type="radio" value="{{ $value }}" name="{{ $name }}"
                    {{ $attributes->merge(['class' => 'text-blue-600 focus:ring-blue-500']) }} />
                <span>{{ $text }}</span>
            </label>
        @endforeach
    </div>

    @error($name)
        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
    @enderror
</div>
