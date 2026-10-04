<x-guest-layout title="Reset password">
    <div class="mb-8">
        <h1 class="text-2xl font-semibold tracking-tightest text-bone-50">
            Choose a new password
        </h1>

        <p class="mt-2 text-sm leading-relaxed text-bone-400">
            Use a long, random password. It is hashed before it is stored.
        </p>
    </div>

    <form method="POST" action="{{ route('password.store') }}" class="space-y-5">
        @csrf

        {{-- Password reset token, unchanged from Breeze. --}}
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <x-admin.field for="email" label="Email" required>
            <x-admin.input
                id="email"
                name="email"
                type="email"
                :value="old('email', $request->email)"
                :invalid="$errors->has('email')"
                autocomplete="username"
                inputmode="email"
                required
                autofocus
            />

            @error('email')
                <p class="text-xs text-red-300">{{ $message }}</p>
            @enderror
        </x-admin.field>

        <x-admin.field for="password" label="New password" required>
            <x-admin.input
                id="password"
                name="password"
                type="password"
                :invalid="$errors->has('password')"
                autocomplete="new-password"
                required
                placeholder="••••••••••••"
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
                placeholder="••••••••••••"
            />

            @error('password_confirmation')
                <p class="text-xs text-red-300">{{ $message }}</p>
            @enderror
        </x-admin.field>

        <x-admin.button type="submit" size="lg" class="w-full">
            Reset password
        </x-admin.button>
    </form>
</x-guest-layout>