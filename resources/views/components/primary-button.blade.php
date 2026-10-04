{{-- Restyled onto the Portfolio V2 tokens. --}}
@props(['as' => 'button', 'type' => 'submit'])

@php
    $classes = 'inline-flex items-center justify-center gap-2 rounded-lg bg-accent-400 px-4 py-2 text-sm font-medium text-ink-950 transition-colors duration-150 hover:bg-accent-300 disabled:cursor-not-allowed disabled:opacity-60 focus:outline-none focus-visible:ring-2 focus-visible:ring-accent-400 focus-visible:ring-offset-2 focus-visible:ring-offset-ink-950';
@endphp

@if ($as == 'button')
    <button {{ $attributes->merge(['type' => $type, 'class' => $classes]) }}>
        {{ $slot }}
    </button>
@else
    <a {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@endif