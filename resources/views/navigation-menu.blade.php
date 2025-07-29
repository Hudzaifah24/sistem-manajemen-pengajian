<nav class="h-full flex flex-col bg-white dark:bg-gray-800 border-r border-gray-100 dark:border-gray-700">
    <!-- Logo dan Nama Aplikasi di Bagian Atas Sidebar -->
    <div class="shrink-0 flex items-center px-4 py-4 border-b border-gray-100 dark:border-gray-700">
        <a href="{{ Auth::user()->isAdmin ? route('admin.dashboard') : route('home') }}" class="flex items-center">
            <x-application-mark class="block h-9 w-auto" />
            <span
                class="ml-3 text-xl font-semibold text-gray-800 dark:text-gray-200">{{ config('app.name', 'Laravel') }}</span>
        </a>
    </div>

    <!-- Area Navigasi Utama -->
    <div class="flex-1 overflow-y-auto pt-2 pb-4 space-y-1">
        @if (Auth::user()->isAdmin)
            {{-- Menggunakan x-responsive-nav-link untuk semua item navigasi --}}
            <x-responsive-nav-link href="{{ route('admin.dashboard') }}" :active="request()->routeIs('admin.dashboard')" class="flex items-center">
                <x-heroicon-o-home class="w-5 h-5 mr-2" />
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link href="{{ route('admin.barcodes') }}" :active="request()->routeIs('admin.barcodes')" class="flex items-center">
                <x-heroicon-o-qr-code class="w-5 h-5 mr-2" />
                {{ __('Barcode') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link href="{{ route('admin.scan') }}" :active="request()->routeIs('admin.scan')" class="flex items-center">
                <x-heroicon-o-camera class="w-5 h-5 mr-2" /> {{-- Ikon kamera untuk pemindai --}}
                {{ __('Scan Absensi') }} {{-- Menjelaskan bahwa ini untuk petugas --}}
            </x-responsive-nav-link>
            <x-responsive-nav-link href="{{ route('admin.attendances') }}" :active="request()->routeIs('admin.attendances')" class="flex items-center">
                <x-heroicon-o-clipboard-document-check class="w-5 h-5 mr-2" />
                {{ __('Attendance') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link href="{{ route('admin.employees') }}" :active="request()->routeIs('admin.employees')" class="flex items-center">
                <x-heroicon-o-users class="w-5 h-5 mr-2" />
                {{ __('Participant') }}
            </x-responsive-nav-link>

            <!-- Master Data Dropdown -->
            <div x-data="{ open: false }" class="relative">
                <x-responsive-nav-link @click="open = ! open"
                    class="flex items-center justify-between w-full text-left cursor-pointer" :active="request()->routeIs('admin.masters.*')">
                    <span class="flex items-center">
                        <x-heroicon-o-cube class="w-5 h-5 mr-2" />
                        {{ __('Master Data') }}
                    </span>
                    <svg class="ms-2 h-4 w-4 transform transition-transform duration-200"
                        :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19.5 8.25l-7.5 7.5-7.5-7.5"></path>
                    </svg>
                </x-responsive-nav-link>
                <div x-show="open" x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95" class="pl-6 space-y-1 mt-1">
                    <x-responsive-nav-link href="{{ route('admin.masters.division') }}" :active="request()->routeIs('admin.masters.division')"
                        class="flex items-center">
                        {{ __('Kelompok') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link href="{{ route('admin.masters.shift') }}" :active="request()->routeIs('admin.masters.shift')"
                        class="flex items-center">
                        {{ __('Acara') }}
                    </x-responsive-nav-link>
                    <hr class="border-gray-200 dark:border-gray-600 my-2">
                    <x-responsive-nav-link href="{{ route('admin.masters.admin') }}" :active="request()->routeIs('admin.masters.admin')"
                        class="flex items-center">
                        {{ __('Admin') }}
                    </x-responsive-nav-link>
                </div>
            </div>

            <!-- Import & Export Dropdown -->
            <div x-data="{ open: false }" class="relative">
                <x-responsive-nav-link @click="open = ! open"
                    class="flex items-center justify-between w-full text-left cursor-pointer" :active="request()->routeIs('admin.import-export.*')">
                    <span class="flex items-center">
                        <x-heroicon-o-arrow-path class="w-5 h-5 mr-2" />
                        {{ __('Import & Export') }}
                    </span>
                    <svg class="ms-2 h-4 w-4 transform transition-transform duration-200"
                        :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19.5 8.25l-7.5 7.5-7.5-7.5"></path>
                    </svg>
                </x-responsive-nav-link>
                <div x-show="open" x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95" class="pl-6 space-y-1 mt-1">
                    <x-responsive-nav-link href="{{ route('admin.import-export.users') }}" :active="request()->routeIs('admin.import-export.users')"
                        class="flex items-center">
                        {{ __('Participant') }}/{{ __('Admin') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link href="{{ route('admin.import-export.attendances') }}" :active="request()->routeIs('admin.import-export.attendances')"
                        class="flex items-center">
                        {{ __('Attendance') }}
                    </x-responsive-nav-link>
                </div>
            </div>
        @else
            <x-responsive-nav-link href="{{ route('home') }}" :active="request()->routeIs('home')" class="flex items-center">
                <x-heroicon-o-home class="w-5 h-5 mr-2" />
                {{ __('Home') }}
            </x-responsive-nav-link>
        @endif
    </div>

    <!-- Responsive Settings Options di Bagian Bawah Sidebar -->
    <div class="border-t border-gray-200 dark:border-gray-600 pb-1 pt-4">
        <div class="flex items-center px-4">
            @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                <div class="me-3 shrink-0">
                    <img class="h-10 w-10 rounded-full object-cover" src="{{ Auth::user()->profile_photo_url }}"
                        alt="{{ Auth::user()->name }}" />
                </div>
            @endif

            <div>
                <div class="text-base font-medium text-gray-800 dark:text-gray-200">{{ Auth::user()->name }}</div>
                <div class="text-sm font-medium text-gray-500">{{ Auth::user()->email }}</div>
            </div>
        </div>

        <div class="mt-3 space-y-1">
            <x-responsive-nav-link href="{{ route('profile.show') }}" :active="request()->routeIs('profile.show')" class="flex items-center">
                <x-heroicon-o-user-circle class="w-5 h-5 mr-2" />
                {{ __('Profile') }}
            </x-responsive-nav-link>

            @if (Laravel\Jetstream\Jetstream::hasApiFeatures())
                <x-responsive-nav-link href="{{ route('api-tokens.index') }}" :active="request()->routeIs('api-tokens.index')"
                    class="flex items-center">
                    <x-heroicon-o-key class="w-5 h-5 mr-2" />
                    {{ __('API Tokens') }}
                </x-responsive-nav-link>
            @endif

            <div class="border-t border-gray-200 dark:border-gray-600 my-2"></div>

            <!-- Authentication -->
            <form method="POST" action="{{ route('logout') }}" x-data>
                @csrf
                <x-responsive-nav-link href="{{ route('logout') }}" @click.prevent="$root.submit();"
                    class="flex items-center">
                    <x-heroicon-o-arrow-left-on-rectangle class="w-5 h-5 mr-2" />
                    {{ __('Log Out') }}
                </x-responsive-nav-link>
            </form>
        </div>
    </div>

    <!-- Theme Toggle di Bagian Bawah Sidebar (opsional, bisa juga di header) -->
    <div class="px-4 py-3 border-t border-gray-100 dark:border-gray-700">
        <x-theme-toggle />
    </div>
</nav>
