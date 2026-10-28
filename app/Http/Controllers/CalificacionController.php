<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Grupo;
use App\Models\KardexInscripcion;
use App\Models\Periodo;
use App\Models\TipoEvaluacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Módulo de Calificaciones y Promedios.
 *
 * Permisos (se aplican en routes/web.php con el middleware 'rol'):
 *  - Escritura (create/store/edit/update/destroy): Docente y Coordinador de Carrera.
 *      · El Docente solo puede calificar grupos que tiene asignados.
 *      · El Coordinador actúa como respaldo y puede calificar cualquier grupo.
 *  - Lectura (index/promedioAlumno): Docente, Coordinador y Alumno.
 *      · El Alumno solo ve su propia información.
 */
class CalificacionController extends Controller
{
    // ------------------------------------------------------------------
    // Lectura
    // ------------------------------------------------------------------

    /**
     * Lista calificaciones por alumno o por grupo, con filtro por periodo.
     * Si hay un alumno seleccionado (o el usuario es Alumno) muestra su promedio.
     */
    public function index(Request $request)
    {
        return $this->consulta($request, $request->integer('alumno_id') ?: null);
    }

    /**
     * Promedio simple de calificacion_final de las materias aprobadas del alumno.
     * ?periodo_id=N limita el cálculo a ese periodo; sin él se promedian todos.
     * Responde JSON si el cliente lo pide (Accept: application/json), vista en otro caso.
     */
    public function promedioAlumno(Request $request, $alumnoId)
    {
        $alumnoId = (int) $alumnoId;

        if ($this->rol() === 'Alumno') {
            abort_unless((int) Auth::user()->alumno?->id === $alumnoId, 403, 'Solo puedes consultar tu propia información.');
        }

        $alumno    = Alumno::findOrFail($alumnoId);
        $periodoId = $request->integer('periodo_id') ?: null;

        if ($request->wantsJson()) {
            return response()->json([
                'alumno_id'         => $alumno->id,
                'periodo_id'        => $periodoId,
                'promedio'          => KardexInscripcion::promedioSimple($alumno->id, $periodoId),
                'materias_aprobadas' => KardexInscripcion::where('alumno_id', $alumno->id)
                    ->aprobadas()
                    ->when($periodoId, fn ($q) => $q->delPeriodo($periodoId))
                    ->count(),
            ]);
        }

        return $this->consulta($request, $alumno->id);
    }

    // ------------------------------------------------------------------
    // Escritura (Docente y Coordinador de Carrera)
    // ------------------------------------------------------------------

    public function create()
    {
        return view('calificaciones.form', [
            'kardex'          => null,
            'alumnos'         => Alumno::orderBy('apellidos')->orderBy('nombres')->get(),
            'grupos'          => $this->gruposEscribibles(),
            'tiposEvaluacion' => TipoEvaluacion::orderBy('id')->get(),
        ]);
    }

    /**
     * Registra la calificación de un alumno en un grupo.
     *
     * Un alumno tiene un solo kardex por grupo:
     *  - Si ya existe el registro (inscripción) pero aún sin calificación, se completa.
     *  - Si ya tiene calificación, se rechaza como duplicado (para cambiarla se usa edit/update).
     *  - Si no existe, se crea.
     */
    public function store(Request $request)
    {
        $datos = $request->validate([
            'alumno_id'          => ['required', 'integer', 'exists:Alumnos,id'],
            'grupo_id'           => ['required', 'integer', 'exists:Grupos,id'],
            'tipo_evaluacion_id' => ['required', 'integer', 'exists:Tipos_Evaluacion,id'],
            'calificacion_final' => ['required', 'numeric', 'between:0,10', 'decimal:0,2'],
        ], $this->mensajes());

        $this->autorizarGrupo((int) $datos['grupo_id']);

        DB::transaction(function () use ($datos) {
            $existente = KardexInscripcion::where('alumno_id', $datos['alumno_id'])
                ->where('grupo_id', $datos['grupo_id'])
                ->lockForUpdate()
                ->first();

            if ($existente && $existente->calificacion_final !== null) {
                throw ValidationException::withMessages([
                    'alumno_id' => 'Este alumno ya tiene calificación en el grupo. Edita el registro existente en lugar de crear otro.',
                ]);
            }

            $atributos = [
                'tipo_evaluacion_id' => $datos['tipo_evaluacion_id'],
                'calificacion_final' => $datos['calificacion_final'],
                'estatus_materia'    => KardexInscripcion::estatusPara($datos['calificacion_final']),
            ];

            $existente
                ? $existente->update($atributos)
                : KardexInscripcion::create($atributos + [
                    'alumno_id' => $datos['alumno_id'],
                    'grupo_id'  => $datos['grupo_id'],
                ]);
        });

        return redirect()
            ->route('calificaciones.index', ['alumno_id' => $datos['alumno_id']])
            ->with('exito', 'Calificación registrada correctamente.');
    }

    public function edit(KardexInscripcion $kardex)
    {
        $this->autorizarGrupo($kardex->grupo_id);

        $kardex->load(['alumno', 'grupo.materia', 'grupo.periodo']);

        return view('calificaciones.form', [
            'kardex'          => $kardex,
            'alumnos'         => collect(),
            'grupos'          => collect(),
            'tiposEvaluacion' => TipoEvaluacion::orderBy('id')->get(),
        ]);
    }

