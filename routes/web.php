<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\EmailConnectionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    //  Profile
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    //  Connection
        Route::get('/test-connection', [EmailConnectionController::class, 'connection_test'])->name('connection');
        Route::get('/email-info', [EmailConnectionController::class, 'email-info'])->name('connection.email.info');
        Route::get('/email-pdf-letters', [EmailConnectionController::class, 'email_pdf_letters'])->name('connection.email.pdf.letters');
        Route::get('/delete-pdf', [EmailConnectionController::class, 'delete_pdf'])->name('connection.delete.pdf');

});

require __DIR__.'/auth.php';
