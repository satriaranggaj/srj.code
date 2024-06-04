@props(['col' => null, 'colSm' => null, 'colMd' => null, 'colLg' => null, 'colXl' => null, 'colXxl' => null, 'gap' => null, 'gapY' => null])

@php
    $gapClasses = [
        30 => 'px-[15px]',
        24 => 'px-[12px]',
        20 => 'px-[10px]',
        16 => 'px-[8px]',
        10 => 'px-[5px]',
    ];
    
    $gapYClasses = [
        24 => 'py-[12px]',
        20 => 'py-[10px]',
        10 => 'py-[5px]',
        16 => 'py-[8px]',
        30 => 'py-[15px]',
        0 => 'py-0',
    ];
    
    $colClasses = [
        1 => 'w-1/12',
        2 => 'w-2/12',
        3 => 'w-3/12',
        4 => 'w-4/12',
        5 => 'w-5/12',
        6 => 'w-6/12',
        7 => 'w-7/12',
        8 => 'w-8/12',
        9 => 'w-9/12',
        10 => 'w-10/12',
        11 => 'w-11/12',
        12 => 'w-full',
    ];
    
    $colSmClasses = [
        1 => 'sm:w-1/12',
        2 => 'sm:w-2/12',
        3 => 'sm:w-3/12',
        4 => 'sm:w-4/12',
        5 => 'sm:w-5/12',
        6 => 'sm:w-6/12',
        7 => 'sm:w-7/12',
        8 => 'sm:w-8/12',
        9 => 'sm:w-9/12',
        10 => 'sm:w-10/12',
        11 => 'sm:w-11/12',
        12 => 'sm:w-full',
    ];
    
    $colMdClasses = [
        1 => 'md:w-1/12',
        2 => 'md:w-2/12',
        3 => 'md:w-3/12',
        4 => 'md:w-4/12',
        5 => 'md:w-5/12',
        6 => 'md:w-6/12',
        7 => 'md:w-7/12',
        8 => 'md:w-8/12',
        9 => 'md:w-9/12',
        10 => 'md:w-10/12',
        11 => 'md:w-11/12',
        12 => 'md:w-full',
    ];
    
    $colLgClasses = [
        1 => 'lg:w-1/12',
        2 => 'lg:w-2/12',
        3 => 'lg:w-3/12',
        4 => 'lg:w-4/12',
        5 => 'lg:w-5/12',
        6 => 'lg:w-6/12',
        7 => 'lg:w-7/12',
        8 => 'lg:w-8/12',
        9 => 'lg:w-9/12',
        10 => 'lg:w-10/12',
        11 => 'lg:w-11/12',
        12 => 'lg:w-full',
    ];
    
    $colXlClasses = [
        1 => 'xl:w-1/12',
        2 => 'xl:w-2/12',
        3 => 'xl:w-3/12',
        4 => 'xl:w-4/12',
        5 => 'xl:w-5/12',
        6 => 'xl:w-6/12',
        7 => 'xl:w-7/12',
        8 => 'xl:w-8/12',
        9 => 'xl:w-9/12',
        10 => 'xl:w-10/12',
        11 => 'xl:w-11/12',
        12 => 'xl:w-full',
    ];
    
    $colXxlClasses = [
        1 => 'xxl:w-1/12',
        2 => 'xxl:w-2/12',
        3 => 'xxl:w-3/12',
        4 => 'xxl:w-4/12',
        5 => 'xxl:w-5/12',
        6 => 'xxl:w-6/12',
        7 => 'xxl:w-7/12',
        8 => 'xxl:w-8/12',
        9 => 'xxl:w-9/12',
        10 => 'xxl:w-10/12',
        11 => 'xxl:w-11/12',
        12 => 'xxl:w-full',
    ];
    
    $computedAttributes = [
        'class' => implode(' ', ['shrink-0', $gapClasses[$gap] ?? $gapClasses[24], $gapYClasses[$gapY] ?? $gapYClasses[24], $colClasses[$col] ?? $colClasses[12], isset($colSm) ? $colSmClasses[$colSm] : null, isset($colMd) ? $colMdClasses[$colMd] : null, isset($colLg) ? $colLgClasses[$colLg] : null, isset($colXl) ? $colXlClasses[$colXl] : null, isset($colXxl) ? $colXxlClasses[$colXxl] : null]),
    ];
@endphp


<div {{ $attributes->merge($computedAttributes) }}>
    {{ $slot }}
</div>
