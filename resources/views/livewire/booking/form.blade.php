<?php

use Livewire\Volt\Component;
use App\Models\Booking;
use App\Models\Location;
use App\Models\Service;
use App\Models\User;
use App\Notifications\BookingSubmitted;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;
use function Livewire\Volt\{state, rules, computed};

state([
    'name' => '', 
    'nik' => '', 
    'jmo_number' => '', 
    'whatsapp_number' => '', 
    'booking_date' => '', 
    'booking_time' => '', 
    'service' => '', 
    'location_id' => '',
    'submitted' => false,
    'booking_code' => '',
    'booking_uuid' => '',
    'showConfirm' => false,
    'searchLocation' => '',
    'searchService' => '',
]);

rules([
    'name' => 'required|min:3',
    'nik' => 'required|digits:16',
    'jmo_number' => 'required',
    'whatsapp_number' => 'required',
    'booking_date' => 'required|date|after_or_equal:today',
    'service' => 'required',
    'location_id' => 'required|exists:locations,id',
]);

$locations = computed(function () {
    return Location::when($this->searchLocation, function ($query) {
        $query->where('name', 'like', '%' . $this->searchLocation . '%');
    })->get();
});
$services = computed(function () {
    return Service::when($this->searchService, function ($query) {
        $query->where('name', 'like', '%' . $this->searchService . '%');
    })->get();
});

$validateAndConfirm = function () {
    $this->validate(null, [
        'required' => ':Attribute wajib diisi.',
        'min' => ':Attribute minimal :min karakter.',
        'digits' => ':Attribute harus :digits digit (Angka).',
        'date' => 'Format :attribute tidak valid.',
        'after_or_equal' => ':Attribute tidak boleh tanggal lampau.',
        'exists' => ':Attribute yang dipilih tidak valid.',
    ], [
        'name' => 'Nama lengkap',
        'nik' => 'NIK (KTP)',
        'jmo_number' => 'Nomor JMO',
        'whatsapp_number' => 'Nomor WhatsApp',
        'booking_date' => 'Tanggal booking',
        'service' => 'Layanan',
        'location_id' => 'Lokasi',
    ]);

    $this->showConfirm = true;
};

$submit = function () {
    if (!$this->showConfirm) return;

    // The rest of the submit logic remains similar but we close the modal first
    $this->showConfirm = false;

    // Normalize WhatsApp number
    $phone = $this->whatsapp_number;
    if (str_starts_with($phone, '0')) {
        $phone = '62' . substr($phone, 1);
    }

    // WhatsApp Validation Service
    $whatsappService = app(WhatsAppService::class);
    if (!$whatsappService->checkNumber($phone)) {
        $this->addError('whatsapp_number', 'Nomor WhatsApp tidak terdaftar atau tidak valid.');
        return;
    }

    $location = Location::find($this->location_id);
    $locationName = $location ? $location->name : '-';
    $timerInterval = $location ? $location->timer : 5;

    // Automated Booking Time Calculation
    try {
        $latestBooking = Booking::where('location_id', $this->location_id)
            ->where('booking_date', $this->booking_date)
            ->latest('booking_time')
            ->first();

        $baseTime = $latestBooking 
            ? new \DateTime($latestBooking->booking_time) 
            : new \DateTime();

        $baseTime->modify("+{$timerInterval} minutes");
        $this->booking_time = $baseTime->format('H:i');
    } catch (\Exception $e) {
        $this->booking_time = now()->format('H:i');
    }

    $booking = Booking::create([
        'name' => $this->name,
        'nik' => $this->nik, // Encrypted automatically via Model Cast
        'jmo_number' => $this->jmo_number,
        'whatsapp_number' => $this->whatsapp_number,
        'booking_date' => $this->booking_date,
        'booking_time' => $this->booking_time,
        'service' => $this->service,
        'location_id' => $this->location_id,
        'status' => 4,
    ]);

    // Send Browser/Dashboard Notification
    try {
        $usersToNotify = User::where(function ($query) use ($booking) {
            $query->where('role', 0)
                  ->orWhere('location_id', $booking->location_id);
        })->get();

        foreach ($usersToNotify as $user) {
            $user->notify(new BookingSubmitted($booking));
        }
    } catch (\Exception $e) {
        Log::error('Gagal mengirim notifikasi sistem: ' . $e->getMessage());
    }

    $checkUrl = route('booking.check', $booking->uuid);
    $this->booking_uuid = $booking->uuid;
    $this->booking_code = 'BK-' . str_pad($booking->id, 5, '0', STR_PAD_LEFT);
    $this->submitted = true;

    $bookingTimeObj = new \DateTime($this->booking_time);
    $arrivalTime = (clone $bookingTimeObj)->modify("-{$timerInterval} minutes")->format('H:i');

    $message = "Halo *{$this->name}*,\n\n" .
              "Pendaftaran antrian Anda berhasil!\n" .
              "Nomor Antrian: *{$this->booking_code}*\n" .
              "Layanan: *{$this->service}*\n" .
              "Lokasi: *{$locationName}*\n\n" .
              "Waktu Layanan: *{$this->booking_date}* pukul *{$this->booking_time} WIB*\n" .
              "Mohon Datang: Paling lambat pukul *{$arrivalTime} WIB* ({$timerInterval} menit sebelumnya).\n\n" .
              "Cek Detail Pendaftaran:\n{$checkUrl}\n\n" .
              "Terima kasih.";

    $whatsappService->sendMessage($phone, $message);
};

