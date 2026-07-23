<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alumno extends Model
{
    protected $table = 'Alumnos';
    public $timestamps = false;

    protected $fillable = [
        'usuario_id',
        'carrera_id',
        'matricula',
        'nombres',
        'apellidos',
        'curp',
        'escuela_procedencia',
        'estatus',
    ];

    // Un Alumno pertenece a un Usuario del sistema
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    // Un Alumno pertenece a una Carrera
    public function carrera()
    {
        return $this->belongsTo(Carrera::class, 'carrera_id');
    }

    // Un Alumno tiene muchos registros en el Kardex
    public function kardexInscripciones()
    {
        return $this->hasMany(KardexInscripcion::class, 'alumno_id');
    }

    // Un Alumno tiene muchos registros de Asistencia
    public function asistencias()
    {
        return $this->hasMany(Asistencia::class, 'alumno_id');
    }

    // Un Alumno tiene muchos documentos adjuntos
    public function documentos()
    {
        return $this->hasMany(DocumentoAlumno::class, 'alumno_id');
    }

    public function foto()
    {
        return $this->hasOne(DocumentoAlumno::class, 'alumno_id')
                    ->where('tipo', 'foto')
                    ->latestOfMany('fecha_subida');
    }

    public function certificado()
    {
        return $this->hasOne(DocumentoAlumno::class, 'alumno_id')
                    ->where('tipo', 'certificado')
                    ->latestOfMany('fecha_subida');
    }
}
