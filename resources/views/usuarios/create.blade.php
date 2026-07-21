@extends('layouts.app')
@section('titulo', 'Carreras')
 
@section('contenido')
<div class="d-flex justify-content-between align-items-center py-3">
    <h5 class="fw-bold mb-0">Carreras</h5>
    <a href="{{ route('carreras.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i>Nueva Carrera
    </a>
</div>
 
{{-- Búsqueda --}}
<form method="GET" action="{{ route('carreras.index') }}" class="mb-3">
    <div class="input-group input-group-sm" style="max-width:380px">
        <input type="text" name="buscar" class="form-control"
               placeholder="Buscar por nombre o clave…"
               value="{{ request('buscar') }}">
        <button class="btn btn-outline-primary" type="submit">
            <i class="bi bi-search"></i>
        </button>
        @if(request('buscar'))
        <a href="{{ route('carreras.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-x"></i>
        </a>
        @endif
    </div>
</form>
 
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Clave</th>
                    <th>Nombre</th>
                    <th class="text-center">Créditos</th>
                    <th class="text-center">Materias</th>
                    <th class="text-center">Alumnos</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($carreras as $carrera)
                <tr>
                    <td class="fw-semibold text-nowrap">{{ $carrera->clave_oficial }}</td>
                    <td>{{ $carrera->nombre }}</td>
                    <td class="text-center">{{ $carrera->total_creditos }}</td>
                    <td class="text-center">
                        <span class="badge bg-primary rounded-pill">{{ $carrera->materias_count }}</span>
                    </td>
                    <td class="text-center">
                        <span class="badge {{ $carrera->alumnos_count > 0 ? 'bg-success' : 'bg-secondary' }} rounded-pill">
                            {{ $carrera->alumnos_count }}
                        </span>
                    </td>
                    <td class="text-end">
                        <a href="{{ route('carreras.edit', $carrera) }}"
                           class="btn btn-sm btn-outline-secondary me-1" title="Editar">
                            <i class="bi bi-pencil"></i>
                        </a>
                        @if($carrera->alumnos_count === 0)
                        <form action="{{ route('carreras.destroy', $carrera) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('¿Eliminar «{{ $carrera->nombre }}»? Esta acción no se puede deshacer.')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" title="Eliminar">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                        @else
                        <button class="btn btn-sm btn-outline-danger" disabled
                                title="No eliminable: tiene {{ $carrera->alumnos_count }} alumno(s) inscrito(s)">
                            <i class="bi bi-trash"></i>
                        </button>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">
                        @if(request('buscar'))
                            No se encontraron carreras con «{{ request('buscar') }}».
                            <a href="{{ route('carreras.index') }}">Ver todas</a>
                        @else
                            Sin carreras registradas.
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($carreras->hasPages())
    <div class="card-footer bg-transparent d-flex justify-content-between align-items-center">
        <small class="text-muted">
            {{ $carreras->firstItem() }}–{{ $carreras->lastItem() }} de {{ $carreras->total() }} carreras
        </small>
        {{ $carreras->links() }}
    </div>
    @endif
</div>
@endsection
 