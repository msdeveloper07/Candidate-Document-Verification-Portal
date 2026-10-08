<?php

use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('admin.login'))->name('home');

require __DIR__.'/admin.php';
require __DIR__.'/candidate.php';
