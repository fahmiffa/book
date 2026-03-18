<?php

use Livewire\Volt\Component;
use App\Models\Service;
use function Livewire\Volt\{state, rules, computed};

state(['name' => '', 'editingService' => null, 'showModal' => false]);

rules(['name' => 'required|min:3']);

$services = computed(fn () => Service::latest()->get());

$save = function () {
    $this->validate();

    if ($this->editingService) {
        $this->editingService->update(['name' => $this->name]);
    } else {
        Service::create(['name' => $this->name]);
    }

    $this->reset(['name', 'editingService', 'showModal']);
};

$edit = function (Service $service) {
    $this->editingService = $service;
    $this->name = $service->name;
    $this->showModal = true;
};

$delete = function (Service $service) {
    $service->delete();
};

?>

<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-[0.2em] mb-1">Daftar Layanan Tersedia</p>
        </div>
        <button wire:click="$set('showModal', true)" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition duration-300 flex items-center gap-2 shadow-lg">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
            </svg>
            Tambah
        </button>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th class="px-6 py-4 font-semibold text-gray-700 dark:text-gray-200">Nama Layanan</th>
                    <th class="px-6 py-4 font-semibold text-gray-700 dark:text-gray-200 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @foreach($this->services as $service)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                        <td class="px-6 py-4 dark:text-gray-300">{{ $service->name }}</td>
                        <td class="px-6 py-4 text-right flex justify-end gap-2">
                            <button wire:click="edit({{ $service->id }})" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </button>
                            <button wire:click="delete({{ $service->id }})" wire:confirm="Yakin ingin menghapus layanan ini?" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
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
                    <h3 class="text-xl font-bold dark:text-white">{{ $editingService ? 'Edit Layanan' : 'Tambah Layanan Baru' }}</h3>
                    <button wire:click="$set('showModal', false)" class="text-gray-400 hover:text-gray-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <form wire:submit="save" class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Layanan</label>
                        <input type="text" wire:model="name" class="mt-1 block w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition">
                        @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
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
