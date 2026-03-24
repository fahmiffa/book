<?php

use Livewire\Volt\Component;
use App\Models\Booking;
use Illuminate\Support\Facades\DB;
use function Livewire\Volt\{computed, state};

state([
    'activeTab' => 'belum',
    'showModal' => false,
    'selectedBookingId' => null,
    'selectedLoketId' => null,
]);

$loketCounts = computed(function () {
    return Booking::where('booking_date', now()->format('Y-m-d'))
        ->where('status', 2)
        ->whereNotNull('loket_id')
        ->select('loket_id', DB::raw('count(*) as total'))
        ->groupBy('loket_id')
        ->pluck('total', 'loket_id');
});

$lokets = computed(function () {
    $user = auth()->user();
    if ($user->role === 0) return App\Models\Loket::all();
    return App\Models\Loket::where('location_id', $user->location_id)->get();
});

$queuePending = computed(function () {
    $user = auth()->user();
    $today = now()->format('Y-m-d');
    
    $query = Booking::whereDate('booking_date', $today)
        ->where('status', 3);
    
    if ($user->role !== 0 && $user->location_id) {
        $query->where('location_id', $user->location_id);
    } elseif ($user->role !== 0 && !$user->location_id) {
        return collect();
    }

    return $query->oldest('booking_time')->get();
});

$queueServed = computed(function () {
    $user = auth()->user();
    $today = now()->format('Y-m-d');
    
    $query = Booking::with('loket')->whereDate('booking_date', $today)
        ->where('status', 2);
    
    if ($user->role !== 0 && $user->location_id) {
        $query->where('location_id', $user->location_id);
    } elseif ($user->role !== 0 && !$user->location_id) {
        return collect();
    }

    return $query->latest('updated_at')->get();
});

$openModal = function ($id) {
    $this->selectedBookingId = $id;
    // Auto select first loket if available and none selected
    if (!$this->selectedLoketId && $this->lokets->count() > 0) {
        $this->selectedLoketId = $this->lokets->first()->id;
    }
    $this->showModal = true;
};

$panggilAction = function () {
    if (!$this->selectedBookingId || !$this->selectedLoketId) return;
    
    $booking = Booking::find($this->selectedBookingId);
    $loket = App\Models\Loket::find($this->selectedLoketId);
    if (!$booking || !$loket) return;

    // Update loket and timestamp ONLY (without changing status)
    // to sync with the public display
    $booking->update([
        'loket_id' => $this->selectedLoketId
    ]);
};

$selesaiAction = function () {
    if (!$this->selectedBookingId || !$this->selectedLoketId) return;
    
    // Validasi Limit 60 per hari
    $count = $this->loketCounts[$this->selectedLoketId] ?? 0;
    if ($count >= 60) {
        $this->addError('selectedLoketId', 'Loket ini sudah melebihi batas maksimal 60 pelayanan hari ini.');
        return;
    }
    
    Booking::find($this->selectedBookingId)->update([
        'status' => 2,
        'loket_id' => $this->selectedLoketId
    ]);
    
    $this->showModal = false;
    $this->selectedBookingId = null;
};

?>

