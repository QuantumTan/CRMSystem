<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FollowUpController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return Auth::check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
});

Route::middleware(['auth'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/profile', [AuthController::class, 'profile'])->name('profile');
    Route::get('/settings', function () {
        return view('settings.index');
    })->name('settings.index');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/admin', [DashboardController::class, 'admin'])->middleware('role:admin')->name('dashboard.admin');
    Route::get('/dashboard/manager', [DashboardController::class, 'manager'])->middleware('role:manager')->name('dashboard.manager');
    Route::get('/dashboard/sales', [DashboardController::class, 'sales'])->middleware('role:sales')->name('dashboard.sales');

    // Admin only — user management
    Route::middleware('role:admin')->prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('/create', [UserController::class, 'create'])->name('create');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
        Route::put('/{user}', [UserController::class, 'update'])->name('update');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
    });

    // Admin and Sales — customer management
    Route::middleware('role:admin,sales')->prefix('customers')->name('customers.')->group(function () {
        Route::get('/', [CustomerController::class, 'index'])->name('index');
        Route::get('/create', [CustomerController::class, 'create'])->name('create');
        Route::post('/', [CustomerController::class, 'store'])->name('store');
        Route::get('/{customer}/edit', [CustomerController::class, 'edit'])->name('edit');
        Route::put('/{customer}', [CustomerController::class, 'update'])->name('update');
        Route::delete('/{customer}', [CustomerController::class, 'destroy'])->name('destroy');
        Route::get('/{customer}', [CustomerController::class, 'show'])->name('show');
    });

    // Admin, Manager, Sales — leads
    Route::middleware('role:admin,manager,sales')->prefix('leads')->name('leads.')->group(function () {
        Route::get('/', [LeadController::class, 'index'])->name('index');
        Route::get('/create', [LeadController::class, 'create'])->name('create');
        Route::post('/', [LeadController::class, 'store'])->name('store');
        Route::get('/{lead}/edit', [LeadController::class, 'edit'])->name('edit');
        Route::put('/{lead}', [LeadController::class, 'update'])->name('update');
        Route::delete('/{lead}', [LeadController::class, 'destroy'])->name('destroy');
        Route::patch('/{lead}/status', [LeadController::class, 'updateStatus'])->name('update-status');
        Route::get('/{lead}', [LeadController::class, 'show'])->name('show');
    });

    // Admin, Manager, Sales — activities & follow-ups
    Route::middleware('role:admin,manager,sales')->group(function () {
        Route::prefix('activities')->name('activities.')->group(function () {
            Route::get('/', [ActivityController::class, 'index'])->name('index');
            Route::get('/create', [ActivityController::class, 'create'])->name('create');
            Route::post('/', [ActivityController::class, 'store'])->name('store');
        });

        Route::prefix('follow-ups')->name('follow-ups.')->group(function () {
            Route::get('/', [FollowUpController::class, 'index'])->name('index');
            Route::get('/create', [FollowUpController::class, 'create'])->name('create');
            Route::post('/', [FollowUpController::class, 'store'])->name('store');
            Route::get('/{followUp}/edit', [FollowUpController::class, 'edit'])->name('edit');
            Route::put('/{followUp}', [FollowUpController::class, 'update'])->name('update');
            Route::patch('/{followUp}/complete', [FollowUpController::class, 'markComplete'])->name('complete');
        });
    });

    // Admin and Manager only — reports
    Route::middleware('role:admin,manager')->prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
    });
});
