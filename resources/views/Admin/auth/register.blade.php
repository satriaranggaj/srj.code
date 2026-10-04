{{--
    Registration is closed by default (config('portfolio.allow_registration')).
    This view is retained and styled consistently so it can be re-enabled safely
    with ALLOW_REGISTRATION=true, but it is unreachable while that flag is false
    because RegisteredUserController returns 404.
--}}
<x-guest-layout title="Register">
    <div class="mb-8">
        <h1 class="text-2xl font-semibold tracking-tightest text-bone-50">
            Create an account
        </h1>

        <p class="mt-2 text-sm leading-relaxed text-bone-400">
            Only use this in development. Self-registered accounts are never granted
            administrator access.
        </p>
    </div>

    <x-admin.alert type="warning" class="mb-6" title="Development only">
        Production administrator accounts should be created with
        <code class="font-mono">php artisan admin:create</code>, not through this form.
    </x-admin.alert>

    <form method="POST" action="{{ route('register') }}" class="space-y-5">
        @csrf

        <x-admin.field for="name" label="Name" required>
            <x-admin.input
                id="name"
                name="name"
                type="text"
                :value="old('name')"
                :invalid="$errors->has('name')"
                autocomplete="name"
                required
                autofocus
            />

            @error('name')
                <p class="text-xs text-red-300">{{ $message }}</p>
            @enderror
        </x-admin.field>

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
            />

            @error('email')
                <p class="text-xs text-red-300">{{ $message }}</p>
            @enderror
        </x-admin.field>

        <x-admin.field for="password" label="Password" required help="Use at least 12 characters.">
            <x-admin.input
                id="password"
                name="password"
                type="password"
                :invalid="$errors->has('password')"
                autocomplete="new-password"
                required
            />

            @error('password')
                <p class="text-xs text-red-300">{{ $message }}</p>
            @enderror
        </x-admin.field>

        <x-admin.field for="password_confirmation" label="Confirm password" required>
            <x-admin.input
                id="password_confirmation"
                name="password_confirmation"
                type="password"
                autocomplete="new-password"
                required
            />

            @error('password_confirmation')
                <p class="text-xs text-red-300">{{ $message }}</p>
            @enderror
        </x-admin.field>

        <div class="flex flex-wrap items-center justify-between gap-3 pt-1">
            <a href="{{ route('login') }}" class="rounded text-sm text-bone-400 transition-colors duration-150 hover:text-accent-400">
                Already registered?
            </a>
        </div>

        <x-admin.button type="submit" size="lg" class="w-full">
            Register
        </x-admin.button>
    </form>
</x-guest-layout>