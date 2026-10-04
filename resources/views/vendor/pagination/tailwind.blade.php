{{--
    Admin V2 pagination.

    Based on Laravel's default Tailwind paginator, restyled onto the Portfolio V2
    ink/bone/accent tokens. The published framework views for other paginator
    styles were removed because the admin only ever uses Tailwind.
--}}
@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex items-center justify-between gap-4">
        <p class="hidden text-sm text-bone-500 sm:block">
            {!! __('Showing') !!}
            <span class="font-mono text-bone-300">{{ $paginator->firstItem() }}</span>
            {!! __('to') !!}
            <span class="font-mono text-bone-300">{{ $paginator->lastItem() }}</span>
            {!! __('of') !!}
            <span class="font-mono text-bone-300">{{ $paginator->total() }}</span>
        </p>

        <div class="flex items-center gap-1">
            @if ($paginator->onFirstPage())
                <span class="cursor-not-allowed rounded-lg border border-ink-700 bg-ink-850/40 px-3 py-1.5 text-sm text-bone-400" aria-disabled="true">
                    {!! __('Previous') !!}
                </span>
            @else
                <a
                    href="{{ $paginator->previousPageUrl() }}"
                    rel="prev"
                    class="rounded-lg border border-ink-600 bg-ink-850 px-3 py-1.5 text-sm text-bone-300 transition-colors duration-150 hover:border-ink-500 hover:bg-ink-800 hover:text-bone-50"
                >
                    {!! __('Previous') !!}
                </a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="rounded-lg border border-ink-700 bg-ink-850/40 px-3.5 py-1.5 text-sm text-bone-400">
                        {{ $element }}
                    </span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span
                                aria-current="page"
                                class="rounded-lg border border-accent-400/40 bg-accent-400/10 px-3.5 py-1.5 text-sm font-medium text-accent-300"
                            >{{ $page }}</span>
                        @else
                            <a
                                href="{{ $url }}"
                                class="rounded-lg border border-ink-600 bg-ink-850 px-3.5 py-1.5 text-sm text-bone-300 transition-colors duration-150 hover:border-ink-500 hover:bg-ink-800 hover:text-bone-50"
                            >{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a
                    href="{{ $paginator->nextPageUrl() }}"
                    rel="next"
                    class="rounded-lg border border-ink-600 bg-ink-850 px-3 py-1.5 text-sm text-bone-300 transition-colors duration-150 hover:border-ink-500 hover:bg-ink-800 hover:text-bone-50"
                >
                    {!! __('Next') !!}
                </a>
            @else
                <span class="cursor-not-allowed rounded-lg border border-ink-700 bg-ink-850/40 px-3 py-1.5 text-sm text-bone-400" aria-disabled="true">
                    {!! __('Next') !!}
                </span>
            @endif
        </div>
    </nav>
@endif