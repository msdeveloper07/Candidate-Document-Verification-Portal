<?php

use App\Models\OtpCode;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Expired codes are dead weight and a small privacy liability. Clear them daily.
Schedule::call(function () {
    OtpCode::query()->where('created_at', '<', now()->subDays(7))->delete();
})->daily()->name('prune-otp-codes');
