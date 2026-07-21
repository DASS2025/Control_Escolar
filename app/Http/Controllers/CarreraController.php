<?php
 
namespace App\Http\Controllers;
 
use App\Models\Carrera;
use Illuminate\Http\Request;
 
class CarreraController extends Controller
{
    public function index(Request $request)
    {
        $carreras = Carrera::withCount(['alumnos', 'materias'])
            ->when($request->filled('buscar'), function ($q) use ($request) {
                $term = '%' . $request->buscar . '%';
                $q->where('nombre', 'like', $term)
                  ->orWhere('clave_oficial', 'like', $term);
            })
            ->orderBy('nombre')
            ->paginate(15)
            ->withQueryString();
 
        return view('carreras.index', compact('carreras'));
    }
 
    public function create()
    {
        return view('carreras.create');
    }
 
    public function store(Request $request)
    {
        $datos = $request->validate([
            'clave_oficial'  => ['required', 'string', 'max:50',  'unique:carreras,clave_oficial'],
            'nombre'         => ['required', 'string', 'max:150', 'unique:carreras,nombre'],
            'total_creditos' => ['required', 'integer', 'min:100', 'max:2000'],
        ], [
            'clave_oficial.unique'  => 'Ya existe una carrera con esa clave oficial.',
            'nombre.unique'         => 'Ya existe una carrera con ese nombre.',
            'total_creditos.min'    => 'Los créditos deben ser al menos 100.',
            'total_creditos.max'    => 'Los créditos no pueden superar 2000.',
        ]);
 
        Carrera::create($datos);
 
        return redirect()->route('carreras.index')
            ->with('exito', "Carrera \"{$datos['nombre']}\" creada correctamente.");
    }
 
    public function edit(Carrera $carrera)
    {
        return view('carreras.edit', compact('carrera'));
    }
 
    public function update(Request $request, Carrera $carrera)
    {
        $datos = $request->validate([
            'clave_oficial'  => ['required', 'string', 'max:50',  "unique:carreras,clave_oficial,{$carrera->id}"],
            'nombre'         => ['required', 'string', 'max:150', "unique:carreras,nombre,{$carrera->id}"],
            'total_creditos' => ['required', 'integer', 'min:100', 'max:2000'],
        ], [
            'clave_oficial.unique'  => 'Ya existe otra carrera con esa clave oficial.',
            'nombre.unique'         => 'Ya existe otra carrera con ese nombre.',
            'total_creditos.min'    => 'Los créditos deben ser al menos 100.',
            'total_creditos.max'    => 'Los créditos no pueden superar 2000.',
        ]);
 
        $carrera->update($datos);
 
        return redirect()->route('carreras.index')
            ->with('exito', "Carrera \"{$carrera->nombre}\" actualizada correctamente.");
    }
 
    public function destroy(Carrera $carrera)
    {
        if ($carrera->alumnos()->exists()) {
            return redirect()->route('carreras.index')
                ->with('error', "No se puede eliminar \"{$carrera->nombre}\": tiene alumnos inscritos.");
        }
 
        $nombre = $carrera->nombre;
        $carrera->delete();
 
        return redirect()->route('carreras.index')
            ->with('exito', "Carrera \"{$nombre}\" eliminada correctamente.");
    }
}