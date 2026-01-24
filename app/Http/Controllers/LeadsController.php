<?php

namespace App\Http\Controllers;

use App\Services\LeadSynchronizationService;
use App\Services\CalendlyEventImporter;
use App\Services\Contracts\ApiClientFactoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Log;
use App\Models\PipelineStatus;
use App\Models\Lead;
use App\Models\Sale;
use App\Mail\ContractSigningMail;
use Illuminate\Support\Facades\Mail;
use Yajra\DataTables\Facades\DataTables;

class LeadsController extends Controller
{
    protected $apiFactory;

    public function __construct(ApiClientFactoryInterface $apiFactory)
    {
        $this->apiFactory = $apiFactory;
    }

    /**
     * Inicia el proceso de importación de leads y llamadas 
     * para el usuario autenticado.
     */
    public function importar_leads($id_usuario = null)
    {
        set_time_limit(1000); // ← importante

        $user_to_sync = $id_usuario ? User::find($id_usuario) : Auth::user();

        if (!$user_to_sync) {
            return response()->json(['message' => 'No autorizado.'], 401);
        }

        $eventImporter = new CalendlyEventImporter($this->apiFactory);
        $syncService = new LeadSynchronizationService($eventImporter);
        $report = $syncService->synchronizeLeadsAndCalls($user_to_sync);

        return response()->json([
            'message' => 'Proceso de importación de leads y llamadas completado.',
            'imported_calls' => $report['imported'],
            'skipped_calls' => $report['skipped'],
            'errors' => $report['errors'],
        ]);
    }

