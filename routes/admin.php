<?php

use App\Http\Controllers\Admin\AdvancedReportController;
use App\Http\Controllers\Admin\BranchController;
use App\Http\Controllers\Admin\BranchSessionController;
use App\Http\Controllers\Admin\CashBoxController;
use App\Http\Controllers\Admin\ClientContactController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\GuarantorController;
use App\Http\Controllers\Admin\LoanController;
use App\Http\Controllers\Admin\LoanDisbursementController;
use App\Http\Controllers\Admin\LoanIncrementController;
use App\Http\Controllers\Admin\LoanPaymentController;
use App\Http\Controllers\Admin\LoanRefinanceController;
use App\Http\Controllers\Admin\ReminderController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\LoanScheduleController;

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
Route::get('guarantors/consultar/{dniruc}', [GuarantorController::class, 'consultarDniRuc'])
    ->name('guarantors.consultarDniRuc');

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
/* Route::get('/admin/loans/{loan}/disbursements', [LoanDisbursementController::class, 'byLoan'])->name('loans.disbursements.byLoan'); */
Route::get('loans/{loan}/disbursements', [LoanDisbursementController::class, 'byLoan'])
    ->name('loans.disbursements.byLoan');
Route::resource('loan-disbursements', LoanDisbursementController::class)->except(['create']);

//RUTAS PARA PAGOS DE PRÉSTAMOS
Route::get('loan-payments/{payment}/receipt', [LoanPaymentController::class, 'receipt'])
    ->name('loan-payments.receipt');
Route::get('loan-payments/balance/{loan}', [LoanPaymentController::class, 'balance'])
    ->name('loan-payments.balance');
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
Route::post('/admin/reminders/{reminder}/cancel', [ReminderController::class, 'cancel'])->name('reminders.cancel');

Route::get('reminders/list', [ReminderController::class, 'list'])->name('reminders.list');
Route::resource('reminders', ReminderController::class)->except(['create', 'show']);


//RUTAS PARA REFINANCIAMIENTO
Route::get('loans/{loan}/refinance-info', [LoanRefinanceController::class, 'info'])
    ->name('loans.refinance.info');
//RUTAS PARA REFINANCIAMIENTO
Route::post('loans/refinance', [LoanRefinanceController::class, 'store'])->name('loans.refinance');

// Historial refinanciamientos
Route::get('loans/{loan}/refinance/history', [LoanRefinanceController::class, 'history'])
    ->name('loans.refinance.history');



//REPORTES 
// Vista principal de reportes
Route::get('reports', [ReportController::class, 'index'])
    ->name('reports.index');

// DataTables
Route::get('reports/loans', [ReportController::class, 'loans'])
    ->name('reports.loans');

// Exportaciones
Route::get('reports/loans/pdf', [ReportController::class, 'exportLoansPdf'])
    ->name('reports.loans.pdf');

Route::get('reports/loans/excel', [ReportController::class, 'exportLoansExcel'])
    ->name('reports.loans.excel');

Route::get(
    'admin/reports/commercial/pdf',
    [ReportController::class, 'exportCommercialPdf']
)->name('reports.commercial.pdf');

Route::get('reports/details', [ReportController::class, 'details'])
    ->name('reports.details');

Route::get(
    'admin/reports/operations/pdf',
    [ReportController::class, 'operationsPdf']
)->name('reports.operations.pdf');

Route::get('reports/cash/pdf', [ReportController::class, 'cashPdf'])
    ->name('reports.cash.pdf');




// REPORTES - pagos
Route::get('reports/payments', [ReportController::class, 'payments'])
    ->name('reports.payments');

// REPORTES - recuperación (KPIs)
Route::get('reports/recovery', [ReportController::class, 'recovery'])
    ->name('reports.recovery');

Route::get('reports/operations', [ReportController::class, 'operations'])
    ->name('reports.operations');

Route::get('reports/cashbook', [ReportController::class, 'cashBook'])
    ->name('reports.cashbook');

Route::get('reports/commercial', [ReportController::class, 'commercial'])
    ->name('reports.commercial');


//RUTAS CUOTAS 

/* Route::get('loans/{loan}/schedules', [LoanScheduleController::class, 'byLoan'])
    ->name('loans.schedules.byLoan'); */

// RUTAS CUOTAS (CRONOGRAMA)
Route::get('loans/{loan}/schedules', [LoanPaymentController::class, 'schedulesByLoan'])
    ->name('loans.schedules.byLoan');


Route::get('loan-payments/loans-available', [LoanPaymentController::class, 'loansAvailable'])
    ->name('loan-payments.loans-available');


//RUTAS PARA LA APERTURA DE CAJA 

Route::get('cash-box/list', [CashBoxController::class, 'list'])->name('cash-box.list');

Route::get('cash-box/{id}/summary', [CashBoxController::class, 'summary'])
    ->name('cash-box.summary');

Route::post('cash-box/replenish', [CashBoxController::class, 'replenish'])
    ->name('cash-box.replenish');

Route::post('cash-box/withdraw', [CashBoxController::class, 'withdraw'])
    ->name('cash-box.withdraw');

Route::get('cash-box/{id}/movements', [CashBoxController::class, 'movements'])
    ->name('cash-box.movements');

Route::post('cash-box/close', [CashBoxController::class, 'close'])
    ->name('cash-box.close');

Route::get('cash-box/{id}/pdf', [CashBoxController::class, 'pdf'])
    ->name('cash-box.pdf');

Route::resource('cash-box', CashBoxController::class)
    ->except(['create', 'show']);



//REPORTES AVANZADOS 

Route::get('reports/advanced/data', [AdvancedReportController::class, 'data'])
    ->name('reports.advanced.data');

Route::get('reports/advanced/{id}', [AdvancedReportController::class, 'show'])
    ->where('id', '[0-9]+')
    ->name('reports.advanced.show');

Route::get('reports/advanced/kpis', [AdvancedReportController::class, 'kpis'])
    ->name('reports.advanced.kpis');
Route::get('reports/advanced/branches', [AdvancedReportController::class, 'branches']);

Route::get('reports/advanced/pdf', [AdvancedReportController::class, 'exportPdf'])->name('reports.advanced.pdf');

Route::get('reports/advanced/excel', [AdvancedReportController::class, 'exportExcel'])->name('reports.advanced.excel');

Route::get('reports/advanced/payments', [AdvancedReportController::class, 'payments'])
    ->name('reports.advanced.payments');

Route::get('reports/cashbook/pdf', [ReportController::class, 'cashBookPdf'])
    ->name('reports.cashbook.pdf');

Route::get('reports/advanced', [AdvancedReportController::class, 'index'])
    ->name('reports.advanced');
