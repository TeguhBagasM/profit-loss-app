<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ChartOfAccountController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProfitLossReportController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/reports/profit-loss', [ProfitLossReportController::class, 'index'])
        ->name('reports.profit-loss');

    Route::get('/reports/profit-loss/export', [ProfitLossReportController::class, 'export'])
        ->middleware('throttle:10,1')
        ->name('reports.profit-loss.export');

    Route::resource('categories', CategoryController::class)->except('show');

    Route::resource('chart-of-accounts', ChartOfAccountController::class)
        ->parameters(['chart-of-accounts' => 'chartOfAccount'])
        ->except('show');

    Route::resource('transactions', TransactionController::class)->except('show');
});

require __DIR__.'/auth.php';
