<?php

use Livewire\Volt\Component;
use App\Models\User;
use App\Models\Location;
use Illuminate\Support\Facades\Hash;
use function Livewire\Volt\{state, rules, computed, mount};

mount(function () {
    if (auth()->user()->role === 2) {
        return $this->redirect(route('dashboard'), navigate: true);
    }
});

state([
    'name' => '', 
    'email' => '', 
    'password' => '', 
    'location_id' => '', 
    'role' => 1,
    'editingUser' => null, 
    'showModal' => false,
    'search' => '',
    'filter_location_id' => '',
    'searchLocationModal' => '',
]);

rules(fn () => [
    'name' => 'required|min:3',
    'email' => 'required|email|unique:users,email,' . ($this->editingUser?->id ?? 'NULL'),
    // 'password' => 'nullable|min:8',
    'location_id' => 'required|exists:locations,id',
    'role' => 'required|integer',
]);

$users = computed(function () {
    $user = auth()->user();
    $query = User::with('location');

    // Scoping for non-Super Admins
    if ($user->role !== 0) {
        $query->where('location_id', $user->location_id)
              ->where('role', '>', 0);
    }

    return $query->when($this->search, fn($q) => $q->where('name', 'like', '%' . $this->search . '%'))
        ->when($this->filter_location_id, fn($q) => $q->where('location_id', $this->filter_location_id))
        ->latest()
        ->get();
});

$locations = computed(function () {
    $user = auth()->user();
    $query = Location::query();
    
    if ($user->role !== 0) {
        $query->where('id', $user->location_id);
    }

    if ($this->searchLocationModal) {
        $query->where('name', 'like', '%' . $this->searchLocationModal . '%');
    }

    return $query->get();
});

$save = function () {
    $user = auth()->user();
    
    // Auto-set location and role for non-Super Admins
    if ($user->role !== 0) {
        $this->location_id = $user->location_id;
        $this->role = 2; // Non-Super Admins can only create/manage Petugas Loket
    }

    $this->validate();

    if ($this->editingUser) {
        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'location_id' => $this->location_id,
            'role' => $this->role,
        ];
        if ($this->password) {
            $data['password'] = Hash::make($this->password);
        }
        $this->editingUser->update($data);
    } else {
        // $this->validate(['password' => 'required|min:8']);
        User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
            'location_id' => $this->location_id,
            'role' => $this->role,
        ]);
    }

    $this->reset(['name', 'email', 'password', 'location_id', 'role', 'editingUser', 'showModal']);
};

$edit = function (User $user) {
    // Security check: Non-Super Admin cannot edit Role 0 or users from other locations
    if (auth()->user()->role !== 0 && ($user->role === 0 || $user->location_id !== auth()->user()->location_id)) {
        return;
    }

    $this->editingUser = $user;
    $this->name = $user->name;
    $this->email = $user->email;
    $this->location_id = $user->location_id;
    $this->role = $user->role;
    $this->password = '';
    $this->showModal = true;
};

$delete = function (User $user) {
    if ($user->id === auth()->id()) {
        return;
    }

    // Security check: Non-Super Admin cannot delete Role 0 or users from other locations
    if (auth()->user()->role !== 0 && ($user->role === 0 || $user->location_id !== auth()->user()->location_id)) {
        return;
    }

    $user->delete();
};

?>

