@extends('layouts.app')
@section('titulo', 'Pasar Lista — ' . ($grupo->materia->nombre ?? 'Grupo'))

@section('contenido')
<div class="py-3">
    <a href="{{ route('asistencias.index') }}" class="text-decoration-none text-muted small">
        <i class="bi bi-arrow-left me-1"></i>Volver a Asistencias
    </a>
    <h5 class="fw-bold mt-1">Pasar Lista</h5>
    <div class="d-flex flex-wrap gap-2 align-items-center">
        <span class="fw-semibold">{{ $grupo->materia->nombre ?? '—' }}</span>
        <span class="badge bg-info text-dark">{{ $grupo->periodo->codigo ?? '—' }}</span>
        <span class="text-muted small">Aula {{ $grupo->aula->numero_identificador ?? '—' }}</span>
        <span class="text-muted small">{{ $inscripciones->count() }} alumno(s)</span>
    </div>
</div>

@if($inscripciones->isEmpty())
    <div class="alert alert-info"><i class="bi bi-info-circle me-1"></i>No hay alumnos inscritos en este grupo.</div>
@else
<form method="POST" action="{{ route('asistencias.guardar-lista', $grupo) }}">
    @csrf

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body p-3 d-flex align-items-center gap-3">
            <label class="fw-semibold mb-0" for="fecha">Fecha de clase</label>
            <input type="date" id="fecha" name="fecha"
                   class="form-control w-auto @error('fecha') is-invalid @enderror"
                   value="{{ old('fecha', $fechaHoy) }}" required>
            @error('fecha')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>

    {{-- Controles masivos --}}
    <div class="mb-3 d-flex gap-2">
        <button type="button" class="btn btn-sm btn-outline-success" onclick="setAll('Presente')">
            <i class="bi bi-check-all me-1"></i>Todos Presentes
        </button>
        <button type="button" class="btn btn-sm btn-outline-danger" onclick="setAll('Ausente')">
            <i class="bi bi-x-lg me-1"></i>Todos Ausentes
        </button>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Matrícula</th>
                        <th>Alumno</th>
                        <th colspan="3" class="text-center">Asistencia</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($inscripciones as $i => $k)
                    @php
                        $alumno     = $k->alumno;
                        $prevEstatus = $yaRegistradas[$alumno->id] ?? null;
                        // Determinar estatus previo o por defecto "Presente"
                        $default = $prevEstatus ?? 'Presente';
                    @endphp
                    <input type="hidden" name="asistencias[{{ $i }}][alumno_id]" value="{{ $alumno->id }}">
                    <tr class="{{ $prevEstatus ? 'table-secondary' : '' }}">
                        <td class="text-muted small">{{ $alumno->matricula ?? '—' }}</td>
                        <td>
                            {{ $alumno->nombres ?? '—' }} {{ $alumno->apellidos ?? '' }}
                            @if($prevEstatus)
                                <span class="badge bg-warning text-dark ms-1" title="Ya registrado hoy">
                                    <i class="bi bi-arrow-clockwise"></i> Actualizará
                                </span>
                            @endif
                        </td>
                        @foreach(['Presente', 'Ausente', 'Justificada'] as $estatus)
                        <td class="text-center" style="width:120px">
                            <div class="form-check d-flex justify-content-center">
                                <input class="form-check-input asistencia-radio"
                                       type="radio"
                                       name="asistencias[{{ $i }}][estatus_asistencia]"
                                       value="{{ $estatus }}"
                                       id="est_{{ $i }}_{{ $loop->index }}"
                                       {{ $default === $estatus ? 'checked' : '' }}
                                       required>
                                <label class="form-check-label ms-1 small
                                    {{ $estatus === 'Presente' ? 'text-success' : ($estatus === 'Ausente' ? 'text-danger' : 'text-warning') }}"
                                       for="est_{{ $i }}_{{ $loop->index }}">
                                    {{ $estatus }}
                                </label>
                            </div>
                        </td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3 d-flex gap-2">
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-floppy me-1"></i>Guardar Lista
        </button>
        <a href="{{ route('asistencias.index') }}" class="btn btn-outline-secondary">Cancelar</a>
    </div>
</form>

@push('scripts')
<script>
function setAll(estatus) {
    document.querySelectorAll('.asistencia-radio[value="' + estatus + '"]')
        .forEach(el => el.checked = true);
}
</script>
@endpush
@endif
@endsection
