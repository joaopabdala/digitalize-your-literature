<?php

use App\Http\Controllers\DigitalizationProcessorController;
use App\Http\Controllers\DigitalizerController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserDashboard;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {

    return view('index');
})->name('index');

Route::post('/upload', UploadController::class)->name('upload');

Route::get('register', function () {
    return view('auth.register');
})->name('register');
Route::post('register', [UserController::class, 'store'])->name('register');
Route::get('login', function () {
    return view('auth.login');
})->name('login');
Route::post('login', [UserController::class, 'login'])->name('login');
Route::post('logout', [UserController::class, 'logout'])->name('logout');

Route::group(['middleware' => 'auth'], function () {
    Route::get('dashboard', [UserDashboard::class, 'index'])->name('dashboard');
    Route::get('dashboard/{digitalizationBatch}', [UserDashboard::class, 'show'])->name('dashboard.digitalizationBatch');
    Route::post('digitalize/{digitalization}', [DigitalizationProcessorController::class, 'reDigitalize'])->name('reDigitalize');


});

Route::post('digitalize', [DigitalizationProcessorController::class, 'digitalizes'])->name('digitalize');
Route::get('digitalize/{digitalizationBatchHash}', [DigitalizerController::class, 'show'])->name('digitalize.show');
Route::get('digitalize/pdf-download/{digitalizationBatch}', [DigitalizerController::class, 'downloadPDF'])->name('digitalize.pdf');
Route::delete('digitalize/{digitalizationBatch}', [DigitalizerController::class, 'destroy'])->name('digitalize.destroy');
