@extends('layouts.app')
@section('titulo', 'Mis Grupos — Calificaciones')

@section('contenido')
<div class="py-3">
    <h5 class="fw-bold mb-0">Mis Grupos</h5>
    <p class="text-muted small mb-0">Selecciona un grupo para capturar o actualizar calificaciones.</p>
</div>

@if(session('exito'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-1"></i>{{ session('exito') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if($grupos->isEmpty())
    <div class="alert alert-info"><i class="bi bi-info-circle me-1"></i>No tienes grupos asignados actualmente.</div>
@else
<div class="row g-3">
    @foreach($grupos as $grupo)
    <div class="col-md-6 col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex flex-column">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h6 class="fw-bold mb-0">{{ $grupo->materia->nombre ?? '—' }}</h6>
                    <span class="badge bg-info text-dark">{{ $grupo->periodo->codigo ?? '—' }}</span>
                </div>
                <p class="text-muted small mb-1">
                    <i class="bi bi-people me-1"></i>{{ $grupo->kardexInscripciones->count() }} alumno(s) inscritos
                </p>
                <p class="text-muted small mb-3">
                    <i class="bi bi-building me-1"></i>Aula {{ $grupo->aula->numero_identificador ?? '—' }}
                </p>
                <a href="{{ route('kardex.grupo', $grupo) }}"
                   class="btn btn-primary btn-sm mt-auto">
                    <i class="bi bi-pencil-square me-1"></i>Capturar Calificaciones
                </a>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif
@endsection
