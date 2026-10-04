{{--
    Restyled onto the Portfolio V2 ink/bone/accent tokens.

    Admin V2 forms use the dedicated <x-admin.field> / <x-admin.input> components
    instead. This remains so any Breeze component still rendering one keeps the
    same visual language. No public view uses Breeze components.
--}}
@props(['value'])

<label {{ $attributes->merge(['class' => 'block text-sm font-medium text-bone-200']) }}>
    {{ $value ?? $slot }}
</label>