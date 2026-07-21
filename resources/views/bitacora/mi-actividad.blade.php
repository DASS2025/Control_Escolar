@extends('layouts.app')
@section('titulo', 'Mi Actividad')

@section('contenido')
<div class="d-flex justify-content-between align-items-center py-3">
    <div>
        <h5 class="fw-bold mb-0"><i class="bi bi-person-lines-fill me-2 text-primary"></i>Mi Actividad</h5>
        <small class="text-muted">Historial de tus propias acciones en el sistema.</small>
    </div>
</div>

{{-- Filtros --}}
<form method="GET" action="{{ route('bitacora.mi-actividad') }}" class="card border-0 shadow-sm mb-3">
    <div class="card-body py-3">
        <div class="row g-2 align-items-end">
            <div class="col-md-3">
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
            <div class="col-md-3">
                <label class="form-label fw-semibold small mb-1">Desde</label>
                <input type="date" name="fecha_inicio" class="form-control form-control-sm"
                       value="{{ request('fecha_inicio') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold small mb-1">Hasta</label>
                <input type="date" name="fecha_fin" class="form-control form-control-sm"
                       value="{{ request('fecha_fin') }}">
            </div>
            <div class="col-md-3 d-flex gap-1">
                <button type="submit" class="btn btn-sm btn-primary flex-grow-1">
                    <i class="bi bi-funnel me-1"></i>Filtrar
                </button>
                <a href="{{ route('bitacora.mi-actividad') }}" class="btn btn-sm btn-outline-secondary">
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
                    <td class="small text-muted" style="max-width:280px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
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
                    <td colspan="4" class="text-center text-muted py-4">No tienes actividad registrada.</td>
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
