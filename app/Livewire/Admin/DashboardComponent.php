<?php

namespace App\Livewire\Admin;

use App\Livewire\Traits\AttendanceDetailTrait;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;
use Livewire\WithPagination; // Tambahkan ini jika Anda menggunakan paginasi di dashboard

class DashboardComponent extends Component
{
    use AttendanceDetailTrait;
    use WithPagination; // Pastikan ini ada jika tabel di dashboard pakai paginasi

    public $date;

    // Properti baru untuk data chart bulanan
    public $monthlyLabels = [];
    public $monthlyPresentData = [];
    public $monthlyExcusedData = [];
    public $monthlySickData = [];
    public $monthlyAbsentData = [];

    public function mount()
    {
        $this->date = Carbon::now();
        $this->loadMonthlyAttendanceStats(); // Panggil metode untuk memuat data bulanan
    }

    /**
     * Metode untuk memuat data statistik absensi bulanan untuk 12 bulan terakhir.
     */
    private function loadMonthlyAttendanceStats()
    {
        $labels = [];
        $present = [];
        $excused = [];
        $sick = [];
        $absent = [];

        // Loop untuk 12 bulan terakhir
        for ($i = 11; $i >= 0; $i--) {
            $monthDate = Carbon::now()->subMonths($i);
            $monthStart = $monthDate->startOfMonth()->toDateString();
            $monthEnd = $monthDate->endOfMonth()->toDateString();

            $labels[] = $monthDate->format('M Y'); // Format: Jan 2023, Feb 2023

            // Ambil data absensi untuk bulan ini
            $monthlyAttendances = Attendance::whereBetween('date', [$monthStart, $monthEnd])->get();

            // Hitung jumlah untuk setiap status
            $present[] = $monthlyAttendances->where('status', 'present')->count();
            $excused[] = $monthlyAttendances->where('status', 'excused')->count();
            $sick[] = $monthlyAttendances->where('status', 'sick')->count();

            // Untuk 'absent', kita perlu menghitung total user dan mengurangi yang sudah ada statusnya
            // Ini bisa jadi lebih kompleks jika 'absent' berarti 'tidak ada record sama sekali'
            // Untuk kesederhanaan, kita asumsikan 'absent' adalah status yang dicatat.
            // Jika 'absent' berarti tidak ada record, Anda perlu query total user dan mengurangi yang hadir/izin/sakit.
            $absent[] = $monthlyAttendances->where('status', 'absent')->count();
        }

        $this->monthlyLabels = $labels;
        $this->monthlyPresentData = $present;
        $this->monthlyExcusedData = $excused;
        $this->monthlySickData = $sick;
        $this->monthlyAbsentData = $absent;
    }


    public function render()
    {
        /** @var Collection<Attendance> */
        $attendances = Attendance::where('date', $this->date->toDateString())->get();

        /** @var Collection<User> */
        $employees = User::where('group', 'user')
            ->with(['division']) // Eager load relasi 'kelompok' (sebelumnya 'division')
            ->paginate(20)
            ->through(function (User $user) use ($attendances) {
                $user->setAttribute(
                    'attendance',
                    $attendances
                        ->where(fn (Attendance $attendance) => $attendance->user_id === $user->id)
                        ->first(),
                );
                return $user;
            });

        $employeesCount = User::where('group', 'user')->count();
        $presentCount = $attendances->where(fn ($attendance) => $attendance->status === 'present')->count();
        $lateCount = $attendances->where(fn ($attendance) => $attendance->status === 'late')->count();
        $excusedCount = $attendances->where(fn ($attendance) => $attendance->status === 'excused')->count();
        $sickCount = $attendances->where(fn ($attendance) => $attendance->status === 'sick')->count();

        // Perhitungan absentCount yang lebih akurat:
        // Jumlah total peserta dikurangi yang sudah punya status absensi hari ini (present, late, excused, sick)
        $absentCount = $employeesCount - ($presentCount + $lateCount + $excusedCount + $sickCount);

        return view('livewire.admin.dashboard', [
            'date' => $this->date,
            'employees' => $employees,
            'employeesCount' => $employeesCount,
            'presentCount' => $presentCount,
            'lateCount' => $lateCount,
            'excusedCount' => $excusedCount,
            'sickCount' => $sickCount,
            'absentCount' => $absentCount,
            // Teruskan properti data bulanan ke view
            'monthlyLabels' => $this->monthlyLabels,
            'monthlyPresentData' => $this->monthlyPresentData,
            'monthlyExcusedData' => $this->monthlyExcusedData,
            'monthlySickData' => $this->monthlySickData,
            'monthlyAbsentData' => $this->monthlyAbsentData,
        ]);
    }

    // Metode updateAttendanceStatus yang sudah ada
    public function updateAttendanceStatus($pesertaId, $newStatus)
    {
        $peserta = User::find($pesertaId);

        if (!$peserta) {
            session()->flash('error', 'Peserta tidak ditemukan.');
            return;
        }

        $absensi = Attendance::where('user_id', $pesertaId)
                             ->whereDate('date', $this->date->toDateString())
                             ->first();

        if ($newStatus === '-' && $absensi) {
            $absensi->delete();
            session()->flash('message', 'Absensi berhasil dihapus.');
        }
        else if ($newStatus !== '-') {
            if (!$absensi) {
                $absensi = new Attendance();
                $absensi->user_id = $pesertaId;
                $absensi->date = $this->date->toDateString();
                // $absensi->sesi_pengajian_id = $this->activeSesiPengajianId; // Jika ada
            }

            $absensi->status = $newStatus;

            if (in_array($newStatus, ['present', 'late']) && !$absensi->time_in) {
                $absensi->time_in = Carbon::now()->format('H:i:s');
            } else if (!in_array($newStatus, ['present', 'late'])) {
                // $absensi->time_in = null; // Opsional
            }

            $absensi->save();
            session()->flash('message', 'Status absensi berhasil diperbarui.');
        }
        // Setelah update, refresh data bulanan juga jika perlu (misal jika ada filter tahunan)
        // $this->loadMonthlyAttendanceStats();
        // $this->dispatch('$refresh'); // Memaksa refresh komponen
    }
}
