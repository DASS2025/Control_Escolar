<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Carrera extends Model
{
    protected $table = 'carreras';
    public $timestamps = false;

    protected $fillable = [
        'clave_oficial',
        'nombre',
        'total_creditos',
    ];

    // Una Carrera tiene muchas Materias
    public function materias()
    {
        return $this->hasMany(Materia::class, 'carrera_id');
    }

    // Una Carrera tiene muchos Alumnos inscritos
    public function alumnos()
    {
        return $this->hasMany(Alumno::class, 'carrera_id');
    }
}
