@props(['invalid' => false, 'rows' => 5])

@php
    $classes = 'block w-full rounded-lg border bg-ink-900 px-3.5 py-2.5 text-sm leading-relaxed text-bone-100 placeholder:text-bone-500 transition-colors duration-150 focus:border-accent-400 focus:outline-none focus:ring-1 focus:ring-accent-400';
    $classes .= $invalid ? ' border-red-500/60' : ' border-ink-600 hover:border-ink-500';
@endphp

<textarea
    rows="{{ $rows }}"
    {!! $attributes->merge(['class' => $classes]) !!}
>{{ $slot }}</textarea>