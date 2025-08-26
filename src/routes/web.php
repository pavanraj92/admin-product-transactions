<?php

use Illuminate\Support\Facades\Route;
use admin\product_transactions\Controllers\TransactionManagerController;

Route::name('admin.')->middleware(['web', 'admin.auth'])->group(function () {
    Route::resource('transactions', TransactionManagerController::class);
});
