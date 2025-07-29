<?php

namespace App\Livewire\Admin;

use App\Models\Attendance;
use App\Models\User; // Model untuk jamaah
use App\Models\Shift; // Model untuk sesi pengajian
use Carbon\Carbon;
use Livewire\Component;
use Laravel\Jetstream\InteractsWithBanner; // Untuk notifikasi banner
use Livewire\WithPagination;

class ScanComponent extends Component
{
    use InteractsWithBanner;
    use WithPagination;

    public $scannedUserId = null;
    public $scannedUserName = null;
    public $attendanceStatus = null; // success, warning, error
    public $attendanceMessage = null;
    public $currentShiftId = null; // ID shift yang dipilih oleh petugas
    public $shifts; // Daftar shift untuk dropdown

    // Properti untuk status scanner dan pesan error di UI
    public $scannerError = '';
    public $isScanning = false; // Status apakah scanner sedang aktif

    // Listener untuk event dari JavaScript
    protected $listeners = [
        'qrCodeScanned' => 'processQrCode',
        'scannerStarted' => 'setScanningTrue', // Event dari JS ketika scanner sukses start
        'scannerStopped' => 'setScanningFalse', // Event dari JS ketika scanner sukses stop
        'scannerError' => 'setScannerError' // Event dari JS ketika ada error kamera
    ];

    public function mount()
    {
        // Muat semua shift yang tersedia untuk dropdown
        $this->shifts = Shift::all();

        // Coba set shift default jika ada yang sedang aktif sekarang
        $this->currentShiftId = $this->getActiveShiftId();

        // Inisialisasi status scanner
        $this->isScanning = !empty($this->currentShiftId);
        if (empty($this->currentShiftId)) {
            $this->scannerError = 'Pilih sesi pengajian terlebih dahulu untuk memulai pemindai.';
        }
    }

    /**
     * Metode untuk mendapatkan ID shift yang sedang aktif.
     * Sesuaikan logika ini dengan cara Anda mendefinisikan shift di database.
     */
    private function getActiveShiftId()
    {
        $now = Carbon::now();
        // Contoh sederhana: Cari shift yang jamnya sedang berlangsung sekarang
        $shift = Shift::where('start_time', '<=', $now->format('H:i:s'))
                      ->where('end_time', '>=', $now->format('H:i:s'))
                      ->first();
        return $shift ? $shift->id : null;
    }

    /**
     * Dipanggil saat shift dipilih dari dropdown.
     * Mengontrol status scanner.
     */
    public function updatedCurrentShiftId($value)
    {
        // Reset pesan status dan error
        $this->attendanceMessage = null;
        $this->scannerError = '';

        if (empty($value)) {
            $this->scannerError = 'Pilih sesi pengajian terlebih dahulu untuk memulai pemindai.';
            $this->isScanning = false;
            $this->dispatch('stopHtml5QrcodeScanner'); // Emit event ke JS untuk menghentikan scanner
        } else {
            $this->isScanning = true;
            $this->dispatch('startHtml5QrcodeScanner'); // Emit event ke JS untuk memulai scanner
        }
    }

    // Metode listener dari JS
    public function setScanningTrue() { $this->isScanning = true; }
    public function setScanningFalse() { $this->isScanning = false; }
    public function setScannerError($message) { $this->scannerError = $message; $this->isScanning = false; }


    /**
     * Proses data QR code yang dipindai.
     * @param string $qrData Data yang terkandung dalam QR code (diasumsikan user_id atau nip)
     */
    public function processQrCode($qrData)
    {
        // Reset status sebelumnya
        $this->scannedUserId = null;
        $this->scannedUserName = null;
        $this->attendanceStatus = null;
        $this->attendanceMessage = null;
        $this->scannerError = ''; // Bersihkan error scanner

        if (empty($this->currentShiftId)) {
            $this->attendanceStatus = 'error';
            $this->attendanceMessage = 'Pilih sesi pengajian terlebih dahulu.';
            $this->banner($this->attendanceMessage, 'danger');
            $this->dispatch('restartScannerAfterDelay'); // Coba restart scanner setelah jeda
            return;
        }

        // Cari user berdasarkan data QR
        // Diasumsikan QR berisi user ID (primary key) atau NIP
        $user = User::where('id', $qrData)
                    ->orWhere('nip', $qrData) // Tambahkan ini jika QR bisa berisi NIP
                    ->first();

        if (!$user) {
            $this->attendanceStatus = 'error';
            $this->attendanceMessage = 'QR Code tidak valid atau jamaah tidak ditemukan.';
            $this->banner($this->attendanceMessage, 'danger');
            $this->dispatch('restartScannerAfterDelay');
            return;
        }

        $this->scannedUserId = $user->id;
        $this->scannedUserName = $user->name;

        $today = Carbon::now()->toDateString();

        // Cek apakah jamaah sudah absen untuk hari ini dan shift yang dipilih
        $existingAttendance = Attendance::where('user_id', $user->id)
                                             ->whereDate('date', $today)
                                             ->where('shift_id', $this->currentShiftId)
                                             ->first();

        if ($existingAttendance) {
            $this->attendanceStatus = 'warning';
            $this->attendanceMessage = "{$user->name} sudah absen untuk sesi ini ({$existingAttendance->status}).";
            $this->banner($this->attendanceMessage, 'warning');
            $this->dispatch('restartScannerAfterDelay');
            return;
        }

        // Catat absensi baru
        try {
            Attendance::create([
                'user_id' => $user->id,
                'date' => $today,
                'time_in' => Carbon::now()->format('H:i:s'), // Waktu scan sebagai waktu masuk
                'status' => 'present', // Default status 'present' saat scan
                'shift_id' => $this->currentShiftId, // Set shift ID
                // Tambahkan kolom lain yang relevan seperti latitude, longitude, attachment jika diperlukan
                // 'latitude' => $latitude,
                // 'longitude' => $longitude,
                // 'note' => 'Absensi via QR Scan Petugas',
            ]);

            $this->attendanceStatus = 'success';
            $this->attendanceMessage = "Absensi {$user->name} berhasil dicatat!";
            $this->banner($this->attendanceMessage, 'success');
        } catch (\Exception $e) {
            $this->attendanceStatus = 'error';
            $this->attendanceMessage = 'Terjadi kesalahan saat mencatat absensi: ' . $e->getMessage();
            $this->banner($this->attendanceMessage, 'danger');
        } finally {
            // Setelah proses selesai (sukses/gagal), restart scanner setelah jeda
            $this->dispatch('restartScannerAfterDelay');
        }
    }

    public function render()
    {
        $today = Carbon::now()->toDateString();
        $todayAttendances = Attendance::whereDate('date', $today)
                                      ->when($this->currentShiftId, function ($query) {
                                          $query->where('shift_id', $this->currentShiftId);
                                      })
                                      ->with('user', 'shift')
                                      ->orderBy('time_in', 'desc')
                                      ->paginate(10); // Contoh paginasi

        return view('livewire.admin.scan', [
            'todayAttendances' => $todayAttendances,
            'currentShiftId' => $this->currentShiftId,
            'shifts' => $this->shifts,
            'scannerError' => $this->scannerError,
            'isScanning' => $this->isScanning,
        ]);
    }
}
