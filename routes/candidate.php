<?php

use App\Http\Controllers\Candidate\InviteController;
use App\Http\Controllers\Candidate\OtpAuthController;
use App\Http\Controllers\Candidate\PortalController;
use Illuminate\Support\Facades\Route;

/*
| The candidate never sees a password. They arrive on a tokenised link from
| email, confirm the mobile number the agency holds, and verify with an OTP.
*/

Route::name('candidate.')->group(function () {

    Route::get('upload/{token}', [InviteController::class, 'open'])
        ->middleware('throttle:30,1')
        ->name('invite.open');

    Route::get('link-expired', [InviteController::class, 'expired'])->name('link-expired');
    Route::get('signed-out', [PortalController::class, 'signedOut'])->name('signed-out');

    // ---- OTP sign-in ----
    Route::prefix('verify')->group(function () {
        Route::get('phone', [OtpAuthController::class, 'showPhoneForm'])->name('verify-phone');
        Route::post('phone', [OtpAuthController::class, 'sendOtp'])->middleware('throttle:8,1')->name('send-otp');

        Route::get('code', [OtpAuthController::class, 'showCodeForm'])->name('verify-code');
        Route::post('code', [OtpAuthController::class, 'verifyOtp'])->middleware('throttle:12,1')->name('verify-otp');
        Route::post('resend', [OtpAuthController::class, 'resend'])->middleware('throttle:5,5')->name('resend-otp');
    });

    // ---- Signed-in candidate portal ----
    Route::middleware(['auth:candidate', 'candidate.invite'])->prefix('portal')->group(function () {
        Route::get('/', [PortalController::class, 'dashboard'])->name('dashboard');
        Route::post('documents', [PortalController::class, 'upload'])->name('documents.upload');
        Route::get('documents/{document}/preview', [PortalController::class, 'preview'])->name('documents.preview');
        Route::get('documents/{document}/download', [PortalController::class, 'download'])->name('documents.download');
        Route::post('references', [PortalController::class, 'saveReferences'])->name('references.save');
        Route::post('submit', [PortalController::class, 'submit'])->name('submit');
        Route::get('submitted', [PortalController::class, 'submitted'])->name('submitted');
        Route::post('logout', [OtpAuthController::class, 'logout'])->name('logout');
    });
});
