<div>
    @pushOnce('styles')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
            integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    @endpushOnce

    <h3 class="col-span-2 mb-4 text-lg font-semibold leading-tight text-gray-800 dark:text-gray-200">
        Data Absensi
    </h3>

    <div class="mb-1 text-sm dark:text-white">Filter:</div>
    <div class="grid flex-wrap items-center grid-cols-2 gap-5 mb-4 md:gap-8 lg:flex">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center">
            <x-label for="month_filter" value="Per Bulan"></x-label>
            <x-input type="month" name="month_filter" id="month_filter" wire:model.live="month" />
        </div>
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center">
            <x-label for="week_filter" value="Per Minggu"></x-label>
            <x-input type="week" name="week_filter" id="week_filter" wire:model.live="week" />
        </div>
        <div class="flex flex-col col-span-2 gap-3 lg:flex-row lg:items-center">
            <x-label for="day_filter" value="Per Hari"></x-label>
            <x-input type="date" name="day_filter" id="day_filter" wire:model.live="date" />
        </div>
        <x-select id="division" name="division" class="mb-4" wire:model.live="division">
            <option value="">{{ __('Filter Kelompok') }}</option>
            @foreach (App\Models\Division::all() as $divisionOption)
                <option value="{{ $divisionOption->id }}">
                    {{ $divisionOption->name }}
                </option>
            @endforeach
        </x-select>
        <x-select id="shift" name="shift" class="mb-4" wire:model.live="shift">
            <option value="">{{ __('Filter Acara') }}</option>
            @foreach (App\Models\Shift::all() as $shiftOption)
                <option value="{{ $shiftOption->id }}">
                    {{ $shiftOption->name }}
                </option>
            @endforeach
        </x-select>

        <div class="flex items-center col-span-2 gap-2 lg:w-96">
            <x-input type="text" class="w-full" name="search" id="seacrh" wire:model.live="search"
                placeholder="{{ __('Search') }}" />
        </div>
        <div class="lg:hidden"></div>
        <x-secondary-button
            href="{{ route('admin.attendances.report', ['month' => $month, 'week' => $week, 'date' => $date, 'division' => $division, 'jobTitle' => $jobTitle, 'shift' => $shift]) }}"
            class="flex justify-center gap-2">
            Cetak Laporan
            <x-heroicon-o-printer class="w-5 h-5" />
        </x-secondary-button>
    </div>

    <div class="overflow-x-scroll">
        <table class="w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-900">
                <tr>
                    <th scope="col" class="px-4 py-3 text-xs font-medium text-left text-gray-500 dark:text-gray-300">
                        {{ $isPerDayFilter ? __('Nama') : __('Nama') . '/' . __('Tanggal') }}
                    </th>
                    @if ($isPerDayFilter)
                        <th scope="col"
                            class="px-4 py-3 text-xs font-medium text-left text-gray-500 dark:text-gray-300">
                            {{ __('Kelompok') }}
                        </th>
                        <th scope="col"
                            class="px-4 py-3 text-xs font-medium text-left text-gray-500 dark:text-gray-300">
                            {{ __('Acara') }}
                        </th>
                    @endif
                    @foreach ($dates as $dateItem)
                        @php
                            if (!$isPerDayFilter && $dateItem->isSunday()) {
                                $textClass = 'text-red-500 dark:text-red-300';
                            } elseif (!$isPerDayFilter && $dateItem->isFriday()) {
                                $textClass = 'text-green-500 dark:text-green-300';
                            } else {
                                $textClass = 'text-gray-500 dark:text-gray-300';
                            }
                        @endphp
                        <th scope="col"
                            class="{{ $textClass }} text-nowrap border border-gray-300 px-1 py-3 text-center text-xs font-medium dark:border-gray-600">
                            @if ($isPerDayFilter)
                                Status
                            @else
                                {{ $dateItem->format('d/m') }}
                            @endif
                        </th>
                    @endforeach
                    @if ($isPerDayFilter)
                        <th scope="col"
                            class="px-4 py-3 text-xs font-medium text-left text-gray-500 dark:text-gray-300">
                            {{ __('Konfirmasi Manual') }}
                        </th>
                    @endif
                    @if ($isPerDayFilter)
                        <th scope="col"
                            class="px-4 py-3 text-xs font-medium text-left text-gray-500 dark:text-gray-300">
                            {{ __('Waktu Masuk') }}
                        </th>
                        <th scope="col"
                            class="px-4 py-3 text-xs font-medium text-left text-gray-500 dark:text-gray-300">
                            {{ __('Waktu Keluar') }}
                        </th>
                    @endif
                    @if (!$isPerDayFilter)
                        @foreach (['H', 'T', 'I', 'S', 'A'] as $_st)
                            <th scope="col"
                                class="px-1 py-3 text-xs font-medium text-center text-gray-500 border border-gray-300 text-nowrap dark:border-gray-600 dark:text-gray-300">
                                {{ $_st }}
                            </th>
                        @endforeach
                    @endif
                    @if ($isPerDayFilter)
                        <th scope="col" class="relative">
                            <span class="sr-only">Actions</span>
                        </th>
                    @endif
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200 dark:divide-gray-700 dark:bg-gray-800">
                @php
                    $class = 'px-4 py-3 text-sm font-medium text-gray-900 cursor-pointer dark:text-white';
                @endphp
                @foreach ($employees as $employee)
                    @php
                        $attendances = $employee->attendances;
                        $currentDayAttendance = null;
                        $currentStatusForSelect = '-';
                        $currentShiftForSelect = '-';
                        $currentTimeInForSelect = '-';
                        $currentTimeOutForSelect = '-';

                        if ($isPerDayFilter && !empty($date)) {
                            $currentDayAttendance = $attendances->firstWhere('date', $date);
                            $currentStatusForSelect = ($currentDayAttendance ?? ['status' => '-'])['status'];
                            $currentShiftForSelect = $currentDayAttendance['shift'] ?? '-';
                            $currentTimeInForSelect = $currentDayAttendance['time_in'] ?? '-';
                            $currentTimeOutForSelect = $currentDayAttendance['time_out'] ?? '-';
                        }
                    @endphp
                    <tr wire:key="{{ $employee->id }}" class="group">
                        <td
                            class="{{ $class }} text-nowrap group-hover:bg-gray-100 dark:group-hover:bg-gray-700">
                            {{ $employee->name }}
                        </td>
                        @if ($isPerDayFilter)
                            <td
                                class="{{ $class }} text-nowrap group-hover:bg-gray-100 dark:group-hover:bg-gray-700">
                                {{ $employee->division?->name ?? '-' }}
                            </td>
                            <td
                                class="{{ $class }} text-nowrap group-hover:bg-gray-100 dark:group-hover:bg-gray-700">
                                {{ $currentShiftForSelect }}
                            </td>
                        @endif

                        @php
                            $presentCount = 0;
                            $lateCount = 0;
                            $excusedCount = 0;
                            $sickCount = 0;
                            $absentCount = 0;
                        @endphp
                        @foreach ($dates as $dateItem)
                            @php
                                $isWeekend = $dateItem->isWeekend();
                                $attendance = $attendances->firstWhere(
                                    fn($v, $k) => $v['date'] === $dateItem->format('Y-m-d'),
                                );
                                $status = ($attendance ?? [
                                    'status' => $isWeekend || !$dateItem->isPast() ? '-' : 'absent',
                                ])['status'];
                                switch ($status) {
                                    case 'present':
                                        $shortStatus = 'H';
                                        $bgColor =
                                            'bg-green-200 dark:bg-green-800 hover:bg-green-300 dark:hover:bg-green-700 border border-green-300 dark:border-green-600';
                                        $presentCount++;
                                        break;
                                    case 'late':
                                        $shortStatus = 'T';
                                        $bgColor =
                                            'bg-amber-200 dark:bg-amber-800 hover:bg-amber-300 dark:hover:bg-amber-700 border border-amber-300 dark:border-amber-600';
                                        $lateCount++;
                                        break;
                                    case 'excused':
                                        $shortStatus = 'I';
                                        $bgColor =
                                            'bg-blue-200 dark:bg-blue-800 hover:bg-blue-300 dark:hover:bg-blue-700 border border-blue-300 dark:border-blue-600';
                                        $excusedCount++;
                                        break;
                                    case 'sick':
                                        $shortStatus = 'S';
                                        $bgColor =
                                            'hover:bg-gray-100 dark:hover:bg-gray-700 border border-gray-300 dark:border-gray-600';
                                        $sickCount++;
                                        break;
                                    case 'absent':
                                        $shortStatus = 'A';
                                        $bgColor =
                                            'bg-red-200 dark:bg-red-800 hover:bg-red-300 dark:hover:bg-red-700 border border-red-300 dark:border-red-600';
                                        $absentCount++;
                                        break;
                                    default:
                                        $shortStatus = '-';
                                        $bgColor =
                                            'hover:bg-gray-100 dark:hover:bg-gray-700 border border-gray-300 dark:border-gray-600';
                                        break;
                                }
                            @endphp
                            <td
                                class="{{ $bgColor }} text-nowrap cursor-pointer px-1 py-3 text-center text-sm font-medium text-gray-900 dark:text-white">
                                {{ $isPerDayFilter ? __($status) : $shortStatus }}
                            </td>
                        @endforeach

                        @if ($isPerDayFilter)
                            <td class="{{ $class }} group-hover:bg-gray-100 dark:group-hover:bg-gray-700">
                                <select
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:focus:ring-indigo-800"
                                    wire:change="updateAttendanceStatus('{{ $employee->id }}', $event.target.value, '{{ $date }}')">
                                    <option value="" disabled @if ($currentStatusForSelect === '-') selected @endif>
                                        Pilih Status</option>
                                    <option value="present" @selected($currentStatusForSelect === 'present')>Hadir</option>
                                    <option value="excused" @selected($currentStatusForSelect === 'excused')>Izin</option>
                                    <option value="sick" @selected($currentStatusForSelect === 'sick')>Sakit</option>
                                    <option value="absent" @selected($currentStatusForSelect === 'absent')>Alpa</option>
                                </select>
                            </td>
                        @endif

                        @if ($isPerDayFilter)
                            <td class="{{ $class }} group-hover:bg-gray-100 dark:group-hover:bg-gray-700">
                                {{ $currentTimeInForSelect }}
                            </td>
                            <td class="{{ $class }} group-hover:bg-gray-100 dark:group-hover:bg-gray-700">
                                {{ $currentTimeOutForSelect }}
                            </td>
                        @endif

                        @if (!$isPerDayFilter)
                            @foreach ([$presentCount, $lateCount, $excusedCount, $sickCount, $absentCount] as $statusCount)
                                <td
                                    class="px-1 py-3 text-sm font-medium text-center text-gray-900 border border-gray-300 cursor-pointer group-hover:bg-gray-100 dark:border-gray-600 dark:text-white dark:group-hover:bg-gray-700">
                                    {{ $statusCount }}
                                </td>
                            @endforeach
                        @endif

                        @if ($isPerDayFilter)
                            <td
                                class="text-sm font-medium text-center text-gray-900 cursor-pointer group-hover:bg-gray-100 dark:text-white dark:group-hover:bg-gray-700">
                                <div class="flex items-center justify-center gap-3">
                                    @if (
                                        $currentDayAttendance &&
                                            ($currentDayAttendance['attachment'] || $currentDayAttendance['note'] || $currentDayAttendance['coordinates']))
                                        <x-button type="button" wire:click="show({{ $currentDayAttendance['id'] }})"
                                            onclick="setLocation({{ $currentDayAttendance['lat'] ?? 0 }}, {{ $currentDayAttendance['lng'] ?? 0 }})">
                                            {{ __('Detail') }}
                                        </x-button>
                                    @else
                                        -
                                    @endif
                                </div>
                            </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if ($employees->isEmpty())
        <div class="my-2 text-sm font-medium text-center text-gray-900 dark:text-gray-100">
            Tidak ada data
        </div>
    @endif
    <div class="mt-3">
        {{ $employees->links() }}
    </div>

    <x-attendance-detail-modal :current-attendance="$currentAttendance" />
    @stack('attendance-detail-scripts')
</div>
