<?php

use App\Http\Controllers\Admin\BranchController;
use App\Http\Controllers\Admin\BranchSessionController;
use App\Http\Controllers\Admin\ClientContactController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\GuarantorController;
use App\Http\Controllers\Admin\LoanController;
use App\Http\Controllers\Admin\LoanDisbursementController;
use App\Http\Controllers\Admin\LoanIncrementController;
use App\Http\Controllers\Admin\LoanPaymentController;
use App\Http\Controllers\Admin\ReminderController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\RoleController;
use Illuminate\Support\Facades\Route;


//Rutas para la gestión de usuarios en el panel de administración|
Route::get('users/list', [UserController::class, 'list'])->name('users.list');
Route::resource('users', UserController::class)->except(['create', 'show']);

Route::get('roles/list', [RoleController::class, 'list'])->name('roles.list');
Route::get('roles/{role}/permissions', [RoleController::class, 'getPermissions'])->name('roles.permissions');
Route::resource('roles', RoleController::class)->except(['create', 'show']);

Route::get('customers/list', [CustomerController::class, 'list'])->name('customers.list');
Route::resource('customers', CustomerController::class)->except(['create', 'show']);

//RUTAS PARA SUCURSALES
Route::get('branches/list', [BranchController::class, 'list'])->name('branches.list');
Route::resource('branches', BranchController::class)->except(['create', 'show']);

//RUTAS PARA CLIENTES
Route::get('clients/list', [ClientController::class, 'list'])->name('clients.list');
/* Route::get('/admin/clients/dni/{dni}', [ClientController::class, 'consultarDni'])->name('clients.consultarDni'); */
Route::get('clients/document/{dniruc}', [ClientController::class, 'consultarDniRuc'])
    ->name('clients.consultarDniRuc');
Route::resource('clients', ClientController::class)->except(['create', 'show']);

//RUTAS PARA CONTACTOS DE CLIENTES
Route::get('client-contacts/list', [ClientContactController::class, 'list'])->name('client-contacts.list');
Route::resource('client-contacts', ClientContactController::class)->except(['create']);

//RUTAS PARA EL GARANTE 
Route::get('guarantors/list', [GuarantorController::class, 'list'])->name('guarantors.list');
Route::resource('guarantors', GuarantorController::class)->except(['create', 'show']);

//RUTAS PARA EL PRESTAMO
Route::get('loans/list', [LoanController::class, 'list'])->name('loans.list');
Route::get('loans/generate-code', [LoanController::class, 'generateCode'])->name('loans.generate-code');
Route::resource('loans', LoanController::class)->except(['create', 'show']);


//RUTAS PARA INCREMENTOS DE PRÉSTAMOS
Route::get('loans/{loan}/increments', [LoanIncrementController::class, 'byLoan'])->name('loans.increments.byLoan');
Route::post('loans/increments', [LoanIncrementController::class, 'store'])->name('loans.increments.store');


//RUTAS PARA DESMBOLSOS DE PRÉSTAMOS
Route::get('loan-disbursements/list', [LoanDisbursementController::class, 'list'])->name('loan-disbursements.list');
Route::post('loan-disbursements', [LoanDisbursementController::class, 'store'])->name('loan-disbursements.store');
Route::get('/admin/loans/{loan}/disbursements', [LoanDisbursementController::class, 'byLoan'])->name('loans.disbursements.byLoan');
Route::resource('loan-disbursements', LoanDisbursementController::class)->except(['create']);

//RUTAS PARA PAGOS DE PRÉSTAMOS
Route::get('loan-payments/list', [LoanPaymentController::class, 'list'])->name('loan-payments.list');
Route::get('loan-payments/generate-code', [LoanPaymentController::class, 'generateCode'])->name('loan-payments.generate-code');
Route::resource('loan-payments', LoanPaymentController::class)->except(['create', 'show']);


//RUTAS PARA SESSION BRANCH
Route::post('select-branch', [BranchSessionController::class, 'store'])->name('select-branch');

//rutas para recordatorios

Route::get('reminders/clients', [ReminderController::class, 'clients'])->name('reminders.clients');
Route::get('reminders/clients/{client}/loans', [ReminderController::class, 'clientLoans'])->name('reminders.client-loans');
// Navbar reminders
Route::get('reminders/navbar', [ReminderController::class, 'navbar'])->name('reminders.navbar');
Route::post('reminders/{reminder}/mark-read', [ReminderController::class, 'markRead'])->name('reminders.mark-read');
Route::get('reminders/{reminder}/json', [ReminderController::class, 'showJson'])->name('reminders.show-json');
Route::post('/admin/reminders/{reminder}/cancel',[ReminderController::class, 'cancel'])->name('reminders.cancel');

Route::get('reminders/list', [ReminderController::class, 'list'])->name('reminders.list');
Route::resource('reminders', ReminderController::class)->except(['create', 'show']);
