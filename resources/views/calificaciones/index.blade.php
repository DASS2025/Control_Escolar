@extends('layouts.app')
@section('titulo', 'Calificaciones y Promedios')

@section('contenido')
<div class="d-flex justify-content-between align-items-center py-3">
    <h5 class="fw-bold mb-0">{{ $esAlumno ? 'Mis Calificaciones' : 'Calificaciones y Promedios' }}</h5>
    @if($puedeEscribir)
        <a href="{{ route('calificaciones.create', array_filter(['alumno_id' => $filtros['alumno_id'], 'grupo_id' => $filtros['grupo_id']])) }}"
           class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i>Registrar calificación
        </a>
    @endif
</div>

{{-- Filtros --}}
<form method="GET" action="{{ route('calificaciones.index') }}" class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <div class="row g-2 align-items-end">
            @unless($esAlumno)
                <div class="col-md-4">
                    <label for="alumno_id" class="form-label small fw-semibold mb-1">Alumno</label>
                    <select id="alumno_id" name="alumno_id" class="form-select form-select-sm">
                        <option value="">Todos</option>
                        @foreach($alumnos as $a)
                            <option value="{{ $a->id }}" {{ $filtros['alumno_id'] == $a->id ? 'selected' : '' }}>
                                {{ $a->matricula }} — {{ $a->apellidos }} {{ $a->nombres }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="grupo_id" class="form-label small fw-semibold mb-1">Grupo</label>
                    <select id="grupo_id" name="grupo_id" class="form-select form-select-sm">
                        <option value="">Todos</option>
                        @foreach($grupos as $g)
                            <option value="{{ $g->id }}" {{ $filtros['grupo_id'] == $g->id ? 'selected' : '' }}>
                                {{ $g->materia->nombre ?? '—' }} ({{ $g->periodo->codigo ?? '—' }})
                            </option>
                        @endforeach
                    </select>
                </div>
            @endunless
            <div class="col-md-2">
                <label for="periodo_id" class="form-label small fw-semibold mb-1">Periodo</label>
                <select id="periodo_id" name="periodo_id" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    @foreach($periodos as $p)
                        <option value="{{ $p->id }}" {{ $filtros['periodo_id'] == $p->id ? 'selected' : '' }}>{{ $p->codigo }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button class="btn btn-sm btn-primary flex-fill"><i class="bi bi-funnel me-1"></i>Filtrar</button>
                <a href="{{ route('calificaciones.index') }}" class="btn btn-sm btn-outline-secondary">Limpiar</a>
            </div>
        </div>
    </div>
</form>

{{-- Promedio (solo cuando hay un alumno en contexto) --}}
@if($alumnoSeleccionado)
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body d-flex flex-wrap justify-content-between align-items-center">
            <div>
                <div class="fw-semibold">{{ $alumnoSeleccionado->nombres }} {{ $alumnoSeleccionado->apellidos }}
                    <span class="text-muted small">— {{ $alumnoSeleccionado->matricula }}</span></div>
                <div class="text-muted small">
                    Promedio de materias aprobadas ·
                    {{ $filtros['periodo_id'] ? 'Periodo ' . ($periodos->firstWhere('id', $filtros['periodo_id'])->codigo ?? '') : 'Todos los periodos' }}
                </div>
            </div>
            <div class="text-end">
                @if($promedio !== null)
                    <div class="fs-3 fw-bold text-success">{{ number_format($promedio, 2) }}</div>
                    <div class="text-muted small">{{ $aprobadas }} {{ $aprobadas === 1 ? 'materia aprobada' : 'materias aprobadas' }}</div>
                @else
                    <div class="text-muted">Sin materias aprobadas en este alcance</div>
                @endif
            </div>
        </div>
    </div>
@endif

{{-- Listado --}}
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    @unless($esAlumno)<th>Alumno</th>@endunless
                    <th>Materia</th>
                    <th>Periodo</th>
                    <th>Evaluación</th>
                    <th>Calificación</th>
                    <th>Estatus</th>
                    @if($puedeEscribir)<th class="text-end">Acciones</th>@endif
                </tr>
            </thead>
            <tbody>
                @forelse($registros as $k)
                    <tr>
                        @unless($esAlumno)
                            <td>
                                <a href="{{ route('calificaciones.promedio', ['alumno' => $k->alumno_id, 'periodo_id' => $filtros['periodo_id']]) }}"
                                   class="text-decoration-none">
                                    {{ $k->alumno->apellidos ?? '—' }} {{ $k->alumno->nombres ?? '' }}
                                </a>
                                <div class="text-muted small">{{ $k->alumno->matricula ?? '' }}</div>
                            </td>
                        @endunless
                        <td class="fw-semibold">{{ $k->grupo->materia->nombre ?? '—' }}</td>
                        <td><span class="badge bg-info text-dark">{{ $k->grupo->periodo->codigo ?? '—' }}</span></td>
                        <td>{{ $k->tipoEvaluacion->nombre ?? '—' }}</td>
                        <td>
                            @if($k->calificacion_final !== null)
                                <span class="fw-bold {{ $k->calificacion_final >= 6 ? 'text-success' : 'text-danger' }}">
                                    {{ number_format((float) $k->calificacion_final, 2) }}
                                </span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge {{
                                $k->estatus_materia === 'Aprobada'  ? 'bg-success' :
                                ($k->estatus_materia === 'Reprobada' ? 'bg-danger'  : 'bg-secondary')
                            }}">{{ $k->estatus_materia }}</span>
                        </td>
                        @if($puedeEscribir)
                            <td class="text-end">
                                @if($gruposEditables === null || in_array($k->grupo_id, $gruposEditables))
                                    <a href="{{ route('calificaciones.edit', $k) }}" class="btn btn-sm btn-outline-secondary me-1"
                                       title="{{ $k->calificacion_final === null ? 'Calificar' : 'Editar' }}">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @if($k->calificacion_final !== null)
                                        <form action="{{ route('calificaciones.destroy', $k) }}" method="POST" class="d-inline"
                                              onsubmit="return confirm('¿Anular esta calificación? El alumno quedará En Curso.')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger" title="Anular calificación">
                                                <i class="bi bi-x-circle"></i>
                                            </button>
                                        </form>
                                    @endif
                                @endif
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">Sin calificaciones para los filtros seleccionados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($registros->hasPages())
        <div class="card-footer bg-white border-0">{{ $registros->links() }}</div>
    @endif
</div>
@endsection