<div class="p-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <p class="text-[10px] text-gray-500 uppercase font-black tracking-widest">Pengaturan Hak Akses Pengguna</p>
        </div>
        <div class="flex flex-wrap items-center gap-3 w-full md:w-auto">
            <!-- Search Input -->
            <div class="relative flex-grow md:flex-grow-0 group">
                <input type="text" wire:model.live="search" placeholder="Cari nama..." class="pl-10 pr-4 py-2 w-full md:w-64 bg-gray-50 dark:bg-gray-700/50 border-transparent rounded-xl text-sm focus:bg-white dark:focus:bg-gray-700 focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all dark:text-white">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-gray-400 group-focus-within:text-emerald-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>

            <!-- Location Filter -->
            @if(auth()->user()->role === 0)
                <select wire:model.live="filter_location_id" class="bg-gray-50 dark:bg-gray-700/50 border-transparent rounded-xl text-sm py-2 px-3 focus:bg-white dark:focus:bg-gray-700 focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all dark:text-white">
                    <option value="">Semua Lokasi</option>
                    @foreach($this->locations as $loc)
                        <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                    @endforeach
                </select>
            @endif

            <button wire:click="$set('showModal', true)" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition duration-300 flex items-center gap-2 shadow-lg whitespace-nowrap">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                </svg>
                Tambah
            </button>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th class="px-6 py-4 font-semibold text-gray-700 dark:text-gray-200">Nama</th>
                    <th class="px-6 py-4 font-semibold text-gray-700 dark:text-gray-200">Email</th>
                    <th class="px-6 py-4 font-semibold text-gray-700 dark:text-gray-200">Peran</th>
                    <th class="px-6 py-4 font-semibold text-gray-700 dark:text-gray-200">Lokasi</th>
                    <th class="px-6 py-4 font-semibold text-gray-700 dark:text-gray-200 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @foreach($this->users as $user)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                        <td class="px-6 py-4 dark:text-gray-300">{{ $user->name }}</td>
                        <td class="px-6 py-4 dark:text-gray-300">{{ $user->email }}</td>
                        <td class="px-6 py-4">
                            @if($user->role === 0)
                                <span class="px-3 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-widest border bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400 border-purple-200 dark:border-purple-800">Admin</span>
                            @elseif($user->role === 1)
                                <span class="px-3 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-widest border bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-600">Petugas</span>
                            @else
                                <span class="px-3 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-widest border bg-pink-100 text-pink-700 dark:bg-pink-900/30 dark:text-pink-400 border-pink-200 dark:border-pink-800">Petugas Loket</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1.5 bg-indigo-50 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-800/50 rounded-xl text-[10px] font-black uppercase tracking-widest">
                                {{ $user->location?->name ?? 'Belum Diatur' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right flex justify-end gap-2">
                            <button wire:click="edit({{ $user->id }})" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </button>
                            @if($user->id !== auth()->id())
                                <button wire:click="delete({{ $user->id }})" wire:confirm="Yakin ingin menghapus akun ini?" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-50 backdrop-blur-sm">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-md transform transition-all">
                <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                    <h3 class="text-xl font-bold dark:text-white">{{ $editingUser ? 'Edit Akun' : 'Tambah Akun Baru' }}</h3>
                    <button wire:click="$set('showModal', false)" class="text-gray-400 hover:text-gray-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <form wire:submit="save" class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Lengkap</label>
                        <input type="text" wire:model="name" class="mt-1 block w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition">
                        @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                        <input type="email" wire:model="email" class="mt-1 block w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition">
                        @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Password {{ $editingUser ? '(Kosongkan jika tidak diubah)' : '' }}</label>
                        <input type="password" wire:model="password" class="mt-1 block w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition">
                        @error('password') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    @if(auth()->user()->role === 0)
                        <!-- Searchable Location Select -->
                        <div class="relative" x-data="dropdownSearch()" @click.away="close">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 ml-1">Lokasi Penugasan</label>
                            <div class="mt-1 relative">
                                <button type="button" @click="toggle" class="relative w-full bg-gray-50 dark:bg-gray-700 border border-transparent rounded-xl py-3 pl-4 pr-10 text-left focus:outline-none focus:ring-4 focus:ring-emerald-500/10 transition-all dark:text-white">
                                    <span class="block truncate">
                                        {{ $location_id ? ($this->locations->firstWhere('id', $location_id)->name ?? 'Pilih Lokasi') : 'Pilih Lokasi' }}
                                    </span>
                                    <span class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none text-gray-400">
                                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                                    </span>
                                </button>
                                <div x-show="open" class="absolute z-[60] mt-1 w-full bg-white dark:bg-gray-800 shadow-2xl rounded-xl py-1 overflow-hidden border border-gray-100 dark:border-gray-700" x-transition>
                                    <div class="p-2 border-b border-gray-100 dark:border-gray-700">
                                        <input type="text" 
                                            wire:model.live.debounce.300ms="searchLocationModal" 
                                            x-ref="searchInput" 
                                            placeholder="Cari lokasi..." 
                                            class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border-none rounded-lg text-xs focus:ring-2 focus:ring-emerald-500">
                                    </div>
                                    <ul class="max-h-48 overflow-y-auto">
                                        @forelse($this->locations as $loc)
                                            <li>
                                                <button type="button" @click="select('location_id', {{ $loc->id }})" class="w-full text-left px-4 py-2 text-sm hover:bg-emerald-50 dark:hover:bg-emerald-900/20 dark:text-gray-300">
                                                    {{ $loc->name }}
                                                </button>
                                            </li>
                                        @empty
                                            <li class="px-4 py-2 text-xs text-gray-500 text-center italic">Lokasi tidak ditemukan</li>
                                        @endforelse
                                    </ul>
                                </div>
                            </div>
                            @error('location_id') <span class="text-red-500 text-xs mt-1 block px-1">{{ $message }}</span> @enderror
                        </div>
                    @endif

                    <!-- Searchable Role Select -->
                    <div class="relative" x-data="dropdownSearch({ 
                        items: [
                            { id: 2, name: 'Petugas Loket' }
                            @if(auth()->user()->role === 0),
                                { id: 1, name: 'Petugas (Lokasi)' },
                                { id: 0, name: 'Admin' }
                            @endif
                        ]
                    })" @click.away="close">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 ml-1">Peran (Role)</label>
                        <div class="mt-1 relative">
                            <button type="button" @click="toggle" class="relative w-full bg-gray-50 dark:bg-gray-700 border border-transparent rounded-xl py-3 pl-4 pr-10 text-left focus:outline-none focus:ring-4 focus:ring-emerald-500/10 transition-all dark:text-white">
                                <span class="block truncate">
                                    @if($role === 0) Admin
                                    @elseif($role === 1) Petugas
                                    @elseif($role === 2) Petugas Loket
                                    @else Pilih Peran
                                    @endif
                                </span>
                                <span class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none text-gray-400">
                                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                                </span>
                            </button>
                            <div x-show="open" class="absolute z-[60] mt-1 w-full bg-white dark:bg-gray-800 shadow-2xl rounded-xl py-1 overflow-hidden border border-gray-100 dark:border-gray-700" x-transition>
                                <div class="p-2 border-b border-gray-100 dark:border-gray-700">
                                    <input type="text" x-model="search" x-ref="searchInput" placeholder="Cari peran..." class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border-none rounded-lg text-xs focus:ring-2 focus:ring-emerald-500">
                                </div>
                                <ul class="max-h-40 overflow-y-auto">
                                    <template x-for="r in filteredItems" :key="r.id">
                                        <li>
                                            <button type="button" @click="select('role', r.id)" class="w-full text-left px-4 py-2 text-sm hover:bg-emerald-50 dark:hover:bg-emerald-900/20 dark:text-gray-300" x-text="r.name"></button>
                                        </li>
                                    </template>
                                    <li x-show="filteredItems.length === 0" class="px-4 py-2 text-xs text-gray-500">Peran tidak ditemukan</li>
                                </ul>
                            </div>
                        </div>
                        @error('role') <span class="text-red-500 text-xs mt-1 block px-1">{{ $message }}</span> @enderror
                    </div>

                    <div class="pt-4 flex justify-end gap-3">
                        <button type="button" wire:click="$set('showModal', false)" class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-xl transition">Batal</button>
                        <button type="submit" class="px-8 py-3 bg-emerald-600 text-white rounded-xl font-black uppercase tracking-widest hover:bg-emerald-700 shadow-lg shadow-emerald-500/30 transition-all active:scale-95">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
