<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Hacer domicilio nullable usando SQL directo (sin doctrine/dbal)
        DB::statement('ALTER TABLE sales MODIFY domicilio VARCHAR(255) NULL');

        Schema::table('sales', function (Blueprint $table) {
            // Agregar nuevos campos
            $table->string('fuente')->nullable()->after('tipo_contrato');
            $table->date('fecha_cierre')->nullable()->after('fuente');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['fuente', 'fecha_cierre']);
        });

        DB::statement('ALTER TABLE sales MODIFY domicilio VARCHAR(255) NOT NULL');
    }
};
