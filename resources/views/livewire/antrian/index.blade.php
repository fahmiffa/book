<?php

use Livewire\Volt\Component;
use App\Models\Booking;
use function Livewire\Volt\{computed, state};

state(['activeTab' => 'belum']);

$queuePending = computed(function () {
    $user = auth()->user();
    $today = now()->format('Y-m-d');
    
    $query = Booking::whereDate('booking_date', $today)
        ->whereIn('status', ['pending', 'confirmed']);
    
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
    
    $query = Booking::whereDate('booking_date', $today)
        ->where('status', 'completed');
    
    if ($user->role !== 0 && $user->location_id) {
        $query->where('location_id', $user->location_id);
    } elseif ($user->role !== 0 && !$user->location_id) {
        return collect();
    }

    return $query->latest('updated_at')->get();
});

$panggil = function ($id) {
    $booking = Booking::find($id);
    if (!$booking) return;
    
    $code = 'BK-' . str_pad($booking->id, 5, '0', STR_PAD_LEFT);
    $text = "Panggilan untuk nomor antrian, {$code}, atas nama, {$booking->name}. Silakan menuju meja pelayanan.";
    $this->dispatch('play-call', text: $text);
};

$selesai = function ($id) {
    Booking::find($id)->update(['status' => 'completed']);
};

$nowServing = computed(function () {
    $user = auth()->user();
    $query = Booking::where('booking_date', now()->format('Y-m-d'))
        ->whereIn('status', ['pending', 'confirmed']);

    if ($user->role !== 0 && $user->location_id) {
        $query->where('location_id', $user->location_id);
    } elseif ($user->role !== 0 && !$user->location_id) {
        return null;
    }

    return $query->orderBy('booking_time', 'asc')->first();
});

?>

