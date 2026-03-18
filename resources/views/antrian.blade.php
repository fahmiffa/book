<x-app-layout>
    <x-slot name="header">
        <h2 class="font-black text-2xl text-gray-900 dark:text-white leading-tight tracking-tight">
            {{ __('Sistem Antrian') }}
        </h2>
    </x-slot>

    <livewire:antrian.index />
</x-app-layout>
