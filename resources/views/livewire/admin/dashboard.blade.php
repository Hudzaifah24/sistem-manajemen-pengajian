@php
    use Carbon\Carbon;
    // Variabel-variabel ini akan di-pass dari Livewire Component:
    // $presentCount, $excusedCount, $sickCount, $absentCount (untuk statistik chart)
    // Pastikan Livewire Component Anda mengirimkan variabel-variabel ini.
@endphp
<div>
    @pushOnce('styles')
        {{-- Link untuk Leaflet JS (jika masih digunakan, jika tidak bisa dihapus) --}}
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
            integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    @endpushOnce

    {{-- BAGIAN CHART KEHADIRAN SAJA --}}
    {{-- Tambahkan wire:ignore agar Livewire tidak mengganggu inisialisasi Chart.js --}}
    <div class="mb-6 rounded-md bg-white p-6 shadow-md dark:bg-gray-800 flex flex-col items-center justify-center"
        wire:ignore>
        <h4 class="mb-4 text-xl font-semibold text-gray-800 dark:text-gray-200 text-center">Statistik Kehadiran Hari Ini
        </h4>
        {{-- Menggunakan ID dan style dari contoh yang berhasil --}}
        <canvas id="myChart" style="width:100%;max-width:600px"></canvas>
    </div>

    {{-- Header Dashboard yang sudah ada --}}
    <div class="flex flex-col justify-between sm:flex-row">
        <h3 class="mb-4 text-lg font-semibold leading-tight text-gray-800 dark:text-gray-200">
            Absensi Hari Ini
        </h3>
        <h3 class="mb-4 text-lg font-semibold leading-tight text-gray-800 dark:text-gray-200">
            Jumlah Peserta: {{ $employeesCount }} {{-- Menggunakan $employeesCount yang di-pass --}}
        </h3>
    </div>

    {{-- Kotak Statistik Kehadiran yang sudah ada (mengurangi "Terlambat") --}}
    <div class="mb-4 grid grid-cols-2 gap-3 md:grid-cols-3"> {{-- Mengurangi menjadi 3 kolom --}}
        <div
            class="rounded-md bg-green-200 px-8 py-4 text-gray-800 dark:bg-green-900 dark:text-white dark:shadow-gray-700">
            <span class="text-2xl font-semibold md:text-3xl">Hadir: {{ $presentCount }}</span><br>
        </div>
        <div
            class="rounded-md bg-blue-200 px-8 py-4 text-gray-800 dark:bg-blue-900 dark:text-white dark:shadow-gray-700">
            <span class="text-2xl font-semibold md:text-3xl">Izin: {{ $excusedCount }}</span><br>
            <span>Izin/Cuti</span>
        </div>
        <div
            class="rounded-md bg-purple-200 px-8 py-4 text-gray-800 dark:bg-purple-900 dark:text-white dark:shadow-gray-700">
            <span class="text-2xl font-semibold md:text-3xl">Sakit: {{ $sickCount }}</span>
        </div>
        <div class="rounded-md bg-red-200 px-8 py-4 text-gray-800 dark:bg-red-900 dark:text-white dark:shadow-gray-700">
            <span class="text-2xl font-semibold md:text-3xl">Tidak Hadir: {{ $absentCount }}</span><br>
            <span>Tidak/Belum Hadir</span>
        </div>
    </div>

    {{-- Tabel Absensi --}}
    <div class="mb-4 overflow-x-scroll">
        <table class="w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-900">
                <tr>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300">
                        {{ __('Nama Peserta') }}
                    </th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300">
                        {{ __('Kelompok') }}
                    </th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300">
                        {{ __('Sesi Pengajian') }}
                    </th>
                    <th scope="col"
                        class="text-nowrap border border-gray-300 px-1 py-3 text-center text-xs font-medium text-gray-500 dark:border-gray-600 dark:text-gray-300">
                        Status Absensi
                    </th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300">
                        {{ __('Waktu Hadir') }}
                    </th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300">
                        {{ __('Waktu Selesai') }}
                    </th>
                    <th scope="col" class="relative">
                        <span class="sr-only">Actions</span>
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                @php
                    $class = 'px-4 py-3 text-sm font-medium text-gray-900 dark:text-white';
                @endphp
                @foreach ($employees as $employee)
                    @php
                        $absensi = $employee->attendance;
                        $timeIn = $absensi ? $absensi?->time_in?->format('H:i:s') : null;
                        $timeOut = $absensi ? $absensi?->time_out?->format('H:i:s') : null;
                        $isWeekend = isset($date) ? $date->isWeekend() : false;
                        $status = ($absensi ?? [
                            'status' => $isWeekend || (isset($date) && !$date->isPast()) ? '-' : 'absent',
                        ])['status'];
                        switch ($status) {
                            case 'present':
                                $shortStatus = 'H';
                                $bgColor =
                                    'bg-green-200 dark:bg-green-800 hover:bg-green-300 dark:hover:bg-green-700 border border-green-300 dark:border-green-600';
                                break;
                            case 'excused':
                                $shortStatus = 'I';
                                $bgColor =
                                    'bg-blue-200 dark:bg-blue-800 hover:bg-blue-300 dark:hover:bg-blue-700 border border-blue-300 dark:border-blue-600';
                                break;
                            case 'sick':
                                $shortStatus = 'S';
                                $bgColor =
                                    'hover:bg-gray-100 dark:hover:bg-gray-700 border border-gray-300 dark:border-gray-600';
                                break;
                            case 'absent':
                                $shortStatus = 'A';
                                $bgColor =
                                    'bg-red-200 dark:bg-red-800 hover:bg-red-300 dark:hover:bg-red-700 border border-red-300 dark:border-red-600';
                                break;
                            default:
                                $shortStatus = '-';
                                $bgColor =
                                    'hover:bg-gray-100 dark:hover:bg-gray-700 border border-gray-300 dark:border-gray-600';
                                break;
                        }
                    @endphp
                    <tr wire:key="{{ $employee->id }}" class="group">
                        {{-- Detail peserta --}}
                        <td
                            class="{{ $class }} text-nowrap group-hover:bg-gray-100 dark:group-hover:bg-gray-700">
                            {{ $employee->name }}
                        </td>
                        <td
                            class="{{ $class }} text-nowrap group-hover:bg-gray-100 dark:group-hover:bg-gray-700">
                            {{-- Menggunakan relasi 'division' yang ada --}}
                            {{ $employee->division?->name ?? '-' }}
                        </td>
                        <td
                            class="{{ $class }} text-nowrap group-hover:bg-gray-100 dark:group-hover:bg-gray-700">
                            {{-- Menggunakan relasi 'shift' yang ada --}}
                            {{ $absensi->shift?->name ?? '-' }}
                        </td>

                        {{-- Status Absensi --}}
                        <td
                            class="{{ $bgColor }} text-nowrap px-1 py-3 text-center text-sm font-medium text-gray-900 dark:text-white">
                            {{ __($status) }}
                        </td>

                        {{-- Waktu masuk/keluar --}}
                        <td class="{{ $class }} group-hover:bg-gray-100 dark:group-hover:bg-gray-700">
                            {{ $timeIn ?? '-' }}
                        </td>
                        <td class="{{ $class }} group-hover:bg-gray-100 dark:group-hover:bg-gray-700">
                            {{ $timeOut ?? '-' }}
                        </td>

                        {{-- Action --}}
                        <td
                            class="cursor-pointer text-center text-sm font-medium text-gray-900 group-hover:bg-gray-100 dark:text-white dark:group-hover:bg-gray-700">
                            <div class="flex items-center justify-center gap-3">
                                @if ($absensi && ($absensi->attachment || $absensi->note || ($absensi->latitude || $absensi->longitude)))
                                    <x-button type="button" wire:click="show({{ $absensi->id }})"
                                        onclick="setLocation({{ $absensi->latitude ?? 0 }}, {{ $absensi->longitude ?? 0 }})">
                                        {{ __('Detail') }}
                                    </x-button>
                                @else
                                    -
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    {{ $employees->links() }} {{-- Paginasi --}}
    {{-- Modal Detail Absensi (jika ada dan masih dibutuhkan, jika tidak bisa dihapus) --}}
    <x-attendance-detail-modal :current-attendance="$currentAttendance" />
    @stack('attendance-detail-scripts')
