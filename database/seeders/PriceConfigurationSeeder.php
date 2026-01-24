<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PriceConfiguration;
use Spatie\Permission\Models\Role;

class PriceConfigurationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear rol finanzas si no existe
        Role::firstOrCreate(['name' => 'finanzas']);

        // Configuraciones de precios iniciales
        $configurations = [
            [
                'tipo_contrato' => 'low_ticket',
                'monto_base' => 500.00,
                'moneda' => 'USD',
                'activo' => true,
                'monto_editable' => false,
                'cuotas_editables' => false,
            ],
            [
                'tipo_contrato' => 'high_ticket',
                'monto_base' => 2000.00,
                'moneda' => 'USD',
                'activo' => true,
                'monto_editable' => false,
                'cuotas_editables' => false,
            ],
            [
                'tipo_contrato' => 'beca',
                'monto_base' => 0.00, // Editable por el closer
                'moneda' => 'USD',
                'activo' => true,
                'monto_editable' => true, // El closer puede definir el monto
                'cuotas_editables' => false,
            ],
            [
                'tipo_contrato' => 'formacion_anexo',
                'monto_base' => 2000.00, // Valor por defecto, pero editable
                'moneda' => 'USD',
                'activo' => true,
                'monto_editable' => true, // El closer puede editar el monto
                'cuotas_editables' => true, // El closer puede editar las cuotas manualmente
            ],
        ];

        foreach ($configurations as $config) {
            PriceConfiguration::updateOrCreate(
                [
                    'tipo_contrato' => $config['tipo_contrato'],
                    'activo' => true,
                ],
                $config
            );
        }

        $this->command->info('Price configurations created successfully!');
        $this->command->info('Role "finanzas" created successfully!');
    }
}
