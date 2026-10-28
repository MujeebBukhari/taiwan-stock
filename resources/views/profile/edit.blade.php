<x-app-layout>
    <x-slot name="header">
        {{ __('navigation.profile') }}
    </x-slot>

    <div class="max-w-4xl mx-auto space-y-6">
        <!-- Profile Information Card -->
        <div class="p-6 sm:p-8 bg-white border border-slate-200/90 rounded-xl shadow-card">
            <div class="max-w-xl">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>

        <!-- Security & Password Card -->
        <div class="p-6 sm:p-8 bg-white border border-slate-200/90 rounded-xl shadow-card">
            <div class="max-w-xl">
                @include('profile.partials.update-password-form')
            </div>
        </div>

        <!-- Danger Zone Card -->
        <div class="p-6 sm:p-8 bg-white border border-rose-200 rounded-xl shadow-card">
            <div class="max-w-xl">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</x-app-layout>
