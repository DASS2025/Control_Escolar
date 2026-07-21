<?php
 
namespace App\Http\Controllers;
 
use App\Models\BitacoraAuditoria;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
 
class BitacoraAuditoriaController extends Controller
{
    private const ACCIONES = ['LOGIN', 'LOGOUT', 'CREATE', 'UPDATE', 'DELETE'];
 
    /**
     * VISTA 1 — "Mi Actividad"
     * Cada usuario ve únicamente sus propios registros. Accesible para todos los roles.
     */
    public function miActividad(Request $request)
    {
        $registros = BitacoraAuditoria::where('usuario_id', Auth::id())
            ->when($request->filled('accion'), fn($q) => $q->where('accion', $request->accion))
            ->when($request->filled('fecha_inicio'), fn($q) => $q->whereDate('created_at', '>=', $request->fecha_inicio))
            ->when($request->filled('fecha_fin'),    fn($q) => $q->whereDate('created_at', '<=', $request->fecha_fin))
            ->orderByDesc('created_at')
            ->paginate(30)
            ->withQueryString();
 
        return view('bitacora.mi-actividad', [
            'registros' => $registros,
            'acciones'  => self::ACCIONES,
        ]);
    }
 
    /**
     * VISTA 2 — "Monitor del Sistema"
     * Filtrada por jerarquía:
     *   Admin     → ve todos los usuarios
     *   Director  → ve todos menos Admin
     *   Coordinador → ve Docentes y Alumnos
     *   Docente   → ve solo Alumnos
     *   Alumno    → sin acceso (bloqueado en rutas)
     */
    public function monitor(Request $request)
    {
        $rolActual = Auth::user()->rol->nombre ?? '';
 
        // Roles cuyos registros puede ver este usuario
        $rolesVisibles = match ($rolActual) {
            'Admin'                  => null,   // sin restricción
            'Director'               => ['Director', 'Coordinador de Carrera', 'Docente', 'Alumno', 'Contador'],
            'Coordinador de Carrera' => ['Docente', 'Alumno'],
            'Docente'                => ['Alumno'],
            default                  => [],
        };
 
        // IDs de usuarios que puede monitorear
        $idsPermitidos = null;
        if ($rolesVisibles !== null) {
            $idsPermitidos = Usuario::whereHas('rol', fn($q) => $q->whereIn('nombre', $rolesVisibles))
                ->pluck('id');
        }
 
        $query = BitacoraAuditoria::with('usuario.rol')
            ->when($idsPermitidos !== null, fn($q) => $q->whereIn('usuario_id', $idsPermitidos))
            ->when($request->filled('accion'),      fn($q) => $q->where('accion', $request->accion))
            ->when($request->filled('tabla'),        fn($q) => $q->where('tabla_afectada', $request->tabla))
            ->when($request->filled('usuario_id'),   fn($q) => $q->where('usuario_id', $request->usuario_id))
            ->when($request->filled('fecha_inicio'), fn($q) => $q->whereDate('created_at', '>=', $request->fecha_inicio))
            ->when($request->filled('fecha_fin'),    fn($q) => $q->whereDate('created_at', '<=', $request->fecha_fin))
            ->orderByDesc('created_at');
 
        $registros = $query->paginate(50)->withQueryString();
 
        $esAdmin = in_array($rolActual, ['Admin', 'Director']);
 
        return view('bitacora.monitor', [
            'registros' => $registros,
            'acciones'  => self::ACCIONES,
            'esAdmin'   => $esAdmin,
            'rolActual' => $rolActual,
        ]);
    }
 
    /**
     * Exportar CSV del monitor (Admin y Director solamente).
     */
    public function exportar(Request $request)
    {
        $rolActual = Auth::user()->rol->nombre ?? '';
 
        if (!in_array($rolActual, ['Admin', 'Director'])) {
            abort(403);
        }
 
        $rolesVisibles = $rolActual === 'Admin'
            ? null
            : ['Director', 'Coordinador de Carrera', 'Docente', 'Alumno', 'Contador'];
 
        $idsPermitidos = $rolesVisibles
            ? Usuario::whereHas('rol', fn($q) => $q->whereIn('nombre', $rolesVisibles))->pluck('id')
            : null;
 
        $registros = BitacoraAuditoria::with('usuario')
            ->when($idsPermitidos, fn($q) => $q->whereIn('usuario_id', $idsPermitidos))
            ->when($request->filled('accion'),      fn($q) => $q->where('accion', $request->accion))
            ->when($request->filled('tabla'),        fn($q) => $q->where('tabla_afectada', $request->tabla))
            ->when($request->filled('fecha_inicio'), fn($q) => $q->whereDate('created_at', '>=', $request->fecha_inicio))
            ->when($request->filled('fecha_fin'),    fn($q) => $q->whereDate('created_at', '<=', $request->fecha_fin))
            ->orderByDesc('created_at')
            ->get();
 
        $nombre  = 'monitor_bitacora_' . now()->format('Ymd_His') . '.csv';
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$nombre}\"",
        ];
 
        $callback = function () use ($registros) {
            $h = fopen('php://output', 'w');
            fprintf($h, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM UTF-8
            fputcsv($h, ['Fecha', 'Usuario', 'Rol', 'Acción', 'Tabla', 'Detalles']);
            foreach ($registros as $r) {
                fputcsv($h, [
                    $r->created_at,
                    $r->usuario->correo_institucional ?? '—',
                    $r->usuario->rol->nombre ?? '—',
                    $r->accion,
                    $r->tabla_afectada,
                    $r->valores_json ? json_encode($r->valores_json) : '',
                ]);
            }
            fclose($h);
        };
 
        return response()->stream($callback, 200, $headers);
    }
}
 