<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_id',
        'user_id',
        'llamada_id',
        'nombre_cliente',
        'apellido_cliente',
        'email_cliente',
        'telefono_cliente',
        'identificacion_personal',
        'metodo_pago',
        'comprobante_pago_path',
        'tipo_acuerdo',
        'tipo_contrato',
        'fuente',
        'fecha_cierre',
        'comentarios',
        'contract_template_id',
        'contract_approved',
        'contract_data',
        'contract_token',
        'contract_signed_date',
        'upsell',
        'upsell_comprobante_path',
        'upsell_comentarios',
        'upsell_fecha_pendiente',
        'upsell_fecha_aprobado',
        'upsell_user_pendiente',
        'upsell_user_aprobado',
        // Campos financieros
        'monto_total',
        'moneda',
        'numero_cuotas',
        'dias_entre_cuotas',
        'descuento_porcentaje',
        'descuento_monto',
        'monto_final',
        'estado_pago',
    ];

    protected $casts = [
        'contract_data' => 'array',
        'contract_approved' => 'boolean',
        'contract_signed_date' => 'datetime',
        'fecha_cierre' => 'date',
        'upsell_fecha_pendiente' => 'datetime',
        'upsell_fecha_aprobado' => 'datetime',
        // Campos financieros
        'monto_total' => 'decimal:2',
        'monto_final' => 'decimal:2',
        'descuento_porcentaje' => 'decimal:2',
        'descuento_monto' => 'decimal:2',
        'numero_cuotas' => 'integer',
        'dias_entre_cuotas' => 'integer',
    ];

    // Relaciones

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function llamada()
    {
        return $this->belongsTo(Llamada::class);
    }

    public function contractTemplate()
    {
        return $this->belongsTo(ContractTemplate::class);
    }

    public function upsellUserPendiente()
    {
        return $this->belongsTo(User::class, 'upsell_user_pendiente');
    }

    public function upsellUserAprobado()
    {
        return $this->belongsTo(User::class, 'upsell_user_aprobado');
    }

    public function paymentProofs()
    {
        return $this->hasMany(SalePaymentProof::class);
    }

    /**
     * Relación con cuotas
     */
    public function installments()
    {
        return $this->hasMany(SaleInstallment::class)->orderBy('numero_cuota');
    }

    /**
     * Obtener cuotas pendientes
     */
    public function pendingInstallments()
    {
        return $this->installments()->whereIn('estado', ['pendiente', 'vencida']);
    }

    /**
     * Obtener cuotas pagadas
     */
    public function paidInstallments()
    {
        return $this->installments()->where('estado', 'pagada');
    }

    /**
     * Calcular monto total pagado
     */
    public function getTotalPagado(): float
    {
        return (float) $this->installments()->sum('monto_pagado');
    }

    /**
     * Calcular monto pendiente
     */
    public function getMontoPendiente(): float
    {
        $montoFinal = $this->monto_final ?? $this->monto_total ?? 0;
        return max(0, $montoFinal - $this->getTotalPagado());
    }

    /**
     * Actualizar estado de pago basado en las cuotas
     */
    public function updatePaymentStatus(): void
    {
        $totalCuotas = $this->installments()->count();
        $cuotasPagadas = $this->installments()->where('estado', 'pagada')->count();
        $cuotasVencidas = $this->installments()->where('estado', 'vencida')->count();

        if ($cuotasPagadas === $totalCuotas) {
            $this->update(['estado_pago' => 'pagado']);
        } elseif ($cuotasVencidas > 0) {
            $this->update(['estado_pago' => 'vencido']);
        } elseif ($cuotasPagadas > 0) {
            $this->update(['estado_pago' => 'parcial']);
        } else {
            $this->update(['estado_pago' => 'pendiente']);
        }
    }

    /**
     * Obtener etiqueta de estado de pago con badge class
     */
    public function getEstadoPagoBadgeClass(): string
    {
        return match($this->estado_pago) {
            'pendiente' => 'warning',
            'parcial' => 'info',
            'pagado' => 'success',
            'vencido' => 'danger',
            default => 'secondary',
        };
    }

    /**
     * Obtener etiqueta de estado de pago
     */
    public function getEstadoPagoLabel(): string
    {
        return match($this->estado_pago) {
            'pendiente' => 'Pendiente',
            'parcial' => 'Pago Parcial',
            'pagado' => 'Pagado',
            'vencido' => 'Vencido',
            default => 'Desconocido',
        };
    }

    /**
     * Crear cuotas para esta venta
     */
    public function createInstallments(array $cuotas): void
    {
        foreach ($cuotas as $cuota) {
            $this->installments()->create([
                'numero_cuota' => $cuota['numero'],
                'monto' => $cuota['monto'],
                'fecha_vencimiento' => $cuota['fecha_vencimiento'],
                'estado' => 'pendiente',
            ]);
        }
    }

    /**
     * Obtener resumen de cuotas para mostrar
     */
    public function getInstallmentsSummary(): array
    {
        $installments = $this->installments;
        $summary = [];

        foreach ($installments as $installment) {
            $summary[] = [
                'numero' => $installment->numero_cuota,
                'monto' => $installment->monto,
                'fecha_vencimiento' => $installment->fecha_vencimiento->format('d/m/Y'),
                'estado' => $installment->estado,
                'estado_label' => $installment->getEstadoLabel(),
                'estado_badge' => $installment->getEstadoBadgeClass(),
                'fecha_pago' => $installment->fecha_pago ? $installment->fecha_pago->format('d/m/Y') : null,
                'monto_pagado' => $installment->monto_pagado,
            ];
        }

        return $summary;
    }
}
