<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\ContractTemplate;
use App\Models\Form;
use App\Models\FormSubmission;
use App\Models\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ContractSigningController extends Controller
{
    public function show($token)
    {
        $sale = Sale::where('contract_token', $token)->firstOrFail();
        $contractTemplate = $sale->contractTemplate;

        if (!$contractTemplate) {
            abort(404, 'Contrato no encontrado');
        }

        // Verificar si el contrato ya fue firmado
        if ($sale->contract_signed_date) {
            return redirect()->route('contract.preview', $token)->with('info', 'Este contrato ya ha sido firmado.');
        }

        // Obtener campos que necesitan input (excluyendo solo fecha y forma_de_pago)
        $excludedFields = ['dia', 'mes', 'anio', 'forma_de_pago'];
        $missingFields = array_filter($contractTemplate->dynamic_fields, function($field) use ($excludedFields) {
            return !in_array($field, $excludedFields);
        });

        // Obtener formulario Elite Closer Society Onboarding
        $eliteForm = Form::where('slug', 'elite-closer-society-onboarding')
            ->where('status', 'active')
            ->with('fields')
            ->first();

        return view('contracts.sign', compact('sale', 'contractTemplate', 'missingFields', 'eliteForm'));
    }

    public function update(Request $request, $token)
    {
        $sale = Sale::where('contract_token', $token)->firstOrFail();
        $contractTemplate = $sale->contractTemplate;

        // Verificar si el contrato ya fue firmado
        if ($sale->contract_signed_date) {
            return redirect()->route('contract.preview', $token)->with('error', 'Este contrato ya ha sido firmado anteriormente.');
        }

        // ============ PARTE 1: VALIDAR Y GUARDAR CONTRATO (NO TOCAR) ============

        // Validar campos dinámicos del contrato
        $contractRules = [];
        $excludedFields = ['dia', 'mes', 'anio', 'forma_de_pago'];
        foreach ($contractTemplate->dynamic_fields as $field) {
            if (!in_array($field, $excludedFields)) {
                $contractRules[$field] = 'required|string';
            }
        }

        // Manejo especial para imagen de firma
        if (in_array('imagen_firma', $contractTemplate->dynamic_fields)) {
            $contractRules['imagen_firma'] = 'required|string'; // Base64 de la firma
        }

        // Validar contrato
        $validatedContract = $request->validate($contractRules);

        // Combinar con datos existentes
        $contractData = array_merge($sale->contract_data ?? [], $validatedContract);

        // Guardar imagen de firma si existe
        if (isset($validatedContract['imagen_firma'])) {
            $signatureData = $validatedContract['imagen_firma'];

            if (preg_match('/^data:image\/(\w+);base64,/', $signatureData, $type)) {
                $data = substr($signatureData, strpos($signatureData, ',') + 1);
                $type = strtolower($type[1]);

                if (!in_array($type, ['jpg', 'jpeg', 'png'])) {
                    return back()->withErrors(['imagen_firma' => 'Formato de imagen no válido']);
                }

                $data = base64_decode($data);
                $fileName = 'signatures/' . $sale->id . '_' . time() . '.' . $type;

                // Crear directorio si no existe
                $signatureDir = public_path('signatures');
                if (!file_exists($signatureDir)) {
                    mkdir($signatureDir, 0755, true);
                }

                // Guardar directamente en public/signatures
                file_put_contents(public_path($fileName), $data);
                $contractData['imagen_firma'] = asset($fileName);
            }
        }

        // Guardar contrato
        $sale->update([
            'contract_data' => $contractData,
            'contract_signed_date' => now()
        ]);

        // ============ PARTE 2: VALIDAR Y GUARDAR ELITE CLOSER SOCIETY ONBOARDING ============

        // Obtener formulario Elite Closer Society Onboarding
        $eliteForm = Form::where('slug', 'elite-closer-society-onboarding')
            ->where('status', 'active')
            ->with('fields')
            ->first();

        if ($eliteForm) {
            // Validación dinámica de campos Elite
            $eliteRules = [];
            $eliteFieldNames = [];

            foreach ($eliteForm->fields as $field) {
                $eliteFieldNames[] = $field->field_name;
                $fieldRules = [];

                if ($field->is_required) {
                    $fieldRules[] = 'required';
                } else {
                    $fieldRules[] = 'nullable';
                }

                // Agregar validaciones según tipo de campo
                switch ($field->field_type) {
                    case 'email':
                        $fieldRules[] = 'email';
                        break;
                    case 'number':
                    case 'scale':
                        $fieldRules[] = 'numeric';
                        break;
                    case 'date':
                        $fieldRules[] = 'date';
                        break;
                }

                $eliteRules[$field->field_name] = implode('|', $fieldRules);
            }

            // Validar solo campos Elite
            $validatedElite = $request->validate($eliteRules);

            // Crear submission del formulario Elite
            $submission = FormSubmission::create([
                'form_id' => $eliteForm->id,
                'lead_id' => $sale->lead_id, // Relacionar con el lead de la venta
                'user_id' => null, // El lead no está autenticado
                'submission_data' => $validatedElite,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'status' => FormSubmission::STATUS_APPROVED, // Auto-aprobado
                'submitted_at' => now(),
            ]);

            // Crear log de la submission
            Log::create([
                'id_tabla' => $submission->id,
                'tabla' => 'form_submissions',
                'tipo_log' => 'elite_onboarding_submitted',
                'detalle' => 'Formulario Elite Closer Society Onboarding completado durante firma de contrato',
                'valor_nuevo' => 'approved',
                'id_usuario' => null, // Sin usuario autenticado
            ]);
        }

        return redirect()->route('contract.preview', $token)->with('success', 'Contrato completado exitosamente');
    }

    public function preview($token)
    {
        $sale = Sale::where('contract_token', $token)->firstOrFail();
        $contractTemplate = $sale->contractTemplate;

        if (!$contractTemplate) {
            abort(404, 'Contrato no encontrado');
        }

        // Generar HTML del contrato con datos llenados
        $contractHtml = $this->generateContractHtml($sale, $contractTemplate);

        return view('contracts.preview', compact('sale', 'contractTemplate', 'contractHtml'));
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

    public function previewAjax(Request $request, $token)
    {
        $sale = Sale::where('contract_token', $token)->firstOrFail();
        $contractTemplate = $sale->contractTemplate;

        if (!$contractTemplate) {
            return response('Contrato no encontrado', 404);
        }

        // Combinar datos existentes con datos del formulario
        $formData = $request->except(['_token', '_method']);
        $contractData = array_merge($sale->contract_data ?? [], $formData);

        // Crear un objeto temporal para generar la vista previa
        $tempSale = clone $sale;
        $tempSale->contract_data = $contractData;

        return $this->generateContractHtml($tempSale, $contractTemplate);
    }
}
