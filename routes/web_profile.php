<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;

Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::post('/profile/signature', [ProfileController::class, 'uploadSignature'])->name('profile.signature');
    Route::post('/profile/password', [ProfileController::class, 'changePassword'])->name('profile.password');
});

