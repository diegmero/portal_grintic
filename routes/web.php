<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

// Enable broadcasting authentication endpoint
Broadcast::routes(['middleware' => ['web', 'auth']]);

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

    // Finance Routes moved inside auth group

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('clients', \App\Http\Controllers\ClientController::class)->only(['index', 'store', 'edit', 'update']);
    Route::post('/clients/{client}/users', [\App\Http\Controllers\ClientController::class, 'storeUser'])->name('clients.users.store');
    Route::put('/clients/{client}/users/{user}', [\App\Http\Controllers\ClientController::class, 'updateUser'])->name('clients.users.update');
    Route::delete('/clients/{client}/users/{user}', [\App\Http\Controllers\ClientController::class, 'destroyUser'])->name('clients.users.destroy');
    Route::resource('projects', \App\Http\Controllers\ProjectController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
    Route::get('/projects/{project}/board', [\App\Http\Controllers\ProjectController::class, 'board'])->name('projects.board');
    Route::get('/my-projects/{project}', [\App\Http\Controllers\ProjectController::class, 'clientView'])->name('projects.client-view');
    
    // Project Media
    Route::post('/projects/{project}/media', [\App\Http\Controllers\ProjectMediaController::class, 'store'])->name('projects.media.store');
    Route::get('/projects/{project}/media/{media}', [\App\Http\Controllers\ProjectMediaController::class, 'show'])->name('projects.media.show');
    Route::delete('/projects/{project}/media/{media}', [\App\Http\Controllers\ProjectMediaController::class, 'destroy'])->name('projects.media.destroy');

    // Stage Routes
    Route::post('/projects/{project}/stages/reorder', [\App\Http\Controllers\StageController::class, 'reorder'])->name('stages.reorder');
    Route::post('/projects/{project}/stages', [\App\Http\Controllers\StageController::class, 'store'])->name('stages.store');
    Route::put('/stages/{stage}', [\App\Http\Controllers\StageController::class, 'update'])->name('stages.update');
    Route::delete('/stages/{stage}', [\App\Http\Controllers\StageController::class, 'destroy'])->name('stages.destroy');
    Route::post('/stages/{stage}/media', [\App\Http\Controllers\StageMediaController::class, 'store'])->name('stages.media.store');
    Route::get('/stages/{stage}/media/{media}', [\App\Http\Controllers\StageMediaController::class, 'show'])->name('stages.media.show');
    Route::delete('/stages/{stage}/media/{media}', [\App\Http\Controllers\StageMediaController::class, 'destroy'])->name('stages.media.destroy');

    // Task Routes
    Route::post('/stages/{stage}/tasks', [\App\Http\Controllers\TaskController::class, 'store'])->name('tasks.store');
    Route::patch('/tasks/{task}', [\App\Http\Controllers\TaskController::class, 'update'])->name('tasks.update');
    Route::delete('/tasks/{task}', [\App\Http\Controllers\TaskController::class, 'destroy'])->name('tasks.destroy');
    Route::post('/tasks/{task}/media', [\App\Http\Controllers\TaskMediaController::class, 'store'])->name('tasks.media.store');

    // Subtask Routes
    Route::post('/tasks/{task}/subtasks', [\App\Http\Controllers\SubtaskController::class, 'store'])->name('subtasks.store');
    Route::patch('/subtasks/{subtask}', [\App\Http\Controllers\SubtaskController::class, 'update'])->name('subtasks.update');
    Route::delete('/subtasks/{subtask}', [\App\Http\Controllers\SubtaskController::class, 'destroy'])->name('subtasks.destroy');

    // Comments
    Route::post('/comments', [\App\Http\Controllers\CommentController::class, 'store'])->name('comments.store');
    Route::patch('/comments/{comment}', [\App\Http\Controllers\CommentController::class, 'update'])->name('comments.update');
    Route::delete('/comments/{comment}', [\App\Http\Controllers\CommentController::class, 'destroy'])->name('comments.destroy');

    // Project Additionals
    Route::post('/projects/{project}/additionals', [\App\Http\Controllers\ProjectAdditionalController::class, 'store'])->name('project-additionals.store');
    Route::delete('/project-additionals/{additional}', [\App\Http\Controllers\ProjectAdditionalController::class, 'destroy'])->name('project-additionals.destroy');

    Route::get('/finance', [\App\Http\Controllers\InvoiceController::class, 'index'])->name('finance.index'); // Alias for consistent sidebar link if needed, or just redirect.
    // Better: We corrected sidebar to use invoices.index. So we can remove finance.index logic or keep for legacy.
    
    Route::resource('invoices', \App\Http\Controllers\InvoiceController::class);
    Route::post('invoices/{invoice}/payments', [\App\Http\Controllers\PaymentController::class, 'store'])->name('invoices.payments.store');

    // Products Catalog
    Route::resource('products', \App\Http\Controllers\ProductController::class)->only(['index', 'store', 'update', 'destroy']);

    // Notifications
    Route::post('/notifications/{id}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [\App\Http\Controllers\NotificationController::class, 'markAllRead'])->name('notifications.readAll');
    Route::delete('/notifications', [\App\Http\Controllers\NotificationController::class, 'destroy'])->name('notifications.destroy');

    // Client Services
    Route::get('/clients/{client}/active-services', [\App\Http\Controllers\ClientServiceController::class, 'getActiveServices'])->name('clients.services.active');
    Route::resource('services', \App\Http\Controllers\ClientServiceController::class);
});

// Client Portal Routes (isolated from admin routes)
Route::prefix('portal')
    ->middleware(['auth', \App\Http\Middleware\EnsureClientAccess::class])
    ->name('portal.')
    ->group(function () {
        Route::get('/projects', [\App\Http\Controllers\ClientPortalController::class, 'projects'])->name('projects');
        Route::get('/projects/{project}', [\App\Http\Controllers\ClientPortalController::class, 'showProject'])->name('projects.show');
        Route::get('/invoices', [\App\Http\Controllers\ClientPortalController::class, 'invoices'])->name('invoices');
        Route::get('/invoices/{invoice}', [\App\Http\Controllers\ClientPortalController::class, 'showInvoice'])->name('invoices.show');
        Route::get('/services', [\App\Http\Controllers\ClientPortalController::class, 'services'])->name('services');
        Route::get('/media/{media}', [\App\Http\Controllers\ClientPortalController::class, 'showMedia'])->name('media.show');
    });

require __DIR__.'/auth.php';
