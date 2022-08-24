<?php

use Illuminate\Support\Facades\Route;

Route::get('/latest-price', App\Http\Controllers\LatestController::class);
Route::get('/history-price', App\Http\Controllers\HistoryController::class);
