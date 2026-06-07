<x-app-layout>
  <x-slot name="header">
    <h2 class="font-black text-2xl text-gray-900 leading-tight tracking-tight">
      {{ __('Profile') }}
    </h2>
  </x-slot>

  <div class="py-6">
    <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
      <div class="p-6 bg-white rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100">
        <div class="max-w-xl">
          @include('profile.partials.update-profile-information-form')
        </div>
      </div>

      <div class="p-6 bg-white rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100">
        <div class="max-w-xl">
          @include('profile.partials.update-password-form')
        </div>
      </div>

      <div class="p-6 bg-white rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100">
        <div class="max-w-xl">
          @include('profile.partials.delete-user-form')
        </div>
      </div>
    </div>
  </div>
</x-app-layout>
