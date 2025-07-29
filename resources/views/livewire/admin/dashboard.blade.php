@php
    use Carbon\Carbon;
    // Variabel-variabel ini akan di-pass dari Livewire Component:
    // $presentCount, $excusedCount, $sickCount, $absentCount (untuk statistik chart donat)
    // $employeesCount
    // $employees (untuk tabel)
    // $currentAttendance (untuk modal)
    // $date (opsional, untuk cek weekend)

    // VARIABEL UNTUK CHART GARIS (DI-PASS DARI LIVEWIRE COMPONENT)
    // Data ini akan diisi oleh DashboardComponent.php
    $monthlyLabels = $monthlyLabels ?? [];
    $monthlyPresentData = $monthlyPresentData ?? [];
    $monthlyExcusedData = $monthlyExcusedData ?? [];
    $monthlySickData = $monthlySickData ?? [];
    $monthlyAbsentData = $monthlyAbsentData ?? [];

@endphp
<div>
    @pushOnce('styles')
        {{-- Link untuk Leaflet JS (jika masih digunakan, jika tidak bisa dihapus) --}}
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
            integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    @endPushOnce

    {{-- BAGIAN CHART KEHADIRAN HARI INI (DONAT) --}}
    <div class="mb-6 rounded-md bg-white p-6 shadow-md dark:bg-gray-800 flex flex-col items-center justify-center"
        wire:ignore>
        <h4 class="mb-4 text-xl font-semibold text-gray-800 dark:text-gray-200 text-center">Statistik Kehadiran Hari Ini
        </h4>
        <canvas id="dailyAttendanceChart" style="width:100%;max-width:400px; max-height:400px;"></canvas>
    </div>

    {{-- BAGIAN CHART KEHADIRAN BULANAN (GARIS) --}}
    <div class="mb-6 rounded-md bg-white p-6 shadow-md dark:bg-gray-800 flex flex-col items-center justify-center"
        wire:ignore>
        <h4 class="mb-4 text-xl font-semibold text-gray-800 dark:text-gray-200 text-center">Tren Kehadiran Bulanan
        </h4>
        <canvas id="monthlyAttendanceLineChart" style="width:100%;max-width:800px; height:400px;"></canvas>
    </div>


    {{-- Header Dashboard yang sudah ada --}}
    <div class="flex flex-col justify-between sm:flex-row">
        <h3 class="mb-4 text-lg font-semibold leading-tight text-gray-800 dark:text-gray-200">
            Absensi Hari Ini
        </h3>
        <h3 class="mb-4 text-lg font-semibold leading-tight text-gray-800 dark:text-gray-200">
            Jumlah Peserta: {{ $employeesCount ?? 0 }} {{-- Menggunakan $employeesCount yang di-pass --}}
        </h3>
    </div>

    {{-- Kotak Statistik Kehadiran yang sudah ada (mengurangi "Terlambat") --}}
    <div class="mb-4 grid grid-cols-2 gap-3 md:grid-cols-3"> {{-- Mengurangi menjadi 3 kolom --}}
        <div
            class="rounded-md bg-green-200 px-8 py-4 text-gray-800 dark:bg-green-900 dark:text-white dark:shadow-gray-700">
            <span class="text-2xl font-semibold md:text-3xl">Hadir: {{ $presentCount ?? 0 }}</span><br>
        </div>
        <div
            class="rounded-md bg-blue-200 px-8 py-4 text-gray-800 dark:bg-blue-900 dark:text-white dark:shadow-gray-700">
            <span class="text-2xl font-semibold md:text-3xl">Izin: {{ $excusedCount ?? 0 }}</span><br>
            <span>Izin/Cuti</span>
        </div>
        <div
            class="rounded-md bg-purple-200 px-8 py-4 text-gray-800 dark:bg-purple-900 dark:text-white dark:shadow-gray-700">
            <span class="text-2xl font-semibold md:text-3xl">Sakit: {{ $sickCount ?? 0 }}</span>
        </div>
        <div class="rounded-md bg-red-200 px-8 py-4 text-gray-800 dark:bg-red-900 dark:text-white dark:shadow-gray-700">
            <span class="text-2xl font-semibold md:text-3xl">Tidak Hadir: {{ $absentCount ?? 0 }}</span><br>
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
                                    'bg-purple-200 dark:bg-purple-800 hover:bg-purple-300 dark:hover:bg-purple-700 border border-purple-300 dark:border-purple-600'; // Changed to purple-like for sick
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
    {{-- Plugin Chart.js Datalabels untuk menampilkan persentase di dalam pie chart --}}
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@0.7.0/dist/chartjs-plugin-datalabels.min.js">
    </script>
    <script>
        // Data untuk Chart Donat (Statistik Kehadiran Hari Ini)
        const dailyChartData = {
            present: {{ $presentCount ?? 0 }},
            excused: {{ $excusedCount ?? 0 }},
            sick: {{ $sickCount ?? 0 }},
            absent: {{ $absentCount ?? 0 }}
        };

        console.log('Daily Chart Data (from Blade):', dailyChartData);

        // Inisialisasi Chart Donat
        let dailyAttendanceChartInstance = new Chart("dailyAttendanceChart", {
            type: "doughnut", // Menggunakan doughnut chart
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
                        dailyChartData.present,
                        dailyChartData.excused,
                        dailyChartData.sick,
                        dailyChartData.absent
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false, // Penting untuk kontrol ukuran dengan CSS
                title: {
                    display: false, // Tidak menampilkan judul di chart
                },
                legend: { // Menggunakan 'legend' untuk Chart.js v2
                    position: 'top',
                    labels: {
                        fontColor: 'rgb(209, 213, 219)' // Warna teks label untuk dark mode
                    }
                },
                tooltips: {
                    callbacks: {
                        // Hanya menampilkan label dan persentase di tooltip untuk pie chart
                        label: function(tooltipItem, data) {
                            let dataset = data.datasets[tooltipItem.datasetIndex];
                            let total = dataset.data.reduce(function(previousValue, currentValue) {
                                return previousValue + currentValue;
                            });
                            let currentValue = dataset.data[tooltipItem.index];
                            let percentage = parseFloat((currentValue / total * 100).toFixed(1));
                            return data.labels[tooltipItem.index] + ': ' + percentage + '%'; // Hanya persentase
                        }
                    }
                },
                // --- Konfigurasi Datalabels Plugin ---
                plugins: {
                    datalabels: {
                        formatter: (value, ctx) => {
                            let sum = 0;
                            let dataArr = ctx.chart.data.datasets[0].data;
                            dataArr.map(data => {
                                sum += data;
                            });
                            let percentage = (value * 100 / sum).toFixed(1); // Hanya angka persentase
                            if (percentage === '0.0' || sum === 0) { // Sembunyikan 0% atau jika total 0
                                return '';
                            }
                            return percentage + '%'; // Tambahkan simbol persen
                        },
                        color: '#fff', // Warna teks label persentase
                        font: {
                            weight: 'bold'
                        }
                    }
                }
                // --- Akhir Konfigurasi Datalabels Plugin ---
            }
        });

        // Livewire listener untuk update data chart donat
        Livewire.on('dailyChartDataUpdated', (data) => { // Mengubah nama event agar lebih spesifik
            console.log('Received dailyChartDataUpdated event for update:', data);
            if (dailyAttendanceChartInstance) {
                dailyAttendanceChartInstance.data.datasets[0].data = [
                    data.presentCount,
                    data.excusedCount,
                    data.sickCount,
                    data.absentCount
                ];
                dailyAttendanceChartInstance.update(); // Perbarui chart
                console.log('Daily chart data updated and chart redrawn.');
            } else {
                console.error('Daily Chart instance not found when trying to update.');
            }
        });


        // Data untuk Chart Garis (Tren Kehadiran Bulanan)
        const monthlyLabels = @json($monthlyLabels);
        const monthlyPresentData = @json($monthlyPresentData);
        const monthlyExcusedData = @json($monthlyExcusedData);
        const monthlySickData = @json($monthlySickData);
        const monthlyAbsentData = @json($monthlyAbsentData);

        console.log('Monthly Chart Data (from Blade):', {
            monthlyLabels,
            monthlyPresentData,
            monthlyExcusedData,
            monthlySickData,
            monthlyAbsentData
        });

        // Inisialisasi Chart Garis
        let monthlyAttendanceLineChartInstance = new Chart("monthlyAttendanceLineChart", {
            type: "line", // Menggunakan line chart
            data: {
                labels: monthlyLabels, // Bulan sebagai xAxis
                datasets: [{
                        label: "Hadir",
                        borderColor: 'rgb(34, 197, 94)', // Green
                        backgroundColor: 'rgba(34, 197, 94, 0.2)', // Warna latar belakang untuk area di bawah garis
                        data: monthlyPresentData,
                        fill: false // Atur ke true jika ingin mengisi area di bawah garis
                    },
                    {
                        label: "Izin",
                        borderColor: 'rgb(59, 130, 246)', // Blue
                        backgroundColor: 'rgba(59, 130, 246, 0.2)',
                        data: monthlyExcusedData,
                        fill: false
                    },
                    {
                        label: "Sakit",
                        borderColor: 'rgb(168, 85, 247)', // Purple
                        backgroundColor: 'rgba(168, 85, 247, 0.2)',
                        data: monthlySickData,
                        fill: false
                    },
                    {
                        label: "Tidak Hadir",
                        borderColor: 'rgb(239, 68, 68)', // Red
                        backgroundColor: 'rgba(239, 68, 68, 0.2)',
                        data: monthlyAbsentData,
                        fill: false
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                title: {
                    display: false,
                    text: "Tren Kehadiran Bulanan"
                },
                legend: {
                    position: 'top',
                    labels: {
                        fontColor: 'rgb(209, 213, 219)'
                    }
                },
                scales: {
                    xAxes: [{
                        ticks: {
                            fontColor: 'rgb(209, 213, 219)' // Warna teks xAxis
                        }
                    }],
                    yAxes: [{
                        ticks: {
                            beginAtZero: true,
                            fontColor: 'rgb(209, 213, 219)' // Warna teks yAxis
                        }
                    }]
                },
                tooltips: {
                    mode: 'index',
                    intersect: false,
                    callbacks: {
                        // Hanya menampilkan nama dataset di tooltip untuk line chart
                        label: function(tooltipItem, data) {
                            let label = data.datasets[tooltipItem.datasetIndex].label || '';
                            // Tidak menampilkan nilai numerik, hanya label
                            return label;
                        }
                    }
                },
                hover: {
                    mode: 'nearest',
                    intersect: true
                }
            }
        });
    </script>
@endpushOnce
