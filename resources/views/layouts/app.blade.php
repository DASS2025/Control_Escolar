<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SAES — @yield('titulo', 'Sistema de Administración Escolar')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; }
        .sidebar { height:100vh; background-color:#1a2b4a; width:250px; position:fixed; top:0; left:0; overflow-y:auto; z-index:1000; }
        .sidebar .nav-link { color:#adb5bd; padding:.5rem 1rem; border-radius:.375rem; margin:2px 8px; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { color:#fff; background-color:#2d4a7a; }
        .sidebar .nav-link i { width:20px; }
        .sidebar-header { padding:1.25rem 1rem; border-bottom:1px solid #2d4a7a; }
        .main-content { margin-left:250px; min-height:100vh; }
        .topbar { background-color:#fff; border-bottom:1px solid #dee2e6; padding:.75rem 1.5rem; }
        .section-title { font-weight:600; color:#6c757d; font-size:.7rem; text-transform:uppercase; letter-spacing:.08em; padding:.75rem 1rem .25rem; }
    </style>
    @stack('estilos')
</head>
<body>
 
<nav class="sidebar d-flex flex-column">
    <div class="sidebar-header text-center">
        <img src="{{ asset('images/univac-logo.jpeg') }}" alt="UNIVAC" class="img-fluid mb-1 rounded-2"
             style="max-width:180px;max-height:140px;object-fit:contain;background-color:#fff;padding:4px;">
        <div><small class="text-secondary">Sistema Escolar</small></div>
    </div>
 
    <div class="flex-grow-1 py-2">
        <ul class="nav flex-column">
 
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                    <i class="bi bi-speedometer2 me-2"></i>Dashboard
                </a>
            </li>
 
            @php $rol = auth()->user()->rol->nombre ?? ''; @endphp
 
            {{-- ADMIN --}}
            @if($rol === 'Admin')
                <div class="section-title">Administración</div>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('usuarios.*') ? 'active' : '' }}" href="{{ route('usuarios.index') }}">
                        <i class="bi bi-people-fill me-2"></i>Usuarios
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('roles.*') ? 'active' : '' }}" href="{{ route('roles.index') }}">
                        <i class="bi bi-shield-lock me-2"></i>Roles
                    </a>
                </li>
                <div class="section-title">Catálogos</div>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('carreras.*') ? 'active' : '' }}" href="{{ route('carreras.index') }}">
                        <i class="bi bi-building me-2"></i>Carreras
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('periodos.*') ? 'active' : '' }}" href="{{ route('periodos.index') }}">
                        <i class="bi bi-calendar-range me-2"></i>Periodos
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('aulas.*') ? 'active' : '' }}" href="{{ route('aulas.index') }}">
                        <i class="bi bi-door-open me-2"></i>Aulas
                    </a>
                </li>
            @endif
 
            {{-- DIRECTOR --}}
            @if($rol === 'Director')
                <div class="section-title">Gestión</div>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('usuarios.*') ? 'active' : '' }}" href="{{ route('usuarios.index') }}">
                        <i class="bi bi-people-fill me-2"></i>Usuarios
                    </a>
                </li>
            @endif
 
            {{-- COORDINADOR --}}
            @if($rol === 'Coordinador de Carrera')
                <div class="section-title">Gestión</div>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('usuarios.*') ? 'active' : '' }}" href="{{ route('usuarios.index') }}">
                        <i class="bi bi-people-fill me-2"></i>Docentes y Alumnos
                    </a>
                </li>
            @endif
 
            {{-- DOCENTE --}}
            @if($rol === 'Docente')
                <div class="section-title">Gestión</div>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('usuarios.*') ? 'active' : '' }}" href="{{ route('usuarios.index') }}">
                        <i class="bi bi-person-plus me-2"></i>Alumnos
                    </a>
                </li>
                <div class="section-title">Mi Docencia</div>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('kardex.*') ? 'active' : '' }}" href="{{ route('kardex.index') }}">
                        <i class="bi bi-card-list me-2"></i>Calificaciones
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('asistencias.*') ? 'active' : '' }}" href="{{ route('asistencias.index') }}">
                        <i class="bi bi-check2-square me-2"></i>Asistencias
                    </a>
                </li>
            @endif
 
            {{-- ADMIN + PERSONAL ADMINISTRATIVO --}}
            @if(in_array($rol, ['Admin', 'Personal Administrativo']))
                <div class="section-title">Académico</div>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('materias.*') ? 'active' : '' }}" href="{{ route('materias.index') }}">
                        <i class="bi bi-book me-2"></i>Materias
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('grupos.*') ? 'active' : '' }}" href="{{ route('grupos.index') }}">
                        <i class="bi bi-collection me-2"></i>Grupos
                    </a>
                </li>
                <div class="section-title">Personas</div>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('alumnos.*') ? 'active' : '' }}" href="{{ route('alumnos.index') }}">
                        <i class="bi bi-person-badge me-2"></i>Alumnos
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('docentes.*') ? 'active' : '' }}" href="{{ route('docentes.index') }}">
                        <i class="bi bi-person-workspace me-2"></i>Docentes
                    </a>
                </li>
                <div class="section-title">Operaciones</div>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('kardex.*') ? 'active' : '' }}" href="{{ route('kardex.index') }}">
                        <i class="bi bi-card-list me-2"></i>Kardex
                    </a>
                </li>
            @endif
 
            {{-- ALUMNO --}}
            @if($rol === 'Alumno')
                <div class="section-title">Mi Expediente</div>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('kardex.mi-kardex') ? 'active' : '' }}" href="{{ route('kardex.mi-kardex') }}">
                        <i class="bi bi-card-list me-2"></i>Mi Kardex
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('asistencias.mis-asistencias') ? 'active' : '' }}" href="{{ route('asistencias.mis-asistencias') }}">
                        <i class="bi bi-check2-square me-2"></i>Mis Asistencias
                    </a>
                </li>
            @endif
 
            {{-- BITÁCORA — siempre visible para todos --}}
            <div class="section-title">Bitácora</div>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('bitacora.mi-actividad') ? 'active' : '' }}" href="{{ route('bitacora.mi-actividad') }}">
                    <i class="bi bi-person-lines-fill me-2"></i>Mi Actividad
                </a>
            </li>
            @if(in_array($rol, ['Admin', 'Director', 'Coordinador de Carrera']))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('bitacora.index') ? 'active' : '' }}" href="{{ route('bitacora.index') }}">
                    <i class="bi bi-display me-2"></i>Monitor del Sistema
                </a>
            </li>
            @endif
 
        </ul>
    </div>
</nav>
 
<div class="main-content">
    <div class="topbar d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-semibold">@yield('titulo', 'Dashboard')</h6>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-primary">{{ $rol }}</span>
            <span class="text-muted small">{{ auth()->user()->correo_institucional }}</span>
            <form method="POST" action="{{ route('logout') }}" class="mb-0">
                @csrf
                <button type="submit" class="btn btn-outline-danger btn-sm">
                    <i class="bi bi-box-arrow-left me-1"></i>Cerrar sesión
                </button>
            </form>
        </div>
    </div>
 
    <div class="px-4 pt-3">
        @if(session('exito'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>{{ session('exito') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
    </div>
 
    <div class="px-4 pb-4">
        @yield('contenido')
    </div>
</div>
 
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
 