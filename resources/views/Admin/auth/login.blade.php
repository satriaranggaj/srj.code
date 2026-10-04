@props(['title' => null])

<x-guest-layout title="{{ $title ?? 'Sign in' }}">
    <div class="mb-8">
        <h1 class="text-2xl font-semibold tracking-tightest text-bone-50">
            Welcome back
        </h1>

        <p class="mt-2 text-sm leading-relaxed text-bone-400">
            Sign in to manage the {{ config('portfolio.short_name') }} Portfolio.
        </p>
    </div>

    {{-- Session status, e.g. after a password reset. --}}
    @if (session('status'))
        <x-admin.alert type="success" class="mb-6">
            {{ session('status') }}
        </x-admin.alert>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <x-admin.field for="email" label="Email" required>
            <x-admin.input
                id="email"
                name="email"
                type="email"
                :value="old('email')"
                :invalid="$errors->has('email')"
                autocomplete="username"
                inputmode="email"
                required
                autofocus
                placeholder="you@example.com"
            />

            @error('email')
                <p class="text-xs text-red-300">{{ $message }}</p>
            @enderror
        </x-admin.field>

        <x-admin.field for="password" label="Password" required>
            {{--
                Password visibility toggle. Alpine only, no dependency.
                The button is a real <button type="button"> so it is keyboard
                reachable and never submits the form.
            --}}
            <div x-data="{ show: false }" class="relative">
                <x-admin.input
                    id="password"
                    name="password"
                    x-bind:type="show ? 'text' : 'password'"
                    :invalid="$errors->has('password')"
                    autocomplete="current-password"
                    required
                    placeholder="••••••••••••"
                    class="pr-11"
                />

                <button
                    type="button"
                    x-on:click="show = ! show"
                    x-bind:aria-label="show ? 'Hide password' : 'Show password'"
                    class="absolute right-1 top-1/2 -translate-y-1/2 rounded-md p-2 text-bone-500 transition-colors duration-150 hover:bg-ink-800 hover:text-bone-200"
                >
                    <x-portfolio.icon name="eye" class="h-4 w-4" x-show="! show" />
                    <x-portfolio.icon name="eye-off" class="hidden h-4 w-4" x-show="show" x-cloak />
                </button>
            </div>

            @error('password')
                <p class="text-xs text-red-300">{{ $message }}</p>
            @enderror
        </x-admin.field>

        <div class="flex flex-wrap items-center justify-between gap-3 pt-1">
            <x-admin.checkbox name="remember" id="remember_me" label="Remember me" value="1" />

            @if (Route::has('password.request'))
                <a
                    href="{{ route('password.request') }}"
                    class="rounded text-sm text-bone-400 transition-colors duration-150 hover:text-accent-400"
                >
                    Forgot password?
                </a>
            @endif
        </div>

        <x-admin.button type="submit" size="lg" class="w-full">
            Sign in
        </x-admin.button>
    </form>
</x-guest-layout>