<?php

namespace App\Livewire\Admin;

use App\Livewire\Traits\AttendanceDetailTrait;
use App\Models\Attendance;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Laravel\Jetstream\InteractsWithBanner;
use Livewire\Component;
use Livewire\WithPagination;

class AttendanceComponent extends Component
{
    use AttendanceDetailTrait; // Untuk modal detail absensi
    use WithPagination, InteractsWithBanner; // Untuk paginasi dan notifikasi banner

    // Properti filter
    public ?string $month = null;
    public ?string $week = null;
    public ?string $date = null;
    public ?string $division = null;
    public ?string $search = null;
    public ?string $shift = null;

    /**
     * Metode mount akan dijalankan saat komponen diinisialisasi.
     * Mengatur filter tanggal default ke hari ini.
     */
    public function mount()
    {
        $this->date = Carbon::now()->format('Y-m-d'); // Set default filter ke hari ini
        $this->month = Carbon::now()->format('Y-m'); // Set default filter bulan ke bulan ini
    }

    /**
     * Metode ini dipanggil saat salah satu properti filter diperbarui.
     * Mengatur ulang paginasi dan filter lainnya untuk menghindari konflik.
     */
    public function updating($key): void
    {
        if (in_array($key, ['search', 'division', 'jobTitle', 'shift'])) {
            $this->resetPage();
        }

        // Logika untuk memastikan hanya satu filter waktu yang aktif
        if ($key === 'month') {
            $this->resetPage();
            $this->week = null;
            $this->date = null;
        }
        if ($key === 'week') {
            $this->resetPage();
            $this->month = null;
            $this->date = null;
        }
        if ($key === 'date') {
            $this->resetPage();
            $this->month = null;
            $this->week = null;
        }
    }

    /**
     * Metode untuk memperbarui status absensi secara manual.
     * @param int $employeeId ID karyawan
     * @param string $newStatus Status baru (present, late, excused, sick, absent, atau '-')
     * @param string $attendanceDate Tanggal absensi yang akan diperbarui (format YYYY-MM-DD)
     */
    public function updateAttendanceStatus($employeeId, $newStatus, $attendanceDate)
    {
        $currentTime = Carbon::now();

        if (!$this->shift) {
            $this->dangerBanner(__('Pilih acara terlebih dahulu.'));
            return;
        }

        // Periksa otorisasi: hanya admin yang bisa mengubah status
        if (Auth::user()->isNotAdmin) {
            $this->dangerBanner(__('Akses ditolak. Anda tidak memiliki izin untuk melakukan tindakan ini.'));
            return;
        }

        // Pastikan tanggal absensi valid
        if (empty($attendanceDate) || !Carbon::parse($attendanceDate)->isValid()) {
            $this->dangerBanner(__('Error: Tanggal absensi tidak valid.'));
            return;
        }

        // Cari karyawan
        $employee = User::find($employeeId);
        if (!$employee) {
            $this->dangerBanner(__('Error: Karyawan tidak ditemukan.'));
            return;
        }

        // Cari catatan absensi yang ada untuk karyawan dan tanggal spesifik
        $attendance = Attendance::where('user_id', $employeeId)
                                ->whereDate('date', $attendanceDate)
                                ->first();

        // Logika berdasarkan status baru yang dipilih
        if ($newStatus === '-') {
            // Jika status diatur ke '-', hapus catatan absensi jika ada
            if ($attendance) {
                $attendance->delete();
                $this->banner(__('Status absensi berhasil direset.'));
            } else {
                $this->dangerBanner(__('Tidak ada catatan absensi untuk direset.'));
            }
        } else {
            // Jika catatan absensi sudah ada, perbarui statusnya
            if ($attendance) {
                $attendance->status = $newStatus;
                $attendance->save();
                $this->banner(__('Status absensi berhasil diperbarui.'));
            } else {
                $shift = Shift::find($this->shift);

                Attendance::create([
                    'user_id' => $employeeId,
                    'date' => $attendanceDate,
                    'status' => $newStatus,
                    'shift_id' => $this->shift,
                    'time_in' => $currentTime->format('H:i:s'),
                    'time_out' => $shift->end_time,
                    // 'latitude' => null,
                    // 'longitude' => null,
                    // 'attachment' => null,
                    // 'note' => 'Manual confirmation by admin',
                ]);
                $this->banner(__('Catatan absensi baru berhasil dibuat.'));
            }
        }

        // Setelah update/create/delete, kosongkan cache yang relevan
        // Ini penting agar metode render mengambil data terbaru
        $cacheKey = "attendance-user-{$employeeId}-date-{$attendanceDate}";
        Cache::forget($cacheKey);

        // Jika Anda memiliki cache per minggu atau bulan, Anda juga perlu mengosongkannya
        // jika perubahan ini memengaruhi tampilan minggu/bulan.
        // Contoh:
        // $monthKey = Carbon::parse($attendanceDate)->format('Y-m');
        // Cache::forget("attendance-user-{$employeeId}-month-{$monthKey}");
        // $weekKey = Carbon::parse($attendanceDate)->startOfWeek()->format('Y-W'); // Contoh format week
        // Cache::forget("attendance-user-{$employeeId}-week-{$weekKey}");

        // Refresh komponen untuk memperbarui tampilan tabel
        $this->dispatch('$refresh');
    }

