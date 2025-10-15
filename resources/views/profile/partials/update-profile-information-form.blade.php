<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
                <x-input-label for="first_name" :value="__('First name')" />
                <x-text-input id="first_name" name="first_name" type="text" class="mt-1 block w-full" :value="old('first_name', $user->first_name)" autofocus autocomplete="given-name" />
                <x-input-error class="mt-2" :messages="$errors->get('first_name')" />
            </div>
            <div>
                <x-input-label for="last_name" :value="__('Last name')" />
                <x-text-input id="last_name" name="last_name" type="text" class="mt-1 block w-full" :value="old('last_name', $user->last_name)" autocomplete="family-name" />
                <x-input-error class="mt-2" :messages="$errors->get('last_name')" />
            </div>
        </div>

        @php $addr = optional($user->address); @endphp
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div class="sm:col-span-2">
                <x-input-label for="address_line" :value="__('Address')" />
                <x-text-input id="address_line" name="address[address_line]" type="text" class="mt-1 block w-full" :value="old('address.address_line', $addr->address_line)" autocomplete="street-address" />
                <x-input-error class="mt-2" :messages="$errors->get('address.address_line')" />
            </div>
            <div>
                <x-input-label for="city" :value="__('City')" />
                <x-text-input id="city" name="address[city]" type="text" class="mt-1 block w-full" :value="old('address.city', $addr->city)" />
                <x-input-error class="mt-2" :messages="$errors->get('address.city')" />
            </div>
            <div>
                <x-input-label for="province" :value="__('Province')" />
                <x-text-input id="province" name="address[province]" type="text" class="mt-1 block w-full" :value="old('address.province', $addr->province)" />
                <x-input-error class="mt-2" :messages="$errors->get('address.province')" />
            </div>
            <div>
                <x-input-label for="postal_code" :value="__('Postal Code')" />
                <x-text-input id="postal_code" name="address[postal_code]" type="text" class="mt-1 block w-full" :value="old('address.postal_code', $addr->postal_code)" />
                <x-input-error class="mt-2" :messages="$errors->get('address.postal_code')" />
            </div>
            <div>
                <x-input-label for="phone_number" :value="__('Phone Number')" />
                <x-text-input id="phone_number" name="address[phone_number]" type="text" class="mt-1 block w-full" :value="old('address.phone_number', $addr->phone_number)" autocomplete="tel" />
                <x-input-error class="mt-2" :messages="$errors->get('address.phone_number')" />
            </div>
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
