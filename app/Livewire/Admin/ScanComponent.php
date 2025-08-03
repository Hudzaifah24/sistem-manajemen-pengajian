<?php

namespace App\Livewire\Admin;

use App\Models\Attendance;
use App\Models\Barcode;
use App\Models\Shift;
use Carbon\Carbon;
use Livewire\Component;
use Laravel\Jetstream\InteractsWithBanner;
use Livewire\WithPagination;

class ScanComponent extends Component
{
    use InteractsWithBanner;
    use WithPagination;

    public $attendanceStatus = null;
    public $attendanceMessage = null;
    public $currentShiftId = null;
    public $shiftId = null;
    public $shifts;
    public $barcodes;
    public $attendance;

    public $scannerError = '';
    public $isScanning = false;

    protected $listeners = [
        'codeHasSaved' => 'setCodeHasSavedProcess',
    ];

    public function mount()
    {
        $this->shifts = Shift::all();
        $this->barcodes = Barcode::with(['user'])->get();

        $this->isScanning = !empty($this->currentShiftId);
        if (empty($this->currentShiftId)) {
            $this->scannerError = 'Pilih sesi pengajian terlebih dahulu untuk memulai pemindai.';
        }
    }

    public function updatedCurrentShiftId($value)
    {
        $this->attendanceMessage = null;
        $this->scannerError = '';

        if (empty($value)) {
            $this->scannerError = 'Pilih sesi pengajian terlebih dahulu untuk memulai pemindai.';
            $this->shiftId = null;
            $this->isScanning = false;
        } else {
            $this->shiftId = $value;
            $this->isScanning = true;
        }

        $this->dispatch('shiftHasSelected', $value);
    }

    public function setScanningTrue() { $this->isScanning = true; }
    public function setScanningFalse() { $this->isScanning = false; }
    public function setScannerError($message) { $this->scannerError = $message; $this->isScanning = false; }

    public function setCodeHasSavedProcess($barcodeValue) {
        $this->attendanceStatus = null;
        $this->attendanceMessage = null;
        $this->scannerError = '';

        $currentTime = Carbon::now();

        $barcode = Barcode::where('value', $barcodeValue)->first();

        $isAttendance = Attendance::whereDate('date', $currentTime->toDateString())
                                        ->where('shift_id', $this->shiftId)
                                        ->where('barcode_id', $barcode->id)
                                        ->first();

        $shift = Shift::find($this->shiftId);

        if (!$isAttendance) {
            Attendance::create([
                'barcode_id' => $barcode->id,
                'shift_id' => $this->shiftId,
                'user_id' => $barcode->user_id,
                'date' => $currentTime->toDateString(),
                'time_in' => $currentTime->format('H:i:s'),
                'time_out' => $shift->end_time,
                'status' => 'present',
            ]);

            $this->attendanceStatus = 'success';
            $this->attendanceMessage = "Peserta berhasil absen.";
            $this->banner($this->attendanceMessage);
        } else {
            $this->attendanceStatus = 'warning';
            $this->attendanceMessage = "Peserta sudah absen di acara ini.";
            $this->warningBanner($this->attendanceMessage);
        }

    }

    public function render()
    {
        $today = Carbon::now()->toDateString();
        $todayAttendances = Attendance::whereDate('date', $today)
                                      ->when($this->shiftId, function ($query) {
                                          $query->where('shift_id', $this->shiftId);
                                      })
                                      ->with('user', 'shift')
                                      ->orderBy('time_in', 'desc')
                                      ->paginate(10);

        return view('livewire.admin.scan', [
            'currentShiftId' => $this->currentShiftId,

            'shifts' => $this->shifts,
            'shiftId' => $this->shiftId,
            'barcodes' => $this->barcodes,
            'todayAttendances' => $todayAttendances,

            'scannerError' => $this->scannerError,
            'isScanning' => $this->isScanning,
        ]);
    }
}
