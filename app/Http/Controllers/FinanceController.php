<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\SaleInstallment;
use App\Models\InstallmentPayment;
use App\Models\Log;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FinanceController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $role = auth()->user()->getRoleNames()->first();
            if (!in_array($role, ['admin', 'finanzas'])) {
                abort(403, 'Acceso no autorizado');
            }
            return $next($request);
        });
    }

    /**
     * Dashboard de finanzas
     */
    public function dashboard()
    {
        $stats = $this->getDashboardStats();
        return view('finance.dashboard', compact('stats'));
    }

    /**
     * Lista de cuotas
     */
    public function installments(Request $request)
    {
        if ($request->ajax()) {
            $query = SaleInstallment::with(['sale', 'sale.lead', 'sale.user'])
                ->orderBy('fecha_vencimiento');

            // Filtrar por estado si se especifica
            if ($request->filled('estado')) {
                if ($request->estado === 'vencida') {
                    $query->where(function($q) {
                        $q->where('estado', 'vencida')
                          ->orWhere(function($q2) {
                              $q2->where('estado', 'pendiente')
                                 ->where('fecha_vencimiento', '<', Carbon::today());
                          });
                    });
                } else {
                    $query->where('estado', $request->estado);
                }
            }

            return DataTables::of($query)
                ->addColumn('lead_name', function ($installment) {
                    return $installment->sale && $installment->sale->lead
                        ? $installment->sale->lead->nombre
                        : 'N/A';
                })
                ->addColumn('client_name', function ($installment) {
                    return $installment->sale
                        ? $installment->sale->nombre_cliente . ' ' . $installment->sale->apellido_cliente
                        : 'N/A';
                })
                ->addColumn('tipo_contrato', function ($installment) {
                    return $installment->sale ? ucfirst($installment->sale->tipo_contrato) : 'N/A';
                })
                ->addColumn('closer_name', function ($installment) {
                    return $installment->sale && $installment->sale->user
                        ? $installment->sale->user->name
                        : 'N/A';
                })
                ->addColumn('monto_formateado', function ($installment) {
                    $moneda = $installment->sale ? $installment->sale->moneda : 'USD';
                    return '$' . number_format($installment->monto, 2) . ' ' . $moneda;
                })
                ->addColumn('fecha_formateada', function ($installment) {
                    return $installment->fecha_vencimiento
                        ? $installment->fecha_vencimiento->format('d/m/Y')
                        : '-';
                })
                ->addColumn('dias_vencido', function ($installment) {
                    if ($installment->estado === 'pagada') {
                        return '-';
                    }
                    if ($installment->fecha_vencimiento < Carbon::today()) {
                        return Carbon::today()->diffInDays($installment->fecha_vencimiento) . ' dias';
                    }
                    return '-';
                })
                ->addColumn('estado_badge', function ($installment) {
                    $estado = $installment->estado;
                    // Verificar si esta vencida aunque el estado sea pendiente
                    if ($estado === 'pendiente' && $installment->fecha_vencimiento < Carbon::today()) {
                        $estado = 'vencida';
                    }
                    $badgeClass = match($estado) {
                        'pendiente' => 'warning',
                        'pagada' => 'success',
                        'vencida' => 'danger',
                        'parcial' => 'info',
                        default => 'secondary',
                    };
                    $label = match($estado) {
                        'pendiente' => 'Pendiente',
                        'pagada' => 'Pagada',
                        'vencida' => 'Vencida',
                        'parcial' => 'Pago Parcial',
                        default => 'Desconocido',
                    };
                    return '<span class="badge bg-' . $badgeClass . '">' . $label . '</span>';
                })
                ->addColumn('action', function ($installment) {
                    $buttons = '<div class="d-flex gap-1">';
                    if ($installment->estado !== 'pagada') {
                        $buttons .= '<button class="btn btn-sm btn-success btn-register-payment"
                                        data-installment-id="' . $installment->id . '"
                                        data-monto="' . $installment->monto . '"
                                        data-monto-pendiente="' . $installment->getMontoPendiente() . '"
                                        title="Registrar Pago">
                                    <i class="bi bi-cash-coin"></i>
                                </button>';
                    }
                    $buttons .= '<button class="btn btn-sm btn-info btn-view-history"
                                    data-installment-id="' . $installment->id . '"
                                    title="Ver Historial">
                                <i class="bi bi-clock-history"></i>
                            </button>';
                    $buttons .= '</div>';
                    return $buttons;
                })
                ->rawColumns(['estado_badge', 'action'])
                ->make(true);
        }

        return view('finance.installments');
    }

    /**
     * Registrar pago de cuota
     */
    public function registerPayment(Request $request, $installmentId)
    {
        $installment = SaleInstallment::with('sale')->findOrFail($installmentId);

        $validated = $request->validate([
            'monto' => 'required|numeric|min:0.01',
            'fecha_pago' => 'required|date',
            'metodo_pago' => 'required|in:D LOCAL GO,WHOP,TRANSFERENCIA USD,TRANSFERENCIA PESOS,CRIPTO,EFECTIVO,WIRE,PAYPAL',
            'comprobante' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'referencia' => 'nullable|string|max:255',
            'notas' => 'nullable|string',
        ]);

        $comprobantePath = null;
        if ($request->hasFile('comprobante')) {
            $file = $request->file('comprobante');
            $filename = time() . '_' . uniqid() . '_pago_' . $file->getClientOriginalName();
            $file->move(public_path('pagos_cuotas'), $filename);
            $comprobantePath = 'pagos_cuotas/' . $filename;
        }

        // Crear registro de pago
        $payment = InstallmentPayment::create([
            'installment_id' => $installment->id,
            'monto' => $validated['monto'],
            'fecha_pago' => $validated['fecha_pago'],
            'metodo_pago' => $validated['metodo_pago'],
            'comprobante_path' => $comprobantePath,
            'referencia' => $validated['referencia'],
            'notas' => $validated['notas'],
            'registrado_por' => Auth::id(),
        ]);

        // Actualizar cuota
        $nuevoMontoPagado = ($installment->monto_pagado ?? 0) + $validated['monto'];
        $installment->monto_pagado = $nuevoMontoPagado;

        // Determinar nuevo estado
        if ($nuevoMontoPagado >= $installment->monto) {
            $installment->estado = 'pagada';
            $installment->fecha_pago = $validated['fecha_pago'];
        } else {
            $installment->estado = 'parcial';
        }

        $installment->metodo_pago = $validated['metodo_pago'];
        $installment->referencia_pago = $validated['referencia'];
        $installment->comprobante_path = $comprobantePath ?? $installment->comprobante_path;
        $installment->save();

        // Actualizar estado de pago de la venta
        $installment->sale->updatePaymentStatus();

        // Crear log
        Log::create([
            'id_tabla' => $installment->sale->lead_id,
            'tabla' => 'leads',
            'tipo_log' => 'pago_cuota',
            'detalle' => sprintf(
                'Pago de cuota %d registrado. Monto: $%s %s. Metodo: %s',
                $installment->numero_cuota,
                number_format($validated['monto'], 2),
                $installment->sale->moneda,
                $validated['metodo_pago']
            ),
            'archivo_soporte' => $comprobantePath ? asset($comprobantePath) : null,
            'valor_viejo' => 'pendiente',
            'valor_nuevo' => $installment->estado,
            'id_usuario' => Auth::id(),
            'estado' => 1,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pago registrado exitosamente',
        ]);
    }

    /**
     * Obtener historial de pagos de una cuota
     */
    public function getInstallmentHistory($installmentId)
    {
        $installment = SaleInstallment::with(['payments', 'payments.registeredBy', 'sale'])->findOrFail($installmentId);

        $history = $installment->payments->map(function ($payment) {
            return [
                'id' => $payment->id,
                'monto' => number_format($payment->monto, 2),
                'fecha_pago' => $payment->fecha_pago->format('d/m/Y'),
                'metodo_pago' => $payment->getMetodoPagoLabel(),
                'referencia' => $payment->referencia,
                'notas' => $payment->notas,
                'comprobante_url' => $payment->getComprobanteUrl(),
                'registrado_por' => $payment->registeredBy ? $payment->registeredBy->name : 'Sistema',
                'created_at' => $payment->created_at->format('d/m/Y H:i'),
            ];
        });

        return response()->json([
            'installment' => [
                'numero_cuota' => $installment->numero_cuota,
                'monto' => number_format($installment->monto, 2),
                'monto_pagado' => number_format($installment->monto_pagado ?? 0, 2),
                'monto_pendiente' => number_format($installment->getMontoPendiente(), 2),
                'fecha_vencimiento' => $installment->fecha_vencimiento->format('d/m/Y'),
                'estado' => $installment->getEstadoLabel(),
                'moneda' => $installment->sale->moneda ?? 'USD',
            ],
            'payments' => $history,
        ]);
    }

    /**
     * Obtener estadisticas del dashboard
     */
    private function getDashboardStats(): array
    {
        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        // Total ventas del mes
        $totalVentasMes = Sale::whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->sum('monto_final');

        // Total recaudado del mes
        $totalRecaudadoMes = InstallmentPayment::whereBetween('fecha_pago', [$startOfMonth, $endOfMonth])
            ->sum('monto');

        // Total pendiente de cobrar
        $totalPendiente = SaleInstallment::whereIn('estado', ['pendiente', 'parcial', 'vencida'])
            ->sum(DB::raw('monto - COALESCE(monto_pagado, 0)'));

        // Total vencido
        $totalVencido = SaleInstallment::where(function($q) use ($today) {
            $q->where('estado', 'vencida')
              ->orWhere(function($q2) use ($today) {
                  $q2->where('estado', 'pendiente')
                     ->where('fecha_vencimiento', '<', $today);
              });
        })->sum(DB::raw('monto - COALESCE(monto_pagado, 0)'));

        // Cuotas vencidas count
        $cuotasVencidas = SaleInstallment::where(function($q) use ($today) {
            $q->where('estado', 'vencida')
              ->orWhere(function($q2) use ($today) {
                  $q2->where('estado', 'pendiente')
                     ->where('fecha_vencimiento', '<', $today);
              });
        })->count();

        // Proximos vencimientos (7 dias)
        $proximosVencimientos = SaleInstallment::where('estado', 'pendiente')
            ->whereBetween('fecha_vencimiento', [$today, $today->copy()->addDays(7)])
            ->count();

        // Cuotas por estado
        $cuotasPorEstado = SaleInstallment::select('estado', DB::raw('count(*) as total'))
            ->groupBy('estado')
            ->pluck('total', 'estado')
            ->toArray();

        // Recaudacion ultimos 6 meses
        $recaudacionMensual = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $total = InstallmentPayment::whereYear('fecha_pago', $month->year)
                ->whereMonth('fecha_pago', $month->month)
                ->sum('monto');
            $recaudacionMensual[] = [
                'mes' => $month->format('M Y'),
                'total' => (float) $total,
            ];
        }

        return [
            'total_ventas_mes' => $totalVentasMes,
            'total_recaudado_mes' => $totalRecaudadoMes,
            'total_pendiente' => $totalPendiente,
            'total_vencido' => $totalVencido,
            'cuotas_vencidas' => $cuotasVencidas,
            'proximos_vencimientos' => $proximosVencimientos,
            'cuotas_por_estado' => $cuotasPorEstado,
            'recaudacion_mensual' => $recaudacionMensual,
        ];
    }

    /**
     * Exportar a Excel
     */
    public function exportExcel(Request $request)
    {
        // TODO: Implementar exportacion a Excel
        return response()->json(['message' => 'Funcion en desarrollo']);
    }
}
