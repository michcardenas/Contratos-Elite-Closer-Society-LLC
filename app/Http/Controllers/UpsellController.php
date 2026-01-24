<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\Lead;
use App\Models\Log;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class UpsellController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $userRole = auth()->user()->getRoleNames()->first();
            if (!in_array($userRole, ['admin'])) {
                abort(403, 'Acceso no autorizado. Solo usuarios con rol Admin pueden acceder a este módulo.');
            }
            return $next($request);
        });
    }

    /**
     * Muestra la vista principal de upsells
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Sale::with(['lead', 'upsellUserPendiente', 'upsellUserAprobado', 'paymentProofs', 'contractTemplate'])
                ->whereNotNull('upsell')
                ->orderByDesc('upsell_fecha_pendiente');

            // Filtro por estado
            if ($request->has('status') && in_array($request->status, ['pendiente', 'aprobado'])) {
                $query->where('upsell', $request->status);
            }

            return DataTables::of($query)
                ->addColumn('action', function ($sale) {
                    $buttons = '<div class="d-flex justify-content-start gap-1">';

                    // Preparar comprobantes como JSON
                    $comprobantes = $sale->paymentProofs->map(function($proof) {
                        return [
                            'url' => asset($proof->file_path),
                            'name' => $proof->original_name ?? basename($proof->file_path)
                        ];
                    })->toArray();
                    // Si no hay en la nueva tabla, usar el comprobante principal
                    if (empty($comprobantes) && $sale->comprobante_pago_path) {
                        $comprobantes = [[
                            'url' => asset($sale->comprobante_pago_path),
                            'name' => basename($sale->comprobante_pago_path)
                        ]];
                    }

                    // Botón para ver detalles de venta
                    $buttons .= '<button type="button" class="btn btn-outline-primary btn-sm view-sale-btn"
                        data-lead-id="' . $sale->lead_id . '"
                        data-nombre="' . e($sale->nombre_cliente) . '"
                        data-apellido="' . e($sale->apellido_cliente) . '"
                        data-email="' . e($sale->email_cliente) . '"
                        data-telefono="' . e($sale->telefono_cliente) . '"
                        data-identificacion="' . e($sale->identificacion_personal) . '"
                        data-fuente="' . e($sale->fuente) . '"
                        data-metodo_pago="' . e($sale->metodo_pago) . '"
                        data-tipo_acuerdo="' . e($sale->tipo_acuerdo) . '"
                        data-tipo_contrato="' . e($sale->tipo_contrato) . '"
                        data-fecha_cierre="' . ($sale->fecha_cierre ? $sale->fecha_cierre->format('d/m/Y') : 'N/A') . '"
                        data-comentarios="' . e($sale->comentarios) . '"
                        data-comprobantes=\'' . json_encode($comprobantes) . '\'
                        data-contrato="' . e($sale->contractTemplate->name ?? 'N/A') . '"
                        data-contrato_estado="' . ($sale->contract_approved ? 'Aprobado' : 'Pendiente') . '"
                        data-forma_pago="' . e($sale->contract_data['forma_de_pago'] ?? 'N/A') . '"
                        data-fecha_firma="' . ($sale->contract_signed_date ? $sale->contract_signed_date->format('d/m/Y H:i') : 'N/A') . '"
                        title="Ver Detalles de la Venta">
                        <i class="bi bi-eye"></i>
                    </button>';

                    // Botón para ver historial de upsell
                    $buttons .= '<button type="button" class="btn btn-outline-info btn-sm view-upsell-logs-btn"
                        data-sale-id="' . $sale->id . '"
                        title="Ver Historial de Upsell">
                        <i class="bi bi-clock-history"></i>
                    </button>';

                    // Botón para aprobar upsell (solo si está pendiente)
                    if ($sale->upsell === 'pendiente') {
                        $buttons .= '<button type="button" class="btn btn-outline-success btn-sm approve-upsell-btn"
                            data-sale-id="' . $sale->id . '"
                            data-lead-name="' . e($sale->lead->nombre ?? 'N/A') . '"
                            title="Aprobar Upsell - Pasar a High Ticket">
                            <i class="bi bi-check-circle"></i>
                        </button>';
                    }

                    $buttons .= '</div>';
                    return $buttons;
                })
                ->addColumn('lead_info', function ($sale) {
                    return e($sale->lead->nombre ?? 'N/A') . '<br><small>' . e($sale->lead->email ?? 'N/A') . '</small>';
                })
                ->addColumn('upsell_status', function ($sale) {
                    if ($sale->upsell === 'pendiente') {
                        return '<span class="badge bg-warning">Pendiente</span>';
                    } elseif ($sale->upsell === 'aprobado') {
                        return '<span class="badge bg-success">Aprobado</span>';
                    }
                    return '<span class="badge bg-secondary">N/A</span>';
                })
                ->addColumn('fecha_pendiente', function ($sale) {
                    return $sale->upsell_fecha_pendiente ? $sale->upsell_fecha_pendiente->format('d/m/Y H:i') : 'N/A';
                })
                ->addColumn('fecha_aprobado', function ($sale) {
                    return $sale->upsell_fecha_aprobado ? $sale->upsell_fecha_aprobado->format('d/m/Y H:i') : 'N/A';
                })
                ->addColumn('user_pendiente', function ($sale) {
                    return $sale->upsellUserPendiente->name ?? 'N/A';
                })
                ->addColumn('user_aprobado', function ($sale) {
                    return $sale->upsellUserAprobado->name ?? 'N/A';
                })
                ->rawColumns(['action', 'lead_info', 'upsell_status'])
                ->make(true);
        }

        return view('upsell.index');
    }

    /**
     * Marca una venta como upsell pendiente
     */
    public function markPendiente(Request $request, $saleId)
    {
        $sale = Sale::findOrFail($saleId);

        // Verificar que sea low ticket y no tenga upsell
        if ($sale->tipo_contrato !== 'low ticket' || !is_null($sale->upsell)) {
            return response()->json(['error' => 'Esta venta no es elegible para upsell'], 400);
        }

        // Actualizar la venta
        $sale->update([
            'upsell' => 'pendiente',
            'upsell_comentarios' => $request->comentarios,
            'upsell_fecha_pendiente' => now(),
            'upsell_user_pendiente' => Auth::id(),
        ]);

        // Crear log
        Log::create([
            'id_tabla' => $sale->id,
            'tabla' => 'sales',
            'detalle' => 'Enviado a Upsell desde Onboarding. ' . ($request->comentarios ? 'Comentarios: ' . $request->comentarios : ''),
            'tipo_log' => 'upsell',
            'valor_viejo' => null,
            'valor_nuevo' => 'pendiente',
            'id_usuario' => Auth::id(),
            'estado' => 1,
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Aprueba un upsell y lo convierte a high ticket
     */
    public function approve(Request $request, $saleId)
    {
        $validated = $request->validate([
            'comprobante_upsell' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'comentarios_aprobacion' => 'nullable|string',
        ]);

        $sale = Sale::findOrFail($saleId);

        // Verificar que esté pendiente
        if ($sale->upsell !== 'pendiente') {
            return response()->json(['error' => 'Esta venta no está en estado pendiente para upsell'], 400);
        }

        // Guardar comprobante directamente en public
        $file = $request->file('comprobante_upsell');
        $filename = time() . '_upsell_' . $file->getClientOriginalName();
        $file->move(public_path('upsell_comprobantes'), $filename);
        $path = 'upsell_comprobantes/' . $filename;

        // Actualizar la venta
        $sale->update([
            'upsell' => 'aprobado',
            'tipo_contrato' => 'high ticket',
            'upsell_comprobante_path' => $path,
            'upsell_comentarios' => $sale->upsell_comentarios . ($validated['comentarios_aprobacion'] ? "\n\nAprobación: " . $validated['comentarios_aprobacion'] : ''),
            'upsell_fecha_aprobado' => now(),
            'upsell_user_aprobado' => Auth::id(),
        ]);

        // Crear log
        Log::create([
            'id_tabla' => $sale->id,
            'tabla' => 'sales',
            'detalle' => 'Upsell aprobado, cambiado a High Ticket. ' . ($validated['comentarios_aprobacion'] ? 'Comentarios: ' . $validated['comentarios_aprobacion'] : ''),
            'archivo_soporte' => asset($path),
            'tipo_log' => 'upsell',
            'valor_viejo' => 'pendiente',
            'valor_nuevo' => 'aprobado',
            'id_usuario' => Auth::id(),
            'estado' => 1,
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Obtiene el historial completo de cambios para una venta (trazabilidad completa)
     */
    public function getLogs($saleId)
    {
        $sale = Sale::with(['lead.onboardingCalls', 'paymentProofs'])->findOrFail($saleId);
        $lead = $sale->lead;

        // Obtener todos los logs relacionados con este lead
        $allLogs = collect();

        // 1. Logs del lead (cambios de pipeline)
        $leadLogs = Log::where('tabla', 'leads')
            ->where('id_tabla', $lead->id)
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

        // 3. Logs de upsell de esta venta
        $saleLogs = Log::where('tabla', 'sales')
            ->where('id_tabla', $saleId)
            ->where('tipo_log', 'upsell')
            ->with('usuario')
            ->get();
        $allLogs = $allLogs->merge($saleLogs);

        // Ordenar todos los logs por fecha descendente
        $allLogs = $allLogs->sortByDesc('created_at');

        // Preparar comprobantes multiples para logs de venta
        $paymentProofs = $sale->paymentProofs->map(function($proof) {
            return [
                'url' => asset($proof->file_path),
                'name' => $proof->original_name ?? basename($proof->file_path)
            ];
        })->toArray();
        // Fallback al comprobante principal si no hay en la tabla nueva
        if (empty($paymentProofs) && $sale->comprobante_pago_path) {
            $paymentProofs = [[
                'url' => asset($sale->comprobante_pago_path),
                'name' => basename($sale->comprobante_pago_path)
            ]];
        }

        return response()->json($allLogs->map(function ($log) use ($paymentProofs) {
            $archivos = $log->archivo_soporte;

            // Para logs de venta, incluir todos los comprobantes
            if ($log->tipo_log === 'venta' && !empty($paymentProofs)) {
                $archivos = $paymentProofs;
            }

            return [
                'valor_viejo' => $log->valor_viejo ?? 'N/A',
                'valor_nuevo' => $log->valor_nuevo ?? 'N/A',
                'detalle' => $log->detalle,
                'usuario' => $log->usuario->name ?? 'Usuario eliminado',
                'fecha' => $log->created_at->format('d/m/Y H:i:s'),
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
}
