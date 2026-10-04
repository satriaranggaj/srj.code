{{-- Restyled onto the Portfolio V2 tokens. --}}
<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center justify-center gap-2 rounded-lg border border-ink-600 bg-ink-850 px-4 py-2 text-sm font-medium text-bone-100 transition-colors duration-150 hover:border-ink-500 hover:bg-ink-800 focus:outline-none']) }}>
    {{ $slot }}
</button>