<div class="p-6" wire:poll.5s>
    <div 
        x-data="{
            playCall(text) {
                const utterance = new SpeechSynthesisUtterance(text);
                utterance.lang = 'id-ID';
                utterance.rate = 0.9;
                utterance.pitch = 1;
                window.speechSynthesis.speak(utterance);
            }
        }"
        @play-call.window="playCall($event.detail.text)"
    >
    <div class="mb-8">
        <h2 class="text-3xl font-black text-gray-800 dark:text-white">Antrian Hari Ini</h2>
        <p class="text-gray-500">{{ now()->format('d F Y') }}</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Dashboard Utama Antrian -->
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-indigo-600 rounded-3xl p-8 text-white shadow-2xl relative overflow-hidden">
                <div class="relative z-10">
                    <h3 class="text-lg font-medium opacity-80">Sedang Melayani</h3>
                    @if($this->queuePending->count() > 0)
                        @php $first = $this->queuePending->first(); @endphp
                        <div class="mt-4">
                            <span class="text-xs font-bold uppercase tracking-widest opacity-60">Nomor Antrian</span>
                            <div class="text-6xl font-black">BK-{{ str_pad($first->id, 5, '0', STR_PAD_LEFT) }}</div>
                            <p class="mt-4 text-2xl font-bold italic">{{ $first->name }}</p>
                            <p class="text-sm opacity-80">{{ $first->service }}</p>
                            
                            <div class="mt-8 grid grid-cols-1 gap-3">
                                <button 
                                    wire:click="panggil({{ $first->id }})"
                                    class="w-full py-4 bg-white text-indigo-600 rounded-2xl font-black uppercase tracking-widest flex items-center justify-center gap-3 hover:bg-gray-100 transition shadow-xl active:scale-95"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" />
                                    </svg>
                                    Panggil Antrian
                                </button>
                                <button 
                                    wire:click="selesai({{ $first->id }})"
                                    class="w-full py-3 bg-indigo-500 text-white rounded-2xl font-bold uppercase tracking-widest flex items-center justify-center gap-2 hover:bg-indigo-400 transition shadow-lg active:scale-95 text-xs"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Tandai Selesai
                                </button>
                            </div>
                        </div>
                    @else
                        <div class="mt-4 text-2xl font-bold italic opacity-50 text-center py-4">Belum ada antrian</div>
                    @endif
                </div>
                <div class="absolute -right-4 -bottom-4 opacity-10">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-40 w-40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm">
                <h4 class="text-gray-700 dark:text-gray-200 font-bold mb-4">Statistik Antrian</h4>
                <div class="grid grid-cols-2 gap-4">
                    <div class="p-4 bg-gray-50 dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-700">
                        <div class="text-[10px] text-gray-500 uppercase font-black tracking-widest">Total Antrian</div>
                        <div class="text-2xl font-black text-indigo-600">{{ $this->queuePending->count() + $this->queueServed->count() }}</div>
                    </div>
                    <div class="p-4 bg-emerald-50 dark:bg-emerald-900/20 rounded-2xl border border-emerald-100 dark:border-emerald-800">
                        <div class="text-[10px] text-emerald-600 dark:text-emerald-400 uppercase font-black tracking-widest">Tersisa</div>
                        <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400">{{ $this->queuePending->count() }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- List Antrian -->
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
                <div class="px-6 py-4 bg-gray-50/50 dark:bg-gray-900/50 border-b border-gray-100 dark:border-gray-700 flex gap-4">
                    <button 
                        wire:click="$set('activeTab', 'belum')"
                        class="px-5 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest transition-all {{ $activeTab === 'belum' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/30' : 'bg-white dark:bg-gray-800 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700' }}"
                    >
                        Belum ({{ $this->queuePending->count() }})
                    </button>
                    <button 
                        wire:click="$set('activeTab', 'sudah')"
                        class="px-5 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest transition-all {{ $activeTab === 'sudah' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/30' : 'bg-white dark:bg-gray-800 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700' }}"
                    >
                        Sudah ({{ $this->queueServed->count() }})
                    </button>
                </div>

                <div class="divide-y divide-gray-50 dark:divide-gray-700 max-h-[600px] overflow-y-auto">
                    @if($activeTab === 'belum')
                        @forelse($this->queuePending as $index => $item)
                            <div class="p-6 flex items-center gap-6 hover:bg-gray-50 dark:hover:bg-gray-700 transition group/item">
                                <div class="w-12 h-12 flex items-center justify-center bg-indigo-50 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 rounded-2xl font-black text-xl border border-indigo-100 dark:border-indigo-800">
                                    {{ $index + 1 }}
                                </div>
                                <div class="flex-1">
                                    <div class="font-bold text-gray-900 dark:text-white uppercase">{{ $item->name }}</div>
                                    <div class="text-xs font-bold text-indigo-500 uppercase tracking-widest mt-1">BK-{{ str_pad($item->id, 5, '0', STR_PAD_LEFT) }}</div>
                                    <div class="text-xs text-gray-500 mt-0.5">{{ $item->service }} • {{ $item->booking_time }}</div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button 
                                        wire:click="panggil({{ $item->id }})"
                                        class="p-3 text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-900/40 rounded-xl transition group"
                                        title="Panggil"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transform group-hover:scale-110" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" />
                                        </svg>
                                    </button>
                                    <button 
                                        wire:click="selesai({{ $item->id }})"
                                        class="p-3 text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-900/40 rounded-xl transition group"
                                        title="Selesaikan"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transform group-hover:scale-110" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
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
                                    <div class="text-xs font-bold text-gray-400 uppercase tracking-widest mt-1">BK-{{ str_pad($item->id, 5, '0', STR_PAD_LEFT) }}</div>
                                    <div class="text-[10px] text-emerald-600 font-bold mt-1 uppercase tracking-widest">Selesai pada {{ \Carbon\Carbon::parse($item->updated_at)->format('H:i') }} WIB</div>
                                </div>
                                <div>
                                    <span class="px-3 py-1 bg-emerald-50 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400 rounded-lg text-[10px] font-black uppercase tracking-widest border border-emerald-100 dark:border-emerald-800">
                                        Served
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
    </div>
</div>
