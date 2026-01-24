<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\ContractTemplate;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ContractApprovalController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            // Construir la consulta base una sola vez
            $query = Sale::with(['lead', 'contractTemplate', 'user'])
                ->whereNotNull('contract_template_id')
                ->where('contract_approved', false)
                ->whereNotNull('contract_data')
                ->orderByDesc('id');

            return DataTables::of($query)
                ->addColumn('action', function ($sale) {
                    $buttons = '<div class="d-flex justify-content-start gap-1">';
                    $buttons .= '<button class="btn btn-sm btn-primary edit-contract-btn"
                                    data-sale-id="' . $sale->id . '"
                                    title="Editar y Aprobar">
                                <i class="bi bi-pencil-check"></i> Revisar
                            </button>';
                    $buttons .= '</div>';
                    return $buttons;
                })
                ->addColumn('lead_name', function ($sale) {
                    return $sale->lead ? $sale->lead->nombre : 'N/A';
                })
                ->addColumn('client_name', function ($sale) {
                    return $sale->nombre_cliente . ' ' . $sale->apellido_cliente;
                })
                ->addColumn('contract_name', function ($sale) {
                    return $sale->contractTemplate ? $sale->contractTemplate->name : 'N/A';
                })
                ->addColumn('closer_name', function ($sale) {
                    return $sale->user ? $sale->user->name : 'N/A';
                })
                ->addColumn('created_at_formatted', function ($sale) {
                    return $sale->created_at->format('d/m/Y H:i');
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('contracts.approval.index');
    }

    public function edit($saleId)
    {
        $sale = Sale::with(['contractTemplate', 'lead'])->findOrFail($saleId);

        if ($sale->contract_approved) {
            return redirect()->route('contracts.approval.index')
                ->with('error', 'Este contrato ya ha sido aprobado.');
        }

        return view('contracts.approval.edit', compact('sale'));
    }

    public function update(Request $request, $saleId)
    {
        $sale = Sale::with('contractTemplate')->findOrFail($saleId);

        if ($sale->contract_approved) {
            return back()->with('error', 'Este contrato ya ha sido aprobado.');
        }

        // Validar todos los campos dinámicos
        $rules = [];
        foreach ($sale->contractTemplate->dynamic_fields as $field) {
            $rules[$field] = 'required|string';
        }

        $validated = $request->validate($rules);

        // Preparar datos para actualizar
        $updateData = ['contract_data' => $validated];

        // Agregar fecha de firma si se proporcionó
        if ($request->filled('contract_signed_date')) {
            $updateData['contract_signed_date'] = $request->contract_signed_date;
        }

        // Actualizar datos del contrato
        $sale->update($updateData);

        return back()->with('success', 'Contrato actualizado exitosamente.');
    }

    public function approve($saleId)
    {
        $sale = Sale::with(['lead', 'contractTemplate'])->findOrFail($saleId);

        if ($sale->contract_approved) {
            return back()->with('error', 'Este contrato ya ha sido aprobado.');
        }

        $sale->update(['contract_approved' => true]);

        // Crear log de contrato aprobado
        \App\Models\Log::create([
            'id_tabla' => $sale->lead->id,
            'tabla' => 'leads',
            'detalle' => 'Contrato aprobado por CSM. Contrato: ' . ($sale->contractTemplate->name ?? 'N/A'),
            'archivo_soporte' => route('contracts.download', $sale->id),
            'tipo_log' => 'contrato',
            'valor_viejo' => 'pendiente_aprobacion',
            'valor_nuevo' => 'aprobado',
            'id_usuario' => \Illuminate\Support\Facades\Auth::id(),
            'estado' => 1,
        ]);

        return redirect()->route('contracts.approval.index')
            ->with('success', 'Contrato aprobado exitosamente.');
    }

    public function previewAjax(Request $request, $saleId)
    {
        $sale = Sale::with('contractTemplate')->findOrFail($saleId);

        if (!$sale->contractTemplate) {
            return response('Contrato no encontrado', 404);
        }

        // Combinar datos existentes con datos del formulario
        $formData = $request->except(['_token', '_method']);
        $contractData = array_merge($sale->contract_data ?? [], $formData);

        // Crear objeto temporal
        $tempSale = clone $sale;
        $tempSale->contract_data = $contractData;

        return $this->generateContractHtml($tempSale, $sale->contractTemplate);
    }

    private function generateContractHtml($sale, $contractTemplate)
    {
        $html = $contractTemplate->html_content;
        $contractData = $sale->contract_data ?? [];

        // Asegurar que siempre tenga la fecha actual en la vista previa
        $today = now();
        $contractData['dia'] = $contractData['dia'] ?? $today->day;
        $contractData['mes'] = $contractData['mes'] ?? $this->getMonthName($today->month);
        $contractData['anio'] = $contractData['anio'] ?? $today->year;

        // Generar tabla de cuotas si existe el placeholder
        if (strpos($html, '{tabla_cuotas}') !== false) {
            $tablaCuotas = $this->generateTablaCuotas($sale);
            $html = str_replace('{tabla_cuotas}', $tablaCuotas, $html);
        }

        // Usar solo los datos del contrato tal como estan
        foreach ($contractData as $key => $value) {
            if ($key === 'imagen_firma' && $value) {
                // Para imagenes de firma, insertar como img tag
                $html = str_replace('{' . $key . '}', '<img src="' . $value . '" class="signature-image">', $html);
            } else {
                $html = str_replace('{' . $key . '}', $value ?? '', $html);
            }
        }

        return $html;
    }

    /**
     * Generar HTML de la tabla de cuotas para el contrato
     */
    private function generateTablaCuotas($sale): string
    {
        // Cargar las cuotas de la venta
        $installments = $sale->installments ?? collect();
        $moneda = $sale->moneda ?? 'USD';
        $montoFinal = $sale->monto_final ?? $sale->monto_total ?? 0;

        // Si no hay cuotas, mostrar pago unico
        if ($installments->isEmpty()) {
            return sprintf(
                '<p style="margin: 10px 0;"><strong>Pago unico:</strong> $%s %s al momento de la firma del contrato.</p>',
                number_format($montoFinal, 2),
                $moneda
            );
        }

        // Si es pago completo (1 cuota)
        if ($installments->count() === 1) {
            $cuota = $installments->first();
            $fechaFormateada = $cuota->fecha_vencimiento
                ? $cuota->fecha_vencimiento->format('d') . ' de ' . $this->getMonthName($cuota->fecha_vencimiento->month) . ' de ' . $cuota->fecha_vencimiento->year
                : 'al momento de la firma';

            return sprintf(
                '<p style="margin: 10px 0;"><strong>Pago unico:</strong> $%s %s - Vencimiento: %s</p>',
                number_format($cuota->monto, 2),
                $moneda,
                $fechaFormateada
            );
        }

        // Multiples cuotas - generar tabla
        $tableHtml = '
        <table style="width: 100%; border-collapse: collapse; margin: 15px 0; font-size: 14px;">
            <thead>
                <tr style="background-color: #f5f5f5;">
                    <th style="border: 1px solid #ddd; padding: 10px; text-align: center;">Cuota</th>
                    <th style="border: 1px solid #ddd; padding: 10px; text-align: right;">Monto</th>
                    <th style="border: 1px solid #ddd; padding: 10px; text-align: center;">Fecha de Vencimiento</th>
                </tr>
            </thead>
            <tbody>';

        foreach ($installments as $installment) {
            $fechaFormateada = $installment->fecha_vencimiento
                ? $installment->fecha_vencimiento->format('d') . ' de ' . $this->getMonthName($installment->fecha_vencimiento->month) . ' de ' . $installment->fecha_vencimiento->year
                : '-';

            $tableHtml .= sprintf(
                '<tr>
                    <td style="border: 1px solid #ddd; padding: 10px; text-align: center;">%d</td>
                    <td style="border: 1px solid #ddd; padding: 10px; text-align: right;">$%s %s</td>
                    <td style="border: 1px solid #ddd; padding: 10px; text-align: center;">%s</td>
                </tr>',
                $installment->numero_cuota,
                number_format($installment->monto, 2),
                $moneda,
                $fechaFormateada
            );
        }

        $tableHtml .= sprintf(
            '<tr style="background-color: #f5f5f5; font-weight: bold;">
                <td style="border: 1px solid #ddd; padding: 10px; text-align: right;" colspan="2">Total:</td>
                <td style="border: 1px solid #ddd; padding: 10px; text-align: right;">$%s %s</td>
            </tr>',
            number_format($montoFinal, 2),
            $moneda
        );

        $tableHtml .= '</tbody></table>';

        return $tableHtml;
    }

    private function getMonthName($monthNumber)
    {
        $months = [
            1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril',
            5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto',
            9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre'
        ];

        return $months[$monthNumber] ?? 'enero';
    }
}
