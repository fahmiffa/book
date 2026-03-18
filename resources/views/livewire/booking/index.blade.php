<?php

use Livewire\Volt\Component;
use App\Models\Booking;
use Livewire\WithPagination;
use function Livewire\Volt\{state, computed, on, usesPagination};

usesPagination();

state([
    'search' => '',
    'filter_date' => now()->format('Y-m-d'),
]);

$bookings = computed(function () {
    $user = auth()->user();
    $query = Booking::with('location');

    if ($user->role !== 0) {
        if ($user->location_id) {
            $query->where('location_id', $user->location_id);
        } else {
            return collect();
        }
    }

    if ($this->filter_date) {
        $query->whereDate('booking_date', $this->filter_date);
    }

    if ($this->search) {
        $searchTerm = $this->search;
        if (str_starts_with(strtoupper($searchTerm), 'BK-')) {
            $id = (int) substr($searchTerm, 3);
            $query->where('id', $id);
        } else {
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('id', 'like', '%' . $searchTerm . '%');
            });
        }
    }

    return $query->latest()->paginate(10);
});

$updateStatus = function ($bookingId, $status) {
    Booking::find($bookingId)->update(['status' => $status]);
};

?>

<div class="p-6" wire:poll.30s>
    <x-slot name="header">
        <h2 class="font-black text-2xl text-gray-900 dark:text-white leading-tight tracking-tight">
            {{ __('Daftar Pendaftaran') }}
        </h2>
    </x-slot>

    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
        <div>
            <p class="text-[10px] text-gray-500 uppercase font-black tracking-widest">Sistem Manajemen Antrian Transaksi</p>
        </div>
        <div class="flex flex-wrap items-center gap-3 w-full md:w-auto">
            <div class="relative w-full sm:w-64">
                <input 
                    type="text" 
                    wire:model.live="search" 
                    placeholder="Cari Kode atau Nama..." 
                    class="w-full pl-10 pr-4 py-3 bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 rounded-2xl text-sm focus:ring-indigo-500 focus:border-indigo-500 transition-all shadow-sm"
                >
                <div class="absolute left-3.5 top-3.5 text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
            </div>
            <div class="relative w-full sm:w-auto">
                <input 
                    type="date" 
                    wire:model.live="filter_date" 
                    class="w-full pl-10 pr-4 py-3 bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 rounded-2xl text-sm focus:ring-indigo-500 focus:border-indigo-500 shadow-sm"
                >
                <div class="absolute left-3.5 top-3.5 text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Desktop Table View -->
    <div class="hidden md:block bg-white dark:bg-gray-800/50 backdrop-blur-md rounded-3xl shadow-2xl border border-gray-100 dark:border-gray-700/50 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-separate border-spacing-y-2">
                <thead class="bg-gray-50/50 dark:bg-gray-900/50">
                    <tr>
                        <th class="px-6 py-5 font-bold text-[11px] uppercase tracking-wider text-gray-400 dark:text-gray-500">Kode</th>
                        <th class="px-6 py-5 font-bold text-[11px] uppercase tracking-wider text-gray-400 dark:text-gray-500">Pendaftar</th>
                        <th class="px-6 py-5 font-bold text-[11px] uppercase tracking-wider text-gray-400 dark:text-gray-500">NIK</th>
                        <th class="px-6 py-5 font-bold text-[11px] uppercase tracking-wider text-gray-400 dark:text-gray-500">No JMO</th>
                        <th class="px-6 py-5 font-bold text-[11px] uppercase tracking-wider text-gray-400 dark:text-gray-500">Kontak</th>
                        <th class="px-6 py-5 font-bold text-[11px] uppercase tracking-wider text-gray-400 dark:text-gray-500">Jadwal</th>
                        <th class="px-6 py-5 font-bold text-[11px] uppercase tracking-wider text-gray-400 dark:text-gray-500">Layanan</th>
                        <th class="px-6 py-5 font-bold text-[11px] uppercase tracking-wider text-gray-400 dark:text-gray-500">Status</th>
                        <th class="px-6 py-5 font-bold text-[11px] uppercase tracking-wider text-gray-400 dark:text-gray-500 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y-0">
                    @forelse($this->bookings as $booking)
                        <tr class="group hover:bg-white dark:hover:bg-gray-700/50 transition-all duration-300">
                            <td class="px-6 py-5 first:rounded-l-2xl last:rounded-r-2xl">
                                <span class="px-3 py-1.5 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 rounded-xl font-black text-xs tracking-tighter shadow-sm border border-indigo-100 dark:border-indigo-800">
                                    BK-{{ str_pad($booking->id, 5, '0', STR_PAD_LEFT) }}
                                </span>
                            </td>
                            <td class="px-6 py-5">
                                <div class="font-bold text-gray-900 dark:text-white uppercase tracking-tight">{{ $booking->name }}</div>
                            </td>
                            <td class="px-6 py-5">
                                <div class="font-black text-[10px] text-indigo-500 tracking-tighter bg-indigo-50 dark:bg-indigo-900/40 px-2 py-1 rounded-lg inline-block border border-indigo-100/50 dark:border-indigo-800/50">
                                    {{ substr($booking->nik, 0, 3) }}**********{{ substr($booking->nik, -3) }}
                                </div>
                            </td>
                            <td class="px-6 py-5">
                                <div class="font-black text-[10px] text-gray-500 tracking-tighter bg-gray-50 dark:bg-gray-800 px-2 py-1 rounded-lg inline-block border border-gray-100 dark:border-gray-700">
                                    {{ $booking->jmo_number }}
                                </div>
                            </td>
                            <td class="px-6 py-5">
                                <a href="https://wa.me/{{ $booking->whatsapp_number }}" target="_blank" class="inline-flex items-center gap-2 px-3 py-1 bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400 rounded-lg text-xs font-bold border border-green-100 dark:border-green-800 hover:scale-105 transition active:scale-95">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.319 1.592 5.548 0 10.058-4.51 10.06-10.059.002-2.689-1.047-5.215-2.951-7.121-1.905-1.904-4.432-2.951-7.125-2.952-5.548 0-10.06 4.511-10.062 10.06-.001 2.112.571 4.14 1.642 5.922l-.982 3.585 3.69-.968z"/></svg>
                                    {{ $booking->whatsapp_number }}
                                </a>
                            </td>
                            <td class="px-6 py-5">
                                <div class="text-xs font-bold dark:text-gray-200">{{ \Carbon\Carbon::parse($booking->booking_date)->translatedFormat('d M Y') }}</div>
                                <div class="text-[10px] text-indigo-500 font-black mt-0.5 tracking-widest uppercase">{{ $booking->booking_time }} WIB</div>
                            </td>
                            <td class="px-6 py-5">
                                <div class="text-xs font-bold p-1 px-2 inline-block bg-gray-100 dark:bg-gray-700 rounded-lg text-gray-700 dark:text-gray-300">{{ $booking->service }}</div>
                            </td>
                            <td class="px-6 py-5">
                                @php
                                    $statusColors = [
                                        3 => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400 border-indigo-200 dark:border-indigo-800',
                                        2 => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800',
                                        0 => 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400 border-rose-200 dark:border-rose-800',
                                    ];
                                    $statusNames = [
                                        3 => 'Pendaftaran',
                                        2 => 'Selesai',
                                        0 => 'Batal',
                                    ];
                                @endphp
                                <span class="px-3 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-widest border transition-all duration-300 {{ $statusColors[$booking->status] ?? 'bg-gray-100 text-gray-600' }}">
                                    {{ $statusNames[$booking->status] ?? 'Proses' }}
                                </span>
                            </td>
                            <td class="px-6 py-5 text-right flex justify-end gap-2 pr-6">
                                @if($booking->status === 3)
                                    <button wire:click="updateStatus({{ $booking->id }}, 2)" class="p-2 text-emerald-600 hover:bg-emerald-100 dark:hover:bg-emerald-900/30 rounded-xl transition duration-300" title="Selesaikan">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </button>
                                @endif
                                @if($booking->status !== 0)
                                    <button wire:click="updateStatus({{ $booking->id }}, 0)" wire:confirm="Yakin ingin membatalkan?" class="p-2 text-rose-600 hover:bg-rose-100 dark:hover:bg-rose-900/30 rounded-xl transition duration-300" title="Batalkan">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-20 text-center text-gray-400 dark:text-gray-600 italic font-medium">
                                <div class="flex flex-col items-center gap-3">
                                    <svg class="w-12 h-12 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <span>Belum ada data pendaftaran booking.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="px-6 py-4 bg-gray-50/50 dark:bg-gray-900/50 border-t border-gray-100 dark:border-gray-700">
            {{ $this->bookings->links() }}
        </div>
    </div>

    <!-- Mobile Card View -->
    <div class="md:hidden space-y-4">
        @forelse($this->bookings as $booking)
            <div class="bg-white dark:bg-gray-800 p-5 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700">
                <div class="flex justify-between items-start mb-4">
                    <span class="px-3 py-1 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 rounded-xl font-black text-[10px] tracking-tight border border-indigo-100 dark:border-indigo-800">
                        BK-{{ str_pad($booking->id, 5, '0', STR_PAD_LEFT) }}
                    </span>
                    @php
                        $statusColors = [
                            3 => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400 border-indigo-200 dark:border-indigo-800',
                            2 => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800',
                            0 => 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400 border-rose-200 dark:border-rose-800',
                        ];
                        $statusNames = [
                            3 => 'Pendaftaran',
                            2 => 'Selesai',
                            0 => 'Batal',
                        ];
                    @endphp
                    <span class="px-2.5 py-1 rounded-lg text-[9px] font-black uppercase tracking-widest border {{ $statusColors[$booking->status] ?? 'bg-gray-100 text-gray-600' }}">
                        {{ $statusNames[$booking->status] ?? 'Proses' }}
                    </span>
                </div>

                <div class="space-y-3">
                    <div class="p-4 bg-gray-50/50 dark:bg-gray-900/50 rounded-2xl border border-gray-100 dark:border-gray-700/50">
                        <div class="font-black text-gray-900 dark:text-white uppercase tracking-tight text-sm">{{ $booking->name }}</div>
                        <div class="grid grid-cols-2 gap-4 mt-3">
                            <div>
                                <p class="text-[8px] font-black text-gray-400 uppercase tracking-widest mb-1">NIK (Masked)</p>
                                <p class="text-[10px] font-black text-indigo-500 tracking-tighter">{{ substr($booking->nik, 0, 3) }}***{{ substr($booking->nik, -3) }}</p>
                            </div>
                            <div>
                                <p class="text-[8px] font-black text-gray-400 uppercase tracking-widest mb-1">No. JMO</p>
                                <p class="text-[10px] font-black text-gray-600 dark:text-gray-300 tracking-tighter">{{ $booking->jmo_number }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <div class="flex items-center gap-1.5 px-2 py-1 bg-gray-50 dark:bg-gray-900 rounded-lg text-[10px] text-gray-600 dark:text-gray-400 font-bold border border-gray-100 dark:border-gray-800">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            {{ \Carbon\Carbon::parse($booking->booking_date)->translatedFormat('d M Y') }}
                        </div>
                        <div class="flex items-center gap-1.5 px-2 py-1 bg-indigo-50 dark:bg-indigo-900/20 rounded-lg text-[10px] text-indigo-600 dark:text-indigo-400 font-bold border border-indigo-100 dark:border-indigo-800">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            {{ $booking->booking_time }} WIB
                        </div>
                    </div>

                    <div class="pt-3 border-t border-gray-50 dark:border-gray-700 flex justify-between items-center">
                        <a href="https://wa.me/{{ $booking->whatsapp_number }}" class="text-[11px] font-bold text-green-600 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.319 1.592 5.548 0 10.058-4.51 10.06-10.059.002-2.689-1.047-5.215-2.951-7.121-1.905-1.904-4.432-2.951-7.125-2.952-5.548 0-10.06 4.511-10.062 10.06-.001 2.112.571 4.14 1.642 5.922l-.982 3.585 3.69-.968z"/></svg>
                            WhatsApp
                        </a>
                        <div class="flex gap-2">
                            @if($booking->status === 3)
                                <button wire:click="updateStatus({{ $booking->id }}, 2)" class="px-3 py-1.5 bg-emerald-600 text-white rounded-xl text-[10px] font-bold shadow-sm shadow-emerald-500/20">Selesai</button>
                            @endif
                            @if($booking->status !== 0)
                                <button wire:click="updateStatus({{ $booking->id }}, 0)" wire:confirm="Yakin ingin membatalkan?" class="px-3 py-1.5 bg-rose-50 text-rose-600 dark:bg-rose-900/30 dark:text-rose-400 rounded-xl text-[10px] font-bold border border-rose-100 dark:border-rose-800">Batal</button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-gray-50/50 dark:bg-gray-800/50 p-10 rounded-3xl text-center border-2 border-dashed border-gray-100 dark:border-gray-700">
                <p class="text-sm text-gray-400 italic font-medium tracking-tight">Belum ada pendaftaran.</p>
            </div>
        @endforelse

        <div class="pt-2">
            {{ $this->bookings->links() }}
        </div>
    </div>
</div>
