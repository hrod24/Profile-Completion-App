<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeExcelImportController;
use App\Http\Controllers\EmployeeFormController;
use App\Http\Controllers\HrFormController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SetPicController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Entry point setelah login
|--------------------------------------------------------------------------
*/

Route::get('/', function (Request $request) {
    $user = $request->user();

    if (in_array($user->role, ['admin', 'od'], true)) {
        return redirect()->route('dashboard');
    }

    if ($user->role === 'employee') {
        return redirect()->route('employee.form');
    }

    abort(403, 'Akun belum memiliki role yang valid.');
})
    ->middleware('auth')
    ->name('home');

/*
|--------------------------------------------------------------------------
| Admin / OD
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'can:access-admin-pages',
])->group(function () {
    Route::get(
        '/dashboard',
        [DashboardController::class, 'index']
    )->name('dashboard');

    Route::get(
        '/dashboard/employees/{employeeId}/details',
        [DashboardController::class, 'employeeDetails']
    )->name('dashboard.employee-details');

    Route::get(
        '/set-pic',
        [SetPicController::class, 'show']
    )->name('set-pic.index');

    Route::post(
        '/set-pic/assign',
        [SetPicController::class, 'assign']
    )->name('set-pic.assign');

    Route::get(
        '/employee-import',
        [EmployeeExcelImportController::class, 'create']
    )->name('employee.import.create');

    Route::post(
        '/employee-import/start',
        [EmployeeExcelImportController::class, 'startImport']
    )->name('employee.import.start');

    Route::post(
        '/employee-import/chunk',
        [EmployeeExcelImportController::class, 'processImportChunk']
    )->name('employee.import.chunk');

    Route::post(
        '/employee-import/finish',
        [EmployeeExcelImportController::class, 'finishImport']
    )->name('employee.import.finish');

    Route::post(
        '/synchronize-account/start',
        [EmployeeExcelImportController::class, 'startSynchronization']
    )->name('employee.accounts.synchronize.start');

    Route::post(
        '/synchronize-account/chunk',
        [EmployeeExcelImportController::class, 'synchronizeChunk']
    )->name('employee.accounts.synchronize.chunk');

    Route::get(
        '/hr-form',
        [HrFormController::class, 'index']
    )->name('hr-form.index');

    Route::get(
        '/hr-form/{employeeId}/edit',
        [HrFormController::class, 'edit']
    )->name('hr-form.edit');

    Route::put(
        '/hr-form/{employeeId}',
        [HrFormController::class, 'update']
    )->name('hr-form.update');
});

/*
|--------------------------------------------------------------------------
| Employee
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'can:access-employee-form',
])->group(function () {
    Route::get(
        '/form',
        [EmployeeFormController::class, 'show']
    )->name('employee.form');

    Route::post(
        '/form',
        [EmployeeFormController::class, 'submit']
    )->name('employee.form.submit');
});

/*
|--------------------------------------------------------------------------
| Profile Breeze
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::get(
        '/profile',
        [ProfileController::class, 'edit']
    )->name('profile.edit');

    Route::patch(
        '/profile',
        [ProfileController::class, 'update']
    )->name('profile.update');

    Route::delete(
        '/profile',
        [ProfileController::class, 'destroy']
    )->name('profile.destroy');
});

require __DIR__ . '/auth.php';
