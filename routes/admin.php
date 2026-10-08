<?php

use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CandidateController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DocumentReviewController;
use App\Http\Controllers\Admin\DocumentTypeController;
use App\Http\Controllers\Admin\EmailTemplateController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\SettingController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->group(function () {

    Route::middleware('guest.as:admin')->group(function () {
        Route::get('login', [AuthController::class, 'showLogin'])->name('login');
        Route::post('login', [AuthController::class, 'login'])->middleware('throttle:10,1');
    });

    Route::middleware(['auth:admin', 'admin.active'])->group(function () {

        Route::post('logout', [AuthController::class, 'logout'])->name('logout');

        Route::get('/', DashboardController::class)->name('dashboard');

        // ---- Candidates ----
        Route::controller(CandidateController::class)->prefix('candidates')->name('candidates.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('{candidate}', 'show')->name('show');
            Route::get('{candidate}/edit', 'edit')->name('edit');
            Route::put('{candidate}', 'update')->name('update');
            Route::delete('{candidate}', 'destroy')->name('destroy');
            Route::post('{candidate}/resend-invite', 'resendInvite')->name('resend-invite');
            Route::post('{candidate}/revoke-invite', 'revokeInvite')->name('revoke-invite');
            Route::post('{candidate}/status', 'changeStatus')->name('status');
        });

        // ---- Document review queue ----
        Route::controller(DocumentReviewController::class)->prefix('documents')->name('documents.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('{document}/preview', 'preview')->name('preview');
            Route::get('{document}/download', 'download')->name('download');
            Route::post('{document}/review', 'review')->name('review');
            Route::delete('{document}', 'destroy')->name('destroy');
        });

        // ---- Checklist configuration ----
        Route::get('settings', [SettingController::class, 'edit'])->name('settings.edit');
        Route::put('settings', [SettingController::class, 'update'])->name('settings.update');

        Route::get('email-templates', [EmailTemplateController::class, 'index'])->name('email-templates.index');
        Route::get('email-templates/{email_template}', [EmailTemplateController::class, 'edit'])->name('email-templates.edit');
        Route::put('email-templates/{email_template}', [EmailTemplateController::class, 'update'])->name('email-templates.update');
        Route::get('email-templates/{email_template}/preview', [EmailTemplateController::class, 'preview'])->name('email-templates.preview');
        Route::post('email-templates/{email_template}/test', [EmailTemplateController::class, 'test'])->name('email-templates.test');
        Route::post('email-templates/{email_template}/restore', [EmailTemplateController::class, 'restore'])->name('email-templates.restore');

        Route::post('document-types/reorder', [DocumentTypeController::class, 'reorder'])->name('document-types.reorder');
        Route::resource('document-types', DocumentTypeController::class)->except('show');

        // ---- Own profile ----
        Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::put('profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

        // ---- Team (super admin only) ----
        Route::middleware('admin.role:super_admin')->group(function () {
            Route::resource('users', AdminUserController::class)->except('show');
        });
    });
});
