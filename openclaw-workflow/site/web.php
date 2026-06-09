<?php

use App\Http\Controllers\LeadController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'landing')->name('home');
Route::post('/leads', [LeadController::class, 'store'])->name('leads.store');
