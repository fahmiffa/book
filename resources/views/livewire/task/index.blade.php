<?php

use Livewire\Volt\Component;
use App\Models\Booking;
use Livewire\WithPagination;
use function Livewire\Volt\{state, computed, on, usesPagination};

usesPagination();

state([
    'search' => '',
    'editingCatatan' => null,
    'catatan' => '',
    'selectedLoketId' => null,
    'filter_date' => now()->format('Y-m-d'),
    'showVerifModal' => false,
    'selectedTaskId' => null,
]);

$lokets = computed(function () {
    $user = auth()->user();
    if ($user->role === 0) return App\Models\Loket::all();
    return $user->loket ? collect([$user->loket]) : collect();
});

$loketCounts = computed(function () {
    return Booking::where('status', 2)
        ->select('loket_id', \Illuminate\Support\Facades\DB::raw('count(*) as total'))
        ->groupBy('loket_id')
        ->pluck('total', 'loket_id');
});

$tasks = computed(function () {
    $user = auth()->user();
    $query = Booking::with(['location', 'loket'])->where('status', 2);

    if ($this->filter_date) {
        $query->whereDate('booking_date', $this->filter_date);
    }

    if ($user->role !== 0) {
        $myLoketId = $user->loket?->id;
        if ($myLoketId) {
            $query->where('loket_id', $myLoketId);
        } else {
            $query->whereRaw('1 = 0');
        }
    } elseif ($this->selectedLoketId) {
        $query->where('loket_id', $this->selectedLoketId);
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

$startEdit = function ($bookingId) {
    $booking = Booking::find($bookingId);
    $this->editingCatatan = $bookingId;
    $this->catatan = $booking->catatan ?? '';
};

$saveCatatan = function () {
    if ($this->editingCatatan) {
        Booking::where('id', $this->editingCatatan)->update([
            'catatan' => $this->catatan
        ]);
        $this->editingCatatan = null;
        $this->catatan = '';
    }
};

$cancelEdit = function () {
    $this->editingCatatan = null;
    $this->catatan = '';
};

$openVerif = function ($id) {
    $task = Booking::find($id);
    $this->selectedTaskId = $id;
    $this->catatan = $task->catatan ?? '';
    $this->showVerifModal = true;
};

$submitVerif = function () {
    if ($this->selectedTaskId) {
        Booking::where('id', $this->selectedTaskId)->update([
            'catatan' => $this->catatan,
            'status' => 1 // Selesai
        ]);
        $this->showVerifModal = false;
        $this->selectedTaskId = null;
        $this->catatan = '';
    }
};

$closeVerif = function () {
    $this->showVerifModal = false;
    $this->selectedTaskId = null;
    $this->catatan = '';
};

?>

<div class="pb-10" wire:poll.30s>
    <x-slot name="header">
        <h2 class="font-black text-2xl text-gray-900 dark:text-white leading-tight tracking-tight">
            {{ __('Task Monitoring') }}
        </h2>
    </x-slot>

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
        <x-datatable 
            title="Task List" 
            subtitle="Monitoring pendaftar yang sedang dilayani saat ini"
        >
            <x-slot name="extraFilters">
                {{-- Loket Filter Tabs --}}
                @if(auth()->user()->role === 0)
                    <div class="flex gap-2 overflow-x-auto pb-2 scrollbar-hide">
                        <button 
                            wire:click="$set('selectedLoketId', null)"
                            class="shrink-0 px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all {{ !$selectedLoketId ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-500/30' : 'bg-white dark:bg-gray-800 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700' }}"
                        >
                            Semua Loket
                        </button>
                        @foreach($this->lokets as $loket)
                            <button 
                                wire:click="$set('selectedLoketId', {{ $loket->id }})"
                                class="shrink-0 px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all {{ $selectedLoketId == $loket->id ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-500/30' : 'bg-white dark:bg-gray-800 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700' }}"
                            >
                                Loket {{ $loket->name }} ({{ $this->loketCounts[$loket->id] ?? 0 }})
                            </button>
                        @endforeach
                    </div>
                @else
                    @if(auth()->user()->loket)
                        <div class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest shadow-lg shadow-emerald-500/20">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                            Loket {{ auth()->user()->loket->name }}
                        </div>
                    @endif
                @endif
            </x-slot>

            <x-slot name="thead">
                <th class="px-6 py-5 font-bold text-[11px] uppercase tracking-wider text-gray-400 dark:text-gray-500 whitespace-nowrap">Kode</th>
                <th class="px-6 py-5 font-bold text-[11px] uppercase tracking-wider text-gray-400 dark:text-gray-500 whitespace-nowrap">Pendaftar</th>
                <th class="px-6 py-5 font-bold text-[11px] uppercase tracking-wider text-gray-400 dark:text-gray-500 whitespace-nowrap">Nomor (NIK/JMO)</th>
                <th class="px-6 py-5 font-bold text-[11px] uppercase tracking-wider text-gray-400 dark:text-gray-500 whitespace-nowrap">Jadwal</th>
                <th class="px-6 py-5 font-bold text-[11px] uppercase tracking-wider text-gray-400 dark:text-gray-500 whitespace-nowrap">Lokasi</th>
                <th class="px-6 py-5 font-bold text-[11px] uppercase tracking-wider text-gray-400 dark:text-gray-500 whitespace-nowrap">Catatan</th>
                <th class="px-6 py-5 font-bold text-[11px] uppercase tracking-wider text-gray-400 dark:text-gray-500 whitespace-nowrap text-right">Aksi</th>
            </x-slot>

            <x-slot name="tbody">
                @forelse($this->tasks as $task)
                    <tr class="group hover:bg-white dark:hover:bg-gray-700/50 transition-all duration-300">
                        {{-- Existing cells --}}
                        <td class="px-6 py-5 whitespace-nowrap first:rounded-l-2xl last:rounded-r-2xl">
                            <span class="px-2.5 py-1.5 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 rounded-xl font-black text-[11px] tracking-tighter shadow-sm border border-indigo-100 dark:border-indigo-800">
                                BK-{{ str_pad($task->id, 5, '0', STR_PAD_LEFT) }}
                            </span>
                        </td>
                        <td class="px-6 py-5">
                            <div class="font-bold text-gray-900 dark:text-white uppercase tracking-tight text-xs truncate max-w-[160px]">{{ $task->name }}</div>
                            <div class="text-[10px] text-gray-400 font-medium mt-0.5">{{ $task->whatsapp_number }}</div>
                        </td>
                        <td class="px-6 py-5">
                            <div class="text-xs font-bold p-1 px-2 inline-block bg-emerald-50 dark:bg-emerald-900/20 rounded-lg text-emerald-700 dark:text-emerald-400 border border-emerald-100/50 dark:border-emerald-800/30 mb-2 whitespace-nowrap">{{ $task->service }}</div>
                            <div class="flex flex-col gap-1">
                                <div class="font-black text-[9px] text-indigo-500 tracking-tighter bg-indigo-50 dark:bg-indigo-900/40 px-2 py-1 rounded-lg inline-block border border-indigo-100/50 dark:border-indigo-800/50 w-fit">
                                    <span class="text-[7px] text-gray-400 uppercase mr-1">NIK:</span>{{ substr($task->nik, 0, 3) }}****{{ substr($task->nik, -3) }}
                                </div>
                                <div class="font-black text-[9px] text-gray-500 tracking-tighter bg-gray-50 dark:bg-gray-800 px-2 py-1 rounded-lg inline-block border border-gray-100 dark:border-gray-700 w-fit">
                                    <span class="text-[7px] text-gray-400 uppercase mr-1">JMO:</span>{{ $task->jmo_number }}
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-5">
                            <div class="text-xs font-bold dark:text-gray-200">{{ \Carbon\Carbon::parse($task->booking_date)->translatedFormat('d M Y') }}</div>
                            <div class="text-[10px] text-indigo-500 font-black mt-0.5 tracking-widest uppercase">{{ $task->booking_time }} WIB</div>
                        </td>
                        <td class="px-6 py-5">
                            <div class="text-xs font-bold text-gray-700 dark:text-gray-300">{{ $task->location?->name ?? '-' }}</div>
                            <div class="text-[10px] font-black text-emerald-600 uppercase tracking-widest mt-1">LOKET {{ $task->loket?->name ?? '-' }}</div>
                        </td>
                        <td class="px-6 py-5">
                            <div class="text-[10px] text-gray-500 dark:text-gray-400 font-medium bg-gray-50 dark:bg-gray-900/50 px-3 py-2 rounded-xl border border-gray-100 dark:border-gray-700 max-w-[150px] truncate italic">
                                {{ $task->catatan ?: 'Tidak ada catatan...' }}
                            </div>
                        </td>
                        <td class="px-6 py-5 text-right whitespace-nowrap">
                            @if($task->status === 1)
                                <div class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400 rounded-xl text-[10px] font-black uppercase tracking-widest border border-emerald-200 dark:border-emerald-800">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                    Selesai
                                </div>
                            @elseif($task->status === 0)
                                <div class="inline-flex items-center gap-2 px-4 py-2 bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-400 rounded-xl text-[10px] font-black uppercase tracking-widest border border-rose-200 dark:border-rose-800">
                                    Batal
                                </div>
                            @else
                                <button 
                                    wire:click="openVerif({{ $task->id }})"
                                    class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-[10px] font-black uppercase tracking-widest shadow-lg shadow-emerald-500/30 transition active:scale-95"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    Verifikasi
                                </button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-20 text-center text-gray-400 dark:text-gray-600 italic font-medium">
                            <div class="flex flex-col items-center gap-3">
                                <svg class="w-12 h-12 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                                <span>Belum ada task yang tersedia.</span>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </x-slot>

            <x-slot name="mobile">
                @forelse($this->tasks as $task)
                    <div class="bg-white dark:bg-gray-800 p-4 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="px-2.5 py-1 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 rounded-xl font-black text-[10px] tracking-tight border border-indigo-100 dark:border-indigo-800">
                                BK-{{ str_pad($task->id, 5, '0', STR_PAD_LEFT) }}
                            </span>
                            <div class="text-[9px] font-black text-emerald-600 uppercase tracking-widest">LOKET {{ $task->loket?->name ?? '-' }}</div>
                        </div>
                        
                        <div class="p-3 bg-gray-50/50 dark:bg-gray-900/50 rounded-2xl border border-gray-100 dark:border-gray-700/50">
                            <div class="font-black text-gray-900 dark:text-white uppercase tracking-tight text-sm">{{ $task->name }}</div>
                            <div class="grid grid-cols-2 gap-3 mt-3">
                                <div class="min-w-0">
                                    <p class="text-[8px] font-black text-gray-400 uppercase tracking-widest mb-1">NIK</p>
                                    <p class="text-[10px] font-black text-indigo-500 tracking-tighter truncate">{{ substr($task->nik, 0, 3) }}****{{ substr($task->nik, -3) }}</p>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-[8px] font-black text-gray-400 uppercase tracking-widest mb-1">No. JMO</p>
                                    <p class="text-[10px] font-black text-gray-600 dark:text-gray-300 tracking-tighter truncate">{{ $task->jmo_number }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="p-3 bg-emerald-50 dark:bg-emerald-900/20 rounded-2xl border border-emerald-100 dark:border-emerald-800/50 flex items-center justify-between group">
                            <div>
                                <p class="text-[8px] font-black text-emerald-600 dark:text-emerald-400 uppercase tracking-widest mb-1">Status Task</p>
                                @if($task->status === 1)
                                    <p class="text-[10px] font-black text-emerald-700 dark:text-emerald-400 uppercase">Selesai</p>
                                @elseif($task->status === 0)
                                    <p class="text-[10px] font-black text-rose-700 dark:text-rose-400 uppercase">Batal</p>
                                @else
                                    <p class="text-[10px] font-bold text-gray-900 dark:text-white">Proses</p>
                                @endif
                            </div>
                            
                            @if($task->status !== 1 && $task->status !== 0)
                                <button @click="openVerif({{ $task->id }})" class="px-3 py-2 bg-emerald-600 text-white rounded-xl text-[9px] font-black uppercase tracking-widest shadow-lg shadow-emerald-500/20 transition group-active:scale-90">Verifikasi</button>
                            @else
                                <div class="p-2 bg-white dark:bg-gray-800 rounded-lg text-emerald-500">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="bg-gray-50/50 dark:bg-gray-800/50 p-10 rounded-3xl text-center border-2 border-dashed border-gray-100 dark:border-gray-700">
                        <p class="text-sm text-gray-400 italic font-medium tracking-tight">Belum ada task.</p>
                    </div>
                @endforelse
            </x-slot>

            <x-slot name="pagination">
                {{ $this->tasks->links() }}
            </x-slot>
        </x-datatable>
    </div>

    {{-- Modal Verifikasi --}}
    @if($showVerifModal)
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-gray-900/80 backdrop-blur-sm px-6">
            <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] shadow-2xl w-full max-w-lg overflow-hidden border border-gray-100 dark:border-gray-700 mx-auto" x-transition>
                <div class="p-8 text-center border-b border-gray-50 dark:border-gray-700 relative">
                    <button wire:click="closeVerif" class="absolute right-6 top-6 text-gray-400 hover:text-gray-600 transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                    <h3 class="text-xl font-black text-gray-900 dark:text-white uppercase tracking-tight">Verifikasi Penyelesaian</h3>
                    <p class="text-[10px] text-gray-500 mt-1 uppercase tracking-widest font-bold">Lengkapi catatan sebelum menyelesaikan task</p>
                </div>

                <div class="p-8 space-y-6">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Catatan Pelayanan</label>
                        <textarea 
                            wire:model="catatan" 
                            rows="4"
                            placeholder="Tuliskan catatan hasil pelayanan di sini..."
                            class="w-full px-5 py-4 bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-800 rounded-3xl text-sm focus:ring-emerald-500 focus:border-emerald-500 border-2 transition-all shadow-inner"
                        ></textarea>
                    </div>

                    <div class="p-4 bg-emerald-50 dark:bg-emerald-900/10 rounded-2xl border border-emerald-100 dark:border-emerald-800/30">
                        <div class="flex gap-3">
                            <div class="p-2 bg-emerald-100 dark:bg-emerald-900/40 rounded-xl text-emerald-600 dark:text-emerald-400 h-fit">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <p class="text-[10px] text-emerald-700 dark:text-emerald-400 font-bold leading-relaxed">Setelah menekan tombol simpan, status task akan berubah menjadi "SELESAI" dan pendaftar akan keluar dari daftar monitoring.</p>
                        </div>
                    </div>
                </div>

                <div class="p-8 bg-gray-50/50 dark:bg-gray-900/30 flex gap-4">
                    <button wire:click="closeVerif" class="flex-1 py-4 bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400 rounded-2xl font-black uppercase tracking-widest border border-gray-200 dark:border-gray-700 hover:bg-gray-50 transition">
                        Batal
                    </button>
                    <button wire:click="submitVerif" class="flex-[2] py-4 bg-emerald-600 text-white rounded-2xl font-black uppercase tracking-widest shadow-lg shadow-emerald-500/30 hover:bg-emerald-700 active:scale-95 transition">
                        Simpan & Selesai
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
