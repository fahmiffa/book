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
    <body class="font-sans antialiased selection:bg-indigo-500 selection:text-white bg-slate-50 dark:bg-gray-950 text-gray-900 dark:text-gray-100 transition-colors duration-300">
        <div class="min-h-screen relative">
            <!-- Background Decoration -->
            <div class="absolute top-0 left-0 w-full h-96 bg-gradient-to-b from-indigo-600/10 to-transparent -z-10 pointer-events-none dark:from-indigo-500/5"></div>
            
            <livewire:layout.navigation />

            <!-- Page Heading -->
            @if (isset($header))
                <header class="bg-white/70 dark:bg-gray-900/70 backdrop-blur-md border-b border-gray-100 dark:border-gray-800 sticky top-0 z-30 shadow-sm transition-all duration-300">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        <div class="flex items-center justify-between">
                            {{ $header }}
                            <div class="hidden sm:block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest">
                                {{ now()->format('l, d F Y') }}
                            </div>
                        </div>
                    </div>
                </header>
            @endif

            <!-- Page Content -->
            <main class="relative z-10 transition-all duration-500 ease-in-out">
                {{ $slot }}
            </main>


        </div>
    </body>
</html>
