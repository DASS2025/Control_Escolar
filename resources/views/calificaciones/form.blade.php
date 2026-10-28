@extends('layouts.app')
@section('titulo', $kardex ? 'Editar Calificación' : 'Registrar Calificación')

@section('contenido')
<div class="py-3">
    <a href="{{ route('calificaciones.index') }}" class="text-decoration-none text-muted small">
        <i class="bi bi-arrow-left me-1"></i>Volver a Calificaciones
    </a>
    <h5 class="fw-bold mt-1">{{ $kardex ? 'Editar Calificación' : 'Registrar Calificación' }}</h5>
</div>

<div class="card border-0 shadow-sm" style="max-width:560px">
    <div class="card-body p-4">

        @if($tiposEvaluacion->isEmpty())
            <div class="alert alert-warning small">
                No hay tipos de evaluación. Ejecuta <code>php artisan db:seed --class=TiposEvaluacionSeeder</code>.
            </div>
        @endif

        <form method="POST"
              action="{{ $kardex ? route('calificaciones.update', $kardex) : route('calificaciones.store') }}">
            @csrf
            @if($kardex) @method('PUT') @endif

            @if($kardex)
                {{-- Alumno y grupo no se editan: preservan la unicidad alumno + grupo --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold">Alumno</label>
                    <input type="text" class="form-control" disabled
                           value="{{ $kardex->alumno->matricula ?? '' }} — {{ $kardex->alumno->nombres ?? '' }} {{ $kardex->alumno->apellidos ?? '' }}">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Grupo / Materia</label>
                    <input type="text" class="form-control" disabled
                           value="{{ $kardex->grupo->materia->nombre ?? '—' }} ({{ $kardex->grupo->periodo->codigo ?? '—' }})">
                </div>
            @else
                <div class="mb-3">
                    <label for="alumno_id" class="form-label fw-semibold">Alumno <span class="text-danger">*</span></label>
                    <select id="alumno_id" name="alumno_id" required
                            class="form-select @error('alumno_id') is-invalid @enderror">
                        <option value="">— Seleccionar —</option>
                        @foreach($alumnos as $alumno)
                            <option value="{{ $alumno->id }}" {{ old('alumno_id', request('alumno_id')) == $alumno->id ? 'selected' : '' }}>
                                {{ $alumno->matricula }} — {{ $alumno->apellidos }} {{ $alumno->nombres }}
                            </option>
                        @endforeach
                    </select>
                    @error('alumno_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label for="grupo_id" class="form-label fw-semibold">Grupo / Materia <span class="text-danger">*</span></label>
                    <select id="grupo_id" name="grupo_id" required
                            class="form-select @error('grupo_id') is-invalid @enderror">
                        <option value="">— Seleccionar —</option>
                        @foreach($grupos as $grupo)
                            <option value="{{ $grupo->id }}" {{ old('grupo_id', request('grupo_id')) == $grupo->id ? 'selected' : '' }}>
                                {{ $grupo->materia->nombre ?? '—' }} ({{ $grupo->periodo->codigo ?? '—' }})
                            </option>
                        @endforeach
                    </select>
                    @error('grupo_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            @endif

            <div class="mb-3">
                <label for="tipo_evaluacion_id" class="form-label fw-semibold">Tipo de Evaluación <span class="text-danger">*</span></label>
                <select id="tipo_evaluacion_id" name="tipo_evaluacion_id" required
                        class="form-select @error('tipo_evaluacion_id') is-invalid @enderror">
                    <option value="">— Seleccionar —</option>
                    @foreach($tiposEvaluacion as $tipo)
                        <option value="{{ $tipo->id }}"
                            {{ old('tipo_evaluacion_id', $kardex->tipo_evaluacion_id ?? null) == $tipo->id ? 'selected' : '' }}>
                            {{ $tipo->nombre }}
                        </option>
                    @endforeach
                </select>
                @error('tipo_evaluacion_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-4">
                <label for="calificacion_final" class="form-label fw-semibold">Calificación (0 – 10) <span class="text-danger">*</span></label>
                <input type="number" id="calificacion_final" name="calificacion_final"
                       min="0" max="10" step="0.01" required
                       value="{{ old('calificacion_final', isset($kardex->calificacion_final) ? number_format((float) $kardex->calificacion_final, 2, '.', '') : '') }}"
                       class="form-control @error('calificacion_final') is-invalid @enderror">
                @error('calificacion_final')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <div class="form-text">Acepta decimales (ej. 8.5). Aprobada con 6 o más; el estatus se calcula solo.</div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i>{{ $kardex ? 'Guardar cambios' : 'Registrar' }}
                </button>
                <a href="{{ route('calificaciones.index') }}" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection
