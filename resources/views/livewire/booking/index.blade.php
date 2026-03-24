<?php

use Livewire\Volt\Component;
use App\Models\Booking;
use Livewire\WithPagination;
use function Livewire\Volt\{state, computed, on, usesPagination, mount};

mount(function () {
    if (auth()->user()->role === 2) {
        return $this->redirect(route('dashboard'), navigate: true);
    }
});

usesPagination();

state([
    'search' => '',
    'filter_date' => now()->format('Y-m-d'),
    'selectedBooking' => null,
    'showModal' => false,
]);

$openModal = function ($id) {
    $this->selectedBooking = Booking::find($id);
    $this->showModal = true;
};

$closeModal = function () {
    $this->showModal = false;
    $this->selectedBooking = null;
};

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

<div class="pb-10" wire:poll.30s>
    <x-slot name="header">
        <h2 class="font-black text-2xl text-gray-900 dark:text-white leading-tight tracking-tight">
            {{ __('Daftar Pendaftaran') }}
        </h2>
    </x-slot>

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
        <x-datatable 
            title="Daftar Pendaftaran" 
            subtitle="Ringkasan pendaftaran pendaftar berdasarkan tanggal"
        >
            <x-slot name="thead">
                <th class="px-6 py-5 font-bold text-[11px] uppercase tracking-wider text-gray-400 dark:text-gray-500 whitespace-nowrap">Kode</th>
                <th class="px-6 py-5 font-bold text-[11px] uppercase tracking-wider text-gray-400 dark:text-gray-500 whitespace-nowrap">Pendaftar</th>
                <th class="px-6 py-5 font-bold text-[11px] uppercase tracking-wider text-gray-400 dark:text-gray-500 whitespace-nowrap">Nomor (NIK/JMO)</th>
                <th class="px-6 py-5 font-bold text-[11px] uppercase tracking-wider text-gray-400 dark:text-gray-500 whitespace-nowrap">Kontak</th>
                <th class="px-6 py-5 font-bold text-[11px] uppercase tracking-wider text-gray-400 dark:text-gray-500 whitespace-nowrap">Jadwal</th>
                <th class="px-6 py-5 font-bold text-[11px] uppercase tracking-wider text-gray-400 dark:text-gray-500 whitespace-nowrap">Layanan</th>
                <th class="px-6 py-5 font-bold text-[11px] uppercase tracking-wider text-gray-400 dark:text-gray-500 whitespace-nowrap text-right">Status</th>
            </x-slot>

            <x-slot name="tbody">
                @forelse($this->bookings as $booking)
                    <tr @click="$wire.openModal({{ $booking->id }})" class="group hover:bg-white dark:hover:bg-gray-700/50 transition-all duration-300 cursor-pointer">
                        <td class="px-6 py-5 whitespace-nowrap first:rounded-l-2xl last:rounded-r-2xl">
                            <span class="px-2.5 py-1.5 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 rounded-xl font-black text-[11px] tracking-tighter shadow-sm border border-indigo-100 dark:border-indigo-800">
                                A-{{ str_pad($booking->id, 4, '0', STR_PAD_LEFT) }}
                            </span>
                        </td>
                        <td class="px-6 py-5 whitespace-nowrap">
                            <div class="font-bold text-gray-900 dark:text-white uppercase tracking-tight text-xs truncate max-w-[160px]" title="{{ $booking->name }}">{{ $booking->name }}</div>
                        </td>
                        <td class="px-6 py-5 whitespace-nowrap">
                            <div class="flex flex-col gap-1">
                                <div class="font-black text-[9px] text-indigo-500 tracking-tighter bg-indigo-50 dark:bg-indigo-900/40 px-2 py-1 rounded-lg inline-block border border-indigo-100/50 dark:border-indigo-800/50 w-fit">
                                    <span class="text-[7px] text-gray-400 uppercase mr-1">NIK:</span>{{ substr($booking->nik, 0, 3) }}****{{ substr($booking->nik, -3) }}
                                </div>
                                <div class="font-black text-[9px] text-gray-500 tracking-tighter bg-gray-50 dark:bg-gray-800 px-2 py-1 rounded-lg inline-block border border-gray-100 dark:border-gray-700 w-fit">
                                    <span class="text-[7px] text-gray-400 uppercase mr-1">JMO:</span>{{ $booking->jmo_number }}
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-5 whitespace-nowrap">
                            <a href="https://wa.me/{{ $booking->whatsapp_number }}" target="_blank" class="text-[11px] font-black text-emerald-600 dark:text-emerald-400 hover:text-emerald-700 dark:hover:text-emerald-300 flex items-center gap-1.5 transition">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.319 1.592 5.548 0 10.058-4.51 10.06-10.059.002-2.689-1.047-5.215-2.951-7.121-1.905-1.904-4.432-2.951-7.125-2.952-5.548 0-10.06 4.511-10.062 10.06-.001 2.112.571 4.14 1.642 5.922l-.982 3.585 3.69-.968z"/></svg>
                                {{ $booking->whatsapp_number }}
                            </a>
                        </td>
                        <td class="px-6 py-5 whitespace-nowrap">
                            <div class="text-[10px] font-bold dark:text-gray-200">{{ \Carbon\Carbon::parse($booking->booking_date)->translatedFormat('d M Y') }}</div>
                            <div class="text-[9px] text-indigo-500 font-black mt-0.5 tracking-widest uppercase">{{ $booking->booking_time }} WIB</div>
                        </td>
                        <td class="px-6 py-5 whitespace-nowrap">
                            <div class="text-[11px] font-bold p-1 px-2 inline-block bg-gray-100 dark:bg-gray-700 rounded-lg text-gray-700 dark:text-gray-300 truncate max-w-[120px]" title="{{ $booking->service }}">{{ $booking->service }}</div>
                        </td>
                        <td class="px-6 py-5 whitespace-nowrap text-right">
                            @php
                                $statusColors = [
                                    4 => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 border-blue-200 dark:border-blue-800',
                                    3 => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400 border-indigo-200 dark:border-indigo-800',
                                    2 => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800',
                                    1 => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800',
                                    0 => 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400 border-rose-200 dark:border-rose-800',
                                ];
                                $statusNames = [
                                    4 => 'Booked',
                                    3 => 'Check In',
                                    2 => 'Serving',
                                    1 => 'Selesai',
                                    0 => 'Batal',
                                ];
                            @endphp
                            <span 
                                @if($booking->status === 4)
                                    @click.stop="bookingActions.checkIn({{ $booking->id }}, $wire)"
                                    class="px-2.5 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-wider border transition-all duration-300 {{ $statusColors[$booking->status] ?? 'bg-gray-100 text-gray-600' }} hover:scale-110 cursor-pointer shadow-sm active:scale-95"
                                @else
                                    class="px-2.5 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-wider border transition-all duration-300 {{ $statusColors[$booking->status] ?? 'bg-gray-100 text-gray-600' }}"
                                @endif
                            >
                                {{ $statusNames[$booking->status] ?? 'Proses' }}
                            </span>
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
            </x-slot>

            <x-slot name="mobile">
                @forelse($this->bookings as $booking)
                    <div @click="$wire.openModal({{ $booking->id }})" class="bg-white dark:bg-gray-800 p-4 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden cursor-pointer active:scale-95 transition-transform">
                        <div class="flex justify-between items-center mb-4 gap-2">
                            <span class="px-2.5 py-1 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 rounded-xl font-black text-[10px] tracking-tight border border-indigo-100 dark:border-indigo-800 shrink-0">
                                BK-{{ str_pad($booking->id, 5, '0', STR_PAD_LEFT) }}
                            </span>
                            @php
                                $statusColors = [
                                    4 => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 border-blue-200 dark:border-blue-800',
                                    3 => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400 border-indigo-200 dark:border-indigo-800',
                                    2 => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800',
                                    1 => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800',
                                    0 => 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400 border-rose-200 dark:border-rose-800',
                                ];
                                $statusNames = [
                                    4 => 'Booked',
                                    3 => 'Check In',
                                    2 => 'Serving',
                                    1 => 'Selesai',
                                    0 => 'Batal',
                                ];
                            @endphp
                            <span class="px-2.5 py-1 rounded-lg text-[9px] font-black uppercase tracking-wider border shrink-0 {{ $statusColors[$booking->status] ?? 'bg-gray-100 text-gray-600' }}">
                                {{ $statusNames[$booking->status] ?? 'Proses' }}
                            </span>
                        </div>

                        <div class="space-y-3">
                            <div class="p-3 bg-gray-50/50 dark:bg-gray-900/50 rounded-2xl border border-gray-100 dark:border-gray-700/50">
                                <div class="font-black text-gray-900 dark:text-white uppercase tracking-tight text-sm truncate">{{ $booking->name }}</div>
                                <div class="grid grid-cols-2 gap-3 mt-3">
                                    <div class="min-w-0">
                                        <p class="text-[8px] font-black text-gray-400 uppercase tracking-widest mb-1">NIK</p>
                                        <p class="text-[10px] font-black text-indigo-500 tracking-tighter truncate">{{ substr($booking->nik, 0, 3) }}****{{ substr($booking->nik, -3) }}</p>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-[8px] font-black text-gray-400 uppercase tracking-widest mb-1">No. JMO</p>
                                        <p class="text-[10px] font-black text-gray-600 dark:text-gray-300 tracking-tighter truncate">{{ $booking->jmo_number }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="p-3 bg-gray-50/50 dark:bg-gray-900/50 rounded-2xl border border-gray-100 dark:border-gray-700/50">
                                <div class="grid grid-cols-2 gap-3">
                                    <div class="min-w-0">
                                        <p class="text-[8px] font-black text-gray-400 uppercase tracking-widest mb-1">Layanan</p>
                                        <p class="text-[10px] font-bold text-gray-700 dark:text-gray-300 truncate">{{ $booking->service }}</p>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-[8px] font-black text-gray-400 uppercase tracking-widest mb-1">Jadwal</p>
                                        <p class="text-[10px] font-bold text-gray-700 dark:text-gray-300">{{ \Carbon\Carbon::parse($booking->booking_date)->translatedFormat('d M Y') }}</p>
                                        <p class="text-[9px] font-black text-indigo-500 tracking-wider uppercase">{{ $booking->booking_time }} WIB</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="bg-gray-50/50 dark:bg-gray-800/50 p-10 rounded-3xl text-center border-2 border-dashed border-gray-100 dark:border-gray-700">
                        <p class="text-sm text-gray-400 italic font-medium tracking-tight">Belum ada pendaftaran.</p>
                    </div>
                @endforelse
            </x-slot>

            <x-slot name="pagination">
                {{ $this->bookings->links() }}
            </x-slot>
        </x-datatable>
    </div>

    <!-- Detail Modal -->
    @if($showModal && $selectedBooking)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/80 backdrop-blur-sm px-6">
            <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] shadow-2xl w-full max-w-lg overflow-hidden border border-gray-100 dark:border-gray-700 mx-auto" x-transition>
                <div class="p-8 text-center border-b border-gray-50 dark:border-gray-700 relative">
                    <button wire:click="closeModal" class="absolute right-6 top-6 text-gray-400 hover:text-gray-600 transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                    <h3 class="text-xl font-black text-gray-900 dark:text-white uppercase tracking-tight">Detail Pendaftaran</h3>
                    <p class="text-xs text-gray-500 mt-1 uppercase tracking-widest font-bold">A-{{ str_pad($this->selectedBooking->id, 4, '0', STR_PAD_LEFT) }}</p>
                </div>

                <div class="p-8 space-y-6">
                    <div class="flex items-center gap-4 p-4 bg-indigo-50 dark:bg-indigo-900/20 rounded-2xl border border-indigo-100 dark:border-indigo-800/50">
                        <div class="h-12 w-12 rounded-full bg-indigo-600 flex items-center justify-center text-white font-black text-xl">
                            {{ substr($selectedBooking->name, 0, 1) }}
                        </div>
                        <div>
                            <p class="text-sm font-black text-gray-900 dark:text-white uppercase">{{ $selectedBooking->name }}</p>
                            <p class="text-[10px] text-gray-500 font-bold tracking-widest">{{ $selectedBooking->whatsapp_number }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="p-4 bg-gray-50 dark:bg-gray-900 rounded-2xl">
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Layanan</p>
                            <p class="text-xs font-bold text-gray-700 dark:text-gray-300">{{ $selectedBooking->service }}</p>
                        </div>
                        <div class="p-4 bg-gray-50 dark:bg-gray-900 rounded-2xl">
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Jadwal</p>
                            <p class="text-xs font-bold text-gray-700 dark:text-gray-300">{{ \Carbon\Carbon::parse($selectedBooking->booking_date)->translatedFormat('d M Y') }}</p>
                            <p class="text-[10px] font-black text-indigo-500">{{ $selectedBooking->booking_time }} WIB</p>
                        </div>
                    </div>

                    <div class="p-4 bg-gray-50 dark:bg-gray-900 rounded-2xl">
                         <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Status Saat Ini</p>
                         @php
                             $statusColors = [4 => 'bg-blue-100 text-blue-700 border-blue-200', 3 => 'bg-indigo-100 text-indigo-700 border-indigo-200', 2 => 'bg-emerald-100 text-emerald-700 border-emerald-200', 1 => 'bg-emerald-100 text-emerald-700 border-emerald-200', 0 => 'bg-rose-100 text-rose-700 border-rose-200'];
                             $statusNames = [4 => 'Booked', 3 => 'Check In', 2 => 'Serving', 1 => 'Selesai', 0 => 'Batal'];
                         @endphp
                         <span class="inline-block px-3 py-1 bg-white dark:bg-gray-800 rounded-lg text-[10px] font-black uppercase tracking-widest border {{ $statusColors[$selectedBooking->status] ?? '' }}">
                            {{ $statusNames[$selectedBooking->status] ?? '-' }}
                         </span>
                    </div>
                </div>

                <div class="p-8 bg-gray-50/50 dark:bg-gray-900/30 flex gap-4">
                    <div class="flex-1 flex gap-3 w-full">
                        @if($selectedBooking->status === 4)
                            <button 
                                @click="bookingActions.checkIn({{ $selectedBooking->id }}, $wire)"
                                class="flex-1 py-4 bg-indigo-600 text-white rounded-2xl font-black uppercase tracking-widest shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 active:scale-95 transition"
                            >
                                Check In
                            </button>
                        @endif
                        
                        @if($selectedBooking->status !== 0)
                            <button 
                                @click="bookingActions.cancel({{ $selectedBooking->id }}, $wire)"
                                class="px-6 py-4 bg-rose-50 text-rose-600 dark:bg-rose-900/20 rounded-2xl font-black uppercase tracking-widest border border-rose-100 dark:border-rose-800 hover:bg-rose-100 active:scale-95 transition"
                            >
                                Batal
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    <script>
        window.bookingActions = {
            checkIn(id, wire) {
                Swal.fire({
                    title: 'Konfirmasi Check In?',
                    text: 'Daftarkan pendaftar ke dalam antrian hari ini?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Check In!',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#4f46e5',
                    background: document.documentElement.classList.contains('dark') ? '#0f172a' : '#fff',
                    color: document.documentElement.classList.contains('dark') ? '#fff' : '#000',
                }).then((result) => {
                    if (result.isConfirmed) {
                        wire.updateStatus(id, 3);
                        if (typeof wire.closeModal === 'function') wire.closeModal();
                    }
                });
            },
            cancel(id, wire) {
                Swal.fire({
                    title: 'Batalkan Pendaftaran?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Batalkan',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#f43f5e',
                    background: document.documentElement.classList.contains('dark') ? '#0f172a' : '#fff',
                    color: document.documentElement.classList.contains('dark') ? '#fff' : '#000',
                }).then((result) => {
                    if (result.isConfirmed) {
                        wire.updateStatus(id, 0);
                        if (typeof wire.closeModal === 'function') wire.closeModal();
                    }
                });
            }
        };
    </script>
</div>