    /**
     * Muestra una lista de los leads que pertenecen al closer autenticado.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            // Construir la consulta base una sola vez
            $query = Lead::with('pipelineStatus', 'user', 'sale.contractTemplate', 'sale.paymentProofs', 'sale.installments')->orderByDesc('id');

            // FILTRO IMPORTANTE: Solo mostrar leads que han sido pasados desde traige
            $query->where('passed_to_closer', true);

            // Aplicar filtro por rol al query existente
            if (auth()->user()->getRoleNames()->first() !== 'admin') {
                $query->where(function($q) {
                    $q->where('user_id', Auth::id())
                      ->orWhereNull('user_id');
                });
            }

            // Filtrar solo los estados originales para closers (excluir estados de traige)
            $traigeStatusNames = [
                'Llamadas agendadas',
                'Asistencias',
                'Canceladas',
                'Calificadas',
                'Tasa de asistencia',
                'Tasa de calificación'
            ];
            $pipelineStatuses = PipelineStatus::whereNotIn('name', $traigeStatusNames)->get();

            return DataTables::of($query)
                ->addColumn('action', function ($lead) {
                    $llamadasUrl = route('llamadas', ['lead_id' => $lead->id]);
                    $buttons = '<div class="d-flex justify-content-start  gap-1">';
                    $buttons .= '<a href="' . $llamadasUrl . '" class="btn btn-outline-secondary btn-sm" title="Ver llamadas"><i class="bi bi-telephone"></i></a>';
                    $buttons .= '<button type="button" class="btn btn-outline-info btn-sm view-logs-btn"
                        data-lead-id="' . $lead->id . '" title="Ver Historial de Cambios">
                        <i class="bi bi-clock-history"></i>
                    </button>';

                    if ($lead->sale) {
                        // Preparar comprobantes como JSON
                        $comprobantes = $lead->sale->paymentProofs->map(function($proof) {
                            return [
                                'url' => asset($proof->file_path),
                                'name' => $proof->original_name ?? basename($proof->file_path)
                            ];
                        })->toArray();
                        // Si no hay en la nueva tabla, usar el comprobante principal
                        if (empty($comprobantes) && $lead->sale->comprobante_pago_path) {
                            $comprobantes = [[
                                'url' => asset($lead->sale->comprobante_pago_path),
                                'name' => basename($lead->sale->comprobante_pago_path)
                            ]];
                        }

                        // Preparar cuotas como JSON
                        $cuotas = $lead->sale->installments->map(function($installment) use ($lead) {
                            return [
                                'numero' => $installment->numero_cuota,
                                'monto' => number_format($installment->monto, 2),
                                'fecha_vencimiento' => $installment->fecha_vencimiento ? $installment->fecha_vencimiento->format('d/m/Y') : 'N/A',
                                'estado' => $installment->getEstadoLabel(),
                                'estado_badge' => $installment->getEstadoBadgeClass(),
                                'monto_pagado' => number_format($installment->monto_pagado ?? 0, 2),
                                'fecha_pago' => $installment->fecha_pago ? $installment->fecha_pago->format('d/m/Y') : null,
                                'moneda' => $lead->sale->moneda ?? 'USD',
                            ];
                        })->toArray();

                        // Ya tiene una venta registrada: botón para ver modal
                        $buttons .= '<button type="button" class="btn btn-outline-primary btn-sm view-sale-btn"
                            data-lead-id="' . $lead->id . '"
                            data-nombre="' . e($lead->sale->nombre_cliente) . '"
                            data-apellido="' . e($lead->sale->apellido_cliente) . '"
                            data-email="' . e($lead->sale->email_cliente) . '"
                            data-telefono="' . e($lead->sale->telefono_cliente) . '"
                            data-identificacion="' . e($lead->sale->identificacion_personal) . '"
                            data-fuente="' . e($lead->sale->fuente) . '"
                            data-metodo_pago="' . e($lead->sale->metodo_pago) . '"
                            data-tipo_acuerdo="' . e($lead->sale->tipo_acuerdo) . '"
                            data-tipo_contrato="' . e($lead->sale->tipo_contrato) . '"
                            data-fecha_cierre="' . ($lead->sale->fecha_cierre ? $lead->sale->fecha_cierre->format('d/m/Y') : 'N/A') . '"
                            data-comentarios="' . e($lead->sale->comentarios) . '"
                            data-comprobantes=\'' . json_encode($comprobantes) . '\'
                            data-contrato="' . e($lead->sale->contractTemplate->name ?? 'N/A') . '"
                            data-contrato_estado="' . ($lead->sale->contract_approved ? 'Aprobado' : 'Pendiente') . '"
                            data-forma_pago="' . e($lead->sale->contract_data['forma_de_pago'] ?? 'N/A') . '"
                            data-fecha_firma="' . ($lead->sale->contract_signed_date ? $lead->sale->contract_signed_date->format('d/m/Y H:i') : 'N/A') . '"
                            data-monto_total="' . number_format($lead->sale->monto_total ?? 0, 2) . '"
                            data-monto_final="' . number_format($lead->sale->monto_final ?? 0, 2) . '"
                            data-descuento="' . ($lead->sale->descuento_porcentaje ? number_format($lead->sale->descuento_porcentaje, 0) . '%' : ($lead->sale->descuento_monto ? '$' . number_format($lead->sale->descuento_monto, 2) : 'Sin descuento')) . '"
                            data-moneda="' . e($lead->sale->moneda ?? 'USD') . '"
                            data-estado_pago="' . e($lead->sale->getEstadoPagoLabel()) . '"
                            data-estado_pago_badge="' . e($lead->sale->getEstadoPagoBadgeClass()) . '"
                            data-total_pagado="' . number_format($lead->sale->getTotalPagado(), 2) . '"
                            data-cuotas=\'' . json_encode($cuotas) . '\'
                            title="Ver Detalles de la Venta">
                            <i class="bi bi-eye"></i>
                        </button>';

                        // Botones de contrato si existe
                        if ($lead->sale->contractTemplate) {
                            if ($lead->sale->contract_approved) {
                                // Contrato aprobado: botón para descargar
                                $buttons .= '<button type="button" class="btn btn-outline-success btn-sm download-contract-btn"
                                    data-sale-id="' . $lead->sale->id . '"
                                    title="Descargar Contrato Aprobado">
                                    <i class="bi bi-download"></i>
                                </button>';
                            } else {
                                // Contrato no aprobado: botón para reenviar email
                                $buttons .= '<button type="button" class="btn btn-outline-warning btn-sm resend-contract-btn"
                                    data-sale-id="' . $lead->sale->id . '"
                                    title="Reenviar Email de Contrato">
                                    <i class="bi bi-envelope"></i>
                                </button>';
                            }
                        }
                    } elseif ($lead->pipelineStatus && $lead->pipelineStatus->name == 'Cerrada/Venta hecha') {
                        // Si no tiene venta pero está en estado de cierre: botón para registrar
                        $buttons .= '<a href="' . route('sales.form', $lead->id) . '" class="btn btn-outline-success btn-sm" title="Registrar Venta"><i class="bi bi-file-earmark-text"></i></a>';
                    }
                    
                    $buttons .= '</div>';
                    return $buttons;
                })
                ->addColumn('pipeline_status', function ($lead) use ($pipelineStatuses) {
                    $options = '';
                    foreach ($pipelineStatuses as $status) {
                        $selected = $lead->pipeline_status_id == $status->id ? 'selected' : '';
                        $options .= '<option value="' . $status->id . '" ' . $selected . '>' . $status->name . '</option>';
                    }
                    return '<select class="form-select form-select-sm pipeline-status-select" data-lead-id="' . $lead->id . '">' . $options . '</select>';
                })
                ->filterColumn('pipeline_status', function($query, $keyword) {
                    $query->whereHas('pipelineStatus', function($q) use ($keyword) {
                        $q->where('name', 'like', "%{$keyword}%");
                    });
                })
                ->rawColumns(['action', 'pipeline_status'])
                ->make(true);
        }

        if (!$request->ajax()) {
            if (auth()->user()->hasRole('admin')) {
                $usuarios = User::where('id', '!=', 1)->get();
                foreach ($usuarios as $usuario) {
                    $this->importar_leads($usuario->id);
                }
            } else {
                $this->importar_leads(Auth::id());
            }
        }
        
        return view('leads.leads_index');
    }

    public function updatePipelineStatus(Request $request, $id)
    {
        $request->validate([
            'status_id' => 'required|exists:pipeline_statuses,id',
            'comentario' => 'nullable|string',
        ]);

        $lead = Lead::findOrFail($id);
        $oldStatus = $lead->pipelineStatus->name;
        
        $lead->pipeline_status_id = $request->status_id;
        $lead->save();

        $newStatus = $lead->fresh()->pipelineStatus->name;

        Log::create([
            'id_tabla' => $lead->id,
            'tabla' => 'leads',
            'detalle' => $request->comentario ?? 'Cambio de estado sin comentario.',
            'valor_viejo' => $oldStatus,
            'valor_nuevo' => $newStatus,
            'id_usuario' => Auth::id(),
        ]);

        return response()->json(['success' => true, 'message' => 'Estado del lead actualizado.']);
    }

    public function infoJson($id)
    {
        $lead = Lead::findOrFail($id);
        return response()->json($lead);
    }

    public function logs($id)
    {
        $lead = Lead::with(['sale.paymentProofs', 'onboardingCalls', 'traigeCalls'])->findOrFail($id);

        // Obtener todos los logs relacionados con este lead
        $allLogs = collect();

        // 1. Logs del lead (cambios de pipeline en traige y closers)
        $leadLogs = Log::where('tabla', 'leads')
            ->where('id_tabla', $id)
            ->with('usuario')
            ->get();
        $allLogs = $allLogs->merge($leadLogs);

        // 2. Logs de llamadas de traige
        $traigeCallIds = $lead->traigeCalls->pluck('id');
        if ($traigeCallIds->isNotEmpty()) {
            $traigeCallLogs = Log::where('tabla', 'traige_calls')
                ->whereIn('id_tabla', $traigeCallIds)
                ->with('usuario')
                ->get();
            $allLogs = $allLogs->merge($traigeCallLogs);
        }

        // 3. Logs de llamadas de onboarding
        $callIds = $lead->onboardingCalls->pluck('id');
        if ($callIds->isNotEmpty()) {
            $callLogs = Log::where('tabla', 'onboarding_calls')
                ->whereIn('id_tabla', $callIds)
                ->with('usuario')
                ->get();
            $allLogs = $allLogs->merge($callLogs);
        }

        // 4. Logs de upsell si tiene venta
        if ($lead->sale) {
            $saleLogs = Log::where('tabla', 'sales')
                ->where('id_tabla', $lead->sale->id)
                ->where('tipo_log', 'upsell')
                ->with('usuario')
                ->get();
            $allLogs = $allLogs->merge($saleLogs);
        }

        // Ordenar todos los logs por fecha descendente
        $allLogs = $allLogs->sortByDesc('created_at');

        // Preparar comprobantes multiples para logs de venta
        $paymentProofs = [];
        if ($lead->sale) {
            $paymentProofs = $lead->sale->paymentProofs->map(function($proof) {
                return [
                    'url' => asset($proof->file_path),
                    'name' => $proof->original_name ?? basename($proof->file_path)
                ];
            })->toArray();
            // Fallback al comprobante principal si no hay en la tabla nueva
            if (empty($paymentProofs) && $lead->sale->comprobante_pago_path) {
                $paymentProofs = [[
                    'url' => asset($lead->sale->comprobante_pago_path),
                    'name' => basename($lead->sale->comprobante_pago_path)
                ]];
            }
        }

        return response()->json($allLogs->map(function ($log) use ($paymentProofs) {
            $archivos = $log->archivo_soporte;

            // Para logs de venta, incluir todos los comprobantes
            if ($log->tipo_log === 'venta' && !empty($paymentProofs)) {
                $archivos = $paymentProofs;
            }

            return [
                'estado_anterior' => $log->valor_viejo ?? 'N/A',
                'estado_nuevo' => $log->valor_nuevo ?? 'N/A',
                'comentario' => $log->detalle,
                'usuario' => $log->usuario->name ?? 'Desconocido',
                'fecha' => $log->created_at->format('Y-m-d H:i'),
                'tipo' => $this->getLogType($log->tabla, $log->tipo_log ?? null),
                'archivo_soporte' => $archivos,
            ];
        })->values());
    }

    private function getLogType($tabla, $tipoLog = null)
    {
        switch ($tabla) {
            case 'leads':
                // Si tiene tipo_log = 'traige', mostrar Traige, sino Pipeline
                if ($tipoLog === 'traige') {
                    return 'Traige';
                } elseif ($tipoLog === 'venta') {
                    return 'Venta';
                } elseif ($tipoLog === 'contrato') {
                    return 'Contrato';
                } else {
                    return 'Pipeline';
                }
            case 'traige_calls':
                return 'Llamadas Traige';
            case 'onboarding_calls':
                return 'Onboarding';
            case 'sales':
                return $tipoLog === 'upsell' ? 'Upsell' : 'Venta';
            default:
                return 'General';
        }
    }

    public function downloadContract($saleId)
    {
        $sale = Sale::with('contractTemplate')->findOrFail($saleId);

        if (!$sale->contract_approved) {
            return back()->with('error', 'El contrato no ha sido aprobado aún.');
        }

        // Generar HTML del contrato
        $contractHtml = $this->generateContractHtml($sale, $sale->contractTemplate);

        // Crear PDF usando DomPDF
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($contractHtml);

        return $pdf->download('contrato_' . $sale->id . '.pdf');
    }

    public function resendContractEmail($saleId)
    {
        $sale = Sale::with(['contractTemplate', 'lead'])->findOrFail($saleId);

        if (!$sale->contractTemplate) {
            return response()->json(['success' => false, 'message' => 'No hay contrato asociado.']);
        }

        try {
            Mail::to($sale->email_cliente)->send(new ContractSigningMail($sale));

            return response()->json([
                'success' => true,
                'message' => 'Email de contrato reenviado exitosamente.',
                'contract_url' => route('contract.sign', $sale->contract_token)
            ]);
        } catch (\Exception $e) {
            \Log::error('Error reenviando email de contrato: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al enviar el email. Inténtalo de nuevo.'
            ]);
        }
    }

    private function generateContractHtml($sale, $contractTemplate)
    {
        $html = $contractTemplate->html_content;
        $contractData = $sale->contract_data ?? [];

        // Usar solo los datos del contrato tal como están
        foreach ($contractData as $key => $value) {
            if ($key === 'imagen_firma' && $value) {
                // Para PDF, convertir URL a ruta absoluta del archivo
                $imagePath = $value;
                if (strpos($value, 'http') === 0) {
                    // Es una URL completa, extraer solo la parte del archivo
                    $imagePath = str_replace(asset(''), '', $value);
                }

                // Construir ruta absoluta para DomPDF
                $absolutePath = public_path($imagePath);

                if (file_exists($absolutePath)) {
                    $html = str_replace('{' . $key . '}', '<img src="' . $absolutePath . '" class="signature-image">', $html);
                } else {
                    // Si no existe el archivo, usar la URL original
                    $html = str_replace('{' . $key . '}', '<img src="' . $value . '" class="signature-image">', $html);
                }
            } else {
                $html = str_replace('{' . $key . '}', $value ?? '', $html);
            }
        }

        return $html;
    }
}