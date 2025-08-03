<x-app-layout>
  <x-slot name="header">
    <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
      {{ __('Profile') }}
    </h2>
  </x-slot>

  <div>
    <div class="py-10 mx-auto max-w-7xl sm:px-6 lg:px-8">
      @if (Laravel\Fortify\Features::canUpdateProfileInformation())
        @livewire('profile.update-profile-information-form')

        <x-section-border />
      @endif

        @if ($user->barcode)
            <x-form-section submit="xxx">
                <x-slot name="title">
                    {{ __('Barcode User') }}
                </x-slot>

                <x-slot name="description">
                    {{ __('user barcode and download.') }}
                </x-slot>

                <x-slot name="form">
                    <div class="col-span-6 sm:col-span-4">
                        <div id="qrcode{{ $user->barcode->id }}" class="bg-transparent w-80 h-80"></div>
                    </div>
                </x-slot>

                <x-slot name="actions">
                    <x-button type="button" wire:loading.attr="disabled" href="{{ route('user.barcodes.download', $user->barcode->id) }}">
                        {{ __('Download Barcode') }}
                    </x-button>
                </x-slot>
            </x-form-section>

            @push('scripts')
                <script>
                    let element = document.getElementById("qrcode{{ $user->barcode->id }}");

                    element.innerHTML = '';

                    new QRCode(element, {
                        text: "{{ $user->barcode->value }}",
                        width: 320,
                        height: 320,
                        colorDark : "#000000",
                        colorLight : "#ffffff",
                        correctLevel : QRCode.CorrectLevel.H
                    });
                </script>
            @endpush

            <x-section-border />
        @endif

      @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::updatePasswords()))
        <div class="mt-10 sm:mt-0">
          @livewire('profile.update-password-form')
        </div>

        <x-section-border />
      @endif

      @if (Laravel\Fortify\Features::canManageTwoFactorAuthentication())
        <div class="mt-10 sm:mt-0">
          @livewire('profile.two-factor-authentication-form')
        </div>

        <x-section-border />
      @endif

      <div class="mt-10 sm:mt-0">
        @livewire('profile.logout-other-browser-sessions-form')
      </div>

      @if (Laravel\Jetstream\Jetstream::hasAccountDeletionFeatures())
        <x-section-border />

        <div class="mt-10 sm:mt-0">
          @livewire('profile.delete-user-form')
        </div>
      @endif
    </div>
  </div>
</x-app-layout>
