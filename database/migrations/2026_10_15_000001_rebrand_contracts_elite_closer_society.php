<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Pedido del cliente (oct-2026) sobre TODAS las plantillas de contrato:
 *  1. La razón social pasa de "UNO X CIENTO LLC" a "Elite Closer Society LLC".
 *  2. Se elimina la cláusula de reembolsos por sesiones de preguntas y respuestas
 *     en directo (5.3 en Acuerdo/FULL, 3.3 en Low Ticket, 4.3 en Beca). Siempre es
 *     la última cláusula de su artículo, así que no hay que renumerar.
 *
 * Antes de tocar nada se respalda el HTML original en contract_templates_backup_20261015
 * para que el rollback lo deje exactamente como estaba.
 */
return new class extends Migration
{
    private const BACKUP_TABLE = 'contract_templates_backup_20261015';
    private const OLD_NAME = 'UNO X CIENTO LLC';
    private const NEW_NAME = 'Elite Closer Society LLC';
    private const REFUND_CLAUSE = '~\s*<div class="subsection">\s*<strong>\d+\.\d+</strong>\s*Se pueden ofrecer reembolsos por los servicios ofrecidos en las sesiones de preguntas y respuestas en directo[^<]*</div>~u';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(self::BACKUP_TABLE, function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->longText('html_content');
            $table->timestamp('updated_at')->nullable();
        });

        DB::table('contract_templates')->orderBy('id')->get()->each(function ($template) {
            DB::table(self::BACKUP_TABLE)->insert([
                'id' => $template->id,
                'html_content' => $template->html_content,
                'updated_at' => $template->updated_at,
            ]);

            $html = str_replace(self::OLD_NAME, self::NEW_NAME, $template->html_content);
            $html = preg_replace(self::REFUND_CLAUSE, '', $html);

            if ($html !== $template->html_content) {
                DB::table('contract_templates')
                    ->where('id', $template->id)
                    ->update(['html_content' => $html, 'updated_at' => now()]);
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table(self::BACKUP_TABLE)->get()->each(function ($backup) {
            DB::table('contract_templates')
                ->where('id', $backup->id)
                ->update(['html_content' => $backup->html_content, 'updated_at' => $backup->updated_at]);
        });

        Schema::dropIfExists(self::BACKUP_TABLE);
    }
};
