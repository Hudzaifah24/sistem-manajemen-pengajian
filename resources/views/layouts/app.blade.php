<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Styles -->
    @livewireStyles

    @stack('styles')
</head>

<body class="font-sans antialiased">
    <x-banner /> {{-- Banner tetap di luar layout utama agar bisa overlay --}}

    <div class="flex h-screen bg-gray-100 dark:bg-gray-900">
        <!-- Sidebar -->
        <div id="sidebar"
            class="fixed inset-y-0 left-0 z-40 w-64 bg-white dark:bg-gray-800 shadow-lg
                   transform -translate-x-full md:translate-x-0 transition-transform duration-200 ease-in-out">
            <div class="h-full overflow-y-auto">
                {{-- Livewire Navigation Menu akan dirender di sini --}}
                @livewire('navigation-menu')
            </div>
        </div>

        <!-- Overlay untuk Mobile saat Sidebar Terbuka -->
        <div id="sidebar-overlay"
            class="fixed inset-0 bg-black opacity-0 z-30 pointer-events-none
                    transition-opacity duration-200 ease-in-out md:hidden">
        </div>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col md:ml-64"> {{-- md:ml-64 untuk menggeser konten di desktop --}}
            <!-- Top Bar untuk Mobile (Hamburger Menu dan Header) -->
            <header class="w-full bg-white dark:bg-gray-800 shadow md:hidden">
                <div class="flex items-center justify-between px-4 py-3">
                    <button id="sidebar-toggle"
                        class="text-gray-500 dark:text-gray-400 focus:outline-none p-2 rounded-md">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                    @if (isset($header))
                        <div class="text-lg font-semibold text-gray-800 dark:text-gray-200">
                            {{ $header }}
                        </div>
                    @endif
                </div>
            </header>

            <!-- Page Heading untuk Desktop -->
            @if (isset($header))
                <header class="bg-white shadow dark:bg-gray-800 hidden md:block">
                    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto p-4">
                {{ $slot }}
            </main>
        </div>
    </div>

    @stack('modals')

    @livewireScripts

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM Content Loaded. Initializing sidebar logic.');

            const sidebarToggle = document.getElementById('sidebar-toggle');
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.querySelector('.flex-1.flex.flex-col');
            const sidebarOverlay = document.getElementById('sidebar-overlay');

            console.log('Elements found:', {
                sidebarToggle: !!sidebarToggle,
                sidebar: !!sidebar,
                mainContent: !!mainContent,
                sidebarOverlay: !!sidebarOverlay
            });

            // Fungsi untuk membuka/menutup sidebar
            function toggleSidebar() {
                console.log('toggleSidebar function called.');
                sidebar.classList.toggle('-translate-x-full');
                sidebarOverlay.classList.toggle('opacity-0');
                sidebarOverlay.classList.toggle('opacity-50');
                sidebarOverlay.classList.toggle('pointer-events-none');
                sidebarOverlay.classList.toggle('pointer-events-auto');

                // Mengatur overflow body agar tidak bisa scroll saat sidebar terbuka di mobile
                if (window.innerWidth < 768) {
                    if (sidebar.classList.contains('-translate-x-full')) {
                        document.body.style.overflow = ''; // Izinkan scroll
                        console.log('Sidebar closed, body overflow auto.');
                    } else {
                        document.body.style.overflow = 'hidden'; // Nonaktifkan scroll
                        console.log('Sidebar opened, body overflow hidden.');
                    }
                }
                console.log('Sidebar classes after toggle:', sidebar.classList.toString());
            }

            // Event listener untuk tombol toggle
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', toggleSidebar);
                console.log('Sidebar toggle listener added.');
            } else {
                console.error('Sidebar toggle button not found!');
            }


            // Event listener untuk overlay (menutup sidebar saat mengklik di luar)
            if (sidebarOverlay) {
                sidebarOverlay.addEventListener('click', toggleSidebar);
                console.log('Sidebar overlay listener added.');
            } else {
                console.error('Sidebar overlay not found!');
            }


            // Handle resize window untuk mengatur ulang tampilan sidebar di desktop
            window.addEventListener('resize', function() {
                console.log('Window resized. Current width:', window.innerWidth);
                if (window.innerWidth >= 768) { // md breakpoint
                    sidebar.classList.remove('-translate-x-full'); // Pastikan sidebar terlihat di desktop
                    mainContent.classList.add('md:ml-64'); // Pastikan margin desktop diterapkan
                    sidebarOverlay.classList.remove('opacity-50',
                    'pointer-events-auto'); // Sembunyikan overlay
                    sidebarOverlay.classList.add('opacity-0', 'pointer-events-none');
                    document.body.style.overflow = ''; // Izinkan scroll
                    console.log('Desktop view: Sidebar visible, overlay hidden, body overflow auto.');
                } else {
                    // Di mobile, jika sidebar terbuka, biarkan terbuka, tetapi hapus margin desktop
                    mainContent.classList.remove('md:ml-64');
                    console.log('Mobile view: Main content md:ml-64 removed.');
                }
            });

            // Inisialisasi posisi sidebar saat halaman dimuat (untuk desktop)
            if (window.innerWidth >= 768) {
                sidebar.classList.remove('-translate-x-full');
                mainContent.classList.add('md:ml-64');
                console.log('Initial load (desktop): Sidebar visible.');
            } else {
                // Di mobile, pastikan sidebar tersembunyi saat dimuat
                sidebar.classList.add('-translate-x-full');
                sidebarOverlay.classList.add('opacity-0',
                'pointer-events-none'); // Pastikan overlay juga tersembunyi
                document.body.style.overflow = ''; // Pastikan scroll diizinkan
                console.log('Initial load (mobile): Sidebar hidden.');
            }
        });
    </script>

    @stack('scripts')
</body>

</html>
