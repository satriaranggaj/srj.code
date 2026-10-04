@props([
    'label' => null,
    'title',
    'description' => null,
    'align' => 'left',
    'id' => null,
])

@php
    $alignmentClasses = $align === 'center' ? 'text-center items-center mx-auto' : 'text-left';
@endphp

<div {{ $attributes->merge(['class' => 'max-w-2xl '.$alignmentClasses]) }}>
    @if ($label)
        <p class="mb-3 font-mono text-xs uppercase tracking-[0.18em] text-accent-400">
            {{ $label }}
        </p>
    @endif

    <h2 {{ $id ? 'id="'.$id.'"' : '' }} class="text-2xl font-semibold tracking-tightest sm:text-3xl">
        {{ $title }}
    </h2>

    @if ($description)
        <p class="mt-3 text-base leading-relaxed text-bone-400 sm:text-lg">
            {{ $description }}
        </p>
    @endif
</div>