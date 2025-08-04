<div>
    @php
        use Carbon\Carbon;
    @endphp

    <div class="p-6 lg:p-8">
        <div class="mb-6">
            <x-label for="shift-select" value="{{ __('Pilih Sesi Pengajian') }}" />
            <x-select id="shift-select" wire:model.live="currentShiftId" class="block w-full mt-1">
                <option value="">-- Pilih Sesi --</option>
                @foreach ($shifts ?? [] as $shift)
                    <option value="{{ $shift->id }}">{{ $shift->name }}
                        ({{ Carbon::parse($shift->start_time)->format('H:i') }} -
                        {{ Carbon::parse($shift->end_time)->format('H:i') }})
                    </option>
                @endforeach
            </x-select>
            @error('currentShiftId')
                <span class="text-sm text-red-500">{{ $message }}</span>
            @enderror
        </div>

        <div class="flex flex-col items-center justify-center mb-6">
            @if ($scannerError ?? null)
                <div id="scanner-error" class="mb-4 text-lg text-center text-red-500">
                    {{ $scannerError }}
                </div>
            @else
                <div id="scanner-error" class="mb-4 text-lg text-center text-red-500"></div>
            @endif

            <div class="mb-4 text-sm font-semibold text-center">
                @if ($isScanning)
                    <span class="text-green-600 dark:text-green-400">Pemindai Aktif...</span>
                @else
                    <span class="text-yellow-600 dark:text-yellow-400">Pemindai Tidak Aktif. Pilih sesi untuk
                        memulai.</span>
                @endif
            </div>

            <div id="scanner" wire:ignore
                class="w-full mx-auto overflow-hidden border-2 border-gray-300 rounded-lg shadow-inner dark:border-gray-600">
            </div>
            <div id="result"></div>
        </div>

        @if ($attendanceMessage ?? false)
            <div
                class="mt-4 p-3 rounded-md text-center
                @if ($attendanceStatus === 'success') bg-green-100 text-green-800 dark:bg-green-700 dark:text-green-100
                @elseif ($attendanceStatus === 'warning') bg-yellow-100 text-yellow-800 dark:bg-yellow-700 dark:text-yellow-100
                @elseif ($attendanceStatus === 'error') bg-red-100 text-red-800 dark:bg-red-700 dark:text-red-100 @endif">
                {{ $attendanceMessage }}
            </div>
        @endif

        <h3 class="mt-8 mb-4 text-xl font-semibold text-gray-800 dark:text-gray-200">Absensi Hari Ini
            ({{ Carbon::now()->format('d M Y') }})
            @if ($currentShiftId)
                - Sesi: {{ $shifts->firstWhere('id', $currentShiftId)->name ?? 'N/A' }}
            @endif
        </h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th
                            class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">
                            Nama Jamaah</th>
                        <th
                            class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">
                            Waktu Hadir</th>
                        <th
                            class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">
                            Status</th>
                        <th
                            class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">
                            Sesi Pengajian</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                    @forelse (($todayAttendances ?? [])  as $attendance)
                        <tr>
                            <td
                                class="px-6 py-4 text-sm font-medium text-gray-900 whitespace-nowrap dark:text-gray-100">
                                {{ $attendance->user->name ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap dark:text-gray-400">
                                {{ $attendance->time_in ? Carbon::parse($attendance->time_in)->format('H:i:s') : '-' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap dark:text-gray-400">
                                {{ __($attendance->status) }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap dark:text-gray-400">
                                {{ $attendance->shift->name ?? '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4"
                                class="px-6 py-4 text-sm text-center text-gray-500 whitespace-nowrap dark:text-gray-400">
                                Belum ada absensi hari ini untuk sesi ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@pushOnce('scripts')
    <script src="https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <script>
        const scanner = new Html5Qrcode("scanner");

        const barcodes = @json($barcodes);

        const scannedBarcodes = new Set();

        let isScannerRunning = false;

        const config = {
            fps: 10,
            qrbox: { width: 350, height: 350 },
            formatsToSupport: [Html5QrcodeSupportedFormats.QR_CODE],
        };

        async function onScanSuccess(barcodeValue) {
            if (scannedBarcodes.has(barcodeValue)) {
                console.log(`Barcode ${barcodeValue} sudah pernah discan`);
                return;
            }

            // document.getElementById("result").innerText = "Hasil: " + barcodeValue;

            // Logic DB Tambah Attendance
            const BarcodeMatch = barcodes.find(b => b.value == barcodeValue);
            scannedBarcodes.add(barcodeValue);
            console.log(`Barcode baru ditemukan: ${barcodeValue}`);
            Livewire.dispatch('codeHasSaved', {barcodeValue: barcodeValue});
        }

        Livewire.on('shiftHasSelected', async (value) => {
            if (value[0]) {
                console.log('Shift:', value);

                if (isScannerRunning) {
                    await scanner.stop();
                    isScannerRunning = false;
                }

                await scanner.start(
                    { facingMode: "environment" },
                    config,
                    onScanSuccess
                );
                isScannerRunning = true;
            } else {
                if (isScannerRunning) {
                    await scanner.stop();
                    isScannerRunning = false;
                }

                console.log('Shift null, Scanner di stop.');
            }
        });
    </script>
@endPushOnce
