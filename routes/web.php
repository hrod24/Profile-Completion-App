<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeFormController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmployeeExcelImportController;
use App\Http\Controllers\SetPicController;

Route::get('/', [DashboardController::class, 'index'])
    ->name('dashboard');

Route::get('/form', [EmployeeFormController::class, 'show'])
    ->name('employee.form');

Route::post('/form', [EmployeeFormController::class, 'submit'])
    ->name('employee.form.submit');

Route::get('/set-pic', [SetPicController::class, 'show'])
    ->name('set-pic.index');

Route::post('/set-pic/assign', [SetPicController::class, 'assign'])
    ->name('set-pic.assign');

Route::get('/employee-import', [EmployeeExcelImportController::class, 'create'])
    ->name('employee.import.create');

Route::post('/employee-import', [EmployeeExcelImportController::class, 'store'])
    ->name('employee.import.store');