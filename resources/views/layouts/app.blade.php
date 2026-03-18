<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,800,900&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <link rel="icon" type="image/png" href="{{ asset('icon.png') }}">
    </head>
    <body x-data="{ sidebarOpen: false }" class="font-sans antialiased selection:bg-indigo-500 selection:text-white bg-slate-50 dark:bg-gray-950 text-gray-900 dark:text-gray-100 transition-colors duration-300 overflow-x-hidden">
        <div class="min-h-screen relative flex">
            <!-- Sidebar Navigation -->
            <livewire:layout.navigation />

            <!-- Main Workspace -->
            <div class="flex-1 flex flex-col min-w-0 min-h-screen transition-all duration-500 ease-in-out">
                <!-- Mobile Header -->
                <header class="block lg:hidden h-16 bg-white/80 dark:bg-gray-950/80 backdrop-blur-xl border-b border-gray-100 dark:border-gray-800 sticky top-0 z-40 transition-all duration-300">
                    <div class="h-full px-4 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <button @click="sidebarOpen = true" class="p-2.5 rounded-xl text-gray-500 bg-gray-50 dark:bg-gray-900 border border-transparent hover:border-gray-200 dark:hover:border-gray-700 transition">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                            </button>
                            <a href="{{ route('dashboard') }}" class="flex items-center">
                                <x-application-logo class="h-8 w-auto fill-current text-indigo-600" />
                            </a>
                        </div>
                        <livewire:layout.notifications />
                    </div>
                </header>

                <!-- Page Heading (Desktop) -->
                @if (isset($header))
                    <div class="hidden lg:block bg-white/70 dark:bg-gray-950/70 backdrop-blur-xl border-b border-gray-100 dark:border-gray-800 sticky top-0 z-30 transition-all duration-300">
                        <div class="px-8 h-24 flex items-center justify-between">
                            <div class="flex flex-col">
                                {{ $header }}
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1">Sistem Antrian & Reservasi</p>
                            </div>
                            <div class="flex items-center gap-8">
                                <livewire:layout.notifications />
                                <div class="h-8 w-px bg-gray-100 dark:bg-gray-800"></div>
                                <div class="flex flex-col items-end">
                                    <div class="text-[11px] font-black text-gray-900 dark:text-white uppercase tracking-wider">
                                        {{ now()->translatedFormat('l, d F Y') }}
                                    </div>
                                    <div class="text-[9px] font-bold text-indigo-500 uppercase tracking-[0.2em] mt-0.5">
                                        Waktu Lokal
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Page Content -->
                <main class="flex-1 flex flex-col z-10">
                    <!-- Section Header for Mobile (Shows after mobile header) -->
                    @if (isset($header))
                        <div class="lg:hidden px-6 py-6 border-b border-gray-50 dark:border-gray-900/50">
                            {{ $header }}
                        </div>
                    @endif

                    <!-- Background Decoration -->
                    <div class="absolute top-0 left-0 w-full h-96 bg-gradient-to-b from-indigo-600/5 to-transparent -z-10 pointer-events-none dark:from-indigo-500/5"></div>
                    
                    <div class="flex-1 p-4 sm:p-6 lg:p-8">
                        {{ $slot }}
                    </div>
                </main>
            </div>
        </div>
    </body>
</html>
