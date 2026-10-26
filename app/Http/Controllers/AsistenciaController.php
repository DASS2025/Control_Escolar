<?php

namespace App\Http\Controllers;

use App\Models\Asistencia;
use App\Models\Alumno;
use App\Models\Grupo;
use App\Models\KardexInscripcion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AsistenciaController extends Controller
{
    /**
     * Muestra los grupos del docente que tienen clase hoy,
     * permitiéndole elegir a cuál pasarle lista.
     */
    public function index()
    {
        $docente = Auth::user()->docente;

        $diasEsp = [
            0 => 'Domingo',
            1 => 'Lunes',
            2 => 'Martes',
            3 => 'Miércoles',
            4 => 'Jueves',
            5 => 'Viernes',
            6 => 'Sábado',
        ];

        $hoy      = Carbon::today();
        $diaHoy   = $diasEsp[$hoy->dayOfWeek];
        $fechaHoy = $hoy->toDateString();

        // Grupos del docente que tienen horario asignado para hoy
        $gruposHoy = Grupo::with(['materia', 'periodo', 'aula', 'horariosGrupo'])
            ->where('docente_id', $docente->id)
            ->whereHas('horariosGrupo', fn($q) => $q->where('dia_semana', $diaHoy))
            ->get();

        // Todos los grupos del docente (para consulta histórica)
        $todosLosGrupos = Grupo::with(['materia', 'periodo'])
            ->where('docente_id', $docente->id)
            ->get();

        // Asistencias recientes del docente (últimas 50)
        $asistencias = Asistencia::with(['alumno', 'grupo.materia'])
            ->whereIn('grupo_id', $todosLosGrupos->pluck('id'))
            ->orderByDesc('fecha')
            ->limit(50)
            ->get();

        return view('asistencias.index', compact(
            'gruposHoy', 'diaHoy', 'fechaHoy', 'asistencias', 'todosLosGrupos'
        ));
    }

    /**
     * Muestra el checklist de alumnos del grupo seleccionado para pasar lista.
     */
    public function tomarLista(Grupo $grupo)
    {
        $docente = Auth::user()->docente;
        abort_unless($grupo->docente_id === $docente->id, 403);

        $grupo->load(['materia', 'periodo', 'aula']);

        $inscripciones = KardexInscripcion::with('alumno')
            ->where('grupo_id', $grupo->id)
            ->get();

        $fechaHoy = Carbon::today()->toDateString();

        // Pre-cargar asistencias ya existentes para hoy (para no duplicar)
        $yaRegistradas = Asistencia::where('grupo_id', $grupo->id)
            ->where('fecha', $fechaHoy)
            ->pluck('estatus_asistencia', 'alumno_id');

        return view('asistencias.tomar-lista', compact(
            'grupo', 'inscripciones', 'fechaHoy', 'yaRegistradas'
        ));
    }

    /**
     * Guarda la lista completa de asistencia de un grupo en un solo envío.
     * Hace upsert: crea o actualiza según (alumno_id, grupo_id, fecha).
     */
    public function guardarLista(Request $request, Grupo $grupo)
    {
        $docente = Auth::user()->docente;
        abort_unless($grupo->docente_id === $docente->id, 403);

        $request->validate([
            'fecha'                          => ['required', 'date'],
            'asistencias'                    => ['required', 'array'],
            'asistencias.*.alumno_id'        => ['required', 'exists:Alumnos,id'],
            'asistencias.*.estatus_asistencia' => ['required', 'in:Presente,Ausente,Justificada'],
        ]);

        foreach ($request->asistencias as $item) {
            Asistencia::updateOrCreate(
                [
                    'alumno_id' => $item['alumno_id'],
                    'grupo_id'  => $grupo->id,
                    'fecha'     => $request->fecha,
                ],
                ['estatus_asistencia' => $item['estatus_asistencia']]
            );
        }

        return redirect()->route('asistencias.index')
                         ->with('exito', 'Lista de asistencia guardada correctamente.');
    }

    public function edit(Asistencia $asistencia)
    {
        $docente = Auth::user()->docente;
        abort_unless($asistencia->grupo->docente_id === $docente->id, 403);

        $grupos = Grupo::with('materia')->where('docente_id', $docente->id)->get();

        return view('asistencias.edit', compact('asistencia', 'grupos'));
    }

    public function update(Request $request, Asistencia $asistencia)
    {
        $docente = Auth::user()->docente;
        abort_unless($asistencia->grupo->docente_id === $docente->id, 403);

        $datos = $request->validate([
            'fecha'              => ['required', 'date'],
            'estatus_asistencia' => ['required', 'in:Presente,Ausente,Justificada'],
        ]);

        $asistencia->update($datos);

        return redirect()->route('asistencias.index')->with('exito', 'Asistencia actualizada correctamente.');
    }

    public function destroy(Asistencia $asistencia)
    {
        $docente = Auth::user()->docente;
        abort_unless($asistencia->grupo->docente_id === $docente->id, 403);

        $asistencia->delete();
        return redirect()->route('asistencias.index')->with('exito', 'Asistencia eliminada correctamente.');
    }

    /**
     * Listado de asistencias del alumno autenticado.
     */
    public function misAsistencias()
    {
        $alumno      = Auth::user()->alumno;
        $asistencias = $alumno->asistencias()->with(['grupo.materia'])->orderByDesc('fecha')->get();

        return view('asistencias.mis-asistencias', compact('alumno', 'asistencias'));
    }
}
