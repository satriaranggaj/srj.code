@props([
    'type' => 'info',
    'title' => null,
])

@php
    $tones = [
        'success' => ['border-emerald-500/30 bg-emerald-500/10 text-emerald-200', 'check'],
        'error' => ['border-red-500/30 bg-red-500/10 text-red-200', 'alert'],
        'warning' => ['border-amber-500/30 bg-amber-500/10 text-amber-200', 'alert'],
        'info' => ['border-ink-600 bg-ink-850/70 text-bone-200', 'award'],
    ];

    [$classes, $icon] = $tones[$type] ?? $tones['info'];
@endphp

<div
    @if ($type === 'error') role="alert" @else role="status" @endif
    {{ $attributes->merge(['class' => 'flex items-start gap-3 rounded-xl border p-4 text-sm '.$classes]) }}
>
    <x-portfolio.icon :name="$icon" class="mt-0.5 h-4 w-4 shrink-0" />

    <div class="min-w-0 flex-1 leading-relaxed">
        @if ($title)
            <p class="font-medium">{{ $title }}</p>
        @endif

        <div>{{ $slot }}</div>
    </div>
</div>