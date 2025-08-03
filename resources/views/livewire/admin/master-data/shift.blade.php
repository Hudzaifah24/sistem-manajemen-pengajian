<div>
    <div class="flex-col items-center gap-5 mb-4 sm:flex-row md:flex md:justify-between lg:mr-4">
        <h3 class="mb-4 text-lg font-semibold leading-tight text-gray-800 dark:text-gray-200 md:mb-0">
            Data Acara
        </h3>
        <x-button wire:click="showCreating">
            <x-heroicon-o-plus class="w-4 h-4 mr-2" /> Tambah Acara
        </x-button>
    </div>
    <table class="w-full divide-y divide-gray-200 dark:divide-gray-700">
        <thead class="bg-gray-50 dark:bg-gray-900">
            <tr>
                <th scope="col" class="px-6 py-3 text-xs font-medium text-left text-gray-500 dark:text-gray-300">
                    Acara
                </th>
                <th scope="col" class="px-6 py-3 text-xs font-medium text-left text-gray-500 dark:text-gray-300">
                    {{ __('Time Start') }}
                </th>
                <th scope="col" class="px-6 py-3 text-xs font-medium text-left text-gray-500 dark:text-gray-300">
                    {{ __('Time End') }}
                </th>
                <th scope="col" class="relative px-6 py-3">
                    <span class="sr-only">Actions</span>
                </th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200 dark:divide-gray-700 dark:bg-gray-800">
            @foreach ($shifts as $shift)
                <tr>
                    <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">
                        {{ $shift->name }}
                    </td>
                    <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">
                        {{ $shift->start_time }}
                    </td>
                    <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">
                        {{ $shift->end_time ?? '-' }}
                    </td>
                    <td class="relative flex justify-end gap-2 px-6 py-4">
                        <x-button type="button" onclick="showQrShiftFn{{ $shift->id }}(true, '{{ $shift->id }}')">
                            Qr Code
                        </x-button>
                        <x-button wire:click="edit({{ $shift->id }})" wire:loading.attr="disabled">
                            Edit
                        </x-button>
                        <x-danger-button wire:click="confirmDeletion({{ $shift->id }}, '{{ $shift->name }}')" wire:loading.attr="disabled">
                            Delete
                        </x-danger-button>
                    </td>
                </tr>

                <div style="display: none;" class="fixed inset-0 z-50 flex justify-center w-full h-screen mt-4 overflow-y-auto modalShowQr{{ $shift->id }} jetstream-modal">
                    <div class="px-10">
                        <div style="display: none;" class="fixed inset-0 transition-all transform modalShowQr{{ $shift->id }}" onclick="showQrShiftFn{{ $shift->id }}(false, '{{ $shift->id }}')">
                            <div class="absolute inset-0 bg-gray-500 opacity-75 dark:bg-gray-900"></div>
                        </div>

                        <div style="display: none;" class="mb-6 overflow-hidden transition-all transform bg-white pt-10 pb-10 rounded-lg shadow-xl modalShowQr{{ $shift->id }} dark:bg-gray-800 sm:mx-auto sm:w-full">
                            <div class="px-6 py-4 mb-5">
                                <div class="flex justify-center w-full mt-6">
                                    <div id="qrcodeShift{{ $shift->barcode->id }}" class="bg-transparent w-80 h-80"></div>
                                </div>
                                <script>
                                    function showQrShiftFn{{ $shift->id }}(show, id) {
                                        const modals = document.getElementsByClassName('modalShowQr'+id);

                                        for (let i = 0; i < modals.length; i++) {
                                            if (show) {
                                                modals[i].style.display = 'block';
                                            } else {
                                                modals[i].style.display = 'none';
                                            }
                                        }

                                        let element = document.getElementById("qrcodeShift{{ $shift->barcode->id }}");

                                        element.innerHTML = '';

                                        new QRCode(element, {
                                            text: "{{ $shift->barcode->value }}",
                                            width: 320,
                                            height: 320,
                                            colorDark : "#000000",
                                            colorLight : "#ffffff",
                                            correctLevel : QRCode.CorrectLevel.H
                                        });
                                    }

                                </script>
                                <div class="flex justify-center gap-3 mt-5">
                                    <x-secondary-button onclick="showQrShiftFn{{ $shift->id }}(false, '{{ $shift->id }}')" wire:loading.attr="disabled">
                                        {{ __('Tutup') }}
                                    </x-secondary-button>
                                    <x-button href="{{ route('admin.barcodes.download', $shift->barcode->id) }}">
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

    <x-confirmation-modal wire:model="confirmingDeletion">
        <x-slot name="title">
            Hapus Acara
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
            Acara Baru
        </x-slot>

        <form wire:submit="create">
            <x-slot name="content">
                <div class="flex flex-col gap-4 mt-4 sm:flex-row sm:gap-3">
                    <div class="w-full">
                        <x-label for="name">Nama Acara</x-label>
                        <x-input id="name" class="block w-full mt-1" type="text" wire:model="form.name" />
                        @error('form.name')
                            <x-input-error for="form.name" class="mt-2" message="{{ $message }}" />
                        @enderror
                    </div>
                    <div class="w-full">
                        <x-label for="date">{{ __('Date') }}</x-label>
                        <x-input id="date" class="block w-full mt-1" type="date" wire:model="form.date"
                            required />
                        @error('form.date')
                            <x-input-error for="form.date" class="mt-2" message="{{ $message }}" />
                        @enderror
                    </div>
                </div>
                <div class="flex flex-col gap-4 mt-4 sm:flex-row sm:gap-3">
                    <div class="w-full">
                        <x-label for="start_time">{{ __('Time Start') }}</x-label>
                        <x-input id="start_time" class="block w-full mt-1" type="time" wire:model="form.start_time"
                            required />
                        @error('form.start_time')
                            <x-input-error for="form.start_time" class="mt-2" message="{{ $message }}" />
                        @enderror
                    </div>
                    <div class="w-full">
                        <x-label for="end_time">{{ __('Time End') }}</x-label>
                        <x-input id="end_time" class="block w-full mt-1" type="time" wire:model="form.end_time" />
                        @error('form.end_time')
                            <x-input-error for="form.end_time" class="mt-2" message="{{ $message }}" />
                        @enderror
                    </div>
                </div>
            </x-slot>

            <x-slot name="footer">
                <x-secondary-button wire:click="$toggle('creating')" wire:loading.attr="disabled">
                    {{ __('Cancel') }}
                </x-secondary-button>

                <x-button class="ml-2" wire:click="create" wire:loading.attr="disabled">
                    {{ __('Confirm') }}
                </x-button>
            </x-slot>
        </form>
    </x-dialog-modal>

    <x-dialog-modal wire:model="editing">
        <x-slot name="title">
            Edit Acara
        </x-slot>

        <form wire:submit.prevent="update" id="shift-edit">
            <x-slot name="content">
                <div>
                    <x-label for="name">Nama Acara</x-label>
                    <x-input id="name" class="block w-full mt-1" type="text" wire:model="form.name" />
                    @error('form.name')
                        <x-input-error for="form.name" class="mt-2" message="{{ $message }}" />
                    @enderror
                </div>
                <div class="flex flex-col gap-4 mt-4 sm:flex-row sm:gap-3">
                    <div class="w-full">
                        <x-label for="start_time">{{ __('Time Start') }}</x-label>
                        <x-input id="start_time" class="block w-full mt-1" type="time" wire:model="form.start_time"
                            required />
                        @error('form.start_time')
                            <x-input-error for="form.start_time" class="mt-2" message="{{ $message }}" />
                        @enderror
                    </div>
                    <div class="w-full">
                        <x-label for="end_time">{{ __('Time End') }}</x-label>
                        <x-input id="end_time" class="block w-full mt-1" type="time" wire:model="form.end_time" />
                        @error('form.end_time')
                            <x-input-error for="form.end_time" class="mt-2" message="{{ $message }}" />
                        @enderror
                    </div>
                </div>
            </x-slot>

            <x-slot name="footer">
                <x-secondary-button wire:click="$toggle('editing')" wire:loading.attr="disabled">
                    {{ __('Cancel') }}
                </x-secondary-button>

                <x-button class="ml-2" wire:click="update" wire:loading.attr="disabled">
                    {{ __('Confirm') }}
                </x-button>
            </x-slot>
        </form>
    </x-dialog-modal>
</div>

@push('scripts')
    <script>
        Livewire.on('reload-page', () => {
            window.location.reload();
        });
    </script>
@endpush
