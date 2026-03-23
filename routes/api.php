<?php
 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BookingApiController;

Route::post('/booking/update-status', [BookingApiController::class, 'updateStatus']);
