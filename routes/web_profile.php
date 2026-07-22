<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::post('/profile/signature', [ProfileController::class, 'uploadSignature'])->name('profile.signature');
    Route::post('/profile/password', [ProfileController::class, 'changePassword'])->name('profile.password');
});
