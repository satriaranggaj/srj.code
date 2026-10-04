<x-guest-layout title="Forgot password">
    <div class="mb-8">
        <h1 class="text-2xl font-semibold tracking-tightest text-bone-50">
            Reset your password
        </h1>

        <p class="mt-2 text-sm leading-relaxed text-bone-400">
            Enter the e-mail address for your account and we will send you a link to choose a new password.
        </p>
    </div>

    @if (session('status'))
        <x-admin.alert type="success" class="mb-6">
            {{ session('status') }}
        </x-admin.alert>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
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

        <x-admin.button type="submit" size="lg" class="w-full">
            Email password reset link
        </x-admin.button>
    </form>

    <a
        href="{{ route('login') }}"
        class="mt-6 flex items-center justify-center gap-1.5 rounded-lg px-2 py-1.5 text-sm text-bone-400 transition-colors duration-150 hover:text-bone-100"
    >
        <x-portfolio.icon name="arrow-left" class="h-4 w-4" />
        Back to sign in
    </a>
</x-guest-layout>