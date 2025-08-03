<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name', 'Laravel') }}</title>

    <!-- qr code -->
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs/qrcode.min.js"></script>

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
    <div class="flex h-screen bg-gray-100 dark:bg-gray-900">
        <!-- Sidebar -->
        <div id="sidebar"
            class="fixed inset-y-0 left-0 z-40 w-64 transition-transform duration-200 ease-in-out transform -translate-x-full bg-white shadow-lg dark:bg-gray-800 md:translate-x-0">
            <div class="h-full overflow-y-auto">
                {{-- Livewire Navigation Menu akan dirender di sini --}}
                @livewire('navigation-menu')
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="flex flex-col flex-1 md:ml-64"> {{-- md:ml-64 untuk menggeser konten di desktop --}}
            <!-- Top Bar untuk Mobile (Hamburger Menu dan Header) -->
            <header class="w-full bg-white shadow dark:bg-gray-800 md:hidden">
                <div class="flex items-center justify-between px-4 py-3">
                    <button id="sidebar-toggle" class="text-gray-500 dark:text-gray-400 focus:outline-none">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
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
                <header class="hidden bg-white shadow dark:bg-gray-800 md:block">
                    <div class="px-4 py-6 mx-auto max-w-7xl sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <!-- Page Content -->
            <main class="flex-1 p-4 overflow-y-auto">
                <x-banner /> {{-- Banner tetap di luar layout utama agar bisa overlay --}}

                {{ $slot }}
            </main>
        </div>
    </div>

    @stack('modals')

    @livewireScripts

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarToggle = document.getElementById('sidebar-toggle');
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.querySelector('.flex-1.flex.flex-col');

            // Fungsi untuk membuka/menutup sidebar
            function toggleSidebar() {
                sidebar.classList.toggle('-translate-x-full');
                // Hapus atau tambahkan kelas ml-64 pada mainContent di mobile
                if (window.innerWidth < 768) { // Hanya berlaku untuk mobile
                    if (sidebar.classList.contains('-translate-x-full')) {
                        mainContent.classList.remove('ml-64');
                    } else {
                        mainContent.classList.add('ml-64');
                    }
                }
            }

            // Event listener untuk tombol toggle
            sidebarToggle.addEventListener('click', toggleSidebar);

            // Menutup sidebar saat mengklik di luar sidebar (hanya di mobile)
            document.addEventListener('click', function(event) {
                const isClickInsideSidebar = sidebar.contains(event.target);
                const isClickOnToggle = sidebarToggle.contains(event.target);

                if (window.innerWidth < 768 && !isClickInsideSidebar && !isClickOnToggle && !sidebar
                    .classList.contains('-translate-x-full')) {
                    toggleSidebar(); // Tutup sidebar
                }
            });

            // Handle resize window untuk mengatur ulang tampilan sidebar di desktop
            window.addEventListener('resize', function() {
                if (window.innerWidth >= 768) { // md breakpoint
                    sidebar.classList.remove('-translate-x-full'); // Pastikan sidebar terlihat di desktop
                    mainContent.classList.add('md:ml-64'); // Pastikan margin desktop diterapkan
                } else {
                    // Di mobile, jika sidebar terbuka, biarkan terbuka, tetapi hapus margin desktop
                    mainContent.classList.remove('md:ml-64');
                }
            });

            // Inisialisasi posisi sidebar saat halaman dimuat (untuk desktop)
            if (window.innerWidth >= 768) {
                sidebar.classList.remove('-translate-x-full');
                mainContent.classList.add('md:ml-64');
            }
        });
    </script>

    @stack('scripts')
</body>

</html>
