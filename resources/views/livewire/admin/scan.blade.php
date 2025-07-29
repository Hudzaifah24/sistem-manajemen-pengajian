<div>
    @php
        use Carbon\Carbon; // Tambahkan baris ini untuk mengimpor kelas Carbon
        $currentShiftId = $currentShiftId ?? null;
    @endphp

    <div class="p-6 lg:p-8">

        {{-- Dropdown Pilihan Sesi Pengajian (Shift) --}}
        <div class="mb-6">
            <x-label for="shift-select" value="{{ __('Pilih Sesi Pengajian') }}" />
            <x-select id="shift-select" wire:model.live="currentShiftId" class="mt-1 block w-full">
                <option value="">-- Pilih Sesi --</option>
                @foreach ($shifts ?? [] as $shift)
                    <option value="{{ $shift->id }}">{{ $shift->name }}
                        ({{ Carbon::parse($shift->start_time)->format('H:i') }} -
                        {{ Carbon::parse($shift->end_time)->format('H:i') }})
                    </option>
                @endforeach
            </x-select>
            @error('currentShiftId')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <div class="flex flex-col items-center justify-center mb-6">
            {{-- Pesan error scanner dari Livewire --}}
            @if ($scannerError ?? null)
                <div id="scanner-error" class="text-red-500 text-lg mb-4 text-center">
                    {{ $scannerError }}
                </div>
            @else
                <div id="scanner-error" class="text-red-500 text-lg mb-4 text-center"></div>
            @endif

            {{-- Indikator status scanner --}}
            <div class="mb-4 text-center text-sm font-semibold">
                @if ($isScanning ?? false)
                    {{-- <--- PERBAIKAN DI SINI --}}
                    <span class="text-green-600 dark:text-green-400">Pemindai Aktif...</span>
                @else
                    <span class="text-yellow-600 dark:text-yellow-400">Pemindai Tidak Aktif. Pilih sesi untuk
                        memulai.</span>
                @endif
            </div>

            {{-- Div tempat HTML5-QRCode akan merender video dan kontrol --}}
            <div id="scanner" wire:ignore
                class="w-[320px] mx-auto border-2 border-gray-300 dark:border-gray-600 rounded-lg shadow-inner overflow-hidden">
            </div>
        </div>

        {{-- Tampilan status absensi dari Livewire --}}
        @if ($attendanceMessage ?? false)
            <div
                class="mt-4 p-3 rounded-md text-center
                @if ($attendanceStatus === 'success') bg-green-100 text-green-800 dark:bg-green-700 dark:text-green-100
                @elseif ($attendanceStatus === 'warning') bg-yellow-100 text-yellow-800 dark:bg-yellow-700 dark:text-yellow-100
                @elseif ($attendanceStatus === 'error') bg-red-100 text-red-800 dark:bg-red-700 dark:text-red-100 @endif">
                {{ $attendanceMessage }}
                @if ($scannedUserName)
                    <p class="font-bold text-xl mt-1">{{ $scannedUserName }}</p>
                @endif
            </div>
        @endif

        <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mt-8 mb-4">Absensi Hari Ini
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
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Nama Jamaah</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Waktu Hadir</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Status</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Sesi Pengajian</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse (($todayAttendances ?? [])  as $attendance)
                        <tr>
                            <td
                                class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                {{ $attendance->user->name ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $attendance->time_in ? Carbon::parse($attendance->time_in)->format('H:i:s') : '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ __($attendance->status) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $attendance->shift->name ?? '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4"
                                class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 text-center">
                                Belum ada absensi hari ini untuk sesi ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{-- <div class="mt-4">
            {{ $todayAttendances->links() }}
        </div> --}}
    </div>
</div>
@pushOnce('scripts')
    {{-- Library untuk memindai QR code dari kamera --}}
    <script src="https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <script>
        let html5QrCode;
        const scannerId = 'scanner'; // ID elemen div untuk scanner
        const errorMsgElement = document.getElementById('scanner-error');

        // Fungsi untuk memulai scanner
        function startHtml5QrcodeScanner() {
            if (!html5QrCode) {
                html5QrCode = new Html5Qrcode(scannerId);
            }

            // Cek apakah scanner sudah berjalan
            if (html5QrCode.getState() === Html5QrcodeScannerState.SCANNING) {
                console.log('Scanner already running.');
                return;
            }

            // Konfigurasi scanner
            const config = {
                formatsToSupport: [Html5QrcodeSupportedFormats.QR_CODE],
                fps: 15, // Frames per second
                aspectRatio: 1, // Rasio aspek 1:1 untuk kotak pemindaian
                qrbox: {
                    width: 280,
                    height: 280
                }, // Ukuran kotak pemindaian
                supportedScanTypes: [Html5QrcodeScanType.SCAN_TYPE_CAMERA]
            };

            html5QrCode.start({
                    facingMode: "environment" // Menggunakan kamera belakang
                },
                config,
                (decodedText, decodedResult) => {
                    console.log(`QR Code scanned: ${decodedText}`, decodedResult);
                    // Hentikan scanner sementara setelah berhasil scan
                    // html5QrCode.pause(true);
                    // Kirim data QR ke Livewire menggunakan Livewire.dispatch
                    Livewire.dispatch('qrCodeScanned', {
                        qrData: decodedText
                    });
                },
                (errorMessage) => {
                    // console.warn(`QR Code scanning error: ${errorMessage}`);
                    // Error kecil tidak perlu ditampilkan terus-menerus
                }
            ).then(() => {
                console.log('QR Code scanner started successfully.');
                Livewire.dispatch('scannerStarted'); // Beri tahu Livewire bahwa scanner sudah aktif
                errorMsgElement.innerText = ''; // Bersihkan pesan error
            }).catch((err) => {
                console.error("Error starting QR Code scanner: ", err);
                errorMsgElement.innerText =
                    "Gagal memulai kamera. Pastikan izin kamera diberikan dan tidak ada aplikasi lain yang menggunakan kamera.";
                Livewire.dispatch('scannerError', {
                    message: "Gagal memulai kamera."
                }); // Beri tahu Livewire ada error
                Livewire.dispatch('scannerStopped'); // Pastikan Livewire tahu scanner tidak aktif
            });
        }

        // Fungsi untuk menghentikan scanner
        function stopHtml5QrcodeScanner() {
            if (html5QrCode && html5QrCode.getState() !== Html5QrcodeScannerState.NOT_STARTED) {
                html5QrCode.stop().then(() => {
                    console.log("QR Code scanner stopped.");
                    Livewire.dispatch('scannerStopped'); // Beri tahu Livewire scanner berhenti
                }).catch((err) => {
                    console.error("Error stopping QR Code scanner: ", err);
                });
            } else {
                console.log("Scanner not running or not initialized.");
            }
        }

        // Livewire load event: Inisialisasi scanner jika shift sudah dipilih
        document.addEventListener('livewire:load', function() {
            // Ambil nilai currentShiftId langsung dari instance Livewire yang aktif
            const currentShiftIdFromLivewire = Livewire.first().get('currentShiftId');
            if (currentShiftIdFromLivewire) {
                startHtml5QrcodeScanner();
            } else {
                errorMsgElement.innerText = 'Pilih sesi pengajian terlebih dahulu untuk memulai pemindai.';
                Livewire.dispatch('scannerStopped'); // Pastikan Livewire tahu scanner tidak aktif
            }
        });

        // Livewire unmount event: Pastikan scanner berhenti saat komponen dihancurkan
        document.addEventListener('livewire:unmount', function() {
            stopHtml5QrcodeScanner();
        });

        // Livewire listeners untuk mengontrol scanner dari backend
        Livewire.on('startHtml5QrcodeScanner', () => {
            startHtml5QrcodeScanner();
        });

        Livewire.on('stopHtml5QrcodeScanner', () => {
            stopHtml5QrcodeScanner();
        });

        Livewire.on('restartScannerAfterDelay', () => {
            stopHtml5QrcodeScanner(); // Pastikan berhenti dulu
            setTimeout(() => {
                // Cek currentShiftId dari instance Livewire yang aktif
                const currentShiftIdFromLivewire = Livewire.first().get('currentShiftId');
                if (currentShiftIdFromLivewire) {
                    startHtml5QrcodeScanner();
                } else {
                    errorMsgElement.innerText =
                        'Pilih sesi pengajian terlebih dahulu untuk memulai pemindai.';
                    Livewire.dispatch('scannerStopped'); // Pastikan Livewire tahu scanner tidak aktif
                }
            }, 3000); // Jeda 3 detik sebelum restart
        });

        // Observer untuk menata ulang tombol-tombol HTML5-QRCode
        const observer = new MutationObserver((mutationList, observer) => {
            const classes = ['text-white', 'bg-blue-500', 'dark:bg-blue-400', 'rounded-md', 'px-3', 'py-1',
                'm-1'
            ]; // Tambah margin
            for (const mutation of mutationList) {
                if (mutation.type === 'childList') {
                    const startBtn = document.querySelector('#html5-qrcode-button-camera-start');
                    const stopBtn = document.querySelector('#html5-qrcode-button-camera-stop');
                    const fileBtn = document.querySelector('#html5-qrcode-button-file-selection');
                    const permissionBtn = document.querySelector('#html5-qrcode-button-camera-permission');

                    if (startBtn) {
                        startBtn.classList.add(...classes);
                        if (stopBtn) stopBtn.classList.add(...classes, 'bg-red-500');
                        if (fileBtn) fileBtn.classList.add(...classes);
                    }

                    if (permissionBtn)
                        permissionBtn.classList.add(...classes);
                }
            }
        });

        // Mulai observer pada elemen scanner
        const scannerDiv = document.getElementById(scannerId);
        if (scannerDiv) {
            observer.observe(scannerDiv, {
                childList: true,
                subtree: true,
            });
        }
    </script>
@endPushOnce
