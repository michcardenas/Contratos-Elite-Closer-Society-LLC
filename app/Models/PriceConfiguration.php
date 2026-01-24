<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PriceConfiguration extends Model
{
    use HasFactory;

    protected $fillable = [
        'tipo_contrato',
        'monto_base',
        'moneda',
        'activo',
        'monto_editable',
        'cuotas_editables',
    ];

    protected $casts = [
        'monto_base' => 'decimal:2',
        'activo' => 'boolean',
        'monto_editable' => 'boolean',
        'cuotas_editables' => 'boolean',
    ];

    /**
     * Obtener la configuración activa para un tipo de contrato
     */
    public static function getActiveForType(string $tipoContrato): ?self
    {
        return self::where('tipo_contrato', $tipoContrato)
            ->where('activo', true)
            ->first();
    }

    /**
     * Obtener todas las configuraciones activas
     */
    public static function getAllActive()
    {
        return self::where('activo', true)->get();
    }

    /**
     * Obtener el monto para un tipo de contrato
     */
    public static function getMontoForType(string $tipoContrato): ?float
    {
        $config = self::getActiveForType($tipoContrato);
        return $config ? (float) $config->monto_base : null;
    }

    /**
     * Verificar si el monto es editable para un tipo de contrato
     */
    public static function isMontoEditable(string $tipoContrato): bool
    {
        $config = self::getActiveForType($tipoContrato);
        return $config ? $config->monto_editable : false;
    }

    /**
     * Verificar si las cuotas son editables para un tipo de contrato
     */
    public static function areCuotasEditables(string $tipoContrato): bool
    {
        $config = self::getActiveForType($tipoContrato);
        return $config ? $config->cuotas_editables : false;
    }

    /**
     * Mapear tipo_contrato de sales a tipo de configuración
     */
    public static function mapSaleTypeToConfigType(string $saleType): string
    {
        return match($saleType) {
            'low ticket' => 'low_ticket',
            'high ticket' => 'high_ticket',
            'beca' => 'beca',
            default => 'formacion_anexo',
        };
    }
}
