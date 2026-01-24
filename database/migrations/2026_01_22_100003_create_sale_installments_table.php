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
        Schema::create('sale_installments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('sales')->onDelete('cascade');
            $table->tinyInteger('numero_cuota')->comment('1, 2 o 3');
            $table->decimal('monto', 10, 2);
            $table->date('fecha_vencimiento');
            $table->enum('estado', ['pendiente', 'pagada', 'vencida', 'parcial'])->default('pendiente');
            $table->date('fecha_pago')->nullable();
            $table->decimal('monto_pagado', 10, 2)->nullable();
            $table->string('comprobante_path')->nullable();
            $table->string('metodo_pago')->nullable();
            $table->string('referencia_pago')->nullable();
            $table->text('notas')->nullable();
            $table->boolean('recordatorio_enviado')->default(false);
            $table->timestamp('recordatorio_enviado_at')->nullable();
            $table->timestamps();

            // Índices
            $table->index(['sale_id', 'numero_cuota']);
            $table->index('estado');
            $table->index('fecha_vencimiento');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_installments');
    }
};
