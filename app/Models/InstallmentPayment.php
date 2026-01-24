<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstallmentPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'installment_id',
        'monto',
        'fecha_pago',
        'metodo_pago',
        'comprobante_path',
        'referencia',
        'notas',
        'registrado_por',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'fecha_pago' => 'date',
    ];

    /**
     * Relación con la cuota
     */
    public function installment()
    {
        return $this->belongsTo(SaleInstallment::class, 'installment_id');
    }

    /**
     * Relación con el usuario que registró el pago
     */
    public function registeredBy()
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }

    /**
     * Obtener el nombre del método de pago formateado
     */
    public function getMetodoPagoLabel(): string
    {
        $metodos = [
            'D LOCAL GO' => 'D Local Go',
            'WHOP' => 'Whop',
            'TRANSFERENCIA USD' => 'Transferencia USD',
            'TRANSFERENCIA PESOS' => 'Transferencia Pesos',
            'CRIPTO' => 'Criptomonedas',
            'EFECTIVO' => 'Efectivo',
            'WIRE' => 'Wire Transfer',
            'PAYPAL' => 'PayPal',
        ];

        return $metodos[$this->metodo_pago] ?? $this->metodo_pago;
    }

    /**
     * Obtener URL del comprobante
     */
    public function getComprobanteUrl(): ?string
    {
        if ($this->comprobante_path) {
            return asset($this->comprobante_path);
        }
        return null;
    }
}
