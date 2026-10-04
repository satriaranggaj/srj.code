@props(['disabled' => false])

{{--
    Restyled onto the Portfolio V2 tokens. Colours are restated explicitly rather
    than inherited from @tailwindcss/forms, whose base styles default to a light
    border and background.
--}}
<input {!! $attributes->merge([
    'class' => 'rounded-lg border border-ink-600 bg-ink-900 px-3.5 py-2.5 text-sm text-bone-100 placeholder:text-bone-500 transition-colors duration-150 focus:border-accent-400 focus:outline-none focus:ring-1 focus:ring-accent-400 disabled:opacity-60',
]) !!} @disabled($disabled)>