</div>

{{-- SCRIPT UNTUK CHART.JS --}}
@pushOnce('scripts')
    {{-- Menggunakan CDN Chart.js versi 2.9.4 yang Anda berikan --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.js"></script>
    <script>
        // Data dari PHP diinterpolasi langsung ke JavaScript
        const chartData = {
            present: {{ $presentCount ?? 0 }},
            excused: {{ $excusedCount ?? 0 }},
            sick: {{ $sickCount ?? 0 }},
            absent: {{ $absentCount ?? 0 }}
        };

        console.log('Chart Data (from Blade):', chartData);

        // Periksa apakah semua data adalah 0
        const totalData = chartData.present + chartData.excused + chartData.sick + chartData.absent;
        if (totalData === 0) {
            console.warn('All chart data values are zero. Chart will appear empty.');
            // Opsional: Sembunyikan canvas jika semua data nol
            // const ctx = document.getElementById('myChart');
            // if (ctx) ctx.style.display = 'none';
        }

        // Inisialisasi Chart.js
        let attendanceChartInstance = new Chart("myChart", {
            type: "doughnut", // Menggunakan doughnut chart seperti sebelumnya
            data: {
                labels: ["Hadir", "Izin", "Sakit", "Tidak Hadir"],
                datasets: [{
                    backgroundColor: [
                        'rgb(34, 197, 94)', // Hadir (green)
                        'rgb(59, 130, 246)', // Izin (blue)
                        'rgb(168, 85, 247)', // Sakit (purple)
                        'rgb(239, 68, 68)' // Tidak Hadir (red)
                    ],
                    data: [
                        chartData.present,
                        chartData.excused,
                        chartData.sick,
                        chartData.absent
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false, // Penting untuk kontrol ukuran dengan CSS
                title: {
                    display: false, // Tidak menampilkan judul di chart
                    text: "Statistik Kehadiran"
                },
                legend: { // Menggunakan 'legend' bukan 'plugins.legend' untuk Chart.js v2
                    position: 'top',
                    labels: {
                        fontColor: 'rgb(209, 213, 219)' // Warna teks label untuk dark mode
                    }
                }
            }
        });

        // Livewire listener untuk update data chart secara dinamis
        Livewire.on('chartDataUpdated', (data) => {
            console.log('Received chartDataUpdated event for update:', data);
            if (attendanceChartInstance) {
                attendanceChartInstance.data.datasets[0].data = [
                    data.presentCount,
                    data.excusedCount,
                    data.sickCount,
                    data.absentCount
                ];
                attendanceChartInstance.update(); // Perbarui chart
                console.log('Chart data updated and chart redrawn.');
            } else {
                console.error('Chart instance not found when trying to update.');
            }
        });

        // Tidak perlu Livewire.emit('requestChartData') di sini karena data awal sudah diinterpolasi.
        // Livewire Component akan meng-emit 'chartDataUpdated' setelah mount/render.
    </script>
@endpushOnce
