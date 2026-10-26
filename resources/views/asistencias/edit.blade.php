@extends('layouts.app')
@section('titulo', 'Editar Asistencia')

@section('contenido')
<div class="py-3">
    <a href="{{ route('asistencias.index') }}" class="text-decoration-none text-muted small">
        <i class="bi bi-arrow-left me-1"></i>Volver a Asistencias
    </a>
    <h5 class="fw-bold mt-1">Editar Asistencia</h5>
    <p class="text-muted small mb-0">
        Alumno: <strong>{{ $asistencia->alumno->nombres ?? '—' }} {{ $asistencia->alumno->apellidos ?? '' }}</strong>
        &nbsp;|&nbsp;
        Grupo: <strong>{{ $asistencia->grupo->materia->nombre ?? '—' }}</strong>
    </p>
</div>

<div class="card border-0 shadow-sm" style="max-width:400px">
    <div class="card-body p-4">
        <form method="POST" action="{{ route('asistencias.update', $asistencia) }}">
            @csrf @method('PUT')
            <div class="mb-3">
                <label for="fecha" class="form-label fw-semibold">Fecha <span class="text-danger">*</span></label>
                <input type="date" id="fecha" name="fecha"
                       class="form-control @error('fecha') is-invalid @enderror"
                       value="{{ old('fecha', $asistencia->fecha) }}" required>
                @error('fecha')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-4">
                <label for="estatus_asistencia" class="form-label fw-semibold">Estatus <span class="text-danger">*</span></label>
                <select id="estatus_asistencia" name="estatus_asistencia" class="form-select" required>
                    @foreach(['Presente', 'Ausente', 'Justificada'] as $est)
                        <option value="{{ $est }}"
                            {{ old('estatus_asistencia', $asistencia->estatus_asistencia) === $est ? 'selected' : '' }}>
                            {{ $est }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Actualizar</button>
                <a href="{{ route('asistencias.index') }}" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection
