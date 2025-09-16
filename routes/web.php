<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\IncomeController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\DashboardController;
use App\Models\Expense;

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

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('projects', ProjectController::class);
    Route::resource('employees', EmployeeController::class);
    Route::resource('expenses', ExpenseController::class);
    Route::resource('incomes', IncomeController::class);

    // API route for duplicate check (called from frontend)
    Route::get('/api/check-duplicate', [ExpenseController::class, 'checkDuplicate'])->name('api.check-duplicate');

    // simple export routes (we'll add functions later)
    Route::get('/exports/expenses', [ExpenseController::class, 'export'])->name('expenses.export');
    Route::get('/exports/incomes', [IncomeController::class, 'export'])->name('incomes.export');
});

require __DIR__ . '/auth.php';
