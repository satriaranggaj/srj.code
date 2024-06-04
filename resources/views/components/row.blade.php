@props(['gap' => null, 'gapY' => null])

@php
    $gapClasses = [
        30 => 'mx-[-15px]',
        24 => 'mx-[-12px]',
        20 => 'mx-[-10px]',
        10 => 'mx-[-5px]',
        16 => 'mx-[-8px]',
    ];
    
    $gapYClasses = [
        24 => 'my-[-12px]',
        16 => 'my-[-8px]',
        20 => 'my-[-10px]',
        10 => 'my-[-5px]',
        30 => 'my-[-15px]',
        0 => 'my-0',
    ];
    
    $computedAttributes = [
        'class' => implode(' ', ['flex', 'flex-wrap', $gapClasses[$gap] ?? $gapClasses[24], $gapYClasses[$gapY] ?? $gapYClasses[24]]),
    ];
@endphp

<div {{ $attributes->merge($computedAttributes) }}>
    {{ $slot }}
</div>
