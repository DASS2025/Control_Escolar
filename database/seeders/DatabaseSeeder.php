<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Rol;
use App\Models\Usuario;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Crear los 4 roles del sistema
        $roles = [
            ['nombre' => 'Admin',                   'descripcion' => 'Acceso total al sistema'],
            ['nombre' => 'Personal Administrativo', 'descripcion' => 'Gestión académica y operativa'],
            ['nombre' => 'Docente',                 'descripcion' => 'Captura de calificaciones y asistencias'],
            ['nombre' => 'Alumno',                  'descripcion' => 'Consulta de expediente personal'],
        ];

        foreach ($roles as $rolData) {
            Rol::firstOrCreate(['nombre' => $rolData['nombre']], $rolData);
        }

        // Crear el usuario administrador inicial
        $rolAdmin = Rol::where('nombre', 'Admin')->first();

        Usuario::firstOrCreate(
            ['correo_institucional' => 'admin@saes.edu.mx'],
            [
                'rol_id'        => $rolAdmin->id,
                'password_hash' => Hash::make('Admin1234!'),
            ]
        );

        $this->command->info('Usuario: admin@saes.edu.mx / Contraseña: Admin1234!');

        $this->call(DatosInicialesSeeder::class);
    }
}