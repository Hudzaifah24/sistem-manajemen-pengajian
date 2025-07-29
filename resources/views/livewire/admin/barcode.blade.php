<div class="p-6 lg:p-8">
  <script src="https://cdn.jsdelivr.net/npm/qrcodejs/qrcode.min.js"></script>
  <x-button class="mb-4 mr-2" href="{{ route('admin.barcodes.create') }}">
    Buat Barcode Baru
  </x-button>
  <x-secondary-button class="mb-4">
    <a href="{{ route('admin.barcodes.downloadall') }}">Download Semua</a>
  </x-secondary-button>
  <div class="grid grid-cols-1 gap-4 sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-3">
    @foreach ($barcodes as $barcode)
      <div
        class="flex flex-col p-4 bg-white rounded-lg shadow hover:bg-gray-100 dark:bg-gray-800 dark:shadow-gray-600 hover:dark:bg-gray-700">

        <div class="flex items-center justify-center gap-2 mt-4">
          <x-secondary-button href="{{ route('admin.barcodes.download', $barcode->id) }}">
            Download
          </x-secondary-button>
          <x-button href="{{ route('admin.barcodes.edit', $barcode->id) }}">
            Edit
          </x-button>
          <x-danger-button wire:click="confirmDeletion({{ $barcode->id }}, '{{ $barcode->name }}')">
            Delete
          </x-danger-button>
        </div>
        <div class="container flex items-center justify-center p-4">
          <div id="qrcode{{ $barcode->id }}" class="w-64 h-64 bg-transparent">
          </div>
          <script>
            new QRCode(document.getElementById("qrcode{{ $barcode->id }}"), {
                text: "{{ $barcode->value }}",
                colorDark : "#000000",
                colorLight : "#ffffff",
                correctLevel : QRCode.CorrectLevel.H
            });
          </script>
        </div>
        <h3 class="mb-3 text-lg font-semibold leading-tight text-center text-gray-800 dark:text-white">
          {{ $barcode->name }}
        </h3>
        <ul class="pl-4 list-disc dark:text-gray-400">
          <li>
            <a href="https://www.google.com/maps/search/?api=1&query={{ $barcode->latitude }},{{ $barcode->longitude }}"
              target="_blank" class="hover:text-blue-500 hover:underline">
              {{ __('Coords') . ': ' . $barcode->latitude . ', ' . $barcode->longitude }}
            </a>
          </li>
          <li> {{ __('Radius (meter)') }}: {{ $barcode->radius }}</li>
        </ul>
      </div>
    @endforeach
  </div>

  <x-confirmation-modal wire:model="confirmingDeletion">
    <x-slot name="title">
      Hapus Barcode
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
</div>
