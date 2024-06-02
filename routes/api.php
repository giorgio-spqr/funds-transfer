<?php

use App\Http\Controllers\AccountsController;
use App\Http\Controllers\ClientsController;
use Illuminate\Support\Facades\Route;

Route::get('/clients', [ClientsController::class, 'index'])
    ->name('clients');
Route::get('/clients/{client}/accounts', [AccountsController::class, 'index'])
    ->name('client.accounts');
Route::post('/accounts/transfer', [AccountsController::class, 'transfer'])
    ->name('accounts.transfer');
Route::get('/accounts/{account}/transactions', [AccountsController::class, 'transactions'])
    ->name('account.transactions');
