@php
    $totalBooking = \App\Models\Booking::count();
    $confirmedBooking = \App\Models\Booking::where('status', 2)->count();
    $pendingBooking = \App\Models\Booking::whereIn('status', [3, 4])->count();
    $totalLokasi = \App\Models\Location::count();
    $user = auth()->user();

    // Stats for Role 2
    if ($user->role === 2) {
        $myLoketId = $user->loket?->id;
        $taskSelesai = $myLoketId ? \App\Models\Booking::where('loket_id', $myLoketId)->where('status', 1)->count() : 0;
        $taskBelum = $myLoketId ? \App\Models\Booking::where('loket_id', $myLoketId)->where('status', 2)->count() : 0;
    }
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
                            Anda sedang masuk sebagai <span class="font-bold underline">Administrator Utama</span>. Anda memiliki akses penuh untuk mengelola semua lokasi, layanan, and data antrian di seluruh sistem.
                        @elseif($user->role === 2)
                            Selamat datang pahlawan pelayanan! Anda bertugas di <span class="font-bold text-white">{{ $user->loket?->name ?? 'Loket' }} ({{ $user->location?->name ?? 'Lokasi' }})</span>. Mari berikan senyuman terbaik hari ini.
                        @else
                            Selamat datang kembali! Anda bertugas di <span class="font-bold text-white">{{ $user->location?->name ?? 'Lokasi Terpusat' }}</span>. Mari berikan pelayanan terbaik hari ini.
                        @endif
                    </p>
                    <div class="mt-8 flex flex-wrap gap-4">
                        @if($user->role !== 2)
                            <a href="{{ route('booking.index') }}" class="px-6 py-3 bg-white text-indigo-600 rounded-2xl font-bold hover:bg-indigo-50 transition transform active:scale-95 shadow-lg">Kelola Booking</a>
                            <a href="{{ route('antrian') }}" class="px-6 py-3 bg-indigo-500/50 text-white border border-indigo-400/30 rounded-2xl font-bold hover:bg-indigo-500/70 transition backdrop-blur-sm shadow-lg">Lihat Antrian Hari Ini</a>
                        @endif
                        <a href="{{ route('task') }}" class="px-6 py-3 bg-emerald-500 text-white rounded-2xl font-bold hover:bg-emerald-600 transition transform active:scale-95 shadow-lg">Kelola Task</a>
                        @if($user->location_id)
                            <a href="{{ route('display.public', $user->location_id) }}" target="_blank" class="px-6 py-3 bg-white/20 text-white border border-white/30 rounded-2xl font-bold hover:bg-white/30 transition backdrop-blur-md shadow-lg flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                Buka Display
                            </a>
                        @endif
                    </div>
                </div>
                
                <!-- Decorative Icon -->
                <div class="absolute right-[-2%] top-[-10%] opacity-10 group-hover:rotate-12 transition-transform duration-1000">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-80 h-80" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
            </div>

            @if($user->role !== 2)
                <!-- Stats Grid (Admin/Petugas) -->
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
                        <p class="text-sm text-gray-500 font-medium">Booking Serving</p>
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
            @else
                <!-- Stats Grid (Petugas Loket Role 2) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <!-- Stat Card: Sudah Selesai -->
                    <div class="bg-white dark:bg-gray-900 p-8 rounded-[2.5rem] shadow-sm border border-gray-100 dark:border-gray-800 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                        <div class="flex items-center justify-between mb-6">
                            <div class="p-4 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 rounded-2xl">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest bg-gray-50 dark:bg-gray-800 px-3 py-1 rounded-full border border-gray-100 dark:border-gray-700">Terselesaikan</span>
                        </div>
                        <h4 class="text-5xl font-black text-gray-900 dark:text-white mb-2">{{ $taskSelesai }}</h4>
                        <p class="text-sm text-gray-500 font-bold uppercase tracking-tight">Total Pelayanan Selesai</p>
                    </div>

                    <!-- Stat Card: Belum Selesai -->
                    <div class="bg-white dark:bg-gray-900 p-8 rounded-[2.5rem] shadow-sm border border-gray-100 dark:border-gray-800 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                        <div class="flex items-center justify-between mb-6">
                            <div class="p-4 bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 rounded-2xl">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <span class="text-[10px] font-black text-amber-500 uppercase tracking-widest bg-amber-50 dark:bg-amber-900/10 px-3 py-1 rounded-full border border-amber-100 dark:border-amber-900/30">Antrian Aktif</span>
                        </div>
                        <h4 class="text-5xl font-black text-gray-900 dark:text-white mb-2">{{ $taskBelum }}</h4>
                        <p class="text-sm text-gray-500 font-bold uppercase tracking-tight">Tunggu Verifikasi</p>
                    </div>
                </div>
            @endif
        </div>

        @if($user->role === 0)
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 pb-10">
                <div class="bg-white dark:bg-gray-900 rounded-[2.5rem] p-8 border border-gray-100 dark:border-gray-800 shadow-sm">
                    <h4 class="font-black text-gray-900 dark:text-white uppercase tracking-widest text-sm mb-6 flex items-center gap-2">
                        <span class="w-2 h-2 bg-purple-500 rounded-full animate-pulse"></span>
                        Quick Access: Display Antrian Per Lokasi
                    </h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach(\App\Models\Location::all() as $loc)
                            <a href="{{ route('display.public', $loc->id) }}" target="_blank" class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-800 rounded-2xl hover:bg-purple-50 dark:hover:bg-purple-900/20 transition group border border-transparent hover:border-purple-200">
                                <span class="font-bold text-gray-700 dark:text-gray-300 group-hover:text-purple-600">{{ $loc->name }}</span>
                                <svg class="w-5 h-5 text-gray-400 group-hover:text-purple-600 transform group-hover:translate-x-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
