<?php

use Livewire\Volt\Component;
use App\Models\Booking;
use App\Models\Loket;
use Illuminate\Support\Facades\DB;
use function Livewire\Volt\{state, computed, mount};

state(['location_id' => null, 'last_call_id' => null, 'last_call_time' => null]);

mount(function ($location = null) {
    if ($location) $this->location_id = $location;
    
    $booking = Booking::where('location_id', $this->location_id)
        ->whereNotNull('loket_id')
        ->whereDate('booking_date', now()->format('Y-m-d'))
        ->latest('updated_at')
        ->first();

    if ($booking) {
        $this->last_call_id = $booking->id;
        $this->last_call_time = $booking->updated_at->toDateTimeString();
    }
});

$lokets = computed(function () {
    return Loket::where('location_id', $this->location_id)->get();
});

$lastCalled = computed(function () {
    return Booking::where('location_id', $this->location_id)
        ->whereNotNull('loket_id')
        ->whereDate('booking_date', now()->format('Y-m-d'))
        ->latest('updated_at')
        ->first();
});

$totalCalled = computed(function () {
    return Booking::where('location_id', $this->location_id)
        ->where('status', 2) // Sudah dipanggil
        ->whereDate('booking_date', now()->format('Y-m-d'))
        ->count();
});

$totalPending = computed(function () {
    return Booking::where('location_id', $this->location_id)
        ->where('status', 3) // Belum dipanggil
        ->whereDate('booking_date', now()->format('Y-m-d'))
        ->count();
});

$checkNewCall = function () {
    $booking = $this->lastCalled;
    
    if ($booking && ($booking->id !== $this->last_call_id || $booking->updated_at->toDateTimeString() !== $this->last_call_time)) {
        
        $idStr = str_pad($booking->id, 4, '0', STR_PAD_LEFT);
        $numberChars = str_split($idStr);
        
        $audioList = [];
        $audioList[] = asset('audio/kata_umum/nomor_antrian.wav');
        $audioList[] = asset('audio/huruf/A.wav');
        
        foreach ($numberChars as $char) {
            $audioList[] = asset('audio/angka/' . $char . '.wav');
        }
        
        $audioList[] = asset('audio/kata_umum/silakan_ke.wav');
        
        $loketType = $booking->loket?->type ?? 'loket';
        
        if ($loketType === 'loket') {
            $audioList[] = asset('audio/kata_umum/loket.wav');
        } elseif ($loketType === 'customer service') {
            $audioList[] = asset('audio/kata_umum/customer_service.wav');
        } elseif ($loketType === 'meja') {
            $audioList[] = asset('audio/kata_umum/meja.wav');
        } elseif ($loketType === 'teller') {
            $audioList[] = asset('audio/kata_umum/teller.wav');
        } else {
            $audioList[] = asset('audio/kata_umum/loket.wav');
        }
        
        preg_match_all('/\d+/', $booking->loket?->name ?? '', $matches);
        if (!empty($matches[0])) {
            $numberStr = (string)$matches[0][0];
            $loketChars = str_split($numberStr);
            foreach ($loketChars as $lChar) {
                $audioList[] = asset('audio/angka/' . $lChar . '.wav');
            }
        }
        
        $this->dispatch('panggil-publik', audio: $audioList);
        
        $this->last_call_id = $booking->id;
        $this->last_call_time = $booking->updated_at->toDateTimeString();
    }
};

?>

