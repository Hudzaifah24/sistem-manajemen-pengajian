<div class="p-6 lg:p-8">
  <script src="https://cdn.jsdelivr.net/npm/qrcodejs/qrcode.min.js"></script>
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
      </div>
    @endforeach
  </div>
</div>
