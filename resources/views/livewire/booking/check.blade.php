<?php

use App\Models\Booking;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use function Livewire\Volt\{state, mount};

state(['booking' => null]);

mount(function ($uuid) {
    $this->booking = Booking::with('location')->where('uuid', $uuid)->firstOrFail();
});

?>

<div class="min-h-screen flex flex-col items-center justify-center p-4">
    <div class="w-full max-w-xl">
        <!-- Success Animation/Icon -->
        <div class="mb-8 flex justify-center">
            <div class="relative">
                <div class="w-24 h-24 bg-emerald-100 dark:bg-emerald-900/40 rounded-full flex items-center justify-center border-4 border-white dark:border-gray-800 shadow-xl animate-bounce">
                    <svg class="w-12 h-12 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <!-- Sparkles decorative -->
                <div class="absolute -top-1 -right-1 w-4 h-4 bg-indigo-500 rounded-full animate-ping"></div>
            </div>
        </div>

        <!-- Main Card -->
        <div id="modern-receipt" class="bg-white dark:bg-gray-800 rounded-[3rem] shadow-2xl border border-gray-100 dark:border-gray-700 overflow-hidden relative">
            <!-- Header Background Accent -->
            <div class="absolute top-0 inset-x-0 h-32 bg-gray-50 dark:bg-gray-900/50"></div>
            
            <div class="relative p-8 sm:p-12">
                <div class="text-center mb-10">
                    <h2 class="text-3xl font-black text-gray-900 dark:text-white tracking-tight uppercase">Pendaftaran Berhasil</h2>
                    <p class="text-gray-500 mt-2 font-medium">Informasi Antrian & Jadwal Pelayanan</p>
                </div>

                <!-- Queue Number Highlight -->
                <div class="bg-indigo-600 rounded-[2.5rem] p-8 text-white text-center shadow-xl shadow-indigo-500/30 transform hover:scale-105 transition-all mb-10 flex flex-col items-center">
                    <span class="text-[10px] font-black uppercase tracking-[0.2em] opacity-80">Nomor Antrian Anda</span>
                    <div class="text-5xl sm:text-7xl font-black mt-2 mb-6">A-{{ str_pad($booking->id, 4, '0', STR_PAD_LEFT) }}</div>
                    
                    <div class="p-3 bg-white rounded-2xl shadow-inner">
                        {!! QrCode::size(120)->generate($booking->uuid) !!}
                    </div>
                    <span class="text-[8px] font-bold uppercase tracking-widest mt-3 opacity-60">Scan barcode saat tiba di lokasi</span>
                </div>

                <!-- Details Grid -->
                <div class="space-y-6">
                    <div class="flex items-center gap-6 p-5 bg-gray-50 dark:bg-gray-900/50 rounded-2xl border border-gray-100 dark:border-gray-700/50">
                        <div class="w-12 h-12 shrink-0 flex items-center justify-center bg-white dark:bg-gray-800 rounded-xl shadow-sm text-gray-400">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        </div>
                        <div class="min-w-0">
                            <div class="text-[10px] font-black text-indigo-500 uppercase tracking-widest">Nama Pendaftar</div>
                            <div class="text-lg font-bold text-gray-900 dark:text-white truncate">{{ $booking->name }}</div>
                        </div>
                    </div>

                    <div class="flex items-center gap-6 p-5 bg-gray-50 dark:bg-gray-900/50 rounded-2xl border border-gray-100 dark:border-gray-700/50">
                        <div class="w-12 h-12 shrink-0 flex items-center justify-center bg-white dark:bg-gray-800 rounded-xl shadow-sm text-gray-400">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                        </div>
                        <div class="min-w-0">
                            <div class="text-[10px] font-black text-indigo-500 uppercase tracking-widest">Tempat Pelayanan</div>
                            <div class="text-lg font-bold text-gray-900 dark:text-white truncate">{{ $booking->location->name }}</div>
                            <div class="text-xs text-gray-400 truncate">{{ $booking->location->address ?? 'Alamat tersedia di lokasi' }}</div>
                        </div>
                    </div>

                    <div class="flex items-center gap-6 p-5 bg-gray-50 dark:bg-gray-900/50 rounded-2xl border border-gray-100 dark:border-gray-700/50">
                        <div class="w-12 h-12 shrink-0 flex items-center justify-center bg-white dark:bg-gray-800 rounded-xl shadow-sm text-gray-400">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </div>
                        <div class="min-w-0">
                            <div class="text-[10px] font-black text-indigo-500 uppercase tracking-widest">Waktu Kedatangan</div>
                            <div class="text-lg font-bold text-gray-900 dark:text-white">{{ \Carbon\Carbon::parse($booking->booking_date)->format('l, d F Y') }}</div>
                            <div class="text-lg font-black text-indigo-600 dark:text-indigo-400">{{ $booking->booking_time }} WIB</div>
                        </div>
                    </div>
                </div>

                <!-- Footer Recommendation -->
                <div class="mt-12 p-6 bg-amber-50 dark:bg-amber-900/20 border-2 border-dashed border-amber-200 dark:border-amber-800 rounded-[2rem] text-center">
                    @php
                        $arrivalTime = \Carbon\Carbon::parse($booking->booking_time)->subMinutes($booking->location->timer ?? 5)->format('H:i');
                    @endphp
                    <span class="text-amber-700 dark:text-amber-400 font-bold uppercase tracking-tight text-xs block mb-1">Peringatan Penting</span>
                    <p class="text-amber-800 dark:text-amber-300 font-medium">Mohon datang paling lambat pukul <span class="font-black text-lg underline">{{ $arrivalTime }} WIB</span> ({{ $booking->location->timer ?? 5 }} menit sebelum waktu layanan).</p>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="mt-10 flex flex-col sm:flex-row gap-4 justify-center no-print">
            <button onclick="downloadPDF()" class="px-8 py-4 bg-gray-900 dark:bg-white text-white dark:text-gray-900 rounded-full font-black uppercase tracking-widest shadow-xl hover:scale-105 active:scale-95 transition-all text-xs flex items-center justify-center gap-3">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Download PDF
            </button>
            <button onclick="printPOS()" class="px-8 py-4 bg-indigo-600 text-white rounded-full font-black uppercase tracking-widest shadow-xl hover:scale-105 active:scale-95 transition-all text-xs flex items-center justify-center gap-3">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 00-2 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 00-2 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h6z"></path></svg>
                Cetak Struk (POS)
            </button>
        </div>
        <p class="text-gray-400 mt-6 text-center text-[10px] font-medium uppercase tracking-widest no-print">Pilih format cetak yang sesuai dengan perangkat Anda.</p>
    </div>

    <!-- Hidden POS Thermal Template -->
    <div id="thermal-receipt" class="hidden font-mono text-[10pt] text-black bg-white p-4" style="width: 58mm;">
        <div class="text-center font-bold text-lg mb-2">{{ config('app.name') }}</div>
        <div class="text-center text-xs mb-4 border-b border-dashed border-black pb-2">BUKTI ANTRIAN LAYANAN</div>
        <div class="text-center text-3xl font-black mb-1">A-{{ str_pad($booking->id, 4, '0', STR_PAD_LEFT) }}</div>
        <div class="text-center text-[10px] mb-4 border-b border-dashed border-black pb-2">NOMOR ANTRIAN</div>
        
        <div class="flex justify-center items-center text-center mb-4 w-full">
            <div id="qr-code-canvas-container" class="inline-block mx-auto overflow-hidden">
                {!! QrCode::size(150)->generate($booking->uuid) !!}
            </div>
        </div>
        
        <div class="space-y-1 text-[9pt]">
            <div class="flex justify-between"><span>Tgl:</span> <span>{{ date('d/m/Y', strtotime($booking->booking_date)) }}</span></div>
            <div class="flex justify-between"><span>Jam:</span> <span>{{ $booking->booking_time }} WIB</span></div>
            <div class="flex justify-between"><span>Petugas:</span> <span>{{ $booking->loket->name ?? '-' }}</span></div>
            <div class="border-t border-dashed border-black my-2"></div>
            <div class="font-bold">Layanan:</div>
            <div>{{ $booking->service }}</div>
            <div class="font-bold mt-2">Nama:</div>
            <div>{{ $booking->name }}</div>
            <div class="border-t border-dashed border-black my-2"></div>
            <div class="text-center font-bold">LOKASI:</div>
            <div class="text-center text-[8pt]">{{ $booking->location->name }}</div>
        </div>
        
        <div class="mt-4 border-t border-dashed border-black pt-2 text-center text-[8pt]">
            Harap Datang 10 Menit Sebelum Waktu Layanan
        </div>
        <div class="text-center mt-2 text-[7pt] opacity-50">Generated via UU-{{ substr($booking->uuid, 0, 8) }}</div>
    </div>

    <style>
        @media print {
            .no-print { display: none !important; }
        }
    </style>
</div>
