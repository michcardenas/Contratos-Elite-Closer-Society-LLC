<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\UsuariosController;
use App\Http\Controllers\LeadsController;
use App\Http\Controllers\LlamadasController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\OnboardingLeadsController;
use App\Http\Controllers\OnboardingCallsController;
use App\Http\Controllers\LeadNotesController;
use App\Http\Controllers\OnboardingDashboardController;
use App\Http\Controllers\ContractSigningController;
use App\Http\Controllers\ContractApprovalController;
use App\Http\Controllers\UpsellController;
use App\Http\Controllers\TraigeController;
use App\Http\Controllers\TraigeCallsController;
use App\Http\Controllers\FormController;
use App\Http\Controllers\FormSubmissionController;
use App\Http\Controllers\FulfillmentFormController;
use App\Http\Controllers\PriceConfigurationController;
use App\Http\Controllers\FinanceController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard',[HomeController::class, 'index'] )->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/usuarios', [UsuariosController::class, 'index'])->name('usuarios');
    Route::get('/importar_usuarios', [UsuariosController::class, 'importar_usuarios'])->name('importar_usuarios');
    Route::get('/usuarios_form/{user?}', [UsuariosController::class, 'form'])->name('usuarios.form');
    Route::post('/usuarios/guardar', [UsuariosController::class, 'guardar'])->name('usuarios.guardar');
    Route::get('/leads', [LeadsController::class, 'index'])->name('leads');
    Route::get('/importar_leads', [LeadsController::class, 'importar_leads'])->name('importar_leads');
    Route::get('/leads_form/{lead?}', [LeadsController::class, 'form'])->name('leads.form');
    Route::get('/llamadas', [LlamadasController::class, 'index'])->name('llamadas');
    Route::get('/leads/{id}/logs', [LeadsController::class, 'logs'])->name('leads.logs');
    Route::get('/contracts/{saleId}/download', [LeadsController::class, 'downloadContract'])->name('contracts.download');
    Route::post('/contracts/{saleId}/resend-email', [LeadsController::class, 'resendContractEmail'])->name('contracts.resend');
    Route::get('/leads_form/{llamada?}', [LlamadasController::class, 'form'])->name('llamadas.form');
Route::get('/llamadas/{id}/respuestas-json', [LlamadasController::class, 'respuestasJson'])->name('llamadas.respuestas.json');
Route::get('/leads/{id}/info-json', [LeadsController::class, 'infoJson'])->name('leads.info');
Route::get('sales/form/{lead}', [SalesController::class, 'form'])->name('sales.form');
    Route::prefix('sales')->name('sales.')->group(function () {
        Route::post('store', [SalesController::class, 'store'])->name('store');
    });
        Route::post('leads/{id}/update-status', [LeadsController::class, 'updatePipelineStatus'])->name('leads.update_status');

    // Rutas del módulo Traige
    Route::prefix('traige')->name('traige.')->group(function () {
        Route::get('/', [TraigeController::class, 'index'])->name('index');
        Route::post('/', [TraigeController::class, 'store'])->name('store');
        Route::post('{id}/update-status', [TraigeController::class, 'updatePipelineStatus'])->name('update_status');
        Route::post('{id}/pass-to-closer', [TraigeController::class, 'passToCloser'])->name('pass_to_closer');
        Route::get('{id}/logs', [TraigeController::class, 'logs'])->name('logs');

        // Rutas para llamadas de traige
        Route::post('calls', [TraigeCallsController::class, 'store'])->name('calls.store');
        Route::get('calls/lead/{leadId}', [TraigeCallsController::class, 'getLeadCalls'])->name('calls.lead');
        Route::put('calls/{callId}/status', [TraigeCallsController::class, 'updateStatus'])->name('calls.update_status');
        Route::put('calls/{callId}/reschedule', [TraigeCallsController::class, 'reschedule'])->name('calls.reschedule');
        Route::get('calls/{callId}/logs', [TraigeCallsController::class, 'getLogs'])->name('calls.logs');
    });

    // Rutas de Onboarding para rol CMS
    Route::prefix('onboarding')->name('onboarding.')->group(function () {
        Route::get('dashboard', [OnboardingDashboardController::class, 'index'])->name('dashboard');
        Route::get('leads', [OnboardingLeadsController::class, 'index'])->name('leads');
        Route::get('leads/{leadId}/calls', [OnboardingCallsController::class, 'getLeadCalls'])->name('leads.calls');
        Route::get('leads/{id}/logs', [OnboardingLeadsController::class, 'logs'])->name('leads.logs');
        Route::get('contracts/{saleId}/download', [OnboardingLeadsController::class, 'downloadContract'])->name('contracts.download');
        Route::post('calls', [OnboardingCallsController::class, 'store'])->name('calls.store');
        Route::put('calls/{callId}/status', [OnboardingCallsController::class, 'updateStatus'])->name('calls.update_status');
        Route::put('calls/{callId}/reschedule', [OnboardingCallsController::class, 'reschedule'])->name('calls.reschedule');
        Route::get('calls/{callId}/logs', [OnboardingCallsController::class, 'getLogs'])->name('calls.logs');
    });

    // Rutas para gestión de notas de leads
    Route::prefix('lead-notes')->name('lead-notes.')->group(function () {
        Route::post('/', [LeadNotesController::class, 'store'])->name('store');
        Route::get('/{leadId}', [LeadNotesController::class, 'getLeadNotes'])->name('get');
        Route::delete('/{noteId}', [LeadNotesController::class, 'destroy'])->name('destroy');
    });

    // Rutas para aprobación de contratos (solo usuarios CMS)
    Route::prefix('contracts/approval')->name('contracts.approval.')->group(function () {
        Route::get('/', [ContractApprovalController::class, 'index'])->name('index');
        Route::get('/{saleId}/edit', [ContractApprovalController::class, 'edit'])->name('edit');
        Route::put('/{saleId}', [ContractApprovalController::class, 'update'])->name('update');
        Route::post('/{saleId}/approve', [ContractApprovalController::class, 'approve'])->name('approve');
        Route::post('/{saleId}/preview', [ContractApprovalController::class, 'previewAjax'])->name('preview');
    });

});

