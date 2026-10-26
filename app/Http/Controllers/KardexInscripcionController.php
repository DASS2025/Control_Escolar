<?php

namespace App\Http\Controllers;

use App\Models\KardexInscripcion;
use App\Models\Alumno;
use App\Models\Grupo;
use App\Models\TipoEvaluacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KardexInscripcionController extends Controller
{
    public function index()
    {
        $inscripciones = KardexInscripcion::with(['alumno', 'grupo.materia', 'tipoEvaluacion'])->get();
        return view('kardex.index', compact('inscripciones'));
    }

    public function create()
    {
        $alumnos         = Alumno::all();
        $grupos          = Grupo::with('materia')->get();
        $tiposEvaluacion = TipoEvaluacion::all();

        return view('kardex.create', compact('alumnos', 'grupos', 'tiposEvaluacion'));
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'alumno_id'          => ['required', 'exists:Alumnos,id'],
            'grupo_id'           => ['required', 'exists:Grupos,id'],
            'tipo_evaluacion_id' => ['required', 'exists:Tipos_Evaluacion,id'],
            'calificacion_final' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'estatus_materia'    => ['nullable', 'string', 'max:50'],
        ]);

        KardexInscripcion::create($datos);

        return redirect()->route('kardex.index')->with('exito', 'Inscripción registrada correctamente.');
    }

    public function show(KardexInscripcion $kardex)
    {
        $kardex->load(['alumno', 'grupo.materia', 'grupo.periodo', 'tipoEvaluacion']);
        return view('kardex.show', compact('kardex'));
    }

    public function edit(KardexInscripcion $kardex)
    {
        $alumnos         = Alumno::all();
        $grupos          = Grupo::with('materia')->get();
        $tiposEvaluacion = TipoEvaluacion::all();

        return view('kardex.edit', compact('kardex', 'alumnos', 'grupos', 'tiposEvaluacion'));
    }

    public function update(Request $request, KardexInscripcion $kardex)
    {
        $datos = $request->validate([
            'tipo_evaluacion_id' => ['required', 'exists:Tipos_Evaluacion,id'],
            'calificacion_final' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'estatus_materia'    => ['nullable', 'string', 'max:50'],
        ]);

        $kardex->update($datos);

        return redirect()->route('kardex.index')->with('exito', 'Calificación actualizada correctamente.');
    }

    public function destroy(KardexInscripcion $kardex)
    {
        $kardex->delete();
        return redirect()->route('kardex.index')->with('exito', 'Inscripción eliminada correctamente.');
    }

    /**
     * Vista del kardex personal del alumno autenticado.
     */
    public function miKardex()
    {
        $alumno = Auth::user()->alumno;
        $inscripciones = $alumno->kardexInscripciones()->with(['grupo.materia', 'grupo.periodo', 'tipoEvaluacion'])->get();

        return view('kardex.mi-kardex', compact('alumno', 'inscripciones'));
    }

    /**
     * Lista de grupos asignados al docente autenticado (para captura de calificaciones).
     */
    public function misGrupos()
    {
        $docente = Auth::user()->docente;
        $grupos  = Grupo::with(['materia', 'periodo', 'kardexInscripciones'])
                        ->where('docente_id', $docente->id)
                        ->get();

        return view('kardex.mis-grupos', compact('grupos'));
    }

    /**
     * Muestra alumnos del grupo con campos editables de calificación.
     */
    public function calificacionesGrupo(Grupo $grupo)
    {
        $docente = Auth::user()->docente;

        // Verificar que el grupo pertenezca al docente autenticado
        abort_unless($grupo->docente_id === $docente->id, 403);

        $grupo->load(['materia', 'periodo']);
        $tiposEvaluacion = TipoEvaluacion::all();
        $inscripciones   = KardexInscripcion::with('alumno')
                            ->where('grupo_id', $grupo->id)
                            ->get();

        return view('kardex.calificaciones-grupo', compact('grupo', 'inscripciones', 'tiposEvaluacion'));
    }

    /**
     * Guarda calificaciones de todos los alumnos del grupo en un solo envío.
     */
    public function guardarCalificaciones(Request $request, Grupo $grupo)
    {
        $docente = Auth::user()->docente;
        abort_unless($grupo->docente_id === $docente->id, 403);

        $request->validate([
            'calificaciones'                       => ['required', 'array'],
            'calificaciones.*.kardex_id'           => ['required', 'exists:Kardex_Inscripciones,id'],
            'calificaciones.*.tipo_evaluacion_id'  => ['required', 'exists:Tipos_Evaluacion,id'],
            'calificaciones.*.calificacion_final'  => ['nullable', 'numeric', 'min:0', 'max:10'],
            'calificaciones.*.estatus_materia'     => ['required', 'string', 'max:50'],
        ]);

        foreach ($request->calificaciones as $item) {
            KardexInscripcion::where('id', $item['kardex_id'])
                ->where('grupo_id', $grupo->id)   // doble verificación de pertenencia
                ->update([
                    'tipo_evaluacion_id' => $item['tipo_evaluacion_id'],
                    'calificacion_final' => $item['calificacion_final'] !== '' ? $item['calificacion_final'] : null,
                    'estatus_materia'    => $item['estatus_materia'],
                ]);
        }

        return redirect()->route('kardex.grupo', $grupo)
                         ->with('exito', 'Calificaciones guardadas correctamente.');
    }
}
