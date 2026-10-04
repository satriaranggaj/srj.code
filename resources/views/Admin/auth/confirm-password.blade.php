<x-guest-layout title="Confirm password">
    <div class="mb-8">
        <h1 class="text-2xl font-semibold tracking-tightest text-bone-50">
            Confirm your password
        </h1>

        <p class="mt-2 text-sm leading-relaxed text-bone-400">
            This is a secure area. Please confirm your password before continuing.
        </p>
    </div>

    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-5">
        @csrf

        <x-admin.field for="password" label="Password" required>
            <x-admin.input
                id="password"
                name="password"
                type="password"
                :invalid="$errors->has('password')"
                autocomplete="current-password"
                required
                autofocus
                placeholder="••••••••••••"
            />

            @error('password')
                <p class="text-xs text-red-300">{{ $message }}</p>
            @enderror
        </x-admin.field>

        <x-admin.button type="submit" size="lg" class="w-full">
            Confirm
        </x-admin.button>
    </form>
</x-guest-layout>