<div class="h-screen bg-[#05050a] text-white p-4 sm:p-6 lg:p-8 flex flex-col gap-4 sm:gap-6 overflow-hidden font-sans selection:bg-indigo-500/30" 
     wire:poll.5s="checkNewCall"
     x-data="{
        audioEnabled: false,
        audioQueue: [],
        isPlaying: false,
        
        enableAudio() {
            this.audioEnabled = true;
        },
        
        panggilSuara(detail) {
            console.log('panggil-publik event detail:', JSON.stringify(detail));
            
            // Extract audio array from Livewire dispatch (handles different formats)
            let audioList = null;
            if (Array.isArray(detail)) {
                // Could be [{audio: [...]}] or directly [url1, url2]
                if (detail.length > 0 && detail[0] && detail[0].audio) {
                    audioList = detail[0].audio;
                } else if (detail.length > 0 && typeof detail[0] === 'string') {
                    audioList = detail;
                }
            } else if (detail && detail.audio) {
                audioList = detail.audio;
            }
            
            console.log('audioList resolved:', audioList);
            if (!this.audioEnabled || !audioList || !audioList.length) return;
            
            this.audioQueue = [...this.audioQueue, ...audioList];
            
            if (!this.isPlaying) {
                this.playNextAudio();
            }
        },
        
        playNextAudio() {
            if (this.audioQueue.length === 0) {
                this.isPlaying = false;
                return;
            }
            
            this.isPlaying = true;
            const url = this.audioQueue.shift();
            console.log('Playing audio:', url);
            const audio = new Audio(url);
            
            audio.onended = () => {
                setTimeout(() => this.playNextAudio(), 100);
            };
            
            audio.onerror = (e) => {
                 console.error('Failed to load audio:', url, e);
                 this.playNextAudio();
            };
            
            audio.play().catch(e => {
                console.error('Play error:', url, e);
                this.playNextAudio();
            });
        }
     }"
     @panggil-publik.window="panggilSuara($event.detail)">
    
    <!-- Audio Unlock Overlay -->
    <template x-if="!audioEnabled">
        <div class="fixed inset-0 z-[100] bg-black/80 backdrop-blur-md flex flex-col items-center justify-center p-6 text-center">
            <div class="max-w-md bg-white/[0.05] p-10 rounded-[3rem] border border-white/10 shadow-2xl">
                <div class="w-24 h-24 bg-indigo-600 rounded-full flex items-center justify-center mb-8 mx-auto animate-pulse">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15.536a5 5 0 001.414 1.414m2.828-9.9a9 9 0 010 12.728M5.586 15.536a5 5 0 001.414 1.414M5 8v8a2 2 0 002 2h3l4 4V4l-4 4H7a2 2 0 00-2 2z"></path></svg>
                </div>
                <h2 class="text-3xl font-black mb-4">Sistem Suara Nonaktif</h2>
                <p class="text-slate-400 mb-8 font-medium">Klik tombol di bawah untuk mengaktifkan panggilan suara otomatis.</p>
                <button @click="enableAudio()" class="w-full py-5 bg-indigo-600 hover:bg-indigo-500 rounded-2xl font-black uppercase tracking-widest transition-all shadow-lg shadow-indigo-500/30 active:scale-95">
                    Aktifkan Suara
                </button>
            </div>
        </div>
    </template>

    <!-- Animated Background Mesh -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none opacity-40">
        <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] bg-indigo-600/20 blur-[120px] rounded-full animate-blob"></div>
        <div class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] bg-blue-600/20 blur-[120px] rounded-full animate-blob animation-delay-2000"></div>
        <div class="absolute top-[20%] right-[10%] w-[30%] h-[30%] bg-purple-600/10 blur-[120px] rounded-full animate-blob animation-delay-4000"></div>
    </div>

    <!-- Top Header Bar -->
    <div class="flex flex-col lg:flex-row justify-between items-center bg-white/[0.03] backdrop-blur-2xl px-6 sm:px-10 py-6 rounded-[2rem] sm:rounded-[2.5rem] border border-white/10 shadow-[0_8px_32px_0_rgba(0,0,0,0.8)] z-20 gap-6 lg:gap-0">
        <div class="flex items-center gap-4 sm:gap-8 w-full lg:w-auto">
            <div class="relative group">
                <div class="absolute -inset-1 bg-gradient-to-r from-indigo-500 to-blue-600 rounded-3xl blur opacity-25 group-hover:opacity-50 transition duration-1000"></div>
                <div class="relative w-16 h-16 bg-white/5 rounded-2xl flex items-center justify-center font-black text-3xl border border-white/10 shrink-0">
                    A
                </div>
            </div>
            <div>
                <div class="flex items-center gap-4">
                    <h1 class="text-xl sm:text-3xl font-black uppercase tracking-tight text-transparent bg-clip-text bg-gradient-to-r from-white via-white to-gray-400">Antrian Pelayanan</h1>
                    <!-- Test Audio Button (only visible to operators, but here small for debug) -->
                    <button @click="panggilSuara({audio: ['/audio/kata_umum/nomor_antrian.wav','/audio/huruf/A.wav', '/audio/angka/0.wav', '/audio/angka/0.wav', '/audio/angka/0.wav', '/audio/angka/1.wav', '/audio/kata_umum/silakan_ke.wav', '/audio/kata_umum/loket.wav', '/audio/angka/1.wav']})" class="p-2 bg-white/5 rounded-lg border border-white/10 opacity-20 hover:opacity-100 transition shadow-sm" title="Tes Suara">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5L6 9H2v6h4l5 4V5zM15.54 8.46a5 5 0 010 7.07M19.07 4.93a9 9 0 010 12.73"></path></svg>
                    </button>
                </div>
                @php $currentLocation = \App\Models\Location::find($location_id); @endphp
                <p class="text-indigo-400/80 font-bold uppercase tracking-[0.4em] text-[10px] sm:text-xs mt-1.5 flex items-center gap-2">
                    <span class="w-1.5 h-1.5 bg-indigo-500 rounded-full animate-pulse shadow-[0_0_8px_rgba(99,102,241,0.8)]"></span>
                    {{ $currentLocation?->name ?? 'Pusat Layanan' }} • {{ now()->translatedFormat('l, d F Y') }}
                </p>
            </div>
        </div>
        <div class="flex items-center gap-6 sm:gap-10 ml-auto lg:ml-0">
            <div class="h-10 sm:h-12 w-px bg-white/10"></div>
            <div class="text-right">
                <div class="text-2xl sm:text-5xl font-black tabular-nums tracking-tighter text-white" x-data="{ time: '' }" x-init="setInterval(() => time = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' }), 1000)" x-text="time"></div>
                <div class="text-slate-500 font-bold uppercase tracking-[0.2em] text-[8px] sm:text-[9px] mt-1">Waktu Lokal (WIB)</div>
            </div>
        </div>
    </div>
    
    <!-- Statistics Bar -->
    <div class="grid grid-cols-2 gap-4 sm:gap-6 z-20">
        <div class="bg-white/[0.03] backdrop-blur-xl p-4 sm:p-6 rounded-[1.5rem] sm:rounded-[2rem] border border-white/10 flex items-center gap-4 sm:gap-6 shadow-xl">
            <div class="w-10 h-10 sm:w-12 sm:h-12 bg-emerald-500/20 rounded-xl sm:rounded-2xl flex items-center justify-center border border-emerald-500/30">
                <svg class="w-5 h-5 sm:w-6 sm:h-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            </div>
            <div>
                <p class="text-slate-500 text-[8px] sm:text-[10px] font-black uppercase tracking-[0.2em] mb-0.5 sm:mb-1 leading-tight text-left">Total Sudah Dipanggil</p>
                <p class="text-xl sm:text-3xl font-black text-white tabular-nums text-left leading-none">{{ $this->totalCalled }} <span class="text-[8px] sm:text-xs text-slate-600 ml-1">ORANG</span></p>
            </div>
        </div>
        <div class="bg-white/[0.03] backdrop-blur-xl p-4 sm:p-6 rounded-[1.5rem] sm:rounded-[2rem] border border-white/10 flex items-center gap-4 sm:gap-6 shadow-xl">
            <div class="w-10 h-10 sm:w-12 sm:h-12 bg-amber-500/20 rounded-xl sm:rounded-2xl flex items-center justify-center border border-amber-500/30">
                <svg class="w-5 h-5 sm:w-6 sm:h-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div>
                <p class="text-slate-500 text-[8px] sm:text-[10px] font-black uppercase tracking-[0.2em] mb-0.5 sm:mb-1 leading-tight text-left">Total Belum Dipanggil</p>
                <p class="text-xl sm:text-3xl font-black text-white tabular-nums text-left leading-none">{{ $this->totalPending }} <span class="text-[8px] sm:text-xs text-slate-600 ml-1">ANTREAN</span></p>
            </div>
        </div>
    </div>


    <!-- Main Content Grid -->
    <div class="flex-1 grid grid-cols-12 gap-6 sm:gap-10 min-h-0 z-10 overflow-hidden">
        <!-- Call Status Panel (Master Box) -->
        <div class="col-span-12 lg:col-span-7 flex flex-col h-full min-h-0">
            <div class="flex-1 bg-gradient-to-br from-indigo-600/90 via-indigo-700/80 to-blue-800/90 rounded-[2.5rem] sm:rounded-[4rem] p-6 sm:p-8 lg:p-12 flex flex-col items-center justify-center text-center shadow-[0_32px_64px_-16px_rgba(0,0,0,0.6)] relative overflow-hidden border border-white/20 group">
                
                <!-- Inner Glow & Pattern -->
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_center,_var(--tw-gradient-stops))] from-white/10 via-transparent to-transparent opacity-50"></div>
                <div class="absolute inset-0 opacity-5 pointer-events-none scale-125 select-none grayscale invert" style="background-image: radial-gradient(#fff 2px, transparent 2px); background-size: 40px 40px;"></div>
                
                <div class="relative z-10 w-full flex flex-col items-center">
                    <div class="inline-flex items-center gap-3 px-8 py-3 bg-black/20 backdrop-blur-xl rounded-full border border-white/10 mb-12 transform group-hover:scale-105 transition-transform duration-500">
                        <span class="w-2 h-2 bg-emerald-400 rounded-full animate-ping"></span>
                        <span class="text-sm font-black uppercase tracking-[0.5em] text-white/90">Panggilan Aktif</span>
                    </div>
                    
                    @if($this->lastCalled)
                        <div class="relative">
                            <div class="absolute -inset-10 bg-white/20 blur-[80px] rounded-full opacity-50 animate-pulse"></div>
                            <h2 class="text-7xl sm:text-[14rem] font-black leading-none drop-shadow-[0_20px_50px_rgba(0,0,0,0.5)] mb-4 sm:mb-6 tracking-tighter text-white relative">
                                <span class="bg-clip-text text-transparent bg-gradient-to-b from-white to-gray-300">A</span><span class="inline-block mx-[-0.05em]">-</span>{{ str_pad($this->lastCalled->id, 4, '0', STR_PAD_LEFT) }}
                            </h2>
                        </div>
                        
                        <div class="text-xl sm:text-5xl font-black uppercase text-white tracking-tight mb-8 sm:mb-16 max-w-[90%] line-clamp-2 drop-shadow-lg">
                             {{ $this->lastCalled->name }}
                        </div>
                        
                        <div class="flex items-center justify-center w-full px-4 sm:px-12">
                            <div class="hidden sm:block w-full h-px bg-gradient-to-r from-transparent via-white/20 to-transparent"></div>
                            <div class="shrink-0 px-8 py-5 sm:px-12 sm:py-7 bg-white text-indigo-950 rounded-[2rem] sm:rounded-[3rem] text-xl sm:text-6xl font-black uppercase tracking-widest shadow-2xl flex items-center gap-3 sm:gap-6 transform hover:scale-105 transition-all duration-500 border-2 sm:border-4 border-white/50">
                                <svg class="w-6 h-6 sm:w-14 sm:h-14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                {{ $this->lastCalled->loket?->type ?? 'LOKET' }} {{ $this->lastCalled->loket?->name }}
                            </div>
                            <div class="hidden sm:block w-full h-px bg-gradient-to-r from-transparent via-white/20 to-transparent"></div>
                        </div>
                    @else
                        <div class="flex flex-col items-center gap-10 py-24">
                            <div class="w-32 h-32 rounded-full border-4 border-white/10 flex items-center justify-center animate-spin-slow">
                                <svg class="w-16 h-16 text-white/20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <div class="text-2xl sm:text-4xl font-black uppercase tracking-[0.3em] text-white/20 italic">Menanti Antrian...</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar Loket States -->
        <div class="col-span-12 lg:col-span-5 flex flex-col gap-4 sm:gap-6 overflow-y-auto lg:pr-4 h-full custom-scrollbar pb-4 sm:pb-10">
            @foreach($this->lokets as $loket)
                @php 
                    $current = App\Models\Booking::where('loket_id', $loket->id)
                        ->whereIn('status', [2, 3])
                        ->whereDate('booking_date', now()->format('Y-m-d'))
                        ->latest('updated_at')
                        ->first();
                    $isBeingCalled = $this->lastCalled && $current && $this->lastCalled->id == $current->id;
                @endphp
                <div class="relative group">
                    <div class="absolute -inset-0.5 bg-gradient-to-r from-indigo-500 to-blue-600 rounded-[2.5rem] blur opacity-0 transition duration-1000 group-hover:opacity-10 {{ $isBeingCalled ? 'opacity-30' : '' }}"></div>
                    <div class="relative flex items-center justify-between p-6 sm:p-8 bg-white/[0.03] backdrop-blur-xl border border-white/10 rounded-[2rem] sm:rounded-[2.5rem] shadow-xl transition-all duration-500 {{ $isBeingCalled ? 'ring-2 ring-indigo-500/50 bg-indigo-500/10' : '' }}">
                        <div class="flex flex-col gap-1 min-w-0">
                            <span class="text-indigo-400 font-black uppercase tracking-[0.3em] text-[8px] sm:text-[9px]">{{ $loket->type ?? 'Pelayanan' }}</span>
                            <h3 class="text-xl sm:text-4xl font-black text-white truncate tracking-tight">{{ $loket->name }}</h3>
                            <div class="flex gap-1.5 mt-2">
                                <div class="h-1.5 w-8 rounded-full {{ $isBeingCalled ? 'bg-indigo-400 shadow-[0_0_10px_rgba(129,140,248,0.8)]' : 'bg-white/10' }}"></div>
                                <div class="h-1.5 w-1.5 rounded-full {{ $isBeingCalled ? 'bg-indigo-400' : 'bg-white/10' }}"></div>
                            </div>
                        </div>
                        
                        <div class="text-right shrink-0">
                            @if($current)
                                <div class="flex flex-col items-end">
                                    <div class="text-3xl sm:text-6xl font-black tabular-nums tracking-tighter {{ $isBeingCalled ? 'text-white' : 'text-indigo-400' }}">
                                        <span class="text-xl sm:text-3xl opacity-50 mr-0.5">A</span>{{ str_pad($current->id, 4, '0', STR_PAD_LEFT) }}
                                    </div>
                                    <div class="text-[8px] sm:text-xs font-bold text-slate-500 uppercase tracking-widest mt-1 max-w-[120px] sm:max-w-[180px] truncate">{{ $current->name }}</div>
                                </div>
                            @else
                                <div class="flex flex-col items-end opacity-20">
                                    <div class="text-3xl sm:text-4xl font-black text-white uppercase tracking-tighter">Standby</div>
                                    <div class="text-[9px] font-bold text-white uppercase tracking-widest mt-1">Menunggu...</div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Bottom Marquee Bar -->
    <div class="relative h-14 sm:h-20 bg-white/[0.03] backdrop-blur-xl rounded-[1.5rem] sm:rounded-[2rem] overflow-hidden border border-white/10 flex items-center z-20 group mt-auto shrink-0">
        <div class="absolute inset-y-0 left-0 w-40 bg-gradient-to-r from-slate-950 to-transparent z-10"></div>
        <div class="absolute inset-y-0 right-0 w-40 bg-gradient-to-l from-slate-950 to-transparent z-10"></div>
        <div class="whitespace-nowrap flex items-center gap-24 sm:gap-40 animate-marquee py-2 px-20">
            <span class="text-indigo-400 font-black uppercase tracking-[0.3em] text-sm sm:text-lg flex items-center gap-6">
                <div class="relative flex h-3 w-3">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-indigo-500"></span>
                </div>
                Selamat Datang di Hub Antrian Digital Pelayanan Terpadu
            </span>
            <span class="text-slate-500 font-black uppercase tracking-[0.2em] text-xs sm:text-base italic flex items-center gap-6">
                <span class="w-1.5 h-1.5 bg-slate-600 rounded-full"></span>
                Mohon perhatian untuk selalu memantau nomor antrian yang tampil pada layar monitor
            </span>
            <span class="text-indigo-400 font-black uppercase tracking-[0.3em] text-sm sm:text-lg flex items-center gap-6">
                 <div class="relative flex h-3 w-3">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-indigo-500"></span>
                </div>
                Siapkan berkas administrasi Anda sebelum dipanggil menuju loket pelayanan
            </span>
        </div>
    </div>

    <style>
        @keyframes marquee {
            0% { transform: translateX(50%); }
            100% { transform: translateX(-150%); }
        }
        @keyframes blob {
            0% { transform: translate(0px, 0px) scale(1); }
            33% { transform: translate(30px, -50px) scale(1.1); }
            66% { transform: translate(-20px, 20px) scale(0.9); }
            100% { transform: translate(0px, 0px) scale(1); }
        }
        @keyframes spin-slow {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        .animate-marquee {
            animation: marquee 50s linear infinite;
        }
        .animate-blob {
            animation: blob 10s infinite;
        }
        .animate-spin-slow {
            animation: spin-slow 12s linear infinite;
        }
        .animation-delay-2000 {
            animation-delay: 2s;
        }
        .animation-delay-4000 {
            animation-delay: 4s;
        }
        .custom-scrollbar::-webkit-scrollbar {
            width: 5px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.02);
            border-radius: 10px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.2);
        }
    </style>
</div>
