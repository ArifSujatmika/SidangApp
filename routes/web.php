<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AnalysisController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RevisionController;
use App\Http\Controllers\SubmissionController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => Auth::check()
    ? redirect()->route('dashboard')
    : redirect()->route('login'))->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('admin/users', [AdminController::class, 'users'])->name('admin.users.index');
    Route::get('admin/users/create', [AdminController::class, 'createUser'])->name('admin.users.create');
    Route::post('admin/users', [AdminController::class, 'storeUser'])->name('admin.users.store');
    Route::get('admin/users/{user}/edit', [AdminController::class, 'editUser'])->name('admin.users.edit');
    Route::put('admin/users/{user}', [AdminController::class, 'updateUser'])->name('admin.users.update');
    Route::delete('admin/users/{user}', [AdminController::class, 'destroyUser'])->name('admin.users.destroy');

    Route::get('admin/schedules', [AdminController::class, 'schedules'])->name('admin.schedules.index');
    Route::get('admin/schedules/create', [AdminController::class, 'createSchedule'])->name('admin.schedules.create');
    Route::post('admin/schedules', [AdminController::class, 'storeSchedule'])->name('admin.schedules.store');
    Route::get('admin/schedules/{schedule}/edit', [AdminController::class, 'editSchedule'])->name('admin.schedules.edit');
    Route::put('admin/schedules/{schedule}', [AdminController::class, 'updateSchedule'])->name('admin.schedules.update');
    Route::delete('admin/schedules/{schedule}', [AdminController::class, 'destroySchedule'])->name('admin.schedules.destroy');

    Route::get('admin/ai-assistant', [AdminController::class, 'aiAssistant'])->name('admin.ai-assistant');

    Route::post('submissions/{submission}/status', [AdminController::class, 'updateSubmissionStatus'])->name('admin.submissions.update-status');
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('submissions/create', [SubmissionController::class, 'create'])->name('submissions.create');
    Route::post('submissions', [SubmissionController::class, 'store'])->name('submissions.store');
    Route::get('submissions/{submission}/edit', [SubmissionController::class, 'edit'])->name('submissions.edit');
    Route::put('submissions/{submission}', [SubmissionController::class, 'update'])->name('submissions.update');
    Route::get('submissions/{submission}', [SubmissionController::class, 'show'])->name('submissions.show');
    Route::get('submissions/{submission}/download', [SubmissionController::class, 'download'])->name('submissions.download');
    Route::get('submissions/{submission}/analysis', [AnalysisController::class, 'show'])
        ->middleware('can:analyze-submission,submission')
        ->name('submissions.analysis');

    Route::get('submissions/{submission}/revisions/create', [RevisionController::class, 'create'])->name('revisions.create');
    Route::post('submissions/{submission}/revisions', [RevisionController::class, 'store'])->name('revisions.store');
    Route::get('revisions/{revisionNote}/reply', [RevisionController::class, 'reply'])->name('revisions.reply');
    Route::post('revisions/{revisionNote}/reply', [RevisionController::class, 'storeReply'])->name('revisions.storeReply');
    Route::post('revisions/{revisionNote}/resolve', [RevisionController::class, 'resolve'])->name('revisions.resolve');
});

require __DIR__.'/settings.php';
