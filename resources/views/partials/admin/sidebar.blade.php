@php
    /*
     * Admin navigation definition.
     *
     * Adding an entry here is the only change needed to extend the sidebar.
     * Badge counts come from the dashboard payload where available.
     */
    $unread = $unreadMessages ?? null;

    $main = [
        ['route' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'layers', 'patterns' => ['dashboard']],
        ['route' => 'project.index', 'label' => 'Projects', 'icon' => 'briefcase', 'patterns' => ['project.index', 'project.create', 'project.edit', 'project.store', 'project.update', 'project.destroy']],
        ['route' => 'skill.index', 'label' => 'Technologies', 'icon' => 'sparkles', 'patterns' => ['skill.*']],
        ['route' => 'certificate.index', 'label' => 'Certificates', 'icon' => 'award', 'patterns' => ['certificate.*']],
        ['route' => 'message.index', 'label' => 'Messages', 'icon' => 'mail', 'patterns' => ['message.*'], 'badge' => $unread],
    ];

    $account = [
        ['route' => 'profile.edit', 'label' => 'Profile', 'icon' => 'terminal', 'patterns' => ['profile.*']],
    ];
@endphp

<nav class="flex h-full flex-col" aria-label="Admin">
    {{-- Branding --}}
    <div class="flex h-16 shrink-0 items-center gap-2 border-b border-ink-700/70 px-5">
        <a href="{{ route('dashboard') }}" class="flex items-baseline gap-0.5 rounded text-base font-semibold tracking-tightest text-bone-50">
            <span>{{ config('portfolio.short_name') }}</span>
            <span class="text-accent-400" aria-hidden="true">.</span>
            <span class="sr-only">— Portfolio administration</span>
        </a>
    </div>

    <div class="flex-1 overflow-y-auto px-3 py-5">
        <p class="px-3 pb-2 font-mono text-[0.7rem] uppercase tracking-[0.16em] text-bone-400">
            Manage
        </p>

        <ul class="space-y-1">
            @foreach ($main as $item)
                <li>
                    <x-admin.sidebar-link
                        :href="route($item['route'])"
                        :label="$item['label']"
                        :icon="$item['icon']"
                        :active="request()->routeIs(...$item['patterns'])"
                        :badge="$item['badge'] ?? null"
                    />
                </li>
            @endforeach
        </ul>

        <p class="px-3 pb-2 pt-6 font-mono text-[0.7rem] uppercase tracking-[0.16em] text-bone-400">
            Account
        </p>

        <ul class="space-y-1">
            @foreach ($account as $item)
                <li>
                    <x-admin.sidebar-link
                        :href="route($item['route'])"
                        :label="$item['label']"
                        :icon="$item['icon']"
                        :active="request()->routeIs(...$item['patterns'])"
                    />
                </li>
            @endforeach

            <li>
                <a
                    href="{{ route('home') }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-bone-400 transition-colors duration-150 hover:bg-ink-850 hover:text-bone-100"
                >
                    <x-portfolio.icon name="external" class="h-4 w-4 shrink-0 text-bone-500 transition-colors group-hover:text-bone-300" />
                    <span class="flex-1">View site</span>
                    <span class="sr-only">(opens in a new tab)</span>
                </a>
            </li>
        </ul>
    </div>

    {{-- Signed-in identity and logout --}}
    <div class="shrink-0 border-t border-ink-700/70 p-3">
        <div class="flex items-center gap-3 rounded-lg px-3 py-2">
            <span
                class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-md border border-ink-600 bg-ink-800 font-mono text-xs font-medium text-accent-400"
                aria-hidden="true"
            >{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr(auth()->user()->name, 0, 2)) }}</span>

            <span class="min-w-0 flex-1">
                <span class="block truncate text-sm font-medium text-bone-100">{{ auth()->user()->name }}</span>
                <span class="block truncate font-mono text-[0.7rem] text-bone-500">{{ auth()->user()->email }}</span>
            </span>
        </div>

        <form method="POST" action="{{ route('logout') }}" class="mt-1">
            @csrf
            <button
                type="submit"
                class="group flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-bone-400 transition-colors duration-150 hover:bg-red-500/10 hover:text-red-300"
            >
                <x-portfolio.icon name="close" class="h-4 w-4 shrink-0" />
                <span>Log out</span>
            </button>
        </form>
    </div>
</nav>