    /** Solo cambian tipo de evaluación y calificación; alumno y grupo no se editan. */
    public function update(Request $request, KardexInscripcion $kardex)
    {
        $this->autorizarGrupo($kardex->grupo_id);

        $datos = $request->validate([
            'tipo_evaluacion_id' => ['required', 'integer', 'exists:Tipos_Evaluacion,id'],
            'calificacion_final' => ['required', 'numeric', 'between:0,10', 'decimal:0,2'],
        ], $this->mensajes());

        $kardex->update($datos + [
            'estatus_materia' => KardexInscripcion::estatusPara($datos['calificacion_final']),
        ]);

        return redirect()
            ->route('calificaciones.index', ['alumno_id' => $kardex->alumno_id])
            ->with('exito', 'Calificación actualizada correctamente.');
    }

    /**
     * Anula la calificación: deja el registro (la inscripción) sin calificación y "En Curso".
     * No borra la fila del kardex para no perder la inscripción del alumno al grupo.
     */
    public function destroy(KardexInscripcion $kardex)
    {
        $this->autorizarGrupo($kardex->grupo_id);

        $kardex->update([
            'calificacion_final' => null,
            'estatus_materia'    => KardexInscripcion::ESTATUS_EN_CURSO,
        ]);

        return redirect()
            ->route('calificaciones.index', ['alumno_id' => $kardex->alumno_id])
            ->with('exito', 'Calificación anulada; el alumno queda En Curso en ese grupo.');
    }

    // ------------------------------------------------------------------
    // Internos
    // ------------------------------------------------------------------

    /** Construye el listado filtrado + promedio; lo comparten index() y promedioAlumno(). */
    private function consulta(Request $request, ?int $alumnoId)
    {
        $rol       = $this->rol();
        $periodoId = $request->integer('periodo_id') ?: null;
        $grupoId   = $request->integer('grupo_id') ?: null;

        // El Alumno solo puede ver lo suyo, sin importar lo que mande en la URL.
        if ($rol === 'Alumno') {
            $propio = Auth::user()->alumno;
            abort_unless($propio, 403, 'Tu usuario no está asociado a un alumno.');
            $alumnoId = $propio->id;
            $grupoId  = null;
        }

        $registros = KardexInscripcion::with(['alumno', 'grupo.materia', 'grupo.periodo', 'tipoEvaluacion'])
            ->when($alumnoId, fn ($q) => $q->where('alumno_id', $alumnoId))
            ->when($grupoId, fn ($q) => $q->where('grupo_id', $grupoId))
            ->when($periodoId, fn ($q) => $q->delPeriodo($periodoId))
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        $promedio = null;
        $aprobadas = 0;
        $alumnoSeleccionado = null;

        if ($alumnoId) {
            $alumnoSeleccionado = Alumno::find($alumnoId);
            $promedio  = KardexInscripcion::promedioSimple($alumnoId, $periodoId);
            $aprobadas = KardexInscripcion::where('alumno_id', $alumnoId)
                ->aprobadas()
                ->when($periodoId, fn ($q) => $q->delPeriodo($periodoId))
                ->count();
        }

        $puedeEscribir = in_array($rol, ['Docente', 'Coordinador de Carrera'], true);

        // null = puede editar cualquier grupo (Coordinador); array = solo esos grupos (Docente)
        $gruposEditables = null;
        if ($rol === 'Docente') {
            $gruposEditables = $this->gruposEscribibles()->pluck('id')->all();
        }

        return view('calificaciones.index', [
            'registros'          => $registros,
            'periodos'           => Periodo::orderByDesc('id')->get(),
            'alumnos'            => $rol === 'Alumno' ? collect() : Alumno::orderBy('apellidos')->orderBy('nombres')->get(),
            'grupos'             => $rol === 'Alumno' ? collect() : Grupo::with(['materia', 'periodo'])->orderByDesc('id')->get(),
            'filtros'            => ['alumno_id' => $alumnoId, 'grupo_id' => $grupoId, 'periodo_id' => $periodoId],
            'alumnoSeleccionado' => $alumnoSeleccionado,
            'promedio'           => $promedio,
            'aprobadas'          => $aprobadas,
            'esAlumno'           => $rol === 'Alumno',
            'puedeEscribir'      => $puedeEscribir,
            'gruposEditables'    => $gruposEditables,
        ]);
    }

    /** Grupos en los que el usuario puede escribir calificaciones. */
    private function gruposEscribibles()
    {
        $query = Grupo::with(['materia', 'periodo'])->orderByDesc('id');

        if ($this->rol() === 'Docente') {
            $docente = Auth::user()->docente;
            abort_unless($docente, 403, 'Tu usuario no está asociado a un docente.');
            $query->where('docente_id', $docente->id);
        }

        return $query->get();
    }

    /** El Docente solo califica sus grupos; el Coordinador (respaldo) cualquiera. */
    private function autorizarGrupo(int $grupoId): void
    {
        if ($this->rol() !== 'Docente') {
            return;
        }

        $docente = Auth::user()->docente;

        abort_unless(
            $docente && Grupo::where('id', $grupoId)->where('docente_id', $docente->id)->exists(),
            403,
            'Solo puedes registrar calificaciones de los grupos que tienes asignados.'
        );
    }

    private function rol(): string
    {
        return Auth::user()->rol->nombre ?? '';
    }

    private function mensajes(): array
    {
        return [
            'calificacion_final.required' => 'La calificación es obligatoria.',
            'calificacion_final.numeric'  => 'La calificación debe ser un número.',
            'calificacion_final.between'  => 'La calificación debe estar entre 0 y 10.',
            'calificacion_final.decimal'  => 'La calificación admite máximo 2 decimales (ej. 8.5).',
        ];
    }
}