    /**
     * Metode render untuk menampilkan data absensi.
     * Mengambil data karyawan dan absensi berdasarkan filter yang aktif.
     */
    public function render()
    {
        $dates = []; // Inisialisasi array tanggal

        // Tentukan rentang tanggal berdasarkan filter yang aktif
        if ($this->date) {
            $dates = [Carbon::parse($this->date)];
        } else if ($this->week) {
            $start = Carbon::parse($this->week)->startOfWeek();
            $end = Carbon::parse($this->week)->endOfWeek();
            $dates = $start->range($end)->toArray();
        } else if ($this->month) {
            $start = Carbon::parse($this->month)->startOfMonth();
            $end = Carbon::parse($this->month)->endOfMonth();
            $dates = $start->range($end)->toArray();
        } else {
            // Jika tidak ada filter yang dipilih, default ke bulan ini
            $this->month = Carbon::now()->format('Y-m');
            $start = Carbon::parse($this->month)->startOfMonth();
            $end = Carbon::parse($this->month)->endOfMonth();
            $dates = $start->range($end)->toArray();
        }

        // Ambil data karyawan dengan filter
        $employees = User::where('group', 'user')
            ->when($this->search, function (Builder $q) {
                return $q->where('name', 'like', '%' . $this->search . '%');
            })->when($this->division, function (Builder $q) {
                return $q->where('division_id', $this->division);
            })
            ->paginate(20);
            // ->through(function (User $user) use ($dates) { // Gunakan $dates di sini
            //     $attendances = new Collection(); // Inisialisasi collection kosong

                // Tentukan kunci cache berdasarkan filter yang aktif
                // $cacheKey = '';
                // if ($this->date) {
                //     $cacheKey = "attendance-user-{$user->id}-date-{$this->date}";
                // } else if ($this->week) {
                //     $cacheKey = "attendance-user-{$user->id}-week-{$this->week}";
                // } else if ($this->month) {
                //     $my = Carbon::parse($this->month);
                //     $cacheKey = "attendance-user-{$user->id}-month-{$my->month}-{$my->year}";
                // }

                // Ambil data absensi dari cache atau database
                // $attendances = new Collection(Cache::remember(
                //     $cacheKey,
                //     now()->addDay(), // Cache selama 1 hari
                //     function () use ($user, $dates) { // Gunakan $dates di sini
                //         /** @var Collection<Attendance> */
                //         $query = Attendance::where('user_id', $user->id);

                //         // Terapkan filter tanggal yang sesuai
                //         if ($this->date) {
                //             $query->whereDate('date', $this->date);
                //         } else if ($this->week) {
                //             $startOfWeek = Carbon::parse($this->week)->startOfWeek()->toDateString();
                //             $endOfWeek = Carbon::parse($this->week)->endOfWeek()->toDateString();
                //             $query->whereBetween('date', [$startOfWeek, $endOfWeek]);
                //         } else if ($this->month) {
                //             $startOfMonth = Carbon::parse($this->month)->startOfMonth()->toDateString();
                //             $endOfMonth = Carbon::parse($this->month)->endOfMonth()->toDateString();
                //             $query->whereBetween('date', [$startOfMonth, $endOfMonth]);
                //         }

                //         // Tambahkan filter shift jika ada
                //         // if ($this->shift) {
                //         //     $query->where('shift_id', $this->shift);
                //         // }

                //         $attendances = $query->get(['id', 'status', 'date', 'latitude', 'longitude', 'attachment', 'note', 'time_in', 'time_out', 'shift_id']);

                //         // Map atribut tambahan untuk tampilan
                //         return $attendances->map(function (Attendance $v) {
                //             $v->setAttribute('coordinates', $v->lat_lng);
                //             $v->setAttribute('lat', $v->latitude);
                //             $v->setAttribute('lng', $v->longitude);
                //             if ($v->attachment) {
                //                 $v->setAttribute('attachment', $v->attachment_url);
                //             }
                //             if ($v->shift) {
                //                 $v->setAttribute('shift', $v->shift->name);
                //             }
                //             if ($v->time_in) {
                //                 $v->setAttribute('time_in', Carbon::parse($v->time_in)->format('H:i'));
                //             }
                //             if ($v->time_out) {
                //                 $v->setAttribute('time_out', Carbon::parse($v->time_out)->format('H:i'));
                //             }
                //             return $v->getAttributes();
                //         })->toArray();
                //     }
                // ) ?? []);

            //     $user->attendances = $attendances;
            //     return $user;
            // });

        return view('livewire.admin.attendance', [
            'employees' => $employees,
            'dates' => $dates,
            // Anda mungkin perlu meneruskan properti filter lain ke Blade jika digunakan di sana
            'isPerDayFilter' => isset($this->date), // Tambahkan ini agar Blade bisa menggunakannya
        ]);
    }
}
