<x-guest-layout title="Verify email">
    <div class="mb-8">
        <h1 class="text-2xl font-semibold tracking-tightest text-bone-50">
            Verify your email
        </h1>

        <p class="mt-2 text-sm leading-relaxed text-bone-400">
            Before you get started, confirm your e-mail address by opening the link we just sent you.
        </p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <x-admin.alert type="success" class="mb-6" title="Link sent">
            A new verification link has been sent to the e-mail address on your account.
        </x-admin.alert>
    @endif

    <div class="space-y-4">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <x-admin.button type="submit" size="lg" class="w-full">
                Resend verification email
            </x-admin.button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button
                type="submit"
                class="w-full rounded-lg px-4 py-2.5 text-sm text-bone-400 transition-colors duration-150 hover:bg-ink-850 hover:text-bone-100"
            >
                Log out
            </button>
        </form>
    </div>
</x-guest-layout>