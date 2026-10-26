<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tablas base del sistema escolar.
 *
 * Las instalaciones existentes ya tienen estas tablas (se crearon manualmente
 * antes de existir esta migración), por eso cada tabla se crea solo si no existe.
 * Las columnas `carreras.capacidad` y `alumnos.escuela_procedencia` y la tabla
 * `DocumentosAlumnos` las agregan migraciones posteriores.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('roles')) {
            Schema::create('roles', function (Blueprint $table) {
                $table->integer('id', true);
                $table->string('nombre', 50);
                $table->text('descripcion')->nullable();
            });
        }

        if (! Schema::hasTable('usuarios')) {
            Schema::create('usuarios', function (Blueprint $table) {
                $table->integer('id', true);
                $table->integer('rol_id')->index();
                $table->string('correo_institucional', 150)->unique();
                $table->string('password_hash', 255);
                $table->timestamp('consentimiento_aviso')->useCurrent();

                $table->foreign('rol_id')->references('id')->on('roles');
            });
        }

        if (! Schema::hasTable('aulas')) {
            Schema::create('aulas', function (Blueprint $table) {
                $table->integer('id', true);
                $table->string('edificio', 100)->nullable();
                $table->string('numero_identificador', 50);
                $table->integer('capacidad_maxima');
                $table->string('tipo_aula', 100)->nullable();
                $table->boolean('estatus')->nullable()->default(true);
            });
        }

        if (! Schema::hasTable('periodos')) {
            Schema::create('periodos', function (Blueprint $table) {
                $table->integer('id', true);
                $table->string('codigo', 20);
                $table->date('fecha_inicio')->nullable();
                $table->date('fecha_fin')->nullable();
            });
        }

        if (! Schema::hasTable('carreras')) {
            Schema::create('carreras', function (Blueprint $table) {
                $table->integer('id', true);
                $table->string('clave_oficial', 50)->unique();
                $table->string('nombre', 150);
                $table->integer('total_creditos');
            });
        }

        if (! Schema::hasTable('tipos_evaluacion')) {
            Schema::create('tipos_evaluacion', function (Blueprint $table) {
                $table->integer('id', true);
                $table->string('nombre', 50);
                $table->text('descripcion')->nullable();
            });
        }

        if (! Schema::hasTable('materias')) {
            Schema::create('materias', function (Blueprint $table) {
                $table->integer('id', true);
                $table->integer('carrera_id')->index();
                $table->string('clave_materia', 50)->unique();
                $table->string('nombre', 150);
                $table->integer('creditos');
                $table->integer('semestre_sugerido')->nullable();

                $table->foreign('carrera_id')->references('id')->on('carreras')->cascadeOnDelete();
            });
        }

        if (! Schema::hasTable('alumnos')) {
            Schema::create('alumnos', function (Blueprint $table) {
                $table->integer('id', true);
                $table->integer('usuario_id')->index();
                $table->integer('carrera_id')->index();
                $table->string('matricula', 50)->unique();
                $table->string('nombres', 100);
                $table->string('apellidos', 100);
                $table->string('curp', 18)->unique();
                $table->string('estatus', 50)->nullable()->default('Activo');

                $table->foreign('usuario_id')->references('id')->on('usuarios')->cascadeOnDelete();
                $table->foreign('carrera_id')->references('id')->on('carreras');
            });
        }

        if (! Schema::hasTable('docentes')) {
            Schema::create('docentes', function (Blueprint $table) {
                $table->integer('id', true);
                $table->integer('usuario_id')->index();
                $table->string('numero_empleado', 50)->unique();
                $table->string('nombres', 100);
                $table->string('apellidos', 100);
                $table->string('especialidad', 150)->nullable();

                $table->foreign('usuario_id')->references('id')->on('usuarios')->cascadeOnDelete();
            });
        }

        if (! Schema::hasTable('personal_administrativo')) {
            Schema::create('personal_administrativo', function (Blueprint $table) {
                $table->integer('id', true);
                $table->integer('usuario_id')->index();
                $table->string('numero_empleado', 50)->unique();
                $table->string('nombres', 100);
                $table->string('apellidos', 100);
                $table->string('puesto', 100)->nullable();
                $table->string('departamento', 100)->nullable();

                $table->foreign('usuario_id')->references('id')->on('usuarios')->cascadeOnDelete();
            });
        }

        if (! Schema::hasTable('grupos')) {
            Schema::create('grupos', function (Blueprint $table) {
                $table->integer('id', true);
                $table->integer('periodo_id')->index();
                $table->integer('materia_id')->index();
                $table->integer('docente_id')->index();
                $table->integer('aula_id')->index();
                $table->integer('cupo_maximo');

                $table->foreign('periodo_id')->references('id')->on('periodos');
                $table->foreign('materia_id')->references('id')->on('materias');
                $table->foreign('docente_id')->references('id')->on('docentes');
                $table->foreign('aula_id')->references('id')->on('aulas');
            });
        }

        if (! Schema::hasTable('horarios_grupo')) {
            Schema::create('horarios_grupo', function (Blueprint $table) {
                $table->integer('id', true);
                $table->integer('grupo_id')->index();
                $table->integer('dia_semana');
                $table->time('hora_inicio');
                $table->time('hora_fin');

                $table->foreign('grupo_id')->references('id')->on('grupos')->cascadeOnDelete();
            });
        }

        if (! Schema::hasTable('kardex_inscripciones')) {
            Schema::create('kardex_inscripciones', function (Blueprint $table) {
                $table->bigInteger('id', true);
                $table->integer('alumno_id')->index();
                $table->integer('grupo_id')->index();
                $table->integer('tipo_evaluacion_id')->index();
                $table->decimal('calificacion_final', 4, 2)->nullable();
                $table->string('estatus_materia', 50)->nullable()->default('En Curso');

                $table->foreign('alumno_id')->references('id')->on('alumnos');
                $table->foreign('grupo_id')->references('id')->on('grupos');
                $table->foreign('tipo_evaluacion_id')->references('id')->on('tipos_evaluacion');
            });
        }

        if (! Schema::hasTable('asistencias')) {
            Schema::create('asistencias', function (Blueprint $table) {
                $table->bigInteger('id', true);
                $table->integer('alumno_id')->index();
                $table->integer('grupo_id')->index();
                $table->date('fecha');
                $table->string('estatus_asistencia', 50);

                $table->foreign('alumno_id')->references('id')->on('alumnos');
                $table->foreign('grupo_id')->references('id')->on('grupos')->cascadeOnDelete();
            });
        }

        if (! Schema::hasTable('bitacora_auditoria')) {
            Schema::create('bitacora_auditoria', function (Blueprint $table) {
                $table->bigInteger('id', true);
                $table->integer('usuario_id')->index();
                $table->string('accion', 50);
                $table->string('tabla_afectada', 100);
                $table->json('valores_json')->nullable();
                $table->timestamp('created_at')->useCurrent();

                $table->foreign('usuario_id')->references('id')->on('usuarios');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('bitacora_auditoria');
        Schema::dropIfExists('asistencias');
        Schema::dropIfExists('kardex_inscripciones');
        Schema::dropIfExists('horarios_grupo');
        Schema::dropIfExists('grupos');
        Schema::dropIfExists('personal_administrativo');
        Schema::dropIfExists('docentes');
        Schema::dropIfExists('alumnos');
        Schema::dropIfExists('materias');
        Schema::dropIfExists('tipos_evaluacion');
        Schema::dropIfExists('carreras');
        Schema::dropIfExists('periodos');
        Schema::dropIfExists('aulas');
        Schema::dropIfExists('usuarios');
        Schema::dropIfExists('roles');
    }
};
