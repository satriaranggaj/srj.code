{{-- Restyled onto the Portfolio V2 tokens. --}}
<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center justify-center gap-2 rounded-lg bg-red-500/90 px-4 py-2 text-sm font-medium text-white transition-colors duration-150 hover:bg-red-500 focus:outline-none disabled:cursor-not-allowed disabled:opacity-60']) }}>
    {{ $slot }}
</button>