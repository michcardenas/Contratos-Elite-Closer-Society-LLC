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
        Schema::create('price_configurations', function (Blueprint $table) {
            $table->id();
            $table->enum('tipo_contrato', ['low_ticket', 'high_ticket', 'beca', 'formacion_anexo']);
            $table->decimal('monto_base', 10, 2);
            $table->enum('moneda', ['USD', 'ARS'])->default('USD');
            $table->boolean('activo')->default(true);
            $table->boolean('monto_editable')->default(false)->comment('Si true, el closer puede modificar el monto');
            $table->boolean('cuotas_editables')->default(false)->comment('Si true, el closer puede modificar montos de cuotas');
            $table->timestamps();

            $table->unique(['tipo_contrato', 'activo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('price_configurations');
    }
};
