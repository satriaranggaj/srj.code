{{--
    Account deletion. The x-modal confirmation flow is Breeze behaviour and is
    preserved exactly; only the presentation changes.
--}}
<div>
    <p class="text-sm leading-relaxed text-bone-400">
        Once your account is deleted, all of its resources and data will be permanently
        deleted. Before deleting your account, please download any data or information
        that you wish to retain.
    </p>

    <x-admin.button
        variant="danger"
        icon="close"
        class="mt-4"
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >
        Delete account
    </x-admin.button>
</div>

<x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
    <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
        @csrf
        @method('delete')

        <h2 class="text-lg font-semibold text-bone-50">
            Are you sure you want to delete your account?
        </h2>

        <p class="mt-2 text-sm leading-relaxed text-bone-400">
            Once your account is deleted, all of its resources and data will be permanently
            deleted. Please enter your password to confirm you would like to permanently
            delete your account.
        </p>

        <div class="mt-6">
            <x-admin.field for="password" label="Password">
                <x-admin.input
                    id="password"
                    name="password"
                    type="password"
                    :invalid="$errors->userDeletion->has('password')"
                    autocomplete="current-password"
                    required
                    autofocus
                    placeholder="••••••••••••"
                />

                @if ($errors->userDeletion->has('password'))
                    <p class="text-xs text-red-300">{{ $errors->userDeletion->first('password') }}</p>
                @endif
            </x-admin.field>
        </div>

        <div class="mt-6 flex flex-wrap justify-end gap-3">
            <x-admin.button variant="ghost" x-on:click="$dispatch('close')">
                Cancel
            </x-admin.button>

            <x-admin.button variant="danger">
                Delete account
            </x-admin.button>
        </div>
    </form>
</x-modal>