<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SaleInstallment;
use App\Mail\InstallmentOverdueMail;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class CheckOverdueInstallments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'finance:check-overdue';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for overdue installments and mark them as overdue, send notification emails';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today();

        // Buscar cuotas pendientes que ya vencieron
        $overdueInstallments = SaleInstallment::with(['sale', 'sale.lead'])
            ->where('estado', 'pendiente')
            ->where('fecha_vencimiento', '<', $today)
            ->get();

        $this->info("Encontradas {$overdueInstallments->count()} cuotas vencidas.");

        $markedCount = 0;
        $emailsSent = 0;

        foreach ($overdueInstallments as $installment) {
            // Marcar como vencida
            $installment->update(['estado' => 'vencida']);
            $installment->sale->updatePaymentStatus();
            $markedCount++;

            // Enviar email si hay email del cliente
            if ($installment->sale && $installment->sale->email_cliente) {
                try {
                    Mail::to($installment->sale->email_cliente)
                        ->send(new InstallmentOverdueMail($installment));
                    $emailsSent++;
                    $this->info("Email enviado a: {$installment->sale->email_cliente}");
                } catch (\Exception $e) {
                    $this->error("Error enviando email a {$installment->sale->email_cliente}: {$e->getMessage()}");
                }
            }
        }

        $this->info("Proceso completado:");
        $this->info("- Cuotas marcadas como vencidas: {$markedCount}");
        $this->info("- Emails de notificacion enviados: {$emailsSent}");

        return Command::SUCCESS;
    }
}
