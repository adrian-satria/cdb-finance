<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\UserAccessController;

// Rute-rute ini akan mewarisi middleware, prefix, dan name dari grup induk di web.php
Route::get('/user/{id}/access/edit', [UserAccessController::class, 'edit'])->name('user.access.edit');
Route::put('/user/{id}/access/update', [UserAccessController::class, 'update'])->name('user.access.update');
Route::delete('/user/{id}/access/clear', [UserAccessController::class, 'clear'])->name('user.access.clear');
Route::delete('/user/{id}/access/row', [UserAccessController::class, 'deleteRow'])->name('user.access.delete_row');

