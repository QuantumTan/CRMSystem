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

Route::middleware(['auth'])->group(function () {
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

    // Admin, Manager, Sales — customer visibility
    Route::middleware('role:admin,manager,sales')->prefix('customers')->name('customers.')->group(function () {
        Route::get('/', [CustomerController::class, 'index'])->name('index');
        Route::get('/{customer}', [CustomerController::class, 'show'])->whereNumber('customer')->name('show');
    });

    // Admin and Sales — customer create/update
    Route::middleware('role:admin,sales')->prefix('customers')->name('customers.')->group(function () {
        Route::get('/create', [CustomerController::class, 'create'])->name('create');
        Route::post('/', [CustomerController::class, 'store'])->name('store');
        Route::get('/{customer}/edit', [CustomerController::class, 'edit'])->whereNumber('customer')->name('edit');
        Route::put('/{customer}', [CustomerController::class, 'update'])->whereNumber('customer')->name('update');
    });

    // Admin only — customer delete
    Route::middleware('role:admin')->prefix('customers')->name('customers.')->group(function () {
        Route::delete('/{customer}', [CustomerController::class, 'destroy'])->whereNumber('customer')->name('destroy');
    });

    // Admin and Manager — assignment review
    Route::middleware('role:admin,manager')->prefix('customers')->name('customers.')->group(function () {
        Route::patch('/{customer}/reassign', [CustomerController::class, 'reassign'])->whereNumber('customer')->name('reassign');
        Route::patch('/{customer}/assignment/approve', [CustomerController::class, 'approveAssignment'])->whereNumber('customer')->name('assignment.approve');
        Route::patch('/{customer}/assignment/reject', [CustomerController::class, 'rejectAssignment'])->whereNumber('customer')->name('assignment.reject');
    });

    // Admin, Manager, Sales — leads
    Route::middleware('role:admin,manager,sales')->prefix('leads')->name('leads.')->group(function () {
        // Kanban routes
        Route::get('/kanban', [LeadController::class, 'kanban'])->name('kanban');
        Route::patch('/kanban/{lead}/status', [LeadController::class, 'updateStatus'])->name('kanban.update-status');
        // Standard CRUD routes
        Route::get('/', [LeadController::class, 'index'])->name('index');
        Route::get('/create', [LeadController::class, 'create'])->name('create');
        Route::post('/', [LeadController::class, 'store'])->name('store');
        Route::get('/{lead}', [LeadController::class, 'show'])->name('show');
        Route::get('/{lead}/edit', [LeadController::class, 'edit'])->name('edit');
        Route::put('/{lead}', [LeadController::class, 'update'])->name('update');
        Route::delete('/{lead}', [LeadController::class, 'destroy'])->name('destroy');
        // Lead management actions
        Route::patch('/{lead}/status', [LeadController::class, 'updateStatus'])->name('update-status');
        Route::patch('/{lead}/assign', [LeadController::class, 'assign'])->name('assign');
        Route::patch('/{lead}/priority', [LeadController::class, 'setPriority'])->name('set-priority');
        // Lost lead handling
        Route::get('/{lead}/lost-form', [LeadController::class, 'showLostForm'])->name('lost-form');
        Route::post('/{lead}/mark-lost', [LeadController::class, 'markAsLost'])->name('mark-lost');
        // Reopen lost lead
        Route::post('/{lead}/reopen', [LeadController::class, 'reopen'])->name('reopen');
        // Conversion routes 
        Route::post('/{lead}/convert', [LeadController::class, 'convert'])->name('convert');
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
