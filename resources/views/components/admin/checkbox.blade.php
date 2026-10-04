@props(['label' => null, 'description' => null])

@php
    /*
     * Attributes must be split explicitly between the label and the input.
     *
     * ComponentAttributeBag::only() accepts a single argument in Laravel 10 - passing
     * several positional arguments silently keeps only the first one, which previously
     * dropped `value` and `checked`. A checked checkbox then submitted the HTML default
     * "on" and failed boolean validation, and an already-featured project rendered as
     * unchecked so saving it silently cleared the flag.
     *
     * `only()` and `except()` therefore both receive an array, never loose arguments.
     */
    $controlAttributes = ['name', 'value', 'checked', 'disabled', 'readonly', 'required', 'id', 'tabindex', 'form', 'aria-describedby'];
@endphp

<label {{ $attributes->except($controlAttributes)->merge(['class' => 'flex cursor-pointer items-start gap-3']) }}>
    <input
        type="checkbox"
        {!! $attributes->only($controlAttributes)->merge([
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