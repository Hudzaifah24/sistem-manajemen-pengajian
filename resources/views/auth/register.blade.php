<x-guest-layout>
  <x-authentication-card>
    <x-slot name="logo">
      <x-authentication-card-logo />
    </x-slot>

    <x-validation-errors class="mb-4" />

    <form method="POST" action="{{ route('register') }}">
      @csrf

      <div>
        <x-label for="name" value="{{ __('Name') }}" />
        <x-input id="name" class="mt-1 block w-full" type="text" name="name" :value="old('name')" required autofocus
          autocomplete="name" />
      </div>

      {{-- <div class="mt-4">
        <x-label for="nip" value="{{ __('NIP') }}" />
        <x-input id="nip" class="mt-1 block w-full" type="text" name="nip" :value="old('nip')"
          autocomplete="nip" />
      </div> --}}

      <div class="mt-4">
        <x-label for="email" value="{{ __('Email') }}" />
        <x-input id="email" class="mt-1 block w-full" type="email" name="email" :value="old('email')" required
          autocomplete="username" />
      </div>

      <div class="mt-4">
        <x-label for="phone" value="{{ __('Phone Number') }}" />
        <x-input id="phone" class="mt-1 block w-full" type="number" name="phone" :value="old('phone')" required
          autocomplete="username" />
      </div>

      <div class="mt-4">
        <x-label for="gender" value="{{ __('Gender') }}" />
        <x-select id="gender" class="mt-1 block w-full" name="gender" required>
          <option disabled selected>{{ __('Select Gender') }}</option>
          <option value="male">
            {{ __('Male') }}
          </option>
          <option value="female">
            {{ __('Female') }}
          </option>
        </x-select>
      </div>

      <div class="mt-4">
        <x-label for="address" value="{{ __('Address') }}" />
        <x-textarea id="address" class="mt-1 block w-full" name="address" :value="old('address')" required />
      </div>

      <div class="mt-4">
        <x-label for="city" value="{{ __('City') }}" />
        <x-input id="city" class="mt-1 block w-full" type="text" name="city" :value="old('city')" required
          autocomplete="city" />
      </div>

      <div class="mt-4">
        <x-label for="password" value="{{ __('Password') }}" />
        <x-input id="password" class="mt-1 block w-full" type="password" name="password" required
          autocomplete="new-password" />
      </div>

      <div class="mt-4">
        <x-label for="password_confirmation" value="{{ __('Confirm Password') }}" />
        <x-input id="password_confirmation" class="mt-1 block w-full" type="password" name="password_confirmation"
          required autocomplete="new-password" />
      </div>

      @if (Laravel\Jetstream\Jetstream::hasTermsAndPrivacyPolicyFeature())
        <div class="mt-4">
          <x-label for="terms">
            <div class="flex items-center">
              <x-checkbox name="terms" id="terms" required />

              <div class="ms-2">
                {!! __('I agree to the :terms_of_service and :privacy_policy', [
                    'terms_of_service' =>
                        '<a target="_blank" href="' .
                        route('terms.show') .
                        '" class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">' .
                        __('Terms of Service') .
                        '</a>',
                    'privacy_policy' =>
                        '<a target="_blank" href="' .
                        route('policy.show') .
                        '" class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">' .
                        __('Privacy Policy') .
                        '</a>',
                ]) !!}
              </div>
            </div>
          </x-label>
        </div>
      @endif

      <div class="mt-4 flex items-center justify-end">
        <a class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:text-gray-400 dark:hover:text-gray-100 dark:focus:ring-offset-gray-800"
          href="{{ route('login') }}">
          {{ __('Already registered?') }}
        </a>

        <x-button class="ms-4">
          {{ __('Register') }}
        </x-button>
      </div>
    </form>
  </x-authentication-card>
</x-guest-layout>
