@props([
    'options' => [],
    'selected' => null,
    'placeholder' => null,
    'invalid' => false,
])

@php
    $classes = 'block w-full appearance-none rounded-lg border bg-ink-900 px-3.5 py-2.5 text-sm text-bone-100 transition-colors duration-150 focus:border-accent-400 focus:outline-none focus:ring-1 focus:ring-accent-400';
    $classes .= $invalid ? ' border-red-500/60' : ' border-ink-600 hover:border-ink-500';
@endphp

{{-- Chevron is a background image so the control needs no extra DOM node. --}}
<select
    {{ $attributes->merge([
        'class' => $classes,
        'style' => 'background-image:url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'%238d8a82\' stroke-width=\'2\' stroke-linecap=\'round\'%3E%3Cpath d=\'m6 9 6 6 6-6\'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 0.75rem center;background-size:1rem;padding-right:2.5rem;',
    ]) }}
>
    @if ($placeholder)
        <option value="">{{ $placeholder }}</option>
    @endif

    @foreach ($options as $value => $label)
        <option value="{{ $value }}" @selected((string) $selected === (string) $value)>{{ $label }}</option>
    @endforeach

    {{ $slot }}
</select>