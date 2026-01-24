<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SaleInstallment;
use App\Mail\InstallmentReminderMail;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class SendInstallmentReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'finance:send-reminders {--days=3 : Days before due date to send reminder}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send reminder emails for installments due soon';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $daysBeforeDue = (int) $this->option('days');
        $targetDate = Carbon::today()->addDays($daysBeforeDue);

        // Buscar cuotas pendientes que vencen en la fecha objetivo
        $dueSoonInstallments = SaleInstallment::with(['sale', 'sale.lead'])
            ->where('estado', 'pendiente')
            ->whereDate('fecha_vencimiento', $targetDate)
            ->where('recordatorio_enviado', false)
            ->get();

        $this->info("Encontradas {$dueSoonInstallments->count()} cuotas que vencen en {$daysBeforeDue} dias.");

        $emailsSent = 0;

        foreach ($dueSoonInstallments as $installment) {
            // Enviar email si hay email del cliente
            if ($installment->sale && $installment->sale->email_cliente) {
                try {
                    Mail::to($installment->sale->email_cliente)
                        ->send(new InstallmentReminderMail($installment));

                    // Marcar como recordatorio enviado
                    $installment->update([
                        'recordatorio_enviado' => true,
                        'recordatorio_enviado_at' => now(),
                    ]);

                    $emailsSent++;
                    $this->info("Recordatorio enviado a: {$installment->sale->email_cliente}");
                } catch (\Exception $e) {
                    $this->error("Error enviando recordatorio a {$installment->sale->email_cliente}: {$e->getMessage()}");
                }
            }
        }

        $this->info("Proceso completado:");
        $this->info("- Recordatorios enviados: {$emailsSent}");

        return Command::SUCCESS;
    }
}
