<?php

use Livewire\Volt\Component;
use App\Models\Loket;
use App\Models\Location;
use App\Models\User;
use function Livewire\Volt\{state, rules, computed, mount, updated};

state(['name' => '', 'location_id' => '', 'user_id' => '', 'editingLoket' => null, 'showModal' => false]);

mount(function () {
    // Role 2 (Petugas) cannot access this page
    if (auth()->user()->role === 2) {
        return $this->redirect(route('dashboard'), navigate: true);
    }
    
    // Auto-set location for non-Super Admins
    if (auth()->user()->role !== 0) {
        $this->location_id = auth()->user()->location_id;
    }
});

rules([
    'name' => 'required|min:2',
    'location_id' => 'required|exists:locations,id',
    'user_id' => 'required|exists:users,id',
]);

$lokets = computed(function () {
    $user = auth()->user();
    $query = Loket::with(['location', 'user']);
    
    if ($user->role !== 0) {
        $query->where('location_id', $user->location_id);
    }
    
    return $query->latest()->get();
});

$locations = computed(function () {
    $user = auth()->user();
    if ($user->role !== 0) {
        return Location::where('id', $user->location_id)->get();
    }
    return Location::all();
});

$users = computed(function () {
    $assignedUserIds = Loket::whereNotNull('user_id')
        ->when($this->editingLoket, fn($q) => $q->where('id', '!=', $this->editingLoket->id))
        ->pluck('user_id')
        ->toArray();

    return User::where('role', 2)
        ->whereNotIn('id', $assignedUserIds)
        ->when($this->location_id, fn($q) => $q->where('location_id', $this->location_id))
        ->get();
});

updated(['location_id' => function () {
    $this->user_id = '';
}]);

$save = function () {
    $user = auth()->user();
    
    // Force location for non-Super Admins
    if ($user->role !== 0) {
        $this->location_id = $user->location_id;
    }

    $this->validate();

    if ($this->editingLoket) {
        $this->editingLoket->update([
            'name' => $this->name,
            'location_id' => $this->location_id,
            'user_id' => $this->user_id,
        ]);
    } else {
        Loket::create([
            'name' => $this->name,
            'location_id' => $this->location_id,
            'user_id' => $this->user_id,
        ]);
    }

    if ($user->role === 0) {
        $this->reset(['name', 'location_id', 'user_id', 'editingLoket', 'showModal']);
    } else {
        $this->reset(['name', 'user_id', 'editingLoket', 'showModal']);
        $this->location_id = $user->location_id; // Keep location_id for non-Super Admins
    }
};

$openCreate = function () {
    $user = auth()->user();
    $this->reset(['name', 'user_id', 'editingLoket']);
    if ($user->role === 0) {
        $this->location_id = '';
    } else {
        $this->location_id = $user->location_id;
    }
    $this->showModal = true;
};

$edit = function (Loket $loket) {
    if (auth()->user()->role !== 0 && $loket->location_id !== auth()->user()->location_id) {
        return;
    }
    
    $this->editingLoket = $loket;
    $this->name = $loket->name;
    $this->location_id = $loket->location_id;
    $this->user_id = $loket->user_id;
    $this->showModal = true;
};

$delete = function (Loket $loket) {
    if (auth()->user()->role !== 0 && $loket->location_id !== auth()->user()->location_id) {
        return;
    }
    $loket->delete();
};

?>

