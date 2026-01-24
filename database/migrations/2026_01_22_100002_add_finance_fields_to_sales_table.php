<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            // Campos de monto
            $table->decimal('monto_total', 10, 2)->nullable()->after('tipo_contrato')->comment('Monto total acordado de la venta');
            $table->enum('moneda', ['USD', 'ARS'])->default('USD')->after('monto_total');

            // Campos de cuotas
            $table->tinyInteger('numero_cuotas')->default(1)->after('moneda')->comment('1=pago completo, 2=2 cuotas, 3=3 cuotas');
            $table->integer('dias_entre_cuotas')->default(30)->after('numero_cuotas');

            // Campos de descuento
            $table->decimal('descuento_porcentaje', 5, 2)->nullable()->after('dias_entre_cuotas');
            $table->decimal('descuento_monto', 10, 2)->nullable()->after('descuento_porcentaje');
            $table->decimal('monto_final', 10, 2)->nullable()->after('descuento_monto')->comment('Monto despues de descuento');

            // Estado de pago general
            $table->enum('estado_pago', ['pendiente', 'parcial', 'pagado', 'vencido'])->default('pendiente')->after('monto_final');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn([
                'monto_total',
                'moneda',
                'numero_cuotas',
                'dias_entre_cuotas',
                'descuento_porcentaje',
                'descuento_monto',
                'monto_final',
                'estado_pago'
            ]);
        });
    }
};
