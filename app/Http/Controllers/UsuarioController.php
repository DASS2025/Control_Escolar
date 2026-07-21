<?php
 
namespace App\Http\Controllers;
 
use App\Models\Usuario;
use App\Models\Rol;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
 
class UsuarioController extends Controller
{
    /**
     * Roles que cada nivel jerárquico puede crear.
     * El Admin no está aquí porque él puede crear TODOS.
     */
    private const PUEDE_CREAR = [
        'Director'               => ['Director', 'Coordinador de Carrera', 'Docente', 'Alumno', 'Contador'],
        'Coordinador de Carrera' => ['Docente', 'Alumno'],
        'Docente'                => ['Alumno'],
    ];
 
    /**
     * Devuelve los roles que el usuario autenticado tiene permitido asignar.
     */
    private function rolesPermitidos(): \Illuminate\Database\Eloquent\Collection
    {
        $rolActual = Auth::user()->rol->nombre ?? '';
 
        if ($rolActual === 'Admin') {
            return Rol::orderBy('nombre')->get();
        }
 
        $nombresPermitidos = self::PUEDE_CREAR[$rolActual] ?? [];
 
        return Rol::whereIn('nombre', $nombresPermitidos)->orderBy('nombre')->get();
    }
 
    /**
     * Lista usuarios filtrados según jerarquía del usuario autenticado.
     */
    public function index(Request $request)
    {
        $rolActual = Auth::user()->rol->nombre ?? '';
 
        // Roles que el usuario actual puede VER según jerarquía
        $rolesVisibles = match ($rolActual) {
            'Admin'                => null,                                                         // ve todos
            'Director'             => ['Director', 'Coordinador de Carrera', 'Docente', 'Alumno', 'Contador'],
            'Coordinador de Carrera' => ['Docente', 'Alumno'],
            'Docente'              => ['Alumno'],
            default                => [],
        };
 
        $query = Usuario::with('rol')
            ->when($rolesVisibles !== null, fn($q) => $q->whereHas('rol', fn($r) => $r->whereIn('nombre', $rolesVisibles)))
            ->when($request->filled('rol_id'), fn($q) => $q->where('rol_id', $request->rol_id))
            ->when($request->filled('buscar'),  fn($q) => $q->where('correo_institucional', 'like', '%' . $request->buscar . '%'))
            ->orderBy('correo_institucional');
 
        $usuarios = $query->paginate(20)->withQueryString();
        $roles    = $this->rolesPermitidos();
 
        return view('usuarios.index', compact('usuarios', 'roles', 'rolActual'));
    }
 
    public function create()
    {
        $roles = $this->rolesPermitidos();
 
        if ($roles->isEmpty()) {
            abort(403, 'No tienes permiso para crear usuarios.');
        }
 
        return view('usuarios.create', compact('roles'));
    }
 
    public function store(Request $request)
    {
        $rolesPermitidos = $this->rolesPermitidos()->pluck('id')->toArray();
 
        $datos = $request->validate([
            'rol_id'               => ['required', 'integer', 'in:' . implode(',', $rolesPermitidos)],
            'correo_institucional' => ['required', 'email', 'max:150', 'unique:Usuarios,correo_institucional'],
            'password'             => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'rol_id.in' => 'No tienes permiso para asignar ese rol.',
        ]);
 
        Usuario::create([
            'rol_id'               => $datos['rol_id'],
            'correo_institucional' => $datos['correo_institucional'],
            'password_hash'        => Hash::make($datos['password']),
        ]);
 
        return redirect()->route('usuarios.index')
            ->with('exito', 'Usuario creado correctamente.');
    }
 
    public function edit(Usuario $usuario)
    {
        // Verificar que el usuario autenticado puede editar este usuario
        $this->autorizarAcceso($usuario);
 
        $roles = $this->rolesPermitidos();
        return view('usuarios.edit', compact('usuario', 'roles'));
    }
 
    public function update(Request $request, Usuario $usuario)
    {
        $this->autorizarAcceso($usuario);
 
        $rolesPermitidos = $this->rolesPermitidos()->pluck('id')->toArray();
 
        $datos = $request->validate([
            'rol_id'               => ['required', 'integer', 'in:' . implode(',', $rolesPermitidos)],
            'correo_institucional' => ['required', 'email', 'max:150', 'unique:Usuarios,correo_institucional,' . $usuario->id],
            'password'             => ['nullable', 'string', 'min:8', 'confirmed'],
        ], [
            'rol_id.in' => 'No tienes permiso para asignar ese rol.',
        ]);
 
        $actualizacion = [
            'rol_id'               => $datos['rol_id'],
            'correo_institucional' => $datos['correo_institucional'],
        ];
 
        if (!empty($datos['password'])) {
            $actualizacion['password_hash'] = Hash::make($datos['password']);
        }
 
        $usuario->update($actualizacion);
 
        return redirect()->route('usuarios.index')
            ->with('exito', 'Usuario actualizado correctamente.');
    }
 
    public function destroy(Usuario $usuario)
    {
        $this->autorizarAcceso($usuario);
 
        // No permitir auto-eliminación
        if ($usuario->id === Auth::id()) {
            return redirect()->route('usuarios.index')
                ->with('error', 'No puedes eliminar tu propio usuario.');
        }
 
        $correo = $usuario->correo_institucional;
        $usuario->delete();
 
        return redirect()->route('usuarios.index')
            ->with('exito', "Usuario «{$correo}» eliminado correctamente.");
    }
 
    /**
     * Verifica que el usuario autenticado tenga jerarquía sobre el usuario objetivo.
     */
    private function autorizarAcceso(Usuario $objetivo): void
    {
        $rolActual   = Auth::user()->rol->nombre ?? '';
        $rolObjetivo = $objetivo->rol->nombre   ?? '';
 
        if ($rolActual === 'Admin') return; // Admin puede todo
 
        $nombresPermitidos = self::PUEDE_CREAR[$rolActual] ?? [];
 
        if (!in_array($rolObjetivo, $nombresPermitidos)) {
            abort(403, 'No tienes permiso para modificar este usuario.');
        }
    }
}