<div class="p-6" wire:poll.5s>
    <div 
        x-data="{ panggilSuara(text) { /* Voice handled by public display */ } }"
    >
    <x-slot name="header">
        <h2 class="font-black text-2xl text-gray-900 dark:text-white leading-tight tracking-tight">
            {{ __('Antrian Hari Ini') }}
        </h2>
    </x-slot>

    <div class="mb-8">
        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-[0.2em]">{{ now()->format('l, d F Y') }}</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Dashboard Utama Antrian -->
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden relative">
                <div class="relative z-10">
                    <h4 class="text-gray-700 dark:text-gray-200 font-bold mb-4">Statistik Antrian</h4>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="p-4 bg-gray-50 dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-700">
                            <div class="text-[10px] text-gray-500 uppercase font-black tracking-widest text-center sm:text-left">Total</div>
                            <div class="text-2xl font-black text-indigo-600 text-center sm:text-left">{{ $this->queuePending->count() + $this->queueServed->count() }}</div>
                        </div>
                        <div class="p-4 bg-emerald-50 dark:bg-emerald-900/20 rounded-2xl border border-emerald-100 dark:border-emerald-800">
                            <div class="text-[10px] text-emerald-600 dark:text-emerald-400 uppercase font-black tracking-widest text-center sm:text-left">Tersisa</div>
                            <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400 text-center sm:text-left">{{ $this->queuePending->count() }}</div>
                        </div>
                    </div>
                </div>
                <!-- Decorative element -->
                <div class="absolute -right-10 -bottom-10 opacity-5 pointer-events-none">
                    <svg class="w-40 h-40" fill="currentColor" viewBox="0 0 24 24"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
        </div>

        <!-- List Antrian -->
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
                <div class="px-4 sm:px-6 py-4 bg-gray-50/50 dark:bg-gray-900/50 border-b border-gray-100 dark:border-gray-700 flex gap-2 sm:gap-4 overflow-x-auto">
                    <button 
                        wire:click="$set('activeTab', 'belum')"
                        class="shrink-0 px-4 sm:px-5 py-2.5 rounded-xl text-[10px] sm:text-xs font-black uppercase tracking-widest transition-all {{ $activeTab === 'belum' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/30' : 'bg-white dark:bg-gray-800 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700' }}"
                    >
                        Belum ({{ $this->queuePending->count() }})
                    </button>
                    <button 
                        wire:click="$set('activeTab', 'sudah')"
                        class="shrink-0 px-4 sm:px-5 py-2.5 rounded-xl text-[10px] sm:text-xs font-black uppercase tracking-widest transition-all {{ $activeTab === 'sudah' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/30' : 'bg-white dark:bg-gray-800 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700' }}"
                    >
                        Sudah ({{ $this->queueServed->count() }})
                    </button>
                </div>

                <div class="divide-y divide-gray-50 dark:divide-gray-700 max-h-[600px] overflow-y-auto custom-scrollbar">
                    @if($activeTab === 'belum')
                        @forelse($this->queuePending as $index => $item)
                            <div @click="$wire.openModal({{ $item->id }})" class="p-4 sm:p-6 flex items-center gap-4 sm:gap-6 hover:bg-emerald-50 dark:hover:bg-emerald-900/10 transition group/item cursor-pointer">
                                <div class="w-10 h-10 sm:w-12 sm:h-12 shrink-0 flex items-center justify-center bg-indigo-50 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 rounded-2xl font-black text-lg sm:text-xl border border-indigo-100 dark:border-indigo-800 transition shadow-sm group-hover/item:scale-105">
                                    {{ $index + 1 }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="font-bold text-gray-900 dark:text-white uppercase truncate text-sm sm:text-base group-hover/item:text-emerald-600 transition">{{ $item->name }}</div>
                                    <div class="text-[9px] sm:text-xs font-bold text-indigo-500 uppercase tracking-widest mt-1">A-{{ str_pad($item->id, 4, '0', STR_PAD_LEFT) }}</div>
                                    <div class="text-[10px] sm:text-xs text-gray-500 mt-0.5 truncate">{{ $item->service }} • {{ $item->booking_time }}</div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button 
                                        @click.stop="$wire.openModal({{ $item->id }})"
                                        class="p-3 text-emerald-600 hover:bg-emerald-100 dark:hover:bg-emerald-900/40 rounded-xl transition group shadow-sm bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 transform group-hover:scale-110" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 5l7 7-7 7M5 5l7 7-7 7" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        @empty
                            <div class="p-12 text-center text-gray-400 italic">
                                Tidak ada antrian yang belum dilayani.
                            </div>
                        @endforelse
                    @else
                        @forelse($this->queueServed as $index => $item)
                            <div class="p-6 flex items-center gap-6 hover:bg-gray-50 dark:hover:bg-gray-700 transition opacity-75">
                                <div class="w-12 h-12 flex items-center justify-center bg-gray-100 dark:bg-gray-800 text-gray-400 rounded-2xl font-black text-xl border border-gray-200 dark:border-gray-700">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                </div>
                                <div class="flex-1">
                                    <div class="font-bold text-gray-500 dark:text-gray-400 uppercase line-through">{{ $item->name }}</div>
                                    <div class="text-xs font-bold text-gray-400 uppercase tracking-widest mt-1">
                                        A-{{ str_pad($item->id, 4, '0', STR_PAD_LEFT) }} 
                                        @if($item->loket) • <span class="text-indigo-400">{{ $item->loket->name }}</span> @endif
                                    </div>
                                    <div class="text-[10px] text-emerald-600 font-bold mt-1 uppercase tracking-widest">Selesai pada {{ \Carbon\Carbon::parse($item->updated_at)->format('H:i') }} WIB</div>
                                </div>
                                <div>
                                    <span class="px-3 py-1 bg-emerald-50 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400 rounded-lg text-[10px] font-black uppercase tracking-widest border border-emerald-100 dark:border-emerald-800">
                                        Serving
                                    </span>
                                </div>
                            </div>
                        @empty
                            <div class="p-12 text-center text-gray-400 italic">
                                Belum ada antrian yang selesai dilayani.
                            </div>
                        @endforelse
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    @if($showModal)
        @teleport('body')
            <div class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-gray-900/80 backdrop-blur-sm px-4 sm:px-6">
                <div class="bg-white dark:bg-gray-800 rounded-[2rem] sm:rounded-[3rem] shadow-2xl w-full max-w-xl max-h-[90vh] flex flex-col overflow-hidden border border-gray-100 dark:border-gray-700 mx-auto" x-transition>
                    <div class="p-6 sm:p-8 text-center border-b border-gray-50 dark:border-gray-700 shrink-0">
                        <h3 class="text-lg sm:text-xl font-black text-gray-900 dark:text-white uppercase tracking-tight">Proses Antrian</h3>
                        <p class="text-xs sm:text-sm text-gray-500 mt-1">Pilih loket sebelum melakukan tindakan.</p>
                    </div>

                    <div class="p-6 sm:p-8 space-y-6 sm:space-y-8 overflow-y-auto flex-1 custom-scrollbar">
                        @php 
                            $currentBooking = collect($this->queuePending)->firstWhere('id', $selectedBookingId);
                        @endphp
                        
                        @if($currentBooking)
                            <div class="flex items-center gap-4 sm:gap-6 p-4 sm:p-6 bg-indigo-50 dark:bg-indigo-900/20 rounded-2xl sm:rounded-[2rem] border border-indigo-100 dark:border-indigo-800/50 shadow-inner">
                                <div class="w-12 h-12 sm:w-16 sm:h-16 shrink-0 flex items-center justify-center bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400 rounded-xl sm:rounded-2xl font-black text-xl sm:text-2xl border border-indigo-100 dark:border-indigo-800 shadow-sm">
                                    A
                                </div>
                                <div class="min-w-0">
                                    <div class="text-[8px] sm:text-[10px] font-black text-indigo-400 uppercase tracking-[0.2em] mb-0.5 sm:mb-1">Sedang Diproses</div>
                                    <div class="text-lg sm:text-xl font-black text-gray-900 dark:text-white uppercase truncate">{{ $currentBooking->name }}</div>
                                    <div class="text-xs sm:text-sm font-bold text-gray-500 italic mt-0.5 truncate">{{ $currentBooking->service }}</div>
                                </div>
                            </div>
                        @endif

                        <div class="space-y-4">
                            <label class="text-[10px] sm:text-xs font-black text-gray-400 uppercase tracking-widest px-1">Pilih Loket Pelayanan</label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                                @foreach($this->lokets as $loket)
                                    @php 
                                        $count = $this->loketCounts[$loket->id] ?? 0;
                                        $isFull = $count >= 60;
                                        $percentage = ($count / 60) * 100;
                                    @endphp
                                    <button 
                                        wire:click="$set('selectedLoketId', {{ $loket->id }})"
                                        @if($isFull) disabled @endif
                                        class="p-4 sm:p-5 rounded-2xl sm:rounded-[2rem] border-2 transition-all flex flex-col items-center gap-1 sm:gap-2 group relative overflow-hidden {{ $selectedLoketId == $loket->id ? 'border-indigo-600 bg-indigo-50 dark:bg-indigo-900/30' : ($isFull ? 'bg-rose-50 border-rose-100 cursor-not-allowed opacity-60' : 'border-gray-100 dark:border-gray-700 hover:border-indigo-300') }}"
                                    >
                                        <div class="text-[8px] sm:text-[10px] font-black uppercase tracking-widest {{ $selectedLoketId == $loket->id ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-400 group-hover:text-indigo-400' }}">Loket</div>
                                        <div class="text-lg sm:text-xl font-black {{ $selectedLoketId == $loket->id ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-700 dark:text-gray-300' }}">{{ $loket->name }}</div>
                                        
                                        <div class="w-full h-1 bg-gray-100 dark:bg-gray-800 rounded-full mt-1 sm:mt-2 overflow-hidden shadow-inner">
                                            <div class="h-full transition-all duration-1000 {{ $isFull ? 'bg-rose-500' : ($percentage > 75 ? 'bg-amber-400' : 'bg-emerald-500') }}" style="width: {{ $percentage }}%"></div>
                                        </div>
                                        <div class="text-[8px] sm:text-[9px] font-black {{ $isFull ? 'text-rose-500 animate-pulse' : 'text-gray-400' }} uppercase tracking-tighter">
                                            {{ $count }}/60 Selesai
                                        </div>

                                        @if($isFull)
                                            <div class="absolute inset-0 bg-white/40 backdrop-blur-[1px] flex items-center justify-center">
                                                <span class="bg-rose-600 text-white text-[7px] sm:text-[8px] font-black px-2 py-1 rounded-full uppercase">Limit Tercapai</span>
                                            </div>
                                        @endif
                                    </button>
                                @endforeach
                            </div>
                            @error('selectedLoketId') <p class="text-xs text-rose-500 font-bold px-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="p-6 sm:p-8 bg-gray-50/50 dark:bg-gray-900/30 flex flex-col sm:flex-row gap-4 shrink-0">
                        <button type="button" wire:click="$set('showModal', false)" class="order-2 sm:order-1 flex-1 py-3 sm:py-4 text-gray-500 font-bold hover:bg-gray-100 dark:hover:bg-gray-700 rounded-xl sm:rounded-[1.5rem] transition text-sm sm:text-base">Tutup</button>
                        
                        <div class="order-1 sm:order-2 flex-2 flex gap-2 sm:gap-3 w-full sm:w-auto">
                            <button 
                                wire:click="panggilAction"
                                class="flex-1 sm:flex-none px-4 sm:px-6 py-3 sm:py-4 bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400 rounded-xl sm:rounded-[1.5rem] font-black uppercase tracking-widest shadow-lg border border-indigo-100 dark:border-indigo-800 hover:bg-indigo-50 active:scale-95 transition text-xs sm:text-sm"
                                @if(!$selectedLoketId) disabled @endif
                            >
                                 🔊 Panggil
                            </button>
                            <button 
                                @click="Swal.fire({
                                    title: 'Selesaikan Antrian?',
                                    text: 'Tandai antrian ini sebagai selesai dan pindahkan ke menu task?',
                                    icon: 'success',
                                    showCancelButton: true,
                                    confirmButtonText: 'Ya, Selesai!',
                                    cancelButtonText: 'Belum',
                                    confirmButtonColor: '#059669',
                                    background: document.documentElement.classList.contains('dark') ? '#1e293b' : '#fff',
                                    color: document.documentElement.classList.contains('dark') ? '#fff' : '#000',
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        $wire.selesaiAction()
                                    }
                                })"
                                class="flex-2 sm:flex-none px-6 sm:px-8 py-3 sm:py-4 bg-emerald-600 text-white rounded-xl sm:rounded-[1.5rem] font-black uppercase tracking-widest shadow-lg shadow-emerald-500/30 hover:bg-emerald-700 active:scale-95 transition text-xs sm:text-sm"
                                @if(!$selectedLoketId) disabled @endif
                            >
                                Selesai
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endteleport
    @endif

    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(100, 116, 139, 0.2);
            border-radius: 10px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: rgba(100, 116, 139, 0.4);
        }
    </style>
    </div>
</div>
