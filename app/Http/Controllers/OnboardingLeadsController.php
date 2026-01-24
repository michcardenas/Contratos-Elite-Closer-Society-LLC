<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lead;
use App\Models\OnboardingCall;
use App\Models\Sale;
use App\Models\Log;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class OnboardingLeadsController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $userRole = auth()->user()->getRoleNames()->first();
            if (!in_array($userRole, ['admin', 'cms'])) {
                abort(403, 'Acceso no autorizado. Solo usuarios con rol Admin o CMS pueden acceder a este módulo.');
            }
            return $next($request);
        });
    }

    /**
     * Muestra todos los leads que tienen una venta cerrada (para onboarding)
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            // Solo leads que tienen una venta registrada con contrato aprobado
            $query = Lead::with(['sale.contractTemplate', 'sale.paymentProofs', 'sale.installments', 'onboardingCalls' => function($query) {
                $query->latest();
            }])
            ->whereHas('sale', function($query) {
                $query->whereNotNull('contract_template_id')
                      ->where('contract_approved', true);
            })
            ->orderByDesc('id');

            return DataTables::of($query)
                ->addColumn('action', function ($lead) {
                    $buttons = '<div class="d-flex justify-content-start gap-1">';
                    
                    // Botón para programar llamada
                    $buttons .= '<button type="button" class="btn btn-outline-success btn-sm schedule-call-btn" 
                        data-lead-id="' . $lead->id . '" 
                        data-lead-name="' . e($lead->nombre) . '"
                        data-lead-email="' . e($lead->email) . '"
                        title="Programar Llamada de Onboarding">
                        <i class="bi bi-calendar-plus"></i>
                    </button>';
                    
                    // Botón para ver información de venta
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

                        // Botón de Upsell solo para low ticket sin upsell
                        if ($lead->sale->tipo_contrato === 'low ticket' && is_null($lead->sale->upsell)) {
                            $buttons .= '<button type="button" class="btn btn-outline-warning btn-sm upsell-btn"
                                data-sale-id="' . $lead->sale->id . '"
                                data-lead-name="' . e($lead->nombre) . '"
                                title="Pasar a Upsell">
                                <i class="bi bi-arrow-up-circle"></i>
                            </button>';
                        }
                    }
                    
                    // Botón para ver trazabilidad de llamadas
                    $buttons .= '<button type="button" class="btn btn-outline-info btn-sm view-calls-btn" 
                        data-lead-id="' . $lead->id . '" 
                        title="Ver Llamadas de Onboarding">
                        <i class="bi bi-telephone"></i>
                    </button>';
                    
                    // Botón para gestionar notas del lead
                    $buttons .= '<button type="button" class="btn btn-outline-warning btn-sm manage-notes-btn"
                        data-lead-id="' . $lead->id . '"
                        data-lead-name="' . e($lead->nombre) . '"
                        data-lead-email="' . e($lead->email) . '"
                        title="Gestionar Notas del Lead">
                        <i class="bi bi-sticky"></i>
                    </button>';

                    // Botón para descargar contrato (solo si existe y está aprobado)
                    if ($lead->sale && $lead->sale->contract_approved && $lead->sale->contractTemplate) {
                        $buttons .= '<button type="button" class="btn btn-outline-success btn-sm download-contract-btn"
                            data-sale-id="' . $lead->sale->id . '"
                            title="Descargar Contrato Aprobado">
                            <i class="bi bi-download"></i>
                        </button>';
                    }

                    // Botón para ver historial de cambios
                    $buttons .= '<button type="button" class="btn btn-outline-secondary btn-sm view-logs-btn"
                        data-lead-id="' . $lead->id . '"
                        title="Ver Historial de Cambios">
                        <i class="bi bi-clock-history"></i>
                    </button>';

                    $buttons .= '</div>';
                    return $buttons;
                })
                ->addColumn('onboarding_status', function ($lead) {
                    $lastCall = $lead->onboardingCalls->first();
                    if (!$lastCall) {
                        return '<span class="badge bg-secondary">Sin llamadas</span>';
                    }
                    
                    $statusLabels = [
                        'pendiente' => '<span class="badge bg-warning">Pendiente</span>',
                        'realizada' => '<span class="badge bg-success">Realizada</span>',
                        'no_realizada' => '<span class="badge bg-danger">No Realizada</span>',
                        'reprogramada' => '<span class="badge bg-info">Reprogramada</span>',
                    ];
                    
                    return $statusLabels[$lastCall->status] ?? '<span class="badge bg-secondary">Desconocido</span>';
                })
                ->addColumn('next_call', function ($lead) {
                    $nextCall = $lead->onboardingCalls()
                        ->whereIn('status', ['pendiente'])
                        ->orderBy('scheduled_date', 'asc')
                        ->first();

                    if ($nextCall) {
                        return $nextCall->scheduled_date->format('d/m/Y H:i');
                    }

                    return '-';
                })
                ->addColumn('payment_status', function ($lead) {
                    if (!$lead->sale) {
                        return '<span class="badge bg-secondary">Sin venta</span>';
                    }

                    $badgeClass = $lead->sale->getEstadoPagoBadgeClass();
                    $label = $lead->sale->getEstadoPagoLabel();

                    // Agregar indicador de cuotas vencidas
                    $overdue = $lead->sale->installments->filter(function($i) {
                        return $i->estado === 'vencida' ||
                               ($i->estado === 'pendiente' && $i->fecha_vencimiento < now());
                    })->count();

                    $html = '<span class="badge bg-' . $badgeClass . '">' . $label . '</span>';

                    if ($overdue > 0) {
                        $html .= ' <span class="badge bg-danger" title="Cuotas vencidas"><i class="bi bi-exclamation-triangle"></i> ' . $overdue . '</span>';
                    }

                    return $html;
                })
                ->rawColumns(['action', 'onboarding_status', 'payment_status'])
                ->make(true);
        }

        return view('onboarding.leads_index');
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

    public function logs($id)
    {
        $lead = Lead::with(['sale.paymentProofs', 'onboardingCalls'])->findOrFail($id);

        // Obtener todos los logs relacionados con este lead
        $allLogs = collect();

        // 1. Logs del lead (cambios de pipeline)
        $leadLogs = Log::where('tabla', 'leads')
            ->where('id_tabla', $id)
            ->with('usuario')
            ->get();
        $allLogs = $allLogs->merge($leadLogs);

        // 2. Logs de llamadas de onboarding
        $callIds = $lead->onboardingCalls->pluck('id');
        if ($callIds->isNotEmpty()) {
            $callLogs = Log::where('tabla', 'onboarding_calls')
                ->whereIn('id_tabla', $callIds)
                ->with('usuario')
                ->get();
            $allLogs = $allLogs->merge($callLogs);
        }

        // 3. Logs de upsell si tiene venta
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
                if ($tipoLog === 'venta') return 'Venta';
                if ($tipoLog === 'contrato') return 'Contrato';
                return 'Pipeline';
            case 'onboarding_calls':
                return 'Onboarding';
            case 'sales':
                return $tipoLog === 'upsell' ? 'Upsell' : 'Venta';
            default:
                return 'General';
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
