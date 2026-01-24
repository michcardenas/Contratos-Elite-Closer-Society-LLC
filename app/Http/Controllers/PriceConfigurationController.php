<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PriceConfiguration;
use Yajra\DataTables\Facades\DataTables;

class PriceConfigurationController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (auth()->user()->getRoleNames()->first() !== 'admin') {
                abort(403, 'Acceso no autorizado');
            }
            return $next($request);
        });
    }

    /**
     * Display price configurations
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $configurations = PriceConfiguration::where('activo', true);

            return DataTables::of($configurations)
                ->addColumn('tipo_contrato_label', function ($config) {
                    return $this->getTipoContratoLabel($config->tipo_contrato);
                })
                ->addColumn('monto_formateado', function ($config) {
                    return '$' . number_format($config->monto_base, 2) . ' ' . $config->moneda;
                })
                ->addColumn('monto_editable_badge', function ($config) {
                    return $config->monto_editable
                        ? '<span class="badge bg-success">Editable</span>'
                        : '<span class="badge bg-secondary">Fijo</span>';
                })
                ->addColumn('cuotas_editables_badge', function ($config) {
                    return $config->cuotas_editables
                        ? '<span class="badge bg-success">Editables</span>'
                        : '<span class="badge bg-secondary">Auto-calculadas</span>';
                })
                ->addColumn('action', function ($config) {
                    return '<button class="btn btn-outline-info btn-sm btn-edit" data-id="' . $config->id . '" title="Editar">
                        <i class="bi bi-pencil"></i>
                    </button>';
                })
                ->rawColumns(['monto_editable_badge', 'cuotas_editables_badge', 'action'])
                ->make(true);
        }

        $configurations = PriceConfiguration::where('activo', true)->get();
        return view('configuration.prices', compact('configurations'));
    }

    /**
     * Get a specific configuration for editing
     */
    public function show($id)
    {
        $config = PriceConfiguration::findOrFail($id);
        return response()->json($config);
    }

    /**
     * Update a price configuration
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'monto_base' => 'required|numeric|min:0',
            'moneda' => 'required|in:USD,ARS',
        ]);

        $config = PriceConfiguration::findOrFail($id);

        // Solo permitir editar el monto base y la moneda
        // Las opciones de monto_editable y cuotas_editables son fijas por tipo
        $config->update([
            'monto_base' => $request->monto_base,
            'moneda' => $request->moneda,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Configuración actualizada correctamente',
        ]);
    }

    /**
     * Get label for tipo_contrato
     */
    private function getTipoContratoLabel(string $tipo): string
    {
        return match($tipo) {
            'low_ticket' => 'Low Ticket',
            'high_ticket' => 'High Ticket',
            'beca' => 'Beca',
            'formacion_anexo' => 'Acuerdo de Formación y Anexo',
            default => $tipo,
        };
    }

    /**
     * API endpoint for getting prices by contract type
     * Used by sales form
     */
    public function getPriceForType(Request $request)
    {
        $tipoContrato = $request->get('tipo_contrato');

        if (!$tipoContrato) {
            return response()->json(['error' => 'Tipo de contrato requerido'], 400);
        }

        $configType = PriceConfiguration::mapSaleTypeToConfigType($tipoContrato);
        $config = PriceConfiguration::getActiveForType($configType);

        if (!$config) {
            return response()->json([
                'monto_base' => 0,
                'moneda' => 'USD',
                'monto_editable' => true,
                'cuotas_editables' => false,
            ]);
        }

        return response()->json([
            'monto_base' => (float) $config->monto_base,
            'moneda' => $config->moneda,
            'monto_editable' => $config->monto_editable,
            'cuotas_editables' => $config->cuotas_editables,
        ]);
    }
}
