<?php

use App\Http\Controllers\DigitalizerController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('main');
});

    Route::get('register', function (){return view('auth.register');})->name('register');
    Route::post('register', [UserController::class, 'store'])->name('register');
    Route::get('login', function (){return view('auth.login');})->name('login');
    Route::post('login', [UserController::class, 'login'])->name('login');
    Route::post('logout', [UserController::class, 'logout'])->name('logout');
    Route::get('dashboard', function (){return 'dashboard';})->name('dashboard');

Route::post('digitalize', [DigitalizerController::class, 'digitalizes'])->name('digitalize');
Route::get('digitalize/pdf-download/{digitalization}', [DigitalizerController::class, 'downloadPDF'])->name('digitalize.pdf');
