<?php

use Livewire\Volt\Component;

new class extends Component {
    public function getNotificationsProperty()
    {
        return auth()->user()->unreadNotifications;
    }

    public function markAsRead($id)
    {
        auth()->user()->unreadNotifications->where('id', $id)->markAsRead();
    }
    
    public function clearAll()
    {
        auth()->user()->unreadNotifications->markAsRead();
    }
}; ?>

<div class="relative ms-3" wire:poll.10s>
    <x-dropdown align="right" width="w-96" contentClasses="py-0 overflow-hidden bg-white dark:bg-gray-800 shadow-2xl rounded-2xl border border-gray-100 dark:border-gray-700">
        <x-slot name="trigger">
            <button class="group relative p-2 text-gray-400 hover:text-indigo-500 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 rounded-xl transition-all duration-300">
                <svg class="h-6 w-6 transform group-hover:rotate-12 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                @if($this->notifications->count() > 0)
                    <span class="absolute top-1.5 right-1.5 flex h-4 w-4">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-4 w-4 bg-red-500 text-[9px] text-white font-black items-center justify-center ring-2 ring-white dark:ring-gray-800">
                            {{ $this->notifications->count() }}
                        </span>
                    </span>
                @endif
            </button>
        </x-slot>

        <x-slot name="content">
            <div class="px-4 py-3 bg-gray-50/50 dark:bg-gray-900/50 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-black uppercase tracking-widest text-gray-500 dark:text-gray-400">Notifikasi</span>
                    <span class="px-1.5 py-0.5 bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 text-[10px] font-bold rounded-md">{{ $this->notifications->count() }}</span>
                </div>
                @if($this->notifications->count() > 0)
                    <button wire:click="clearAll" class="text-[10px] font-bold text-indigo-600 dark:text-indigo-400 hover:underline">Tandai semua dibaca</button>
                @endif
            </div>

            <div class="max-h-[32rem] overflow-y-auto divide-y divide-gray-50 dark:divide-gray-700/50">
                @forelse($this->notifications as $notification)
                    <div class="p-4 hover:bg-indigo-50/30 dark:hover:bg-indigo-900/10 transition-colors flex items-start gap-4">
                        <div class="shrink-0 mt-1">
                            <div class="h-8 w-8 rounded-full bg-indigo-100 dark:bg-indigo-900/40 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-[13px] text-gray-900 dark:text-gray-100 font-bold leading-snug break-words">
                                {{ $notification->data['message'] }}
                            </p>
                            <div class="flex items-center gap-2 mt-2">
                                <span class="text-[10px] font-bold text-gray-500 dark:text-gray-400 flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    {{ $notification->created_at->diffForHumans() }}
                                </span>
                            </div>
                        </div>
                        <button wire:click="markAsRead('{{ $notification->id }}')" class="shrink-0 p-1.5 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg text-gray-400 transition" title="Tandai dibaca">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                        </button>
                    </div>
                @empty
                    <div class="py-12 px-6 text-center space-y-3">
                        <div class="flex justify-center">
                            <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-full text-gray-300 dark:text-gray-600">
                                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 font-medium">Belum ada notifikasi baru untuk Anda.</p>
                    </div>
                @endforelse
            </div>
            
            <div class="p-3 bg-gray-50/50 dark:bg-gray-900/50 text-center border-t border-gray-100 dark:border-gray-700">
                <a href="{{ route('booking.index') }}" class="inline-flex items-center gap-2 text-[11px] text-gray-600 dark:text-gray-400 font-black uppercase tracking-widest hover:text-indigo-600 dark:hover:text-indigo-400 transition">
                    Lihat Semua Booking
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
                </a>
            </div>
        </x-slot>
    </x-dropdown>
</div>
