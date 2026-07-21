@extends('layouts.app')
@section('titulo', 'Monitor del Sistema')

@section('contenido')
<div class="d-flex justify-content-between align-items-center py-3">
    <div>
        <h5 class="fw-bold mb-0"><i class="bi bi-display me-2 text-primary"></i>Monitor del Sistema</h5>
        <small class="text-muted">
            @switch($rolActual)
                @case('Admin') Mostrando actividad de todos los usuarios. @break
                @case('Director') Mostrando actividad de todos excepto Admins. @break
                @case('Coordinador de Carrera') Mostrando actividad de Docentes y Alumnos. @break
                @case('Docente') Mostrando actividad de tus Alumnos. @break
            @endswitch
        </small>
    </div>
    @if($esAdmin)
    <a href="{{ route('bitacora.exportar', request()->query()) }}"
       class="btn btn-sm btn-outline-success">
        <i class="bi bi-download me-1"></i>Exportar CSV
    </a>
    @endif
</div>

{{-- Filtros --}}
<form method="GET" action="{{ route('bitacora.index') }}" class="card border-0 shadow-sm mb-3">
    <div class="card-body py-3">
        <div class="row g-2 align-items-end">
            <div class="col-md-2">
                <label class="form-label fw-semibold small mb-1">Acción</label>
                <select name="accion" class="form-select form-select-sm">
                    <option value="">Todas</option>
                    @foreach($acciones as $accion)
                        <option value="{{ $accion }}" {{ request('accion') === $accion ? 'selected' : '' }}>
                            {{ $accion }}
                        </option>
                    @endforeach
                </select>
            </div>
            @if($esAdmin)
            <div class="col-md-2">
                <label class="form-label fw-semibold small mb-1">Tabla/Módulo</label>
                <input type="text" name="tabla" class="form-control form-control-sm"
                       value="{{ request('tabla') }}" placeholder="Ej. Alumnos">
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold small mb-1">ID Usuario</label>
                <input type="number" name="usuario_id" class="form-control form-control-sm"
                       value="{{ request('usuario_id') }}" placeholder="Ej. 5" min="1">
            </div>
            @endif
            <div class="col-md-2">
                <label class="form-label fw-semibold small mb-1">Desde</label>
                <input type="date" name="fecha_inicio" class="form-control form-control-sm"
                       value="{{ request('fecha_inicio') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold small mb-1">Hasta</label>
                <input type="date" name="fecha_fin" class="form-control form-control-sm"
                       value="{{ request('fecha_fin') }}">
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-sm btn-primary flex-grow-1">
                    <i class="bi bi-funnel me-1"></i>Filtrar
                </button>
                <a href="{{ route('bitacora.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-x"></i>
                </a>
            </div>
        </div>
    </div>
</form>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover table-sm mb-0">
            <thead class="table-light">
                <tr>
                    <th>Fecha y hora</th>
                    <th>Usuario</th>
                    <th>Rol</th>
                    <th>Acción</th>
                    <th>Módulo</th>
                    <th>Detalles</th>
                </tr>
            </thead>
            <tbody>
                @forelse($registros as $registro)
                <tr>
                    <td class="text-muted small text-nowrap">
                        {{ $registro->created_at->format('d/m/Y H:i:s') }}
                    </td>
                    <td class="small">{{ $registro->usuario->correo_institucional ?? '—' }}</td>
                    <td>
                        <span class="badge bg-secondary rounded-pill small">
                            {{ $registro->usuario->rol->nombre ?? '—' }}
                        </span>
                    </td>
                    <td>
                        @php
                            $badge = match($registro->accion) {
                                'LOGIN'  => 'bg-success',
                                'LOGOUT' => 'bg-secondary',
                                'DELETE' => 'bg-danger',
                                'CREATE' => 'bg-primary',
                                'UPDATE' => 'bg-warning text-dark',
                                default  => 'bg-info text-dark',
                            };
                        @endphp
                        <span class="badge {{ $badge }}">{{ $registro->accion }}</span>
                    </td>
                    <td class="small">{{ $registro->tabla_afectada }}</td>
                    <td class="small text-muted" style="max-width:250px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                        @if($registro->valores_json)
                            <code title="{{ json_encode($registro->valores_json, JSON_UNESCAPED_UNICODE) }}">
                                {{ json_encode($registro->valores_json, JSON_UNESCAPED_UNICODE) }}
                            </code>
                        @else —
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">
                        Sin registros para los filtros seleccionados.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($registros->hasPages())
    <div class="card-footer bg-transparent d-flex justify-content-between align-items-center">
        <small class="text-muted">
            {{ $registros->firstItem() }}–{{ $registros->lastItem() }} de {{ $registros->total() }} registros
        </small>
        {{ $registros->links() }}
    </div>
    @endif
</div>
@endsection
