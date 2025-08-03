<div>
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs/qrcode.min.js"></script>

    <div class="flex-col items-center gap-5 mb-4 sm:flex-row md:flex md:justify-between lg:mr-4">
        <h3 class="mb-4 text-lg font-semibold leading-tight text-gray-800 dark:text-gray-200 md:mb-0">
            Data Peserta
        </h3>
        <x-button wire:click="showCreating">
            <x-heroicon-o-plus class="w-4 h-4 mr-2" /> Tambah Peserta
        </x-button>
    </div>
    <div class="mb-1 text-sm dark:text-white">Filter:</div>
    <div class="grid flex-wrap items-center grid-cols-3 gap-5 mb-4 md:gap-8 lg:flex">
        <x-select id="division" wire:model.live="division">
            <option value="">{{ __('Filter Kelompok') }}</option>
            @foreach (App\Models\Division::all() as $_division)
                <option value="{{ $_division->id }}" {{ $_division->id == $division ? 'selected' : '' }}>
                    {{ $_division->name }}
                </option>
            @endforeach
        </x-select>
        <div class="flex items-center col-span-3 gap-2 lg:col-span-1">
            <x-input type="text" wire:model.live="search" class="w-full lg:w-72" name="search" id="seacrh"
                placeholder="{{ __('Search') }}" />
        </div>
    </div>
    <div class="overflow-x-scroll">
        <table class="w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-900">
                <tr>
                    <th scope="col"
                        class="relative px-2 py-2 text-xs font-medium text-center text-gray-500 dark:text-gray-300">
                        No.
                    </th>
                    <th scope="col" class="px-6 py-3 text-xs font-medium text-left text-gray-500 dark:text-gray-300">
                        {{ __('Name') }}
                    </th>
                    <th scope="col" class="px-6 py-3 text-xs font-medium text-left text-gray-500 dark:text-gray-300">
                        {{ __('Email') }}
                    </th>
                    <th scope="col" class="px-6 py-3 text-xs font-medium text-left text-gray-500 dark:text-gray-300">
                        {{ __('Phone Number') }}
                    </th>
                    <th scope="col"
                        class="hidden px-6 py-3 text-xs font-medium text-left text-gray-500 dark:text-gray-300 sm:table-cell">
                        {{ __('City') }}
                    </th>
                    <th scope="col"
                        class="hidden px-6 py-3 text-xs font-medium text-left text-gray-500 dark:text-gray-300 sm:table-cell">
                        {{ __('Kelompok') }}
                    </th>
                    <th scope="col"
                        class="hidden px-6 py-3 text-xs font-medium text-right text-gray-500 dark:text-gray-300 sm:table-cell">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200 dark:divide-gray-700 dark:bg-gray-800">
                @php
                    $class = 'cursor-pointer group-hover:bg-gray-100 dark:group-hover:bg-gray-700';
                @endphp
                @foreach ($users as $user)
                    @php
                        $wireClick = "wire:click=show('$user->id')";
                    @endphp
                    <tr wire:key="{{ $user->id }}" class="group">
                        <td class="{{ $class }} p-2 text-center text-sm font-medium text-gray-900 dark:text-white"
                            {{ $wireClick }}>
                            {{ $loop->iteration }}
                        </td>
                        <td class="{{ $class }} px-6 py-4 text-sm font-medium text-gray-900 dark:text-white"
                            {{ $wireClick }}>
                            {{ $user->name }}
                        </td>
                        <td class="{{ $class }} px-6 py-4 text-sm font-medium text-gray-900 dark:text-white"
                            {{ $wireClick }}>
                            {{ $user->email }}
                        </td>
                        <td class="{{ $class }} px-6 py-4 text-sm font-medium text-gray-900 dark:text-white"
                            {{ $wireClick }}>
                            {{ $user->phone }}
                        </td>
                        <td class="{{ $class }} hidden px-6 py-4 text-sm font-medium text-gray-900 dark:text-white sm:table-cell"
                            {{ $wireClick }}>
                            {{ $user->city }}
                        </td>
                        <td class="{{ $class }} hidden px-6 py-4 text-sm font-medium text-gray-900 dark:text-white sm:table-cell"
                            {{ $wireClick }}>
                            {{ $user->division->name ?? '-' }}
                        </td>
                        <td class="relative px-6 py-4">
                            <div class="flex justify-end gap-2">
                                <x-button type="button" onclick="showQrFn{{ $user->id }}(true, '{{ $user->id }}')">
                                    Qr Code
                                </x-button>
                                <x-button wire:click="edit('{{ $user->id }}')">
                                    Edit
                                </x-button>
                                <x-danger-button
                                    wire:click="confirmDeletion('{{ $user->id }}', '{{ $user->name }}')">
                                    Delete
                                </x-danger-button>
                            </div>
                        </td>
                    </tr>

                    <div style="display: none;" class="fixed inset-0 z-50 flex justify-center w-full h-screen mt-4 overflow-y-auto modalShowQr{{ $user->id }} jetstream-modal">
                        <div class="px-10">
                            <div style="display: none;" class="fixed inset-0 transition-all transform modalShowQr{{ $user->id }}" onclick="showQrFn{{ $user->id }}(false, '{{ $user->id }}')">
                                <div class="absolute inset-0 bg-gray-500 opacity-75 dark:bg-gray-900"></div>
                            </div>

                            <div style="display: none;" class="mb-6 overflow-hidden transition-all transform bg-white pt-10 pb-10 rounded-lg shadow-xl modalShowQr{{ $user->id }} dark:bg-gray-800 sm:mx-auto sm:w-full">
                                <div class="px-6 py-4 mb-5">
                                    <div class="flex justify-center w-full mt-6">
                                        <div id="qrcode{{ $user->barcode->id }}" class="bg-transparent w-80 h-80"></div>
                                    </div>
                                    <script>
                                        function showQrFn{{ $user->id }}(show, id) {
                                            const modals = document.getElementsByClassName('modalShowQr'+id);

                                            for (let i = 0; i < modals.length; i++) {
                                                if (show) {
                                                    modals[i].style.display = 'block';
                                                } else {
                                                    modals[i].style.display = 'none';
                                                }
                                            }

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
                                        }
                                    </script>
                                    <div class="flex justify-center gap-3 mt-5">
                                        <x-secondary-button onclick="showQrFn{{ $user->id }}(false, '{{ $user->id }}')">
                                            {{ __('Tutup') }}
                                        </x-secondary-button>
                                        <x-button href="{{ route('admin.barcodes.download', $user->barcode->id) }}">
                                            Download
                                        </x-button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-3">
        {{ $users->links() }}
    </div>

    <x-confirmation-modal wire:model="confirmingDeletion">
        <x-slot name="title">
            Hapus Peserta
        </x-slot>

        <x-slot name="content">
            Apakah Anda yakin ingin menghapus <b>{{ $deleteName }}</b>?
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$toggle('confirmingDeletion')" wire:loading.attr="disabled">
                {{ __('Cancel') }}
            </x-secondary-button>

            <x-danger-button class="ml-2" wire:click="delete" wire:loading.attr="disabled">
                {{ __('Confirm') }}
            </x-danger-button>
        </x-slot>
    </x-confirmation-modal>

    <x-dialog-modal wire:model="creating">
        <x-slot name="title">
            Peserta Baru
        </x-slot>

        <form wire:submit="create">
            <x-slot name="content">
                @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                    <div x-data="{ photoName: null, photoPreview: null }" class="col-span-6 sm:col-span-4">
                        <!-- Profile Photo File Input -->
                        <input type="file" id="photo" class="hidden" wire:model.live="form.photo" x-ref="photo"
                            x-on:change="
                                    photoName = $refs.photo.files[0].name;
                                    const reader = new FileReader();
                                    reader.onload = (e) => {
                                        photoPreview = e.target.result;
                                    };
                                    reader.readAsDataURL($refs.photo.files[0]);
                            " />

                        <x-label for="photo" value="{{ __('Photo') }}" />

                        <!-- Current Profile Photo -->
                        <div class="w-20 h-20 mt-2 rounded-full outline outline-gray-400" x-show="! photoPreview">
                        </div>

                        <!-- New Profile Photo Preview -->
                        <div class="mt-2" x-show="photoPreview" style="display: none;">
                            <span class="block w-20 h-20 bg-center bg-no-repeat bg-cover rounded-full"
                                x-bind:style="'background-image: url(\'' + photoPreview + '\');'">
                            </span>
                        </div>

                        <x-secondary-button class="mt-2 me-2" type="button" x-on:click.prevent="$refs.photo.click()">
                            {{ __('Select A New Photo') }}
                        </x-secondary-button>

                        @if ($form->user?->profile_photo_path)
                            <x-secondary-button type="button" class="mt-2" wire:click="deleteProfilePhoto">
                                {{ __('Remove Photo') }}
                            </x-secondary-button>
                        @endif

                        @error('form.photo')
                            <x-input-error for="form.photo" message="{{ $message }}" class="mt-2" />
                        @enderror
                    </div>
                @endif
                <div class="mt-4">
                    <x-label for="name">Nama Peserta</x-label>
                    <x-input id="name" class="block w-full mt-1" type="text" wire:model="form.name" />
                    @error('form.name')
                        <x-input-error for="form.name" class="mt-2" message="{{ $message }}" />
                    @enderror
                </div>
                <div class="flex flex-col gap-4 mt-4 sm:flex-row sm:gap-3">
                    <div class="w-full">
                        <x-label for="email">{{ __('Email') }}</x-label>
                        <x-input id="email" class="block w-full mt-1" type="email" wire:model="form.email"
                            placeholder="example@example.com" required />
                        @error('form.email')
                            <x-input-error for="form.email" class="mt-2" message="{{ $message }}" />
                        @enderror
                    </div>
                </div>
                <div class="flex flex-col gap-4 mt-4 sm:flex-row sm:gap-3">
                    <div class="w-full">
                        <x-label for="password">{{ __('Password') }}</x-label>
                        <x-input id="password" class="block w-full mt-1" type="password" wire:model="form.password"
                            placeholder="New Password" />
                        <p class="text-sm dark:text-gray-400">Default password: <b>password</b></p>
                        @error('form.password')
                            <x-input-error for="form.password" class="mt-2" message="{{ $message }}" />
                        @enderror
                    </div>
                </div>
                <div class="flex flex-col gap-4 mt-4 sm:flex-row sm:gap-3">
                    <div class="w-full">
                        <x-label for="gender">{{ __('Gender') }}</x-label>
                        <div class="flex flex-row gap-5 my-3">
                            <div class="flex items-center">
                                <input type="radio" id="gender-male" wire:model="form.gender" value="male" />
                                <x-label for="gender-male" class="ml-2">{{ __('Male') }}</x-label>
                            </div>
                            <div class="flex items-center">
                                <input type="radio" id="gender-female" wire:model="form.gender" value="female" />
                                <x-label for="gender-female" class="ml-2">{{ __('Female') }}</x-label>
                            </div>
                        </div>
                        @error('form.gender')
                            <x-input-error for="form.gender" class="mt-2" message="{{ $message }}" />
                        @enderror
                    </div>
                    <div class="w-full">
                        <x-label for="phone">{{ __('Phone') }}</x-label>
                        <x-input id="phone" class="block w-full mt-1" type="number" wire:model="form.phone"
                            placeholder="+628123456789" />
                        @error('form.phone')
                            <x-input-error for="form.phone" class="mt-2" message="{{ $message }}" />
                        @enderror
                    </div>
                </div>
                <div class="flex flex-col gap-4 mt-4 sm:flex-row sm:gap-3">
                    <div class="w-full">
                        <x-label for="birth_date">{{ __('Birth Date') }}</x-label>
                        <x-input id="birth_date" class="block w-full mt-1" type="date"
                            wire:model="form.birth_date" />
                        @error('form.birth_date')
                            <x-input-error for="form.birth_date" class="mt-2" message="{{ $message }}" />
                        @enderror
                    </div>
                    <div class="w-full">
                        <x-label for="birth_place">{{ __('Birth Place') }}</x-label>
                        <x-input id="birth_place" class="block w-full mt-1" type="text"
                            wire:model="form.birth_place" placeholder="Jakarta" />
                        @error('form.birth_place')
                            <x-input-error for="form.birth_place" class="mt-2" message="{{ $message }}" />
                        @enderror
                    </div>
                </div>
                <div class="flex flex-col gap-4 mt-4 sm:flex-row sm:gap-3">
                    <div class="w-full">
                        <x-label for="city">{{ __('City') }}</x-label>
                        <x-input id="city" class="block w-full mt-1" type="text" wire:model="form.city"
                            placeholder="Domisili" />
                        @error('form.city')
                            <x-input-error for="form.city" class="mt-2" message="{{ $message }}" />
                        @enderror
                    </div>
                    <div class="w-full">
                        <x-label for="address">{{ __('Address') }}</x-label>
                        <x-input id="address" class="block w-full mt-1" type="text" wire:model="form.address"
                            placeholder="Jl. Jend. Sudirman" />
                        @error('form.address')
                            <x-input-error for="form.address" class="mt-2" message="{{ $message }}" />
                        @enderror
                    </div>
                </div>
                <div class="mt-4">
                    <x-label for="form.division_id" value="{{ __('Kelompok') }}" />
                    <x-select id="form.division_id" class="block w-full mt-1" wire:model="form.division_id">
                        <option value="">{{ __('Filter Kelompok') }}</option>
                        @foreach (App\Models\Division::all() as $division)
                            <option value="{{ $division->id }}"
                                {{ $division->id == $form->division_id ? 'selected' : '' }}>
                                {{ $division->name }}
                            </option>
                        @endforeach
                    </x-select>
                    @error('form.division_id')
                        <x-input-error for="form.division_id" class="mt-2" message="{{ $message }}" />
                    @enderror
                </div>
            </x-slot>

            <x-slot name="footer">
                <x-secondary-button wire:click="$toggle('creating')" wire:loading.attr="disabled">
                    {{ __('Cancel') }}
                </x-secondary-button>

                <x-button class="ml-2" wire:click="create" wire:loading.attr="disabled" wire:target="form.photo">
                    {{ __('Confirm') }}
                </x-button>
            </x-slot>
        </form>
    </x-dialog-modal>

    <x-dialog-modal wire:model="editing">
        <x-slot name="title">
            Edit Peserta
        </x-slot>

        <form wire:submit.prevent="update" id="user-edit">
            <x-slot name="content">
                @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                    <div x-data="{ photoName: null, photoPreview: null }" class="col-span-6 sm:col-span-4">
                        <!-- Profile Photo File Input -->
                        <input type="file" id="photo" class="hidden" wire:model.live="form.photo"
                            x-ref="photo"
                            x-on:change="
                                    photoName = $refs.photo.files[0].name;
                                    const reader = new FileReader();
                                    reader.onload = (e) => {
                                        photoPreview = e.target.result;
                                    };
                                    reader.readAsDataURL($refs.photo.files[0]);
                            " />

                        <x-label for="photo" value="{{ __('Photo') }}" />

                        <!-- Current Profile Photo -->
                        <div class="mt-2" x-show="! photoPreview">
                            <img src="{{ $form->user?->profile_photo_url }}" alt="{{ $form->user?->name }}"
                                class="object-cover w-20 h-20 rounded-full">
                        </div>

                        <!-- New Profile Photo Preview -->
                        <div class="mt-2" x-show="photoPreview" style="display: none;">
                            <span class="block w-20 h-20 bg-center bg-no-repeat bg-cover rounded-full"
                                x-bind:style="'background-image: url(\'' + photoPreview + '\');'">
                            </span>
                        </div>

                        <x-secondary-button class="mt-2 me-2" type="button"
                            x-on:click.prevent="$refs.photo.click()">
                            {{ __('Select A New Photo') }}
                        </x-secondary-button>

                        @if ($form->user?->profile_photo_path)
                            <x-secondary-button type="button" class="mt-2" wire:click="deleteProfilePhoto">
                                {{ __('Remove Photo') }}
                            </x-secondary-button>
                        @endif

                        @error('form.photo')
                            <x-input-error for="form.photo" message="{{ $message }}" class="mt-2" />
                        @enderror
                    </div>
                @endif
                <div class="mt-4">
                    <x-label for="name">Nama Peserta</x-label>
                    <x-input id="name" class="block w-full mt-1" type="text" wire:model="form.name" />
                    @error('form.name')
                        <x-input-error for="form.name" class="mt-2" message="{{ $message }}" />
                    @enderror
                </div>
                <div class="flex flex-col gap-4 mt-4 sm:flex-row sm:gap-3">
                    <div class="w-full">
                        <x-label for="email">{{ __('Email') }}</x-label>
                        <x-input id="email" class="block w-full mt-1" type="email" wire:model="form.email"
                            placeholder="example@example.com" required />
                        @error('form.email')
                            <x-input-error for="form.email" class="mt-2" message="{{ $message }}" />
                        @enderror
                    </div>
                </div>
                <div class="flex flex-col gap-4 mt-4 sm:flex-row sm:gap-3">
                    <div class="w-full">
                        <x-label for="password">{{ __('Password') }}</x-label>
                        <x-input id="password" class="block w-full mt-1" type="password" wire:model="form.password"
                            placeholder="New Password" />
                        @error('form.password')
                            <x-input-error for="form.password" class="mt-2" message="{{ $message }}" />
                        @enderror
                    </div>
                </div>
                <div class="flex flex-col gap-4 mt-4 sm:flex-row sm:gap-3">
                    <div class="w-full">
                        <x-label for="gender">{{ __('Gender') }}</x-label>
                        <div class="flex flex-row gap-5 my-3">
                            <div class="flex items-center">
                                <input type="radio" id="gender-male" wire:model="form.gender" value="male" />
                                <x-label for="gender-male" class="ml-2">{{ __('Male') }}</x-label>
                            </div>
                            <div class="flex items-center">
                                <input type="radio" id="gender-female" wire:model="form.gender" value="female" />
                                <x-label for="gender-female" class="ml-2">{{ __('Female') }}</x-label>
                            </div>
                        </div>
                        @error('form.gender')
                            <x-input-error for="form.gender" class="mt-2" message="{{ $message }}" />
                        @enderror
                    </div>
                    <div class="w-full">
                        <x-label for="phone">{{ __('Phone') }}</x-label>
                        <x-input id="phone" class="block w-full mt-1" type="text" wire:model="form.phone"
                            placeholder="+628123456789" />
                        @error('form.phone')
                            <x-input-error for="form.phone" class="mt-2" message="{{ $message }}" />
                        @enderror
                    </div>
                </div>
                <div class="flex flex-col gap-4 mt-4 sm:flex-row sm:gap-3">
                    <div class="w-full">
                        <x-label for="birth_date">{{ __('Birth Date') }}</x-label>
                        <x-input id="birth_date" class="block w-full mt-1" type="date"
                            wire:model="form.birth_date" />
                        @error('form.birth_date')
                            <x-input-error for="form.birth_date" class="mt-2" message="{{ $message }}" />
                        @enderror
                    </div>
                    <div class="w-full">
                        <x-label for="birth_place">{{ __('Birth Place') }}</x-label>
                        <x-input id="birth_place" class="block w-full mt-1" type="text"
                            wire:model="form.birth_place" placeholder="Jakarta" />
                        @error('form.birth_place')
                            <x-input-error for="form.birth_place" class="mt-2" message="{{ $message }}" />
                        @enderror
                    </div>
                </div>
                <div class="flex flex-col gap-4 mt-4 sm:flex-row sm:gap-3">
                    <div class="w-full">
                        <x-label for="city">{{ __('City') }}</x-label>
                        <x-input id="city" class="block w-full mt-1" type="text" wire:model="form.city"
                            placeholder="Domisili" />
                        @error('form.city')
                            <x-input-error for="form.city" class="mt-2" message="{{ $message }}" />
                        @enderror
                    </div>
                    <div class="w-full">
                        <x-label for="address">{{ __('Address') }}</x-label>
                        <x-input id="address" class="block w-full mt-1" type="text" wire:model="form.address"
                            placeholder="Jl. Jend. Sudirman" />
                        @error('form.address')
                            <x-input-error for="form.address" class="mt-2" message="{{ $message }}" />
                        @enderror
                    </div>
                </div>
                <div class="mt-4">
                    <x-label for="form.division_id" value="{{ __('Kelompok') }}" />
                    <x-select id="form.division_id" class="block w-full mt-1" wire:model="form.division_id">
                        <option value="">{{ __('Filter Kelompok') }}</option>
                        @foreach (App\Models\Division::all() as $division)
                            <option value="{{ $division->id }}"
                                {{ $division->id == $form->division_id ? 'selected' : '' }}>
                                {{ $division->name }}
                            </option>
                        @endforeach
                    </x-select>
                    @error('form.division_id')
                        <x-input-error for="form.division_id" class="mt-2" message="{{ $message }}" />
                    @enderror
                </div>
            </x-slot>

            <x-slot name="footer">
                <x-secondary-button wire:click="$toggle('editing')" wire:loading.attr="disabled">
                    {{ __('Cancel') }}
                </x-secondary-button>

                <x-button class="ml-2" wire:click="update" wire:loading.attr="disabled" wire:target="form.photo">
                    {{ __('Confirm') }}
                </x-button>
            </x-slot>
        </form>
    </x-dialog-modal>

    <x-modal wire:model="showDetail">
        @if ($form->user)
            @php
                $division = $form->user->division ? json_decode($form->user->division)->name : '-';
                $jobTitle = $form->user->jobTitle ? json_decode($form->user->jobTitle)->name : '-';
                $education = $form->user->education ? json_decode($form->user->education)->name : '-';
            @endphp
            <div class="px-6 py-4">
                <div class="flex items-center justify-center my-4">
                    <img class="object-cover w-32 h-32 rounded-full" src="{{ $user->profile_photo_url }}"
                        alt="{{ $user->name }}" />
                </div>

                <div class="text-lg font-medium text-center text-gray-900 dark:text-gray-100">
                    {{ $form->user->name }}
                </div>

                <div class="mt-4 text-sm text-gray-600 dark:text-gray-400">
                    {{-- <div class="mt-4">
                        <x-label for="nip" value="NIP" />
                        <p>{{ $form->user->nip }}</p>
                    </div> --}}
                    <div class="mt-4">
                        <x-label for="email" value="{{ __('Email') }}" />
                        <p>{{ $form->user->email }}</p>
                    </div>
                    <div class="mt-4">
                        <x-label for="phone" value="{{ __('Phone') }}" />
                        <p>{{ $form->user->phone }}</p>
                    </div>
                    <div class="mt-4">
                        <x-label for="gender" value="{{ __('Gender') }}" />
                        <p>{{ __($form->user->gender) }}</p>
                    </div>
                    <div class="mt-4">
                        <x-label for="birth_date" value="{{ __('Birth Date') }}" />
                        @if ($form->user->birth_date)
                            <p>{{ \Illuminate\Support\Carbon::parse($form->user->birth_date)->format('D d M Y') }}</p>
                        @else
                            <p>-</p>
                        @endif
                    </div>
                    <div class="mt-4">
                        <x-label for="birth_place" value="{{ __('Birth Place') }}" />
                        <p>{{ $form->user->birth_place ?? '-' }}</p>
                    </div>
                    <div class="mt-4">
                        <x-label for="address" value="{{ __('Address') }}" />
                        @if (empty($form->user->address))
                            <p>-</p>
                        @else
                            <p>{{ $form->user->address }}</p>
                        @endif
                    </div>
                    <div class="mt-4">
                        <x-label for="city" value="{{ __('City') }}" />
                        @if (empty($form->user->city))
                            <p>-</p>
                        @else
                            <p>{{ $form->user->city }}</p>
                        @endif
                    </div>
                    <div class="mt-4">
                        <x-label for="division_id" value="{{ __('Kelompok') }}" />
                        <p>{{ $division }}</p>
                    </div>
                </div>
            </div>
        @endif
    </x-modal>
</div>

@push('scripts')
    <script>
        Livewire.on('reload-page', () => {
            window.location.reload();
        });
    </script>
@endpush
