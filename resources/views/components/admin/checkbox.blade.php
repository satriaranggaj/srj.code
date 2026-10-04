@props(['label' => null, 'description' => null])

<label {{ $attributes->merge(['class' => 'flex cursor-pointer items-start gap-3']) }}>
    <input
        type="checkbox"
        {!! $attributes->only('name', 'value', 'checked', 'disabled', 'id')->merge([
            'class' => 'mt-0.5 h-4 w-4 shrink-0 rounded border-ink-500 bg-ink-900 text-accent-400 focus:ring-accent-400 focus:ring-offset-0',
        ]) !!}
    >

    @if ($label || $description || ! $slot->isEmpty())
        <span class="min-w-0">
            @if ($label)
                <span class="block text-sm font-medium text-bone-200">{{ $label }}</span>
            @endif

            @if ($description)
                <span class="mt-0.5 block text-xs leading-relaxed text-bone-500">{{ $description }}</span>
            @endif

            {{ $slot }}
        </span>
    @endif
</label>