<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-[0.2em] mb-1">Pengaturan Loket Layanan</p>
        </div>
        <button wire:click="openCreate" class="px-4 py-2 bg-pink-600 hover:bg-pink-700 text-white rounded-lg transition duration-300 flex items-center gap-2 shadow-lg">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
            </svg>
            Tambah
        </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($this->lokets as $loket)
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden border border-gray-100 dark:border-gray-700 hover:shadow-xl transition-shadow duration-300">
                <div class="p-5">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $loket->name }}</h3>
                            <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                {{ $loket->location->name }}
                            </p>
                            <p class="text-gray-500 dark:text-gray-400 text-xs mt-2 flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                Akun: <span class="font-bold text-indigo-600 dark:text-indigo-400">{{ $loket->user?->name ?? 'Belum terhubung' }}</span>
                            </p>
                        </div>
                    </div>
                    <div class="mt-4 flex justify-end gap-2 border-t border-gray-50 dark:border-gray-700 pt-4">
                        <button wire:click="edit({{ $loket->id }})" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </button>
                        <button wire:click="delete({{ $loket->id }})" wire:confirm="Yakin ingin menghapus loket ini?" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition">
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
                    <h3 class="text-xl font-bold dark:text-white">{{ $editingLoket ? 'Edit Loket' : 'Tambah Loket Baru' }}</h3>
                    <button wire:click="$set('showModal', false)" class="text-gray-400 hover:text-gray-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <form wire:submit="save" class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Loket</label>
                        <input type="text" wire:model="name" placeholder="Contoh: Loket 1" class="mt-1 block w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-pink-500 focus:ring-pink-500 transition">
                        @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <!-- Searchable Location Select -->
                    @if(auth()->user()->role === 0)
                        <div class="relative" x-data="dropdownSearch">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 ml-1">Lokasi</label>
                            <button type="button" @click="toggle" class="mt-1 relative w-full bg-gray-50 dark:bg-gray-700/50 border-transparent rounded-2xl py-3 pl-4 pr-10 text-left cursor-default focus:outline-none focus:ring-4 focus:ring-pink-500/10 sm:text-sm transition-all dark:text-white">
                                <span class="block truncate">
                                    {{ $location_id ? ($this->locations->firstWhere('id', $location_id)->name ?? 'Pilih Lokasi') : 'Pilih Lokasi' }}
                                </span>
                                <span class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                            </button>

                            <div x-show="open" @click.away="close" x-transition class="absolute z-10 mt-1 w-full bg-white dark:bg-gray-800 shadow-2xl max-h-60 rounded-2xl py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm" x-cloak>
                                <div class="sticky top-0 z-10 bg-white dark:bg-gray-800 p-2 border-b border-gray-100 dark:border-gray-700">
                                    <input type="text" x-model="search" x-ref="searchInput" placeholder="Cari lokasi..." class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border-none rounded-xl text-sm focus:ring-2 focus:ring-pink-500">
                                </div>
                                <ul class="pt-1">
                                    @foreach($this->locations as $loc)
                                        <li x-show="search === '' || '{{ strtolower($loc->name) }}'.includes(search.toLowerCase())" 
                                            @click="select('location_id', {{ $loc->id }})" class="cursor-pointer select-none relative py-3 pl-4 pr-9 hover:bg-pink-50 dark:hover:bg-pink-900/20 text-gray-900 dark:text-gray-200 transition-colors">
                                            <span class="{{ $location_id == $loc->id ? 'font-bold text-pink-600' : 'font-normal' }}">{{ $loc->name }}</span>
                                            @if($location_id == $loc->id)
                                                <span class="absolute inset-y-0 right-0 flex items-center pr-4 text-pink-600">
                                                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                    </svg>
                                                </span>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                            @error('location_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    @endif

                    <!-- Searchable Petugas Select -->
                    <div class="relative" x-data="dropdownSearch">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 ml-1">Akun Petugas Loket</label>
                        <button type="button" @click="toggle" :disabled="!$wire.location_id" class="mt-1 relative w-full bg-gray-50 dark:bg-gray-700/50 disabled:opacity-50 border-transparent rounded-2xl py-3 pl-4 pr-10 text-left cursor-default focus:outline-none focus:ring-4 focus:ring-pink-500/10 sm:text-sm transition-all dark:text-white">
                            <span class="block truncate">
                                {{ $user_id ? ($this->users->firstWhere('id', $user_id)->name ?? 'Pilih Akun Petugas') : 'Pilih Akun Petugas' }}
                            </span>
                            <span class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </span>
                        </button>

                        <div x-show="open" @click.away="close" x-transition class="absolute z-10 mt-1 w-full bg-white dark:bg-gray-800 shadow-2xl max-h-60 rounded-2xl py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm" x-cloak>
                            <div class="sticky top-0 z-10 bg-white dark:bg-gray-800 p-2 border-b border-gray-100 dark:border-gray-700">
                                <input type="text" x-model="search" x-ref="searchInput" placeholder="Cari petugas..." class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border-none rounded-xl text-sm focus:ring-2 focus:ring-pink-500">
                            </div>
                            <ul class="pt-1">
                                @forelse($this->users as $user)
                                    <li x-show="search === '' || '{{ strtolower($user->name) }}'.includes(search.toLowerCase()) || '{{ strtolower($user->email) }}'.includes(search.toLowerCase())" 
                                        @click="select('user_id', {{ $user->id }})" class="cursor-pointer select-none relative py-3 pl-4 pr-9 hover:bg-pink-50 dark:hover:bg-pink-900/20 text-gray-900 dark:text-gray-200 transition-colors">
                                        <div>
                                            <span class="{{ $user_id == $user->id ? 'font-bold text-pink-600' : 'font-normal' }}">{{ $user->name }}</span>
                                            <span class="block text-xs text-gray-400">{{ $user->email }}</span>
                                        </div>
                                        @if($user_id == $user->id)
                                            <span class="absolute inset-y-0 right-0 flex items-center pr-4 text-pink-600">
                                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                </svg>
                                            </span>
                                        @endif
                                    </li>
                                @empty
                                    <li class="py-4 text-center text-gray-500 text-xs">
                                        Tidak ada petugas ditemukan di lokasi ini.
                                    </li>
                                @endforelse
                            </ul>
                        </div>
                        @error('user_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div class="pt-4 flex justify-end gap-3">
                        <button type="button" wire:click="$set('showModal', false)" class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-xl transition">Batal</button>
                        <button type="submit" class="px-6 py-2 bg-pink-600 text-white rounded-xl hover:bg-pink-700 shadow-md transition">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

</div>
