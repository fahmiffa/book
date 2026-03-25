<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'booking.form');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('lokasi', 'lokasi')->name('lokasi');
    Route::view('akun', 'akun')->name('akun');
    Route::view('layanan', 'layanan')->name('layanan');
    Route::view('loket', 'loket')->name('loket');
    Route::view('booking-list', 'booking.index')->name('booking.index');
    Route::view('antrian', 'antrian')->name('antrian');
    Route::view('task', 'task')->name('task');
});

Route::view('booking', 'booking.form')->name('booking.form');
Route::view('booking/check/{uuid}', 'booking.check')->name('booking.check');

Route::get('display/{location}', function (App\Models\Location $location) {
    return view('display', ['location' => $location]);
})->name('display.public');

Route::get('sse/antrian/{location_id}', [App\Http\Controllers\SseController::class, 'stream'])->name('sse.antrian');

require __DIR__.'/auth.php';
