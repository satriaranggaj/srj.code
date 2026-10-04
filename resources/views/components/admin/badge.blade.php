@props([
    'tone' => 'neutral',
    'label' => null,
])

@php
    $tones = [
        'neutral' => 'border-ink-600 bg-ink-800 text-bone-300',
        'accent' => 'border-accent-400/40 bg-accent-400/10 text-accent-300',
        'success' => 'border-emerald-500/40 bg-emerald-500/10 text-emerald-300',
        'warning' => 'border-amber-500/40 bg-amber-500/10 text-amber-300',
        'danger' => 'border-red-500/40 bg-red-500/10 text-red-300',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-medium '.($tones[$tone] ?? $tones['neutral'])]) }}>
    {{ $label ?? $slot }}
</span>