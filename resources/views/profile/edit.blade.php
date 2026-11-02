<x-app-layout>
    <x-slot name="header">
        <h1 class="text-xl font-semibold tracking-tight">
            {{ __('Profile') }}
        </h1>
    </x-slot>

    <div class="space-y-8">
        <div class="border border-line bg-surface p-6">
            <div class="max-w-xl">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>

        <div class="border border-line bg-surface p-6">
            <div class="max-w-xl">
                @include('profile.partials.update-password-form')
            </div>
        </div>

        <div class="border border-line bg-surface p-6">
            <div class="max-w-xl">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</x-app-layout>
