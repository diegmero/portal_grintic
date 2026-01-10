<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('clients', \App\Http\Controllers\ClientController::class)->only(['index', 'store', 'edit', 'update']);
    Route::resource('projects', \App\Http\Controllers\ProjectController::class)->only(['index', 'show']);
    Route::patch('/tasks/{task}', [\App\Http\Controllers\TaskController::class, 'update'])->name('tasks.update');
    Route::post('/tasks/{task}/media', [\App\Http\Controllers\TaskMediaController::class, 'store'])->name('tasks.media.store');
    Route::post('/comments', [\App\Http\Controllers\CommentController::class, 'store'])->name('comments.store');

    Route::get('/finance', [\App\Http\Controllers\FinanceController::class, 'index'])->name('finance.index');
    Route::post('/proposals/{proposal}/convert', [\App\Http\Controllers\FinanceController::class, 'convertProposal'])->name('proposals.convert');
    Route::post('/invoices/{invoice}/payments', [\App\Http\Controllers\FinanceController::class, 'storePayment'])->name('payments.store');
});

require __DIR__.'/auth.php';
