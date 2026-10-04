{{-- Restyled onto the Portfolio V2 tokens. --}}
@props(['active'])

@php
    $classes = ($active ?? false)
        ? 'inline-flex items-center px-1 pt-1 border-b-2 border-accent-400 text-sm font-medium leading-5 text-bone-50 transition-colors duration-150'
        : 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-bone-400 hover:text-bone-100 hover:border-ink-500 transition-colors duration-150';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>