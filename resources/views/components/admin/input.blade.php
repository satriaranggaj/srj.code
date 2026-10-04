@props([
    'type' => 'text',
    'invalid' => false,
])

@php
    /*
     * Explicitly restated rather than relying on @tailwindcss/forms, whose base
     * styles default to a light border and background. Matches the public
     * contact form exactly.
     */
    $classes = 'block w-full rounded-lg border bg-ink-900 px-3.5 py-2.5 text-sm text-bone-100 placeholder:text-bone-500 transition-colors duration-150 focus:border-accent-400 focus:outline-none focus:ring-1 focus:ring-accent-400 disabled:opacity-60';
    $classes .= $invalid ? ' border-red-500/60' : ' border-ink-600 hover:border-ink-500';
@endphp

<input
    type="{{ $type }}"
    {!! $attributes->merge(['class' => $classes]) !!}
>