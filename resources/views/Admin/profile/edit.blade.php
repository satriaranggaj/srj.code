<x-app-layout title="Profile" subtitle="Your account details and password.">
    <div class="mx-auto max-w-2xl space-y-5">
        <x-admin.panel title="Profile information" description="Update your account's name and email address.">
            @include('Admin.profile.partials.update-profile-information-form')
        </x-admin.panel>

        <x-admin.panel title="Password" description="Ensure your account is using a long, random password to stay secure.">
            @include('Admin.profile.partials.update-password-form')
        </x-admin.panel>

        {{-- Destructive action, visually distinct from every other panel. --}}
        <x-admin.panel>
            <x-slot name="header">
                <span class="text-sm font-semibold text-red-300">Danger zone</span>
            </x-slot>

            @include('Admin.profile.partials.delete-user-form')
        </x-admin.panel>
    </div>
</x-app-layout>