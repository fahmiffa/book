<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component
{
    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect(route('login'), navigate: true);
    }
}; ?>

<div class="relative">
    <!-- Background Overlay for Mobile -->
    <div 
        x-show="sidebarOpen" 
        x-transition:enter="transition-opacity ease-linear duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-linear duration-300"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="sidebarOpen = false"
        class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-50 lg:hidden"
    ></div>

    <!-- Sidebar Sidebar -->
    <aside 
        id="sidebar"
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
        class="fixed inset-y-0 left-0 z-50 w-72 bg-white dark:bg-gray-950 border-r border-gray-100 dark:border-gray-800 transition-transform duration-300 ease-in-out lg:static lg:inset-0"
    >
        <div class="h-full flex flex-col">
            <!-- Brand / Logo -->
            <div class="h-24 px-8 flex items-center justify-between">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                    <x-application-logo class="h-10 w-auto fill-current text-indigo-600" />
                    <div class="flex flex-col">
                        <span class="text-lg font-black tracking-tighter text-gray-900 dark:text-white leading-none">BOOKING</span>
                        <span class="text-[10px] font-bold tracking-widest text-gray-500 uppercase">System v2.0</span>
                    </div>
                </a>
                <button @click="sidebarOpen = false" class="lg:hidden p-2 text-gray-400 hover:text-gray-900 dark:hover:text-white">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <!-- Navigation Links -->
            <div class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
                <div class="px-4 mb-4">
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.2em]">Menu Utama</p>
                </div>

                <x-sidebar-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" wire:navigate>
                    <x-slot name="icon">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                    </x-slot>
                    Dashboard
                </x-sidebar-link>

                @if(auth()->user()->role === 0 || auth()->user()->role === 1)
                    <div class="px-4 mt-8 mb-4 border-t border-gray-50 dark:border-gray-900/50 pt-6">
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.2em]">Master Data</p>
                    </div>

                    @if(auth()->user()->role === 0)
                        <x-sidebar-link :href="route('lokasi')" :active="request()->routeIs('lokasi')" wire:navigate>
                            <x-slot name="icon">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            </x-slot>
                            Lokasi
                        </x-sidebar-link>
                    @endif

                    <x-sidebar-link :href="route('akun')" :active="request()->routeIs('akun')" wire:navigate>
                        <x-slot name="icon">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                        </x-slot>
                        Akun
                    </x-sidebar-link>

                    <x-sidebar-link :href="route('loket')" :active="request()->routeIs('loket')" wire:navigate>
                        <x-slot name="icon">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                        </x-slot>
                        Loket
                    </x-sidebar-link>

                    @if(auth()->user()->role === 0)
                        <x-sidebar-link :href="route('layanan')" :active="request()->routeIs('layanan')" wire:navigate>
                            <x-slot name="icon">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                            </x-slot>
                            Layanan
                        </x-sidebar-link>
                    @endif
                @endif

                <div class="px-4 mt-8 mb-4 border-t border-gray-50 dark:border-gray-900/50 pt-6">
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.2em]">Transaksi</p>
                </div>

                @if(auth()->user()->role === 0 || auth()->user()->role === 1)
                    <x-sidebar-link :href="route('booking.index')" :active="request()->routeIs('booking.index')" wire:navigate>
                        <x-slot name="icon">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </x-slot>
                        Pendaftaran
                    </x-sidebar-link>

                    <x-sidebar-link :href="route('antrian')" :active="request()->routeIs('antrian')" wire:navigate>
                        <x-slot name="icon">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </x-slot>
                        Antrian
                    </x-sidebar-link>
                @endif

                <x-sidebar-link :href="route('task')" :active="request()->routeIs('task')" wire:navigate>
                    <x-slot name="icon">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                    </x-slot>
                    Task
                </x-sidebar-link>
            </div>

            <!-- Profile and Logout -->
            <div class="p-4 border-t border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-900/30">
                <div class="p-4 rounded-2xl flex items-center justify-between group transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="h-10 w-10 rounded-full bg-indigo-600 flex items-center justify-center text-white font-black text-sm shadow-lg shadow-indigo-500/20">
                            {{ substr(auth()->user()->name, 0, 1) }}
                        </div>
                        <div class="flex flex-col">
                            <span class="text-xs font-black text-gray-900 dark:text-white truncate max-w-[120px]">{{ auth()->user()->name }}</span>
                            <span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">
                                @if(auth()->user()->role === 0)
                                    Admin
                                @elseif(auth()->user()->role === 1)
                                    Petugas
                                @else
                                    Petugas Loket
                                @endif
                            </span>
                        </div>
                    </div>
                    <button 
                        @click="Swal.fire({
                            title: 'Keluar dari Sesi?',
                            text: 'Anda akan dialihkan ke halaman login.',
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonText: 'Ya, Keluar!',
                            cancelButtonText: 'Batal',
                            confirmButtonColor: '#f43f5e',
                            background: document.documentElement.classList.contains('dark') ? '#0f172a' : '#fff',
                            color: document.documentElement.classList.contains('dark') ? '#fff' : '#000',
                        }).then((result) => {
                            if (result.isConfirmed) {
                                $wire.logout()
                            }
                        })"
                        class="p-2 text-gray-400 hover:text-rose-600 transition-colors" 
                        title="Logout"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    </button>
                </div>
            </div>
        </div>
    </aside>
</div>
