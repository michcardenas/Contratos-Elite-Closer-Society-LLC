<?php
// app/Http/Controllers/SalesController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\Lead;
use App\Models\ContractTemplate;
use App\Models\SalePaymentProof;
use App\Models\SaleInstallment;
use App\Mail\ContractSigningMail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;

class SalesController extends Controller
{
    public function form(Lead $lead)
    {
        $sale = new Sale(); // Para un nuevo formulario
        $contractTemplates = ContractTemplate::all();

        return view('sales.form', compact('lead', 'sale', 'contractTemplates'));
    }

    public function store(Request $request)
    {
        // Mapeo de tipo_contrato a contract_template_id (debe coincidir con el frontend)
        $templateMapping = [
            'low ticket' => 18,
            'high ticket' => 20,
            'beca' => 23,
            'formacion_anexo' => 1,
        ];

        $validated = $request->validate([
            'nombre_cliente' => 'required|string|max:255',
            'apellido_cliente' => 'required|string|max:255',
            'email_cliente' => 'required|email|max:255',
            'telefono_cliente' => 'required|string|max:50',
            'identificacion_personal' => 'nullable|string|max:100',
            'metodo_pago' => 'required|in:D LOCAL GO,WHOP,TRANSFERENCIA USD,TRANSFERENCIA PESOS,CRIPTO,EFECTIVO,WIRE,PAYPAL',
            'comprobante_pago' => 'required|array|min:1',
            'comprobante_pago.*' => 'file|mimes:jpg,jpeg,png,pdf|max:2048',
            'tipo_acuerdo' => 'required|in:Pago completo,Pago en 2 cuotas,Pago en 3 cuotas',
            'tipo_contrato' => 'required|in:low ticket,high ticket,beca,formacion_anexo',
            'fuente' => 'nullable|in:Instagram,Anuncios,YouTube,Referidos,CSM,Otros',
            'fecha_cierre' => 'nullable|date',
            'comentarios' => 'nullable|string',
            'contract_template_id' => 'required|exists:contract_templates,id',
            'forma_de_pago' => 'required|string',
            // Campos financieros
            'monto_total' => 'required|numeric|min:0',
            'moneda' => 'required|in:USD,ARS',
            'descuento_porcentaje' => 'nullable|numeric|min:0|max:100',
            'monto_final' => 'nullable|numeric|min:0',
            'numero_cuotas' => 'required|integer|min:1|max:3',
            'dias_entre_cuotas' => 'nullable|integer|min:1|max:90',
            // Campos de cuotas
            'cuota_monto_1' => 'nullable|numeric|min:0',
            'cuota_fecha_1' => 'nullable|date',
            'cuota_monto_2' => 'nullable|numeric|min:0',
            'cuota_fecha_2' => 'nullable|date',
            'cuota_monto_3' => 'nullable|numeric|min:0',
            'cuota_fecha_3' => 'nullable|date',
        ]);

        // Validar que el contract_template_id corresponda al tipo_contrato
        $expectedTemplateId = $templateMapping[$validated['tipo_contrato']] ?? null;
        if ($expectedTemplateId && (int) $validated['contract_template_id'] !== $expectedTemplateId) {
            // Corregir automaticamente el template_id para evitar inconsistencias
            $validated['contract_template_id'] = $expectedTemplateId;
        }

        // Guardar comprobantes directamente en public (multiples archivos)
        $files = $request->file('comprobante_pago');
        $comprobantePaths = [];
        foreach ($files as $file) {
            $filename = time() . '_' . uniqid() . '_comprobante_' . $file->getClientOriginalName();
            $file->move(public_path('comprobantes'), $filename);
            $comprobantePaths[] = [
                'path' => 'comprobantes/' . $filename,
                'original_name' => $file->getClientOriginalName(),
            ];
        }
        // Usar el primer archivo como comprobante principal (compatibilidad)
        $path = $comprobantePaths[0]['path'];
        $lead = Lead::findOrFail($request->lead_id);

        // Generar token unico para el contrato
        $contractToken = Str::random(64);

        // Calcular monto final si no viene calculado
        $montoTotal = (float) $validated['monto_total'];
        $descuentoPorcentaje = (float) ($validated['descuento_porcentaje'] ?? 0);
        $descuentoMonto = $montoTotal * ($descuentoPorcentaje / 100);
        $montoFinal = $montoTotal - $descuentoMonto;

        // Preparar datos iniciales del contrato con mapeo correcto
        $today = now();
        $contractData = [
            'forma_de_pago' => $validated['forma_de_pago'],
            'nombre' => $validated['nombre_cliente'],
            'dni' => $validated['identificacion_personal'],
            'dia' => $today->day,
            'mes' => $this->getMonthName($today->month),
            'anio' => $today->year,
        ];

        $sale = Sale::create([
            'lead_id' => $lead->id,
            'user_id' => Auth::id(),
            'nombre_cliente' => $validated['nombre_cliente'],
            'apellido_cliente' => $validated['apellido_cliente'],
            'email_cliente' => $validated['email_cliente'],
            'telefono_cliente' => $validated['telefono_cliente'],
            'identificacion_personal' => $validated['identificacion_personal'],
            'metodo_pago' => $validated['metodo_pago'],
            'comprobante_pago_path' => $path,
            'tipo_acuerdo' => $validated['tipo_acuerdo'],
            'tipo_contrato' => $validated['tipo_contrato'],
            'fuente' => $validated['fuente'] ?? null,
            'fecha_cierre' => $validated['fecha_cierre'] ?? null,
            'comentarios' => $validated['comentarios'],
            'contract_template_id' => $validated['contract_template_id'],
            'contract_data' => $contractData,
            'contract_token' => $contractToken,
            // Campos financieros
            'monto_total' => $montoTotal,
            'moneda' => $validated['moneda'],
            'numero_cuotas' => $validated['numero_cuotas'],
            'dias_entre_cuotas' => $validated['dias_entre_cuotas'] ?? 30,
            'descuento_porcentaje' => $descuentoPorcentaje > 0 ? $descuentoPorcentaje : null,
            'descuento_monto' => $descuentoMonto > 0 ? $descuentoMonto : null,
            'monto_final' => $montoFinal,
            'estado_pago' => 'pendiente',
        ]);

        // Crear cuotas (installments)
        $this->createInstallments($sale, $validated);

        // Guardar todos los comprobantes en la tabla de comprobantes multiples
        foreach ($comprobantePaths as $comprobanteData) {
            SalePaymentProof::create([
                'sale_id' => $sale->id,
                'file_path' => $comprobanteData['path'],
                'original_name' => $comprobanteData['original_name'],
            ]);
        }

        // Crear log de venta registrada
        $logDetalle = sprintf(
            'Venta registrada exitosamente. Tipo: %s, Metodo de pago: %s, Monto: $%s %s en %d cuota(s)',
            $validated['tipo_contrato'],
            $validated['metodo_pago'],
            number_format($montoFinal, 2),
            $validated['moneda'],
            $validated['numero_cuotas']
        );

        \App\Models\Log::create([
            'id_tabla' => $lead->id,
            'tabla' => 'leads',
            'detalle' => $logDetalle,
            'archivo_soporte' => asset($path),
            'tipo_log' => 'venta',
            'valor_viejo' => null,
            'valor_nuevo' => 'venta_registrada',
            'id_usuario' => Auth::id(),
            'estado' => 1,
        ]);

        // Cargar la relacion contractTemplate para el email
        $sale->load('contractTemplate');

        // Enviar email de contrato automaticamente
        try {
            Mail::to($sale->email_cliente)->send(new ContractSigningMail($sale));
        } catch (\Exception $e) {
            // Log el error pero no interrumpir el flujo
            \Log::error('Error enviando email de contrato: ' . $e->getMessage());
        }

        return redirect()->route('leads')->with('success', 'Venta registrada correctamente y email de contrato enviado.');
    }

    /**
     * Crear cuotas para la venta
     */
    private function createInstallments(Sale $sale, array $validated): void
    {
        $numeroCuotas = (int) $validated['numero_cuotas'];

        for ($i = 1; $i <= $numeroCuotas; $i++) {
            $montoKey = "cuota_monto_{$i}";
            $fechaKey = "cuota_fecha_{$i}";

            // Solo crear si hay monto definido
            if (isset($validated[$montoKey]) && $validated[$montoKey] > 0) {
                $fechaVencimiento = isset($validated[$fechaKey])
                    ? Carbon::parse($validated[$fechaKey])
                    : Carbon::today()->addDays(($i - 1) * ($validated['dias_entre_cuotas'] ?? 30));

                SaleInstallment::create([
                    'sale_id' => $sale->id,
                    'numero_cuota' => $i,
                    'monto' => (float) $validated[$montoKey],
                    'fecha_vencimiento' => $fechaVencimiento,
                    'estado' => 'pendiente',
                    'monto_pagado' => 0,
                ]);
            }
        }
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
