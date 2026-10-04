@props([
    'for' => null,
    'label' => null,
    'help' => null,
    'required' => false,
])

<div {{ $attributes->merge(['class' => 'space-y-2']) }}>
    @if ($label)
        <label @if ($for) for="{{ $for }}" @endif class="block text-sm font-medium text-bone-200">
            {{ $label }}

            @if ($required)
                <span class="text-accent-400" aria-hidden="true">*</span>
                <span class="sr-only">(required)</span>
            @endif
        </label>
    @endif

    {{ $slot }}

    @if ($help)
        <p class="text-xs leading-relaxed text-bone-500">{{ $help }}</p>
    @endif
</div>