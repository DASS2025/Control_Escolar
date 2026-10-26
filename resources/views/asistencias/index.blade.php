@extends('layouts.app')
@section('titulo', 'Asistencias')

@section('contenido')
<div class="py-3">
    <h5 class="fw-bold mb-0">Asistencias</h5>
    <p class="text-muted small mb-0">Hoy es <strong>{{ $diaHoy }}</strong>, {{ \Carbon\Carbon::today()->isoFormat('D [de] MMMM [de] YYYY') }}</p>
</div>

@if(session('exito'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-1"></i>{{ session('exito') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- GRUPOS DE HOY --}}
<h6 class="fw-semibold mb-2"><i class="bi bi-calendar-check me-1 text-primary"></i>Grupos con clase hoy</h6>
@if($gruposHoy->isEmpty())
    <div class="alert alert-warning mb-4">
        <i class="bi bi-info-circle me-1"></i>No tienes grupos programados para hoy.
    </div>
@else
<div class="row g-3 mb-4">
    @foreach($gruposHoy as $grupo)
    @php
        $horario = $grupo->horariosGrupo->firstWhere('dia_semana', $diaHoy);
    @endphp
    <div class="col-md-6 col-lg-4">
        <div class="card border-0 shadow-sm border-start border-primary border-3 h-100">
            <div class="card-body d-flex flex-column">
                <h6 class="fw-bold mb-1">{{ $grupo->materia->nombre ?? '—' }}</h6>
                <p class="text-muted small mb-1">
                    <span class="badge bg-info text-dark me-1">{{ $grupo->periodo->codigo ?? '—' }}</span>
                    Aula {{ $grupo->aula->numero_identificador ?? '—' }}
                </p>
                @if($horario)
                <p class="text-muted small mb-2">
                    <i class="bi bi-clock me-1"></i>{{ $horario->hora_inicio }} – {{ $horario->hora_fin }}
                </p>
                @endif
                <a href="{{ route('asistencias.tomar', $grupo) }}"
                   class="btn btn-primary btn-sm mt-auto">
                    <i class="bi bi-check2-square me-1"></i>Pasar Lista
                </a>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif

{{-- HISTORIAL RECIENTE --}}
<div class="d-flex justify-content-between align-items-center mb-2">
    <h6 class="fw-semibold mb-0"><i class="bi bi-clock-history me-1 text-secondary"></i>Registros recientes</h6>
</div>
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover mb-0 table-sm">
            <thead class="table-light">
                <tr>
                    <th>Alumno</th>
                    <th>Materia</th>
                    <th>Fecha</th>
                    <th>Estatus</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($asistencias as $asistencia)
                <tr>
                    <td>{{ $asistencia->alumno->nombres ?? '—' }} {{ $asistencia->alumno->apellidos ?? '' }}</td>
                    <td class="text-muted small">{{ $asistencia->grupo->materia->nombre ?? '—' }}</td>
                    <td>{{ $asistencia->fecha }}</td>
                    <td>
                        <span class="badge {{
                            $asistencia->estatus_asistencia === 'Presente' ? 'bg-success' :
                            ($asistencia->estatus_asistencia === 'Justificada' ? 'bg-warning text-dark' : 'bg-danger')
                        }}">{{ $asistencia->estatus_asistencia }}</span>
                    </td>
                    <td class="text-end">
                        <a href="{{ route('asistencias.edit', $asistencia) }}" class="btn btn-sm btn-outline-secondary me-1">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form action="{{ route('asistencias.destroy', $asistencia) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('¿Eliminar este registro?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-muted py-4">Sin registros.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

