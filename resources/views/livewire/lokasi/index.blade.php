<?php

use Livewire\Volt\Component;
use App\Models\Location;
use function Livewire\Volt\{state, rules, computed, mount};

mount(function () {
    if (auth()->user()->role === 2) {
        return $this->redirect(route('dashboard'), navigate: true);
    }
});

state(['name' => '', 'address' => '', 'timer' => 5, 'editingLocation' => null, 'showModal' => false]);

rules([
    'name' => 'required|min:3',
    'address' => 'required',
    'timer' => 'required|integer|min:1',
]);

$locations = computed(fn () => Location::latest()->get());

$save = function () {
    $this->validate();

    if ($this->editingLocation) {
        $this->editingLocation->update([
            'name' => $this->name,
            'address' => $this->address,
            'timer' => $this->timer,
        ]);
    } else {
        Location::create([
            'name' => $this->name,
            'address' => $this->address,
            'timer' => $this->timer,
        ]);
    }

    $this->reset(['name', 'address', 'timer', 'editingLocation', 'showModal']);
};

$edit = function (Location $location) {
    $this->editingLocation = $location;
    $this->name = $location->name;
    $this->address = $location->address;
    $this->timer = $location->timer;
    $this->showModal = true;
};

$delete = function (Location $location) {
    $location->delete();
};

?>

<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-[0.2em] mb-1">Manajemen Lokasi & Cabang</p>
        </div>
        <button wire:click="$set('showModal', true)" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition duration-300 flex items-center gap-2 shadow-lg">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
            </svg>
            Tambah
        </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($this->locations as $location)
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden border border-gray-100 dark:border-gray-700 hover:shadow-xl transition-shadow duration-300">
                <div class="p-5">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $location->name }}</h3>
                            <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                {{ $location->address }}
                            </p>
                            <p class="text-indigo-600 dark:text-indigo-400 text-xs font-bold mt-2 flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Interval: {{ $location->timer }} Menit
                            </p>
                        </div>
                    </div>
                    <div class="mt-4 flex justify-end gap-2 border-t border-gray-50 dark:border-gray-700 pt-4">
                        <button wire:click="edit({{ $location->id }})" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </button>
                        <button wire:click="delete({{ $location->id }})" wire:confirm="Yakin ingin menghapus lokasi ini?" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-50 backdrop-blur-sm">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-md transform transition-all">
                <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                    <h3 class="text-xl font-bold dark:text-white">{{ $editingLocation ? 'Edit Lokasi' : 'Tambah Lokasi Baru' }}</h3>
                    <button wire:click="$set('showModal', false)" class="text-gray-400 hover:text-gray-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <form wire:submit="save" class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Lokasi</label>
                        <input type="text" wire:model="name" class="mt-1 block w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition">
                        @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Alamat</label>
                        <textarea wire:model="address" rows="3" class="mt-1 block w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition"></textarea>
                        @error('address') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Interval Waktu (Menit)</label>
                        <div class="mt-1 flex items-center gap-3">
                            <input type="number" wire:model="timer" class="block w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition">
                            <span class="text-sm font-bold text-gray-500">Menit</span>
                        </div>
                        @error('timer') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div class="pt-4 flex justify-end gap-3">
                        <button type="button" wire:click="$set('showModal', false)" class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-xl transition">Batal</button>
                        <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 shadow-md transition">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
