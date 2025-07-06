<?php
// File: routes/api.php

use App\Http\Controllers\Api\LicenseValidationController; // Impor controller
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// --- RUTE UNTUK VALIDASI LISENSI ---
Route::post('/licenses/validate', [LicenseValidationController::class, 'validateLicense'])
    ->middleware('throttle:10,1'); // Keamanan: Batasi 10 request per menit dari 1 IP
