<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MailjetController;

Route::get('/', [MailjetController::class, 'dashboard']);
Route::get('/mailjet/dashboard', [MailjetController::class, 'dashboard'])->name('mailjet.dashboard');
Route::get('/mailjet/api-info', [MailjetController::class, 'apiInfo'])->name('mailjet.api-info');
Route::get('/mailjet/daily-messages/{date?}', [MailjetController::class, 'dailyMessages'])->name('mailjet.daily-messages');
