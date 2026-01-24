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
        // Primero, actualizar las ventas que usen los templates "2 Cuotas" y "3 Cuotas"
        // para que usen "High Ticket" en su lugar (ya que tienen el mismo contenido)

        // Template "2 Cuotas" (ID 21) -> High Ticket (ID 20)
        DB::table('sales')
            ->where('contract_template_id', 21)
            ->update(['contract_template_id' => 20]);

        // Template "3 Cuotas" (ID 22) -> High Ticket (ID 20)
        DB::table('sales')
            ->where('contract_template_id', 22)
            ->update(['contract_template_id' => 20]);

        // Ahora eliminar los templates redundantes
        DB::table('contract_templates')
            ->whereIn('id', [21, 22])
            ->delete();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // En caso de rollback, recrear los templates (contenido similar a High Ticket)
        $highTicketContent = DB::table('contract_templates')
            ->where('id', 20)
            ->value('html_content');

        $highTicketFields = DB::table('contract_templates')
            ->where('id', 20)
            ->value('dynamic_fields');

        DB::table('contract_templates')->insert([
            [
                'id' => 21,
                'name' => '2 Cuotas',
                'html_content' => $highTicketContent,
                'dynamic_fields' => $highTicketFields,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 22,
                'name' => '3 Cuotas',
                'html_content' => $highTicketContent,
                'dynamic_fields' => $highTicketFields,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
};
