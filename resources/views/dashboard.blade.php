@php
    $totalBooking = \App\Models\Booking::count();
    $confirmedBooking = \App\Models\Booking::where('status', 'confirmed')->count();
    $pendingBooking = \App\Models\Booking::where('status', 'pending')->count();
    $totalLokasi = \App\Models\Location::count();
    $user = auth()->user();
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-black text-2xl text-gray-900 dark:text-white leading-tight tracking-tight">
            {{ __('Ringkasan Sistem') }}
        </h2>
    </x-slot>

    <div class="pb-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-10">
            <!-- Welcome Section -->
            <div class="bg-indigo-600 rounded-3xl sm:rounded-[2.5rem] p-6 md:p-12 text-white shadow-2xl relative overflow-hidden group">
                <div class="relative z-10 max-w-2xl">
                    <h3 class="text-2xl md:text-4xl font-black mb-4">Halo, {{ $user->name }}! 👋</h3>
                    <p class="text-indigo-100 text-lg leading-relaxed opacity-90">
                        @if($user->role === 0)
                            Anda sedang masuk sebagai <span class="font-bold underline">Administrator Utama</span>. Anda memiliki akses penuh untuk mengelola semua lokasi, layanan, dan data antrian di seluruh sistem.
                        @else
                            Selamat datang kembali! Anda bertugas di <span class="font-bold text-white">{{ $user->location?->name ?? 'Lokasi Terpusat' }}</span>. Mari berikan pelayanan terbaik hari ini.
                        @endif
                    </p>
                    <div class="mt-8 flex flex-wrap gap-4">
                        <a href="{{ route('booking.index') }}" class="px-6 py-3 bg-white text-indigo-600 rounded-2xl font-bold hover:bg-indigo-50 transition transform active:scale-95 shadow-lg">Kelola Booking</a>
                        <a href="{{ route('antrian') }}" class="px-6 py-3 bg-indigo-500/50 text-white border border-indigo-400/30 rounded-2xl font-bold hover:bg-indigo-500/70 transition backdrop-blur-sm shadow-lg">Lihat Antrian Hari Ini</a>
                    </div>
                </div>
                
                <!-- Decorative Icon -->
                <div class="absolute right-[-2%] top-[-10%] opacity-10 group-hover:rotate-12 transition-transform duration-1000">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-80 h-80" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Stat Card 1 -->
                <div class="bg-white dark:bg-gray-900 p-6 rounded-[2rem] shadow-sm border border-gray-100 dark:border-gray-800 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-2xl">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <span class="text-xs font-bold text-gray-500 uppercase tracking-widest">Global</span>
                    </div>
                    <h4 class="text-4xl font-black text-gray-900 dark:text-white mb-1">{{ $totalBooking }}</h4>
                    <p class="text-sm text-gray-500 font-medium">Total Booking</p>
                </div>

                <!-- Stat Card 2 -->
                <div class="bg-white dark:bg-gray-900 p-6 rounded-[2rem] shadow-sm border border-gray-100 dark:border-gray-800 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 rounded-2xl">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <span class="text-xs font-bold text-gray-500 uppercase tracking-widest">Valid</span>
                    </div>
                    <h4 class="text-4xl font-black text-gray-900 dark:text-white mb-1">{{ $confirmedBooking }}</h4>
                    <p class="text-sm text-gray-500 font-medium">Booking Dikonfirmasi</p>
                </div>

                <!-- Stat Card 3 -->
                <div class="bg-white dark:bg-gray-900 p-6 rounded-[2rem] shadow-sm border border-gray-100 dark:border-gray-800 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 rounded-2xl">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <span class="text-xs font-bold text-gray-500 uppercase tracking-widest">Waiting</span>
                    </div>
                    <h4 class="text-4xl font-black text-gray-900 dark:text-white mb-1">{{ $pendingBooking }}</h4>
                    <p class="text-sm text-gray-500 font-medium">Menunggu Verifikasi</p>
                </div>

                <!-- Stat Card 4 -->
                <div class="bg-white dark:bg-gray-900 p-6 rounded-[2rem] shadow-sm border border-gray-100 dark:border-gray-800 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 rounded-2xl">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            </svg>
                        </div>
                        <span class="text-xs font-bold text-gray-500 uppercase tracking-widest">Network</span>
                    </div>
                    <h4 class="text-4xl font-black text-gray-900 dark:text-white mb-1">{{ $totalLokasi }}</h4>
                    <p class="text-sm text-gray-500 font-medium">Total Lokasi Kantor</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
