@props(['name', 'label', 'value' => null, 'disabled' => false])

<div>
    {{-- <label for="{{ $name }}" class="block text-primary">{{ $label }}</label> --}}
    <input type="date" name="{{ $name }}" id="{{ $name }}" value="{{ $value }}"
        class="mt-1 block w-full bg-zinc-800 border-2 placeholder:text-zinc-500 border-zinc-600 focus:border-primary-300 focus:ring-primary-300 rounded-md shadow-sm text-white"
        @if ($disabled) disabled @endif mode="dark">
</div>
