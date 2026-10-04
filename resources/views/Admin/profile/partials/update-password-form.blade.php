{{--
    Password update.

    Validation errors for this form live in a named error bag (`updatePassword`),
    so they are read from the bag directly. @error only accepts a single key and
    cannot target a bag.
--}}
<form method="post" action="{{ route('password.update') }}" class="space-y-5">
    @csrf
    @method('put')

    <x-admin.field for="update_password_current_password" label="Current password" required>
        <x-admin.input
            id="update_password_current_password"
            name="current_password"
            type="password"
            :invalid="$errors->updatePassword->has('current_password')"
            autocomplete="current-password"
            required
        />

        @if ($errors->updatePassword->has('current_password'))
            <p class="text-xs text-red-300">{{ $errors->updatePassword->first('current_password') }}</p>
        @endif
    </x-admin.field>

    <x-admin.field for="update_password_password" label="New password" required help="At least 12 characters.">
        <x-admin.input
            id="update_password_password"
            name="password"
            type="password"
            :invalid="$errors->updatePassword->has('password')"
            autocomplete="new-password"
            required
        />

        @if ($errors->updatePassword->has('password'))
            <p class="text-xs text-red-300">{{ $errors->updatePassword->first('password') }}</p>
        @endif
    </x-admin.field>

    <x-admin.field for="update_password_password_confirmation" label="Confirm password" required>
        <x-admin.input
            id="update_password_password_confirmation"
            name="password_confirmation"
            type="password"
            autocomplete="new-password"
            required
        />

        @if ($errors->updatePassword->has('password_confirmation'))
            <p class="text-xs text-red-300">{{ $errors->updatePassword->first('password_confirmation') }}</p>
        @endif
    </x-admin.field>

    <div class="flex items-center gap-4">
        <x-admin.button type="submit">Save</x-admin.button>

        @if (session('status') === 'password-updated')
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