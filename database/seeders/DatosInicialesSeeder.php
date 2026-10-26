<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Rol;
use App\Models\Usuario;
use App\Models\Aula;
use App\Models\Periodo;
use App\Models\Carrera;
use App\Models\Materia;
use App\Models\Docente;
use App\Models\Alumno;
use App\Models\Grupo;
use App\Models\KardexInscripcion;
use App\Models\Asistencia;
use App\Models\TipoEvaluacion;

class DatosInicialesSeeder extends Seeder
{
    public function run(): void
    {
        // ──────────────────────────────────────────────
        // 1. ROLES
        // ──────────────────────────────────────────────
        $rolesData = [
            ['nombre' => 'Admin',                    'descripcion' => 'Acceso total al sistema'],
            ['nombre' => 'Director',                 'descripcion' => 'Dirección académica'],
            ['nombre' => 'Coordinador de Carrera',   'descripcion' => 'Coordinación por carrera'],
            ['nombre' => 'Personal Administrativo',  'descripcion' => 'Gestión académica y operativa'],
            ['nombre' => 'Docente',                  'descripcion' => 'Captura de calificaciones y asistencias'],
            ['nombre' => 'Alumno',                   'descripcion' => 'Consulta de expediente personal'],
        ];
        foreach ($rolesData as $r) {
            Rol::firstOrCreate(['nombre' => $r['nombre']], $r);
        }

        $rolAdmin     = Rol::where('nombre', 'Admin')->first();
        $rolDocente   = Rol::where('nombre', 'Docente')->first();
        $rolAlumno    = Rol::where('nombre', 'Alumno')->first();
        $rolAdminPers = Rol::where('nombre', 'Personal Administrativo')->first();
        $rolDir       = Rol::where('nombre', 'Director')->first();
        $rolCoord     = Rol::where('nombre', 'Coordinador de Carrera')->first();

        // ──────────────────────────────────────────────
        // 2. TIPOS DE EVALUACIÓN
        // ──────────────────────────────────────────────
        $tipoOrdinario = TipoEvaluacion::firstOrCreate(
            ['nombre' => 'Ordinario'],
            ['descripcion' => 'Evaluación ordinaria al término del periodo']
        );
        $tipoExtra = TipoEvaluacion::firstOrCreate(
            ['nombre' => 'Extraordinario'],
            ['descripcion' => 'Evaluación extraordinaria para alumnos reprobados']
        );
        TipoEvaluacion::firstOrCreate(
            ['nombre' => 'Título de suficiencia'],
            ['descripcion' => 'Evaluación de suficiencia académica']
        );

        // ──────────────────────────────────────────────
        // 3. AULAS
        // ──────────────────────────────────────────────
        $aulas = [
            ['edificio' => 'A', 'numero_identificador' => 'A-101', 'capacidad_maxima' => 35, 'tipo_aula' => 'Aula Teórica', 'estatus' => 1],
            ['edificio' => 'A', 'numero_identificador' => 'A-102', 'capacidad_maxima' => 35, 'tipo_aula' => 'Aula Teórica', 'estatus' => 1],
            ['edificio' => 'B', 'numero_identificador' => 'B-201', 'capacidad_maxima' => 20, 'tipo_aula' => 'Laboratorio',  'estatus' => 1],
            ['edificio' => 'C', 'numero_identificador' => 'C-301', 'capacidad_maxima' => 25, 'tipo_aula' => 'Sala Cómputo', 'estatus' => 1],
        ];
        foreach ($aulas as $a) {
            Aula::firstOrCreate(['numero_identificador' => $a['numero_identificador']], $a);
        }
        $aulaA101 = Aula::where('numero_identificador', 'A-101')->first();
        $aulaA102 = Aula::where('numero_identificador', 'A-102')->first();
        $aulaB201 = Aula::where('numero_identificador', 'B-201')->first();
        $aulaC301 = Aula::where('numero_identificador', 'C-301')->first();

        // ──────────────────────────────────────────────
        // 4. PERIODOS
        // ──────────────────────────────────────────────
        $periodo2025A = Periodo::firstOrCreate(
            ['codigo' => '2025-A'],
            ['fecha_inicio' => '2025-01-15', 'fecha_fin' => '2025-06-30']
        );
        $periodo2025B = Periodo::firstOrCreate(
            ['codigo' => '2025-B'],
            ['fecha_inicio' => '2025-08-18', 'fecha_fin' => '2026-01-30']
        );
        $periodo2026A = Periodo::firstOrCreate(
            ['codigo' => '2026-A'],
            ['fecha_inicio' => '2026-02-02', 'fecha_fin' => '2026-07-31']
        );
        $periodo2026B = Periodo::firstOrCreate(
            ['codigo' => '2026-B'],
            ['fecha_inicio' => '2026-08-17', 'fecha_fin' => '2027-01-30']
        );

        // ──────────────────────────────────────────────
        // 5. CARRERAS
        // ──────────────────────────────────────────────
        $carreraISC = Carrera::firstOrCreate(
            ['clave_oficial' => 'ISC'],
            ['nombre' => 'Ingeniería en Sistemas Computacionales', 'total_creditos' => 240, 'capacidad' => 50]
        );
        $carreraLCA = Carrera::firstOrCreate(
            ['clave_oficial' => 'LCA'],
            ['nombre' => 'Licenciatura en Contaduría y Administración', 'total_creditos' => 200, 'capacidad' => 40]
        );
        $carreraIIA = Carrera::firstOrCreate(
            ['clave_oficial' => 'IIA'],
            ['nombre' => 'Ingeniería Industrial y Administración', 'total_creditos' => 220, 'capacidad' => 45]
        );

        // ──────────────────────────────────────────────
        // 6. MATERIAS
        // ──────────────────────────────────────────────
        $materiasISC = [
            ['clave_materia' => 'ISC-101', 'nombre' => 'Fundamentos de Programación',  'creditos' => 5, 'semestre_sugerido' => 1],
            ['clave_materia' => 'ISC-102', 'nombre' => 'Matemáticas Discretas',        'creditos' => 4, 'semestre_sugerido' => 1],
            ['clave_materia' => 'ISC-201', 'nombre' => 'Estructuras de Datos',         'creditos' => 5, 'semestre_sugerido' => 2],
            ['clave_materia' => 'ISC-202', 'nombre' => 'Programación Orientada a Obj', 'creditos' => 5, 'semestre_sugerido' => 2],
            ['clave_materia' => 'ISC-301', 'nombre' => 'Bases de Datos',               'creditos' => 5, 'semestre_sugerido' => 3],
            ['clave_materia' => 'ISC-302', 'nombre' => 'Redes de Computadoras',        'creditos' => 4, 'semestre_sugerido' => 3],
        ];
        foreach ($materiasISC as $m) {
            Materia::firstOrCreate(
                ['clave_materia' => $m['clave_materia']],
                array_merge($m, ['carrera_id' => $carreraISC->id])
            );
        }

        $materiasLCA = [
            ['clave_materia' => 'LCA-101', 'nombre' => 'Contabilidad I',         'creditos' => 5, 'semestre_sugerido' => 1],
            ['clave_materia' => 'LCA-102', 'nombre' => 'Administración General', 'creditos' => 4, 'semestre_sugerido' => 1],
            ['clave_materia' => 'LCA-201', 'nombre' => 'Finanzas Corporativas',  'creditos' => 5, 'semestre_sugerido' => 2],
            ['clave_materia' => 'LCA-202', 'nombre' => 'Derecho Mercantil',      'creditos' => 4, 'semestre_sugerido' => 2],
        ];
        foreach ($materiasLCA as $m) {
            Materia::firstOrCreate(
                ['clave_materia' => $m['clave_materia']],
                array_merge($m, ['carrera_id' => $carreraLCA->id])
            );
        }

        $materiasIIA = [
            ['clave_materia' => 'IIA-101', 'nombre' => 'Cálculo Diferencial',    'creditos' => 5, 'semestre_sugerido' => 1],
            ['clave_materia' => 'IIA-102', 'nombre' => 'Estadística Aplicada',   'creditos' => 4, 'semestre_sugerido' => 1],
            ['clave_materia' => 'IIA-201', 'nombre' => 'Administración Industrial','creditos'=> 5, 'semestre_sugerido' => 2],
            ['clave_materia' => 'IIA-202', 'nombre' => 'Logística y Cadena de Suministro', 'creditos' => 4, 'semestre_sugerido' => 2],
        ];
        foreach ($materiasIIA as $m) {
            Materia::firstOrCreate(
                ['clave_materia' => $m['clave_materia']],
                array_merge($m, ['carrera_id' => $carreraIIA->id])
            );
        }

        $matISC101 = Materia::where('clave_materia', 'ISC-101')->first();
        $matISC201 = Materia::where('clave_materia', 'ISC-201')->first();
        $matISC301 = Materia::where('clave_materia', 'ISC-301')->first();
        $matLCA101 = Materia::where('clave_materia', 'LCA-101')->first();
        $matIIA101 = Materia::where('clave_materia', 'IIA-101')->first();

        // ──────────────────────────────────────────────
        // 7. USUARIOS ADICIONALES
        // ──────────────────────────────────────────────
        // Admin (ya existe en DatabaseSeeder)
        $uAdmin = Usuario::firstOrCreate(
            ['correo_institucional' => 'admin@saes.edu.mx'],
            ['rol_id' => $rolAdmin->id, 'password_hash' => Hash::make('Admin1234!')]
        );

        // Director
        $uDirector = Usuario::firstOrCreate(
            ['correo_institucional' => 'director@saes.edu.mx'],
            ['rol_id' => $rolDir->id, 'password_hash' => Hash::make('Director1234!')]
        );

        // Coordinador de Carrera
        $uCoord = Usuario::firstOrCreate(
            ['correo_institucional' => 'coordinador@saes.edu.mx'],
            ['rol_id' => $rolCoord->id, 'password_hash' => Hash::make('Coord1234!')]
        );

        // Personal Administrativo
        $uAdminPers = Usuario::firstOrCreate(
            ['correo_institucional' => 'administrativo@saes.edu.mx'],
            ['rol_id' => $rolAdminPers->id, 'password_hash' => Hash::make('Administ1234!')]
        );

        // Docentes
        $uDoc1 = Usuario::firstOrCreate(
            ['correo_institucional' => 'jperez@saes.edu.mx'],
            ['rol_id' => $rolDocente->id, 'password_hash' => Hash::make('Docente1234!')]
        );
        $uDoc2 = Usuario::firstOrCreate(
            ['correo_institucional' => 'mgarcia@saes.edu.mx'],
            ['rol_id' => $rolDocente->id, 'password_hash' => Hash::make('Docente1234!')]
        );
        $uDoc3 = Usuario::firstOrCreate(
            ['correo_institucional' => 'clopez@saes.edu.mx'],
            ['rol_id' => $rolDocente->id, 'password_hash' => Hash::make('Docente1234!')]
        );

        // Alumnos
        $uAlm = [];
        $alumnosData = [
            ['correo' => 'alumno1@saes.edu.mx', 'nombres' => 'Andrea',    'apellidos' => 'Martínez Ruiz',   'curp' => 'MARA050101MDFTNNA1', 'carrera' => $carreraISC],
            ['correo' => 'alumno2@saes.edu.mx', 'nombres' => 'Rodrigo',   'apellidos' => 'Sánchez Torres',  'curp' => 'SATR040202HDFNRDA2', 'carrera' => $carreraISC],
            ['correo' => 'alumno3@saes.edu.mx', 'nombres' => 'Valeria',   'apellidos' => 'López Herrera',   'curp' => 'LOHV050303MDFPVLA3', 'carrera' => $carreraLCA],
            ['correo' => 'alumno4@saes.edu.mx', 'nombres' => 'Miguel',    'apellidos' => 'Flores Castillo', 'curp' => 'FOCM040404HDFLLGA4', 'carrera' => $carreraLCA],
            ['correo' => 'alumno5@saes.edu.mx', 'nombres' => 'Sofía',     'apellidos' => 'Ramírez Vega',    'curp' => 'RAVS050505MDFMFFA5', 'carrera' => $carreraIIA],
        ];
        foreach ($alumnosData as $a) {
            $uAlm[] = Usuario::firstOrCreate(
                ['correo_institucional' => $a['correo']],
                ['rol_id' => $rolAlumno->id, 'password_hash' => Hash::make('Alumno1234!')]
            );
        }

        // ──────────────────────────────────────────────
        // 8. DOCENTES
        // ──────────────────────────────────────────────
        $docente1 = Docente::firstOrCreate(
            ['numero_empleado' => 'D001'],
            ['usuario_id' => $uDoc1->id, 'nombres' => 'Juan Carlos', 'apellidos' => 'Pérez Mendoza',   'especialidad' => 'Sistemas Computacionales']
        );
        $docente2 = Docente::firstOrCreate(
            ['numero_empleado' => 'D002'],
            ['usuario_id' => $uDoc2->id, 'nombres' => 'María Elena', 'apellidos' => 'García Soto',      'especialidad' => 'Contaduría y Administración']
        );
        $docente3 = Docente::firstOrCreate(
            ['numero_empleado' => 'D003'],
            ['usuario_id' => $uDoc3->id, 'nombres' => 'Carlos Alberto', 'apellidos' => 'López Fuentes', 'especialidad' => 'Ingeniería Industrial']
        );

        // ──────────────────────────────────────────────
        // 9. ALUMNOS
        // ──────────────────────────────────────────────
        $alumnos = [];
        foreach ($alumnosData as $i => $a) {
            $num = str_pad($i + 1, 3, '0', STR_PAD_LEFT);
            $alumnos[] = Alumno::firstOrCreate(
                ['matricula' => "A{$num}-2026"],
                [
                    'usuario_id'          => $uAlm[$i]->id,
                    'carrera_id'          => $a['carrera']->id,
                    'nombres'             => $a['nombres'],
                    'apellidos'           => $a['apellidos'],
                    'curp'                => $a['curp'],
                    'escuela_procedencia' => 'Preparatoria Regional UNIVAC',
                    'estatus'             => 'activo',
                ]
            );
        }

        // ──────────────────────────────────────────────
        // 10. GRUPOS (Periodo activo 2026-A)
        // ──────────────────────────────────────────────
        $grupoISC101 = Grupo::firstOrCreate(
            ['periodo_id' => $periodo2026A->id, 'materia_id' => $matISC101->id, 'docente_id' => $docente1->id],
            ['aula_id' => $aulaC301->id, 'cupo_maximo' => 25]
        );
        $grupoISC201 = Grupo::firstOrCreate(
            ['periodo_id' => $periodo2026A->id, 'materia_id' => $matISC201->id, 'docente_id' => $docente1->id],
            ['aula_id' => $aulaB201->id, 'cupo_maximo' => 20]
        );
        $grupoISC301 = Grupo::firstOrCreate(
            ['periodo_id' => $periodo2026A->id, 'materia_id' => $matISC301->id, 'docente_id' => $docente1->id],
            ['aula_id' => $aulaB201->id, 'cupo_maximo' => 20]
        );
        $grupoLCA101 = Grupo::firstOrCreate(
            ['periodo_id' => $periodo2026A->id, 'materia_id' => $matLCA101->id, 'docente_id' => $docente2->id],
            ['aula_id' => $aulaA101->id, 'cupo_maximo' => 35]
        );
        $grupoIIA101 = Grupo::firstOrCreate(
            ['periodo_id' => $periodo2026A->id, 'materia_id' => $matIIA101->id, 'docente_id' => $docente3->id],
            ['aula_id' => $aulaA102->id, 'cupo_maximo' => 35]
        );

        // ──────────────────────────────────────────────
        // 11. KARDEX — inscripciones
        // ──────────────────────────────────────────────
        $kardexData = [
            // Alumnos ISC inscritos en grupos ISC
            ['alumno' => $alumnos[0], 'grupo' => $grupoISC101, 'cal' => 85.00, 'estatus' => 'acreditada'],
            ['alumno' => $alumnos[0], 'grupo' => $grupoISC201, 'cal' => 78.00, 'estatus' => 'cursando'],
            ['alumno' => $alumnos[1], 'grupo' => $grupoISC101, 'cal' => 92.00, 'estatus' => 'acreditada'],
            ['alumno' => $alumnos[1], 'grupo' => $grupoISC301, 'cal' => null,  'estatus' => 'cursando'],
            // Alumnos LCA
            ['alumno' => $alumnos[2], 'grupo' => $grupoLCA101, 'cal' => 88.00, 'estatus' => 'cursando'],
            ['alumno' => $alumnos[3], 'grupo' => $grupoLCA101, 'cal' => 70.00, 'estatus' => 'cursando'],
            // Alumno IIA
            ['alumno' => $alumnos[4], 'grupo' => $grupoIIA101, 'cal' => null,  'estatus' => 'cursando'],
        ];
        foreach ($kardexData as $k) {
            KardexInscripcion::firstOrCreate(
                ['alumno_id' => $k['alumno']->id, 'grupo_id' => $k['grupo']->id],
                [
                    'tipo_evaluacion_id' => $tipoOrdinario->id,
                    'calificacion_final' => $k['cal'],
                    'estatus_materia'    => $k['estatus'],
                ]
            );
        }

        // ──────────────────────────────────────────────
        // 12. ASISTENCIAS
        // ──────────────────────────────────────────────
        $fechasClase = ['2026-07-07', '2026-07-08', '2026-07-09', '2026-07-14', '2026-07-15'];
        $estatusOpts = ['presente', 'presente', 'presente', 'ausente', 'justificado'];

        $asistenciasConfig = [
            [$alumnos[0], $grupoISC101],
            [$alumnos[0], $grupoISC201],
            [$alumnos[1], $grupoISC101],
            [$alumnos[2], $grupoLCA101],
            [$alumnos[3], $grupoLCA101],
            [$alumnos[4], $grupoIIA101],
        ];

        foreach ($asistenciasConfig as $config) {
            [$alumno, $grupo] = $config;
            foreach ($fechasClase as $i => $fecha) {
                Asistencia::firstOrCreate(
                    ['alumno_id' => $alumno->id, 'grupo_id' => $grupo->id, 'fecha' => $fecha],
                    ['estatus_asistencia' => $estatusOpts[$i]]
                );
            }
        }

        // ──────────────────────────────────────────────
        // Resumen
        // ──────────────────────────────────────────────
        $this->command->info('✔ DatosInicialesSeeder completado.');
        $this->command->table(
            ['Usuario', 'Contraseña', 'Rol'],
            [
                ['admin@saes.edu.mx',           'Admin1234!',    'Admin'],
                ['director@saes.edu.mx',        'Director1234!', 'Director'],
                ['coordinador@saes.edu.mx',     'Coord1234!',    'Coordinador de Carrera'],
                ['administrativo@saes.edu.mx',  'Administ1234!', 'Personal Administrativo'],
                ['jperez@saes.edu.mx',          'Docente1234!',  'Docente (Juan Pérez)'],
                ['mgarcia@saes.edu.mx',         'Docente1234!',  'Docente (María García)'],
                ['clopez@saes.edu.mx',          'Docente1234!',  'Docente (Carlos López)'],
                ['alumno1@saes.edu.mx',         'Alumno1234!',   'Alumno (Andrea Martínez)'],
                ['alumno2@saes.edu.mx',         'Alumno1234!',   'Alumno (Rodrigo Sánchez)'],
                ['alumno3@saes.edu.mx',         'Alumno1234!',   'Alumno (Valeria López)'],
                ['alumno4@saes.edu.mx',         'Alumno1234!',   'Alumno (Miguel Flores)'],
                ['alumno5@saes.edu.mx',         'Alumno1234!',   'Alumno (Sofía Ramírez)'],
            ]
        );
    }
}
