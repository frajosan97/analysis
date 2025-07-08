<?php

use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Route;

Route::get('/subjects', [ApiController::class, 'subjects'])->name('api.subjects');
