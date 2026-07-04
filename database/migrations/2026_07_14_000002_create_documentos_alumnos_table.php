<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('DocumentosAlumnos', function (Blueprint $table) {
            $table->id();
            $table->integer('alumno_id');
            $table->foreign('alumno_id')->references('id')->on('Alumnos')->cascadeOnDelete();
            $table->enum('tipo', ['foto', 'certificado']);
            $table->string('ruta_archivo', 500);
            $table->string('nombre_original', 255);
            $table->timestamp('fecha_subida')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('DocumentosAlumnos');
    }
};
