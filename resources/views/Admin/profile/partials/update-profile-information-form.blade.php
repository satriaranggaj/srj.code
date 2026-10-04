{{-- Empty form used by the "resend verification email" button. Unchanged from Breeze. --}}
<form id="send-verification" method="post" action="{{ route('verification.send') }}">
    @csrf
</form>

<form method="post" action="{{ route('profile.update') }}" class="space-y-5">
    @csrf
    @method('patch')

    <x-admin.field for="name" label="Name" required>
        <x-admin.input
            id="name"
            name="name"
            type="text"
            :value="old('name', $user->name)"
            :invalid="$errors->has('name')"
            autocomplete="name"
            required
            autofocus
        />

        @error('name')<p class="text-xs text-red-300">{{ $message }}</p>@enderror
    </x-admin.field>

    <x-admin.field for="email" label="Email" required>
        <x-admin.input
            id="email"
            name="email"
            type="email"
            :value="old('email', $user->email)"
            :invalid="$errors->has('email')"
            autocomplete="username"
            inputmode="email"
            required
        />

        @error('email')<p class="text-xs text-red-300">{{ $message }}</p>@enderror

        @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
            <div class="rounded-lg border border-amber-500/30 bg-amber-500/10 p-3.5">
                <p class="text-sm leading-relaxed text-amber-200">
                    Your email address is unverified.

                    <button
                        form="send-verification"
                        type="submit"
                        class="rounded font-medium underline underline-offset-2 transition-colors hover:text-amber-100"
                    >
                        Click here to re-send the verification email.
                    </button>
                </p>

                @if (session('status') === 'verification-link-sent')
                    <p class="mt-2 text-sm text-amber-200">
                        A new verification link has been sent to your email address.
                    </p>
                @endif
            </div>
        @endif
    </x-admin.field>

    <div class="flex items-center gap-4">
        <x-admin.button type="submit">Save</x-admin.button>

        @if (session('status') === 'profile-updated')
            <p
                x-data="{ show: true }"
                x-show="show"
                x-transition
                x-init="setTimeout(() => show = false, 2000)"
                class="text-sm text-accent-400"
            >Saved.</p>
        @endif
    </div>
</form>