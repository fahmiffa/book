<?php

use Livewire\Volt\Component;
use App\Models\Booking;
use App\Models\Location;
use App\Models\Service;
use App\Models\User;
use App\Notifications\BookingSubmitted;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use function Livewire\Volt\{state, rules, computed};

state([
    'name' => '', 
    'jmo_number' => '', 
    'whatsapp_number' => '', 
    'booking_date' => '', 
    'booking_time' => '', 
    'service' => '', 
    'location_id' => '',
    'submitted' => false,
    'booking_code' => ''
]);

rules([
    'name' => 'required|min:3',
    'jmo_number' => 'required',
    'whatsapp_number' => 'required',
    'booking_date' => 'required|date|after_or_equal:today',
    'service' => 'required',
    'location_id' => 'required|exists:locations,id',
]);

$locations = computed(fn () => Location::all());
$services = computed(fn () => Service::all());

$submit = function () {
    $this->validate(null, [
        'required' => ':Attribute wajib diisi.',
        'min' => ':Attribute minimal :min karakter.',
        'date' => 'Format :attribute tidak valid.',
        'after_or_equal' => ':Attribute tidak boleh tanggal lampau.',
        'exists' => ':Attribute yang dipilih tidak valid.',
    ], [
        'name' => 'Nama lengkap',
        'jmo_number' => 'Nomor JMO',
        'whatsapp_number' => 'Nomor WhatsApp',
        'booking_date' => 'Tanggal booking',
        'service' => 'Layanan',
        'location_id' => 'Lokasi',
    ]);

    // Normalize WhatsApp number: Change leading 0 to 62
    $phone = $this->whatsapp_number;
    if (str_starts_with($phone, '0')) {
        $phone = '62' . substr($phone, 1);
    }

    // WhatsApp Validation API
    try {
        $response = Http::post('https://broadcast.qlabcode.com/api/number', [
            'number' => '085640431181', // Sender/System number
            'to'     => $phone
        ]);

        if ($response->failed() || !($response->json('status') ?? false)) {
            $this->addError('whatsapp_number', 'Nomor WhatsApp tidak terdaftar atau tidak valid.');
            return;
        }
    } catch (\Exception $e) {
        $this->addError('whatsapp_number', 'Gagal memvalidasi nomor saat ini.');
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
        'jmo_number' => $this->jmo_number,
        'whatsapp_number' => $this->whatsapp_number,
        'booking_date' => $this->booking_date,
        'booking_time' => $this->booking_time,
        'service' => $this->service,
        'location_id' => $this->location_id,
        'status' => 'confirmed',
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

    // Automatic 5-digit booking code (BK-xxxxx)
    $this->booking_code = 'BK-' . str_pad($booking->id, 5, '0', STR_PAD_LEFT);
    $this->submitted = true;

    // Calculate "Mohon Datang" time (Booking time - location timer)
    $bookingTimeObj = new \DateTime($this->booking_time);
    $arrivalTimeObj = clone $bookingTimeObj;
    $arrivalTimeObj->modify("-{$timerInterval} minutes");
    $arrivalTime = $arrivalTimeObj->format('H:i');

    // Send WhatsApp Notification
    try {
        $message = "Halo *{$this->name}*,\n\n" .
                  "Pendaftaran antrian Anda berhasil!\n" .
                  "Nomor Antrian: *{$this->booking_code}*\n" .
                  "Layanan: *{$this->service}*\n" .
                  "Lokasi: *{$locationName}*\n\n" .
                  "Waktu Layanan: *{$this->booking_date}* pukul *{$this->booking_time} WIB*\n" .
                  "Mohon Datang: Paling lambat pukul *{$arrivalTime} WIB* ({$timerInterval} menit sebelumnya).\n\n" .
                  "Simpan pesan ini sebagai bukti pendaftaran.\n\n" .
                  "Terima kasih.";

        Http::post('https://broadcast.qlabcode.com/api/send', [
            'number'  => '085640431181',
            'to'      => $phone,
            'message' => $message
        ]);
    } catch (\Exception $e) {
        Log::error('Gagal mengirim notifikasi WhatsApp: ' . $e->getMessage());
    }
};

?>

<div class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8 dark:bg-gray-900">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900 dark:text-white">
            Booking Layanan
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600 dark:text-gray-400">
            Silakan isi form di bawah untuk melakukan pendaftaran antrian.
        </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-xl">
        <div class="bg-white py-8 px-4 shadow-xl sm:rounded-2xl sm:px-10 dark:bg-gray-800 border border-gray-100 dark:border-gray-700">
            @if($submitted)
                <div class="text-center space-y-4">
                    <div class="flex items-center justify-center">
                        <div class="bg-green-100 p-3 rounded-full text-green-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white">Booking Berhasil!</h3>
                    
                    <div class="mt-4 bg-white dark:bg-gray-800 border border-indigo-100 dark:border-indigo-800 rounded-2xl overflow-hidden shadow-sm">
                        <div class="bg-indigo-50 dark:bg-indigo-900/30 p-6 border-b border-indigo-100 dark:border-indigo-800 text-center">
                            <p class="text-xs font-bold text-indigo-500 uppercase tracking-widest mb-1">Nomor Antrian</p>
                            <span class="text-4xl font-black text-indigo-600 dark:text-indigo-400 tracking-tighter">{{ $booking_code }}</span>
                        </div>
                        
                        <div class="p-6 text-left space-y-4">
                            <div class="flex items-start gap-3">
                                <div class="p-2 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-tight">Nama Pendaftar</p>
                                    <p class="text-sm font-bold text-gray-900 dark:text-white">{{ $name }}</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div class="flex items-start gap-3">
                                    <div class="p-2 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-tight">Layanan</p>
                                        <p class="text-sm font-bold text-gray-900 dark:text-white">{{ $service }}</p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-3">
                                    <div class="p-2 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-tight">Lokasi</p>
                                        <p class="text-sm font-bold text-gray-900 dark:text-white">{{ \App\Models\Location::find($location_id)?->name }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-indigo-50/50 dark:bg-indigo-900/10 p-4 rounded-xl border border-dashed border-indigo-200 dark:border-indigo-800">
                                <div class="flex flex-col space-y-3">
                                    <div class="flex justify-between items-center text-xs">
                                        <span class="text-gray-500">Waktu Layanan:</span>
                                        <span class="font-bold text-gray-900 dark:text-white">{{ $booking_date }} - {{ $booking_time }} WIB</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-xs text-gray-500">Mohon Datang:</span>
                                        <span class="text-sm font-black text-indigo-600 dark:text-indigo-400">Paling lambat {{ (new \DateTime($booking_time))->modify('-' . (\App\Models\Location::find($location_id)?->timer ?? 5) . ' minutes')->format('H:i') }} WIB</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <p class="text-xs text-center text-gray-500 italic mt-4">
                        * Notifikasi bukti pendaftaran telah dikirimkan ke WhatsApp Anda.
                    </p>

                    <div class="pt-6">
                        <button wire:click="$set('submitted', false)" class="w-full inline-flex justify-center items-center py-3 px-4 border border-transparent shadow-lg text-sm font-bold rounded-xl text-white bg-indigo-600 hover:bg-indigo-700 transition active:scale-95 focus:outline-none">
                            Buat Booking Baru
                        </button>
                    </div>
                </div>
            @else
                <form 
                    x-data="bookingHandler"
                    @submit.prevent="confirmSubmit" 
                    class="space-y-6"
                >
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Lengkap</label>
                            <input type="text" wire:model="name" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition">
                            @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nomor JMO</label>
                            <input type="text" wire:model="jmo_number" oninput="this.value = this.value.replace(/[^0-9]/g, '')" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition">
                            @error('jmo_number') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nomor WhatsApp</label>
                        <input type="text" wire:model="whatsapp_number" oninput="this.value = this.value.replace(/[^0-9]/g, '')" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition">
                        @error('whatsapp_number') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal Booking</label>
                            <input type="date" wire:model="booking_date" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition">
                            @error('booking_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Layanan</label>
                        <select wire:model="service" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition">
                            <option value="">Pilih Layanan</option>
                            @foreach($this->services as $svc)
                                <option value="{{ $svc->name }}">{{ $svc->name }}</option>
                            @endforeach
                        </select>
                        @error('service') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Lokasi</label>
                        <select wire:model="location_id" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition">
                            <option value="">Pilih Lokasi</option>
                            @foreach($this->locations as $loc)
                                <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                            @endforeach
                        </select>
                        @error('location_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <button type="submit" wire:loading.attr="disabled" class="w-full flex items-center justify-center py-3 px-4 border border-transparent rounded-xl shadow-lg shadow-indigo-500/20 text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all active:scale-95 disabled:opacity-75 disabled:cursor-not-allowed">
                            <span wire:loading.remove wire:target="submit">Konfirmasi Booking</span>
                            <div wire:loading.flex wire:target="submit" class="items-center justify-center gap-2">
                                <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span class="text-sm font-bold">Loading...</span>
                            </div>
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>
</div>
