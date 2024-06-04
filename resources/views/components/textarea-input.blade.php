@props(['disabled' => false])

<textarea {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge([
    'class' =>
        'bg-zinc-800 border-2 border-zinc-600 placeholder:text-zinc-500 focus:border-primary-300 focus:ring-primary-300 rounded-md shadow-sm text-white',
]) !!}>{{ $slot }}</textarea>
