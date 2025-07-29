<?php

namespace App\Livewire\Admin;

use App\Livewire\Traits\AttendanceDetailTrait;
use App\Models\Attendance; // Akan diganti ke App\Models\Absensi
use App\Models\User;       // Akan diganti ke App\Models\Peserta
use Carbon\Carbon;          // Pastikan Carbon di-import
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;

class DashboardComponent extends Component
{
    use AttendanceDetailTrait;

    // Tambahkan properti untuk tanggal saat ini
    public $date;

    public function mount()
    {
        // Inisialisasi tanggal saat komponen dimuat
        $this->date = Carbon::now();
    }

    public function render()
    {
        // Menggunakan $this->date yang sudah diinisialisasi di mount()
        /** @var Collection<Attendance> */
        $attendances = Attendance::where('date', $this->date->toDateString())->get();

        /** @var Collection<User> */
        $employees = User::where('group', 'user')
            ->with(['division']) // Eager load relasi 'kelompok' (sebelumnya 'division')
            ->paginate(20)
            ->through(function (User $user) use ($attendances) {
                // Asumsi 'attendance' adalah relasi hasOne yang sudah difilter tanggal di model User/Peserta
                // atau Anda bisa mencari di sini jika relasi 'attendance' di model User tidak difilter tanggal
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
            'date' => $this->date, // Pastikan $date dilewatkan ke view
            'employees' => $employees,
            'employeesCount' => $employeesCount,
            'presentCount' => $presentCount,
            'lateCount' => $lateCount,
            'excusedCount' => $excusedCount,
            'sickCount' => $sickCount,
            'absentCount' => $absentCount,
            // 'currentAttendance' => $this->currentAttendance, // Jika ini dari trait, tidak perlu dilewatkan lagi
        ]);
    }

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
                $absensi->user_id = $pesertaId; // Ganti user_id menjadi peserta_id
                $absensi->date = $this->date->toDateString();
                // Jika Anda memiliki sesi pengajian aktif yang sedang diabsen, set ID-nya di sini
                // $absensi->sesi_pengajian_id = $this->activeSesiPengajianId;
            }

            $absensi->status = $newStatus;

            // Atur time_in jika statusnya hadir/terlambat dan belum ada time_in
            if (in_array($newStatus, ['present', 'late']) && !$absensi->time_in) {
                 $absensi->time_in = Carbon::now()->format('H:i:s');
            } else if (!in_array($newStatus, ['present', 'late'])) {
                // Opsional: Jika statusnya izin/sakit/alpa, time_in bisa di-null-kan
                // $absensi->time_in = null;
            }

            $absensi->save();
            session()->flash('message', 'Status absensi berhasil diperbarui.');
        }

    }
}
