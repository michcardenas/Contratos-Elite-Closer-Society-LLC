<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class SaleInstallment extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'numero_cuota',
        'monto',
        'fecha_vencimiento',
        'estado',
        'fecha_pago',
        'monto_pagado',
        'comprobante_path',
        'metodo_pago',
        'referencia_pago',
        'notas',
        'recordatorio_enviado',
        'recordatorio_enviado_at',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'monto_pagado' => 'decimal:2',
        'fecha_vencimiento' => 'date',
        'fecha_pago' => 'date',
        'recordatorio_enviado' => 'boolean',
        'recordatorio_enviado_at' => 'datetime',
    ];

    /**
     * Relación con Sale
     */
    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * Relación con pagos
     */
    public function payments()
    {
        return $this->hasMany(InstallmentPayment::class, 'installment_id');
    }

    /**
     * Obtener cuotas pendientes
     */
    public static function getPending()
    {
        return self::where('estado', 'pendiente')
            ->orderBy('fecha_vencimiento')
            ->get();
    }

    /**
     * Obtener cuotas vencidas
     */
    public static function getOverdue()
    {
        return self::where('estado', 'vencida')
            ->orWhere(function ($query) {
                $query->where('estado', 'pendiente')
                    ->where('fecha_vencimiento', '<', Carbon::today());
            })
            ->orderBy('fecha_vencimiento')
            ->get();
    }

    /**
     * Obtener cuotas que vencen pronto (en los próximos N días)
     */
    public static function getDueSoon(int $days = 7)
    {
        return self::where('estado', 'pendiente')
            ->whereBetween('fecha_vencimiento', [
                Carbon::today(),
                Carbon::today()->addDays($days)
            ])
            ->orderBy('fecha_vencimiento')
            ->get();
    }

    /**
     * Verificar si la cuota está vencida
     */
    public function isOverdue(): bool
    {
        return $this->estado === 'pendiente' &&
               $this->fecha_vencimiento < Carbon::today();
    }

    /**
     * Calcular días de vencimiento
     */
    public function getDaysOverdue(): int
    {
        if (!$this->isOverdue()) {
            return 0;
        }
        return Carbon::today()->diffInDays($this->fecha_vencimiento);
    }

    /**
     * Calcular monto pendiente
     */
    public function getMontoPendiente(): float
    {
        $pagado = $this->monto_pagado ?? 0;
        return max(0, $this->monto - $pagado);
    }

    /**
     * Marcar como pagada
     */
    public function markAsPaid(float $monto, ?string $comprobante = null, ?string $metodoPago = null, ?string $referencia = null): void
    {
        $this->update([
            'estado' => 'pagada',
            'fecha_pago' => Carbon::today(),
            'monto_pagado' => $monto,
            'comprobante_path' => $comprobante,
            'metodo_pago' => $metodoPago,
            'referencia_pago' => $referencia,
        ]);

        // Actualizar estado de la venta
        $this->sale->updatePaymentStatus();
    }

    /**
     * Marcar como vencida
     */
    public function markAsOverdue(): void
    {
        if ($this->estado === 'pendiente') {
            $this->update(['estado' => 'vencida']);
            $this->sale->updatePaymentStatus();
        }
    }

    /**
     * Obtener etiqueta de estado con badge class
     */
    public function getEstadoBadgeClass(): string
    {
        return match($this->estado) {
            'pendiente' => 'warning',
            'pagada' => 'success',
            'vencida' => 'danger',
            'parcial' => 'info',
            default => 'secondary',
        };
    }

    /**
     * Obtener etiqueta de estado
     */
    public function getEstadoLabel(): string
    {
        return match($this->estado) {
            'pendiente' => 'Pendiente',
            'pagada' => 'Pagada',
            'vencida' => 'Vencida',
            'parcial' => 'Pago Parcial',
            default => 'Desconocido',
        };
    }
}
