<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class KardexInscripcion extends Model
{
    protected $table = 'Kardex_Inscripciones';
    public $timestamps = false;

    // La clave primaria es BIGINT
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    // Escala 0-10; se aprueba con 6 o más.
    public const CALIFICACION_MINIMA      = 0;
    public const CALIFICACION_MAXIMA      = 10;
    public const CALIFICACION_APROBATORIA = 6;

    // Valores de estatus_materia (los mismos que usan las vistas de kardex existentes)
    public const ESTATUS_EN_CURSO  = 'En Curso';
    public const ESTATUS_APROBADA  = 'Aprobada';
    public const ESTATUS_REPROBADA = 'Reprobada';

    protected $fillable = [
        'alumno_id',
        'grupo_id',
        'tipo_evaluacion_id',
        'calificacion_final',
        'estatus_materia',
    ];

    protected $casts = [
        'calificacion_final' => 'decimal:2',
    ];

    // Una inscripción pertenece a un Alumno
    public function alumno()
    {
        return $this->belongsTo(Alumno::class, 'alumno_id');
    }

    // Una inscripción pertenece a un Grupo
    public function grupo()
    {
        return $this->belongsTo(Grupo::class, 'grupo_id');
    }

    // Una inscripción usa un Tipo de Evaluación
    public function tipoEvaluacion()
    {
        return $this->belongsTo(TipoEvaluacion::class, 'tipo_evaluacion_id');
    }

    // ------------------------------------------------------------------
    // Scopes
    // ------------------------------------------------------------------

    /** Materias aprobadas: calificación registrada y >= 6. */
    public function scopeAprobadas(Builder $query): Builder
    {
        return $query->whereNotNull('calificacion_final')
                     ->where('calificacion_final', '>=', self::CALIFICACION_APROBATORIA);
    }

    /** Registros cuyo grupo pertenece al periodo indicado. */
    public function scopeDelPeriodo(Builder $query, int $periodoId): Builder
    {
        return $query->whereHas('grupo', fn (Builder $g) => $g->where('periodo_id', $periodoId));
    }

    // ------------------------------------------------------------------
    // Reglas de negocio
    // ------------------------------------------------------------------

    /**
     * Estatus que corresponde a una calificación:
     * >= 6 aprobada, < 6 reprobada, sin calificación en curso.
     */
    public static function estatusPara(float|string|null $calificacion): string
    {
        if ($calificacion === null || $calificacion === '') {
            return self::ESTATUS_EN_CURSO;
        }

        return (float) $calificacion >= self::CALIFICACION_APROBATORIA
            ? self::ESTATUS_APROBADA
            : self::ESTATUS_REPROBADA;
    }

    /**
     * Promedio simple de calificacion_final de las materias aprobadas de un alumno.
     * Con $periodoId solo considera ese periodo; sin él, todos los periodos.
     * Devuelve null si el alumno no tiene materias aprobadas en el alcance pedido.
     */
    public static function promedioSimple(int $alumnoId, ?int $periodoId = null): ?float
    {
        $promedio = static::query()
            ->where('alumno_id', $alumnoId)
            ->aprobadas()
            ->when($periodoId, fn (Builder $q) => $q->delPeriodo($periodoId))
            ->avg('calificacion_final');

        return $promedio === null ? null : round((float) $promedio, 2);
    }
}
