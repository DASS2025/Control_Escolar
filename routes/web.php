<?php
 
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RolController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\AulaController;
use App\Http\Controllers\PeriodoController;
use App\Http\Controllers\CarreraController;
use App\Http\Controllers\MateriaController;
use App\Http\Controllers\AlumnoController;
use App\Http\Controllers\DocenteController;
use App\Http\Controllers\GrupoController;
use App\Http\Controllers\KardexInscripcionController;
use App\Http\Controllers\AsistenciaController;
use App\Http\Controllers\BitacoraAuditoriaController;
 
// ==========================================
// Rutas públicas
// ==========================================
Route::get('/login',  [AuthController::class, 'mostrarLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout',[AuthController::class, 'logout'])->name('logout')->middleware('auth');
Route::get('/', fn() => redirect()->route('login'));
 
// ==========================================
// Rutas protegidas
// ==========================================
Route::middleware(['auth'])->group(function () {
 
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
 
    // ------------------------------------------
    // BITÁCORA — "Mi Actividad" (todos los roles)
    // ------------------------------------------
    Route::get('/mi-actividad', [BitacoraAuditoriaController::class, 'miActividad'])
        ->name('bitacora.mi-actividad');
 
    // ------------------------------------------
    // BITÁCORA — Monitor jerárquico
    // Solo Admin, Director y Coordinador de Carrera
    // Docentes, Alumnos y Contadores: sin acceso
    // ------------------------------------------
    Route::middleware(['rol:Admin,Director,Coordinador de Carrera'])->group(function () {
        Route::get('/bitacora', [BitacoraAuditoriaController::class, 'monitor'])
            ->name('bitacora.index');
    });
 
    // Exportar CSV: solo Admin y Director
    Route::middleware(['rol:Admin,Director'])->group(function () {
        Route::get('/bitacora/exportar', [BitacoraAuditoriaController::class, 'exportar'])
            ->name('bitacora.exportar');
    });
 
    // ------------------------------------------
    // USUARIOS — Admin, Director, Coordinador, Docente
    // La lógica interna del controller limita qué roles puede crear cada uno
    // ------------------------------------------
    Route::middleware(['rol:Admin,Director,Coordinador de Carrera,Docente'])->group(function () {
        Route::resource('usuarios', UsuarioController::class)->except(['show']);
    });
 
    // ------------------------------------------
    // Solo Admin: roles, catálogos base
    // ------------------------------------------
    Route::middleware(['rol:Admin'])->group(function () {
        Route::resource('roles',    RolController::class)->except(['show']);
        Route::resource('aulas',    AulaController::class)->except(['show']);
        Route::resource('periodos', PeriodoController::class)->except(['show']);
        Route::resource('carreras', CarreraController::class)->except(['show']);
    });
 
    // ------------------------------------------
    // Admin y Personal Administrativo
    // ------------------------------------------
    Route::middleware(['rol:Admin,Personal Administrativo'])->group(function () {
        Route::resource('materias', MateriaController::class)->except(['show']);
        Route::resource('alumnos',  AlumnoController::class);
        Route::resource('docentes', DocenteController::class);
        Route::resource('grupos',   GrupoController::class);
 
        Route::get('/kardex/create',          [KardexInscripcionController::class, 'create'])->name('kardex.create');
        Route::post('/kardex',                [KardexInscripcionController::class, 'store'])->name('kardex.store');
        Route::delete('/kardex/{kardex}',     [KardexInscripcionController::class, 'destroy'])->name('kardex.destroy');
    });
 
    // Kardex — lectura/edición (Admin, Administrativo, Docente)
    Route::middleware(['rol:Admin,Personal Administrativo,Docente'])->group(function () {
        Route::get('/kardex',                    [KardexInscripcionController::class, 'index'])->name('kardex.index');
        Route::get('/kardex/{kardex}',           [KardexInscripcionController::class, 'show'])->name('kardex.show');
        Route::get('/kardex/{kardex}/edit',      [KardexInscripcionController::class, 'edit'])->name('kardex.edit');
        Route::put('/kardex/{kardex}',           [KardexInscripcionController::class, 'update'])->name('kardex.update');
    });
 
    // Docente — asistencias
    Route::middleware(['rol:Docente'])->group(function () {
        Route::resource('asistencias', AsistenciaController::class)->except(['show']);
    });
 
    // Alumno — solo su info
    Route::middleware(['rol:Alumno'])->group(function () {
        Route::get('/mi-kardex',       [KardexInscripcionController::class, 'miKardex'])->name('kardex.mi-kardex');
        Route::get('/mis-asistencias', [AsistenciaController::class, 'misAsistencias'])->name('asistencias.mis-asistencias');
    });
});
 