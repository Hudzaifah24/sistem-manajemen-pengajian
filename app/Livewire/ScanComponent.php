<?php

namespace App\Livewire;

use App\ExtendedCarbon;
use App\Models\Attendance;
use App\Models\Barcode;
use App\Models\Shift;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Ballen\Distical\Calculator as DistanceCalculator;
use Ballen\Distical\Entities\LatLong;
use Illuminate\Support\Carbon;

class ScanComponent extends Component
{
    public ?Attendance $attendance = null;
    public $shift_id = null;
    public $shifts = null;
    public ?array $currentLiveCoords = null;
    public string $successMsg = '';
    public string $failMsg = '';
    public bool $isAbsence = false;

    public function scan(string $barcode)
    {
        if (is_null($this->shift_id)) {
            return __('Pilih acara terlebih dahulu');
        }

        /** @var Barcode */
        $barcode = Barcode::firstWhere('value', $barcode);
        if (!Auth::check() || !$barcode) {
            return 'Invalid barcode';
        }

        // $barcodeLocation = new LatLong($barcode->latLng['lat'], $barcode->latLng['lng']);
        // $userLocation = new LatLong($this->currentLiveCoords[0], $this->currentLiveCoords[1]);

        // if (($distance = $this->calculateDistance($userLocation, $barcodeLocation)) > $barcode->radius) {
        //     return __('Location out of range') . ": $distance" . "m. Max: $barcode->radius" . "m";
        // }

        $now = Carbon::now();

        /** @var Attendance */
        $existingAttendance = Attendance::where('user_id', Auth::user()->id)
            ->where('shift_id', $this->shift_id)
            ->where('barcode_id', $barcode->id);


        if ($barcode->shift->id == $this->shift_id) {
            if (!$existingAttendance->where('date', date('Y-m-d'))->first()) {
                $shift = Shift::where('id', $this->shift_id)->whereDate('date', $now->toDateString())->first();

                $endTime = Carbon::now()->setTimeFromTimeString($shift->end_time);

                if ($now > $endTime) {
                    if ($existingAttendance->first()) {
                        $this->failMsg = __('Acara sudah lewat, Anda sudah absen diacara ini.');
                        return true;
                    } else {
                        $this->failMsg = __('Acara sudah selesai, anda dianggap alpa.');
                        return true;
                    }
                } else {
                    $attendance = $this->createAttendance($barcode);
                    $this->successMsg = __('Attendance In Successful');
                    $this->setAttendance($attendance->fresh());
                    Attendance::clearUserAttendanceCache(Auth::user(), Carbon::parse($attendance->date));
                    return true;
                }
            } else {
                $attendance = $existingAttendance;
                // $attendance->update([
                //     'time_out' => date('H:i:s'),
                // ]);
                $this->failMsg = __('Anda sudah absen diacara ini');
                return true;
            }
        } else {
            $this->failMsg = __('Acara tidak cocok, pilih acara dengan benar.');
            return true;
        }
    }

    public function calculateDistance(LatLong $a, LatLong $b)
    {
        $distanceCalculator = new DistanceCalculator($a, $b);
        $distanceInMeter = floor($distanceCalculator->get()->asKilometres() * 1000); // convert to meters
        return $distanceInMeter;
    }

    /** @return Attendance */
    public function createAttendance(Barcode $barcode)
    {
        $now = Carbon::now();
        $date = $now->format('Y-m-d');
        $timeIn = $now->format('H:i:s');
        /** @var Shift */
        $shift = Shift::find($this->shift_id);
        $status = Carbon::now()->setTimeFromTimeString($shift->start_time)->lt($now) ? 'late' : 'present';
        return Attendance::create([
            'user_id' => Auth::user()->id,
            'barcode_id' => $barcode->id,
            'date' => $date,
            'time_in' => $timeIn,
            'time_out' => $shift->end_time,
            'shift_id' => $shift->id,
            // 'latitude' => $this->currentLiveCoords[0] ? doubleval($this->currentLiveCoords[0]) : null,
            // 'longitude' => $this->currentLiveCoords[0] ? doubleval($this->currentLiveCoords[1]) : null,
            'status' => $status,
            'note' => null,
            'attachment' => null,
        ]);
    }

    protected function setAttendance(Attendance $attendance)
    {
        $this->attendance = $attendance;
        $this->shift_id = $attendance->shift_id;
        $this->isAbsence = $attendance->status !== 'present' && $attendance->status !== 'late';
    }

    public function getAttendance()
    {
        if (is_null($this->attendance)) {
            return null;
        }
        return [
            'time_in' => $this->attendance?->time_in,
            'time_out' => $this->attendance?->time_out,
        ];
    }

    public function mount()
    {
        $now = Carbon::now();

        $this->shifts = Shift::whereDate('date', $now->toDateString())->get();

        /** @var Attendance */
        $attendance = Attendance::where('user_id', Auth::user()->id)
            ->where('date', date('Y-m-d'))->where('shift_id', $this->shift_id)->first();
        if ($attendance) {
            $this->setAttendance($attendance);
        } else {
            // get closest shift from current time
            $closest = ExtendedCarbon::now()
                ->closestFromDateArray($this->shifts->pluck('start_time')->toArray());

            $this->shift_id = $this->shifts
                ->where(fn (Shift $shift) => $shift->start_time == $closest->format('H:i:s'))
                ->first()->id ?? null;
        }
    }

    public function render()
    {
        return view('livewire.scan');
    }
}
