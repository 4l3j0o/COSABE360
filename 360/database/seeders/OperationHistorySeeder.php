<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OperationHistorySeeder extends Seeder
{
    /**
     * Registro de operaciones inicial (bitácora) coherente con el arranque del sistema:
     * creación de configuración, inscripciones, matrículas y cuotas.
     */
    public function run(): void
    {
        $admin = User::where('email', 'admin@sanber.edu.bo')->first();

        $acciones = [
            ['accion' => 'CREAR',  'modulo' => 'Configuración', 'modelo' => 'TaxConfiguration', 'detalle' => 'Configuración fiscal inicial'],
            ['accion' => 'CREAR',  'modulo' => 'Estudiantes',   'modelo' => 'Student',          'detalle' => 'Carga inicial de estudiantes'],
            ['accion' => 'CREAR',  'modulo' => 'Inscripciones', 'modelo' => 'Enrollment',       'detalle' => 'Inscripción de estudiantes para la gestión 2026'],
            ['accion' => 'CREAR',  'modulo' => 'Matrículas',    'modelo' => 'Registration',     'detalle' => 'Matriculación de estudiantes'],
            ['accion' => 'CREAR',  'modulo' => 'Pagos',         'modelo' => 'MonthlyPayment',   'detalle' => 'Generación de cuotas mensuales'],
            ['accion' => 'ACTUALIZAR', 'modulo' => 'Documentación', 'modelo' => 'StudentDocument', 'detalle' => 'Carga de expedientes estudiantiles'],
        ];

        foreach ($acciones as $i => $accion) {
            DB::table('operation_histories')->insert([
                'user_id'    => $admin?->id,
                'accion'     => $accion['accion'],
                'modulo'     => $accion['modulo'],
                'modelo'     => $accion['modelo'],
                'modelo_id'  => $i + 1,
                'detalle'    => $accion['detalle'],
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Seeder',
                'created_at' => now()->subMinutes(count($acciones) - $i),
                'updated_at' => now()->subMinutes(count($acciones) - $i),
            ]);
        }

        $this->command->info('Historial de operaciones inicial registrado.');
    }
}
