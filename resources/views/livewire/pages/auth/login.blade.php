<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div class="w-full">
    <div class="flex items-center gap-6 mb-10 pb-8 border-b border-gray-100 dark:border-gray-800">
        <div class="flex-shrink-0 p-4 bg-white dark:bg-gray-800 rounded-[2rem] shadow-xl border border-gray-50 dark:border-gray-700 transform -rotate-6">
            <x-application-logo class="w-12 h-12" />
        </div>
        <div>
            <h2 class="text-3xl font-black text-gray-900 dark:text-white tracking-tighter leading-none">Selamat Datang</h2>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mt-2">Silakan login untuk mengelola antrian</p>
        </div>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form wire:submit="login" class="space-y-6">
        <!-- Email Address -->
        <div class="space-y-1">
            <label for="email" class="text-sm font-bold text-gray-700 dark:text-gray-300 ml-1">Email</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                    </svg>
                </span>
                <input wire:model="form.email" id="email" type="email" name="email" required autofocus autocomplete="username" 
                    class="block w-full pl-11 pr-4 py-3 bg-gray-50 dark:bg-gray-700/50 border-transparent focus:border-indigo-500 focus:bg-white dark:focus:bg-gray-700 focus:ring-4 focus:ring-indigo-500/10 rounded-2xl transition-all duration-300 dark:text-white"
                    placeholder="admin@jmo.com">
            </div>
            <x-input-error :messages="$errors->get('form.email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="space-y-1">
            <label for="password" class="text-sm font-bold text-gray-700 dark:text-gray-300 ml-1">Password</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </span>
                <input wire:model="form.password" id="password" type="password" name="password" required autocomplete="current-password"
                    class="block w-full pl-11 pr-4 py-3 bg-gray-50 dark:bg-gray-700/50 border-transparent focus:border-indigo-500 focus:bg-white dark:focus:bg-gray-700 focus:ring-4 focus:ring-indigo-500/10 rounded-2xl transition-all duration-300 dark:text-white"
                    placeholder="••••••••">
            </div>
            <x-input-error :messages="$errors->get('form.password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="flex items-center justify-between">
            <label for="remember" class="inline-flex items-center cursor-pointer group">
                <input wire:model="form.remember" id="remember" type="checkbox" class="rounded-lg bg-gray-100 dark:bg-gray-700 border-transparent text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-offset-gray-800 transition-all cursor-pointer" name="remember">
                <span class="ms-2 text-sm text-gray-600 dark:text-gray-400 group-hover:text-indigo-600 transition-colors">{{ __('Ingat saya') }}</span>
            </label>
        </div>

        <div class="pt-2">
            <button type="submit" class="w-full flex justify-center py-4 px-4 border border-transparent rounded-2xl shadow-xl text-md font-black text-white bg-indigo-600 hover:bg-indigo-700 hover:shadow-indigo-500/20 active:scale-[0.98] transition-all duration-200">
                Log in
            </button>
        </div>
    </form>
</div>
