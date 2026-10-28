<?php

namespace Database\Seeders;

use App\Models\TipoEvaluacion;
use Illuminate\Database\Seeder;

/**
 * Catálogo de tipos de evaluación para el módulo de Calificaciones.
 *
 * Es idempotente (firstOrCreate por nombre): se puede ejecutar varias veces
 * y no duplica ni modifica registros que ya existan.
 *
 *   php artisan db:seed --class=TiposEvaluacionSeeder
 */
class TiposEvaluacionSeeder extends Seeder
{
    public function run(): void
    {
        $tipos = [
            ['nombre' => 'Ordinario',      'descripcion' => 'Evaluación ordinaria al término del periodo'],
            ['nombre' => 'Extraordinario', 'descripcion' => 'Evaluación extraordinaria para alumnos reprobados'],
            ['nombre' => 'Titulación',     'descripcion' => 'Evaluación para efectos de titulación'],
        ];

        foreach ($tipos as $tipo) {
            TipoEvaluacion::firstOrCreate(
                ['nombre' => $tipo['nombre']],
                ['descripcion' => $tipo['descripcion']]
            );
        }
    }
}
