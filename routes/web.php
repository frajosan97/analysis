<?php

use App\Http\Controllers\ExamController;
use App\Http\Controllers\ExcelController;
use App\Http\Controllers\PdfController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ResultController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware('auth')->group(function () {

    Route::get('/', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::resources([
        'exams'     => ExamController::class,
    ]);

    Route::get('/results/{exam}/{class}', [ResultController::class, 'index'])->name('results.index');

    Route::prefix('/pdf')->group(function () {
        Route::get('/merit/{exam}/{class}', [PdfController::class, 'merit'])->name('pdf.merit');
        Route::get('/analysis/{exam}/{class}', [PdfController::class, 'analysis'])->name('pdf.analysis');
        Route::get('/report-form/{exam}/{class}', [PdfController::class, 'reportForm'])->name('pdf.report-form');
    });

    Route::prefix('/excel')->group(function () {
        Route::post('/results-import', [ExcelController::class, 'import'])->name('results.import');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
