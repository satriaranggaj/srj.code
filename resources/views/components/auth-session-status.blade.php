{{-- Restyled onto the Portfolio V2 tokens. --}}
@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'text-sm font-medium text-accent-400']) }}>
        {{ $status }}
    </div>
@endif