@extends('layouts.app')
@section('titulo', 'Calificaciones — ' . ($grupo->materia->nombre ?? 'Grupo'))

@section('contenido')
<div class="py-3">
    <a href="{{ route('kardex.mis-grupos') }}" class="text-decoration-none text-muted small">
        <i class="bi bi-arrow-left me-1"></i>Volver a Mis Grupos
    </a>
    <h5 class="fw-bold mt-1">{{ $grupo->materia->nombre ?? 'Grupo' }}</h5>
    <div class="d-flex gap-2 align-items-center">
        <span class="badge bg-info text-dark">{{ $grupo->periodo->codigo ?? '—' }}</span>
        <span class="text-muted small">{{ $inscripciones->count() }} alumno(s)</span>
    </div>
</div>

@if(session('exito'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-1"></i>{{ session('exito') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if($inscripciones->isEmpty())
    <div class="alert alert-info"><i class="bi bi-info-circle me-1"></i>No hay alumnos inscritos en este grupo.</div>
@else
<form method="POST" action="{{ route('kardex.guardar-grupo', $grupo) }}">
    @csrf
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Matrícula</th>
                        <th>Alumno</th>
                        <th style="min-width:160px">Tipo de Evaluación</th>
                        <th style="min-width:120px">Calificación (0–10)</th>
                        <th style="min-width:140px">Estatus</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($inscripciones as $i => $k)
                    <input type="hidden" name="calificaciones[{{ $i }}][kardex_id]" value="{{ $k->id }}">
                    <tr>
                        <td class="text-muted small">{{ $k->alumno->matricula ?? '—' }}</td>
                        <td>{{ $k->alumno->nombres ?? '—' }} {{ $k->alumno->apellidos ?? '' }}</td>
                        <td>
                            <select name="calificaciones[{{ $i }}][tipo_evaluacion_id]"
                                    class="form-select form-select-sm @error('calificaciones.'.$i.'.tipo_evaluacion_id') is-invalid @enderror"
                                    required>
                                @foreach($tiposEvaluacion as $tipo)
                                    <option value="{{ $tipo->id }}"
                                        {{ old('calificaciones.'.$i.'.tipo_evaluacion_id', $k->tipo_evaluacion_id) == $tipo->id ? 'selected' : '' }}>
                                        {{ $tipo->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <input type="number"
                                   name="calificaciones[{{ $i }}][calificacion_final]"
                                   class="form-control form-control-sm @error('calificaciones.'.$i.'.calificacion_final') is-invalid @enderror"
                                   value="{{ old('calificaciones.'.$i.'.calificacion_final', $k->calificacion_final) }}"
                                   step="0.01" min="0" max="10" placeholder="—">
                        </td>
                        <td>
                            <select name="calificaciones[{{ $i }}][estatus_materia]"
                                    class="form-select form-select-sm"
                                    required>
                                @foreach(['En Curso', 'Aprobada', 'Reprobada'] as $est)
                                    <option value="{{ $est }}"
                                        {{ old('calificaciones.'.$i.'.estatus_materia', $k->estatus_materia) === $est ? 'selected' : '' }}>
                                        {{ $est }}
                                    </option>
                                @endforeach
                            </select>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3 d-flex gap-2">
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-floppy me-1"></i>Guardar Calificaciones
        </button>
        <a href="{{ route('kardex.mis-grupos') }}" class="btn btn-outline-secondary">Cancelar</a>
    </div>
</form>
@endif
@endsection