?>

<div class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8 dark:bg-gray-900">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900 dark:text-white">
            Booking Layanan
        </h2>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-xl">
        <div class="bg-white py-8 px-4 shadow-xl sm:rounded-2xl sm:px-10 dark:bg-gray-800 border border-gray-100 dark:border-gray-700">
            @if($submitted)
                <div class="text-center space-y-4">
                    <div class="flex items-center justify-center">
                        <div class="bg-green-100 p-3 rounded-full text-green-600">
                            <svg class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        </div>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white">Booking Berhasil!</h3>
                    <div class="mt-4 bg-indigo-50 dark:bg-indigo-900/30 p-6 rounded-2xl border border-indigo-100 dark:border-indigo-800">
                        <p class="text-xs font-bold text-indigo-500 uppercase tracking-widest mb-1">Nomor Antrian</p>
                        <span class="text-4xl font-black text-indigo-600 dark:text-indigo-400">{{ $booking_code }}</span>
                    </div>
                    <div class="mt-4">
                        <a href="{{ route('booking.check', $booking_uuid) }}" target="_blank" class="inline-flex items-center gap-2 text-indigo-600 dark:text-indigo-400 font-bold hover:underline">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                            Cek Detail Pendaftaran
                        </a>
                        <p class="text-[10px] text-gray-400 mt-1 italic">Link ini juga telah dikirimkan ke WhatsApp Anda.</p>
                    </div>
                    <button wire:click="$set('submitted', false)" class="mt-6 w-full py-3 bg-indigo-600 text-white rounded-xl font-bold hover:bg-indigo-700 transition">Buat Booking Baru</button>
                </div>
            @else
                <form wire:submit.prevent="validateAndConfirm" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 ml-1">Nama Lengkap</label>
                            <input type="text" wire:model="name" class="mt-1 block w-full rounded-2xl border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all dark:text-white">
                            @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 ml-1">NIK</label>
                            <input type="text" wire:model="nik" maxlength="16" oninput="this.value = this.value.replace(/[^0-9]/g, '')" class="mt-1 block w-full rounded-2xl border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all dark:text-white">
                            @error('nik') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 ml-1">Nomor JMO</label>
                            <input type="text" wire:model="jmo_number" class="mt-1 block w-full rounded-2xl border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all dark:text-white">
                            @error('jmo_number') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 ml-1">Nomor WhatsApp</label>
                            <input type="text" wire:model="whatsapp_number" placeholder="Contoh: 0812..." class="mt-1 block w-full rounded-2xl border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all dark:text-white">
                            @error('whatsapp_number') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 ml-1">Tanggal Booking</label>
                        <input type="date" wire:model="booking_date" min="{{ date('Y-m-d') }}" class="mt-1 block w-full rounded-2xl border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all dark:text-white uppercase">
                        @error('booking_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <!-- Searchable Service Select -->
                    <div class="relative" x-data="{ open: false }" @click.away="open = false">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 ml-1">Layanan</label>
                        <div class="mt-1 relative">
                            <button type="button" @click="open = !open" class="relative w-full bg-gray-50 dark:bg-gray-900 border border-gray-100 dark:border-gray-700 rounded-2xl py-3 pl-4 pr-10 text-left focus:outline-none focus:ring-4 focus:ring-indigo-500/10 transition-all dark:text-white">
                                <span class="block truncate">{{ $service ?: 'Pilih Layanan' }}</span>
                                <span class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none text-gray-400">
                                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                                </span>
                            </button>
                            <div x-show="open" class="absolute z-10 mt-1 w-full bg-white dark:bg-gray-800 shadow-2xl rounded-2xl py-1 overflow-hidden" x-transition>
                                <div class="p-2 border-b border-gray-50 dark:border-gray-700">
                                    <input type="text" wire:model.live="searchService" placeholder="Cari layanan..." class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border-none rounded-xl text-xs focus:ring-2 focus:ring-indigo-500">
                                </div>
                                <ul class="max-h-48 overflow-y-auto">
                                    @forelse($this->services as $svc)
                                        <li>
                                            <button type="button" wire:click="$set('service', '{{ $svc->name }}'); open = false" class="w-full text-left px-4 py-2 text-sm hover:bg-indigo-50 dark:hover:bg-indigo-900/20 dark:text-gray-300">
                                                {{ $svc->name }}
                                            </button>
                                        </li>
                                    @empty
                                        <li class="px-4 py-2 text-xs text-gray-500">Layanan tidak ditemukan</li>
                                    @endforelse
                                </ul>
                            </div>
                        </div>
                        @error('service') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <!-- Searchable Location Select -->
                    <div class="relative" x-data="{ open: false }" @click.away="open = false">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 ml-1">Lokasi</label>
                        <div class="mt-1 relative">
                            <button type="button" @click="open = !open" class="relative w-full bg-gray-50 dark:bg-gray-900 border border-gray-100 dark:border-gray-700 rounded-2xl py-3 pl-4 pr-10 text-left focus:outline-none focus:ring-4 focus:ring-indigo-500/10 transition-all dark:text-white">
                                <span class="block truncate">
                                    {{ $location_id ? ($this->locations->firstWhere('id', $location_id)->name ?? 'Pilih Lokasi') : 'Pilih Lokasi' }}
                                </span>
                                <span class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none text-gray-400">
                                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                                </span>
                            </button>
                            <div x-show="open" class="absolute z-10 mt-1 w-full bg-white dark:bg-gray-800 shadow-2xl rounded-2xl py-1 overflow-hidden" x-transition>
                                <div class="p-2 border-b border-gray-100 dark:border-gray-700">
                                    <input type="text" wire:model.live="searchLocation" placeholder="Cari lokasi..." class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border-none rounded-xl text-xs focus:ring-2 focus:ring-indigo-500">
                                </div>
                                <ul class="max-h-48 overflow-y-auto">
                                    @forelse($this->locations as $loc)
                                        <li>
                                            <button type="button" wire:click="$set('location_id', {{ $loc->id }}); open = false" class="w-full text-left px-4 py-2 text-sm hover:bg-indigo-50 dark:hover:bg-indigo-900/20 dark:text-gray-300">
                                                {{ $loc->name }}
                                                <span class="block text-[10px] text-gray-500">{{ $loc->address }}</span>
                                            </button>
                                        </li>
                                    @empty
                                        <li class="px-4 py-2 text-xs text-gray-500">Lokasi tidak ditemukan</li>
                                    @endforelse
                                </ul>
                            </div>
                        </div>
                        @error('location_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <button type="submit" class="w-full py-4 bg-indigo-600 text-white rounded-2xl font-black uppercase tracking-widest shadow-xl shadow-indigo-500/30 hover:bg-indigo-700 hover:-translate-y-1 transition active:translate-y-0">
                        Pesan Sekarang
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- Confirmation Modal -->
    @if($showConfirm)
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-gray-900/80 backdrop-blur-sm px-6">
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden border border-gray-100 dark:border-gray-700" x-transition>
                <div class="p-8 text-center border-b border-gray-50 dark:border-gray-700">
                    <h3 class="text-xl font-black text-gray-900 dark:text-white uppercase tracking-tight">Konfirmasi Pendaftaran</h3>
                    <p class="text-sm text-gray-500 mt-1">Pastikan data Anda sudah benar sebelum melanjutkan.</p>
                </div>
                <div class="p-8 space-y-4 max-h-[60vh] overflow-y-auto">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="p-4 bg-gray-50 dark:bg-gray-900/50 rounded-2xl">
                            <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Nama Lengkap</p>
                            <p class="text-sm font-bold text-gray-900 dark:text-white">{{ $name }}</p>
                        </div>
                        <div class="p-4 bg-gray-50 dark:bg-gray-900/50 rounded-2xl">
                            <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">NIK (KTP)</p>
                            <p class="text-sm font-bold text-gray-900 dark:text-white">{{ $nik }}</p>
                        </div>
                        <div class="p-4 bg-gray-50 dark:bg-gray-900/50 rounded-2xl">
                            <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Nomor JMO</p>
                            <p class="text-sm font-bold text-gray-900 dark:text-white">{{ $jmo_number }}</p>
                        </div>
                        <div class="p-4 bg-gray-50 dark:bg-gray-900/50 rounded-2xl">
                            <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">WhatsApp</p>
                            <p class="text-sm font-bold text-gray-900 dark:text-white">{{ $whatsapp_number }}</p>
                        </div>
                    </div>
                    <div class="p-4 bg-indigo-50 dark:bg-indigo-900/20 rounded-2xl border border-indigo-100 dark:border-indigo-800/50">
                        <p class="text-[9px] font-black text-indigo-400 uppercase tracking-widest mb-1">Detail Booking</p>
                        <div class="space-y-1">
                            <p class="text-sm font-bold text-indigo-600 dark:text-indigo-300">{{ $service }}</p>
                            <p class="text-xs text-gray-500 italic">{{ $this->locations->firstWhere('id', $location_id)->name ?? '-' }}</p>
                            <p class="text-xs font-black text-indigo-500 mt-2">{{ \Carbon\Carbon::parse($booking_date)->translatedFormat('l, d F Y') }}</p>
                        </div>
                    </div>
                </div>
                <div class="p-8 flex gap-3 bg-gray-50/50 dark:bg-gray-900/30">
                    <button type="button" wire:click="$set('showConfirm', false)" class="flex-1 py-3 text-gray-500 font-bold hover:bg-gray-100 dark:hover:bg-gray-700 rounded-2xl transition">Batal</button>
                    <button type="button" wire:click="submit" wire:loading.attr="disabled" class="flex-2 px-8 py-3 bg-indigo-600 text-white rounded-2xl font-black uppercase tracking-widest shadow-lg shadow-indigo-500/40 transition hover:bg-indigo-700">
                        <span wire:loading.remove wire:target="submit">Ya, Daftar Sekarang</span>
                        <span wire:loading wire:target="submit">Memproses...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
