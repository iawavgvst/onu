<?php

use App\Http\Controllers\OnuController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/load-onu-data', [OnuController::class, 'loadData'])->name('load.onu.data');