// Rutas públicas para firma de contratos (sin autenticación)
Route::prefix('contract')->name('contract.')->group(function () {
    Route::get('sign/{token}', [ContractSigningController::class, 'show'])->name('sign');
    Route::put('sign/{token}', [ContractSigningController::class, 'update'])->name('update');
    Route::get('preview/{token}', [ContractSigningController::class, 'preview'])->name('preview');
    Route::post('preview-ajax/{token}', [ContractSigningController::class, 'previewAjax'])->name('preview.ajax');
});

// Rutas para gestión de Upsells (solo admin)
Route::middleware(['auth', 'verified'])->prefix('upsell')->name('upsell.')->group(function () {
    Route::get('/', [UpsellController::class, 'index'])->name('index');
    Route::post('{sale}/pendiente', [UpsellController::class, 'markPendiente'])->name('mark.pendiente');
    Route::post('{sale}/approve', [UpsellController::class, 'approve'])->name('approve');
    Route::get('{sale}/logs', [UpsellController::class, 'getLogs'])->name('logs');
});

// Rutas para gestión de Formularios Dinámicos (admin y cms)
Route::middleware(['auth', 'verified'])->prefix('forms')->name('forms.')->group(function () {
    // CRUD de formularios
    Route::get('/', [FormController::class, 'index'])->name('index');
    Route::get('/create', [FormController::class, 'create'])->name('create');
    Route::post('/', [FormController::class, 'store'])->name('store');
    Route::get('/{id}/edit', [FormController::class, 'edit'])->name('edit');
    Route::put('/{id}', [FormController::class, 'update'])->name('update');
    Route::delete('/{id}', [FormController::class, 'destroy'])->name('destroy');
    Route::get('/{id}/logs', [FormController::class, 'logs'])->name('logs');

    // Gestión de respuestas
    Route::get('/{formId}/submissions', [FormSubmissionController::class, 'index'])->name('submissions.index');
    Route::get('/submissions/{submissionId}/show', [FormSubmissionController::class, 'show'])->name('submissions.show');
    Route::get('/{formId}/export', [FormSubmissionController::class, 'export'])->name('submissions.export');
    Route::get('/{formId}/export-excel', [FormSubmissionController::class, 'exportExcel'])->name('submissions.export.excel');
    Route::get('/{formId}/charts', [FormSubmissionController::class, 'charts'])->name('submissions.charts');

    // Renderizar formularios específicos
    Route::get('/triage-daily/render', [FormSubmissionController::class, 'renderTraigeDaily'])->name('triage-daily.render');
    Route::get('/closer-daily/render', [FormSubmissionController::class, 'renderCloserDaily'])->name('closer-daily.render');

    // Envío de formularios específicos
    Route::post('/traige-daily/submit', [FormSubmissionController::class, 'storeTraigeDaily'])->name('traige-daily.submit');
    Route::post('/closer-daily/submit', [FormSubmissionController::class, 'storeCloserDaily'])->name('closer-daily.submit');

    // Envío genérico de formulario
    Route::post('/{formSlug}/submit', [FormSubmissionController::class, 'store'])->name('submit');
});

// ============================================================
// FULFILLMENT FORM ROUTES
// ============================================================
Route::middleware('auth')->prefix('fulfillment')->name('fulfillment.')->group(function () {
    Route::get('/form', [FulfillmentFormController::class, 'index'])->name('form.index');
    Route::get('/form/submissions', [FulfillmentFormController::class, 'getSubmissions'])->name('form.submissions');
    Route::post('/form/store', [FulfillmentFormController::class, 'store'])->name('form.store');
    Route::get('/form/submission/{submissionId}', [FulfillmentFormController::class, 'show'])->name('form.submission.show');
});

// ============================================================
// CONFIGURATION ROUTES (Admin only)
// ============================================================
Route::middleware(['auth', 'verified'])->prefix('configuration')->name('configuration.')->group(function () {
    // Configuracion de precios
    // IMPORTANTE: La ruta de API debe estar ANTES de la ruta con {id} para evitar que sea capturada
    Route::get('prices/api/get-price', [PriceConfigurationController::class, 'getPriceForType'])->name('prices.api.get');
    Route::get('prices', [PriceConfigurationController::class, 'index'])->name('prices.index');
    Route::get('prices/{id}', [PriceConfigurationController::class, 'show'])->name('prices.show');
    Route::put('prices/{id}', [PriceConfigurationController::class, 'update'])->name('prices.update');
});

// ============================================================
// FINANCE ROUTES (Admin and Finanzas role)
// ============================================================
Route::middleware(['auth', 'verified'])->prefix('finanzas')->name('finanzas.')->group(function () {
    Route::get('dashboard', [FinanceController::class, 'dashboard'])->name('dashboard');
    Route::get('cuotas', [FinanceController::class, 'installments'])->name('cuotas');
    Route::post('cuotas/{installmentId}/pago', [FinanceController::class, 'registerPayment'])->name('cuotas.pago');
    Route::get('cuotas/{installmentId}/historial', [FinanceController::class, 'getInstallmentHistory'])->name('cuotas.historial');
    Route::get('exportar', [FinanceController::class, 'exportExcel'])->name('exportar');
});

require __DIR__.'/auth.php';
