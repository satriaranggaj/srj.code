{{-- Restyled onto the Portfolio V2 tokens. --}}
@props(['as' => 'button'])

@php
    $classes = 'inline-flex items-center justify-center gap-2 rounded-lg px-4 py-2 text-sm font-medium text-bone-400 transition-colors duration-150 hover:bg-ink-850 hover:text-bone-100 focus:outline-none disabled:opacity-25';
@endphp

@if ($as == 'button')
    <button {{ $attributes->merge(['type' => 'button', 'class' => $classes]) }}>
        {{ $slot }}
    </button>
@else
    <a {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@endif