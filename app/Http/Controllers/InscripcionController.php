<?php

namespace App\Http\Controllers;

use App\Models\Carrera;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class InscripcionController extends Controller
{
    // ──────────────────────────────────────────
    // PASO 1: Cuenta de acceso
    // ──────────────────────────────────────────

    public function paso1()
    {
        return view('inscripcion.paso1');
    }

    public function storePaso1(Request $request)
    {
        $datos = $request->validate([
            'correo_institucional' => [
                'required', 'email', 'max:150',
                'unique:Usuarios,correo_institucional',
            ],
            'password'             => ['required', 'string', 'min:8', 'confirmed'],
            'consentimiento'       => ['accepted'],
        ], [
            'correo_institucional.unique'   => 'Este correo ya está registrado en el sistema.',
            'password.confirmed'            => 'Las contraseñas no coinciden.',
            'password.min'                  => 'La contraseña debe tener al menos 8 caracteres.',
            'consentimiento.accepted'       => 'Debes aceptar el aviso de privacidad para continuar.',
        ]);

        // Guardar en sesión (password hasheado)
        session([
            'inscripcion.paso1' => [
                'correo_institucional' => $datos['correo_institucional'],
                'password_hash'        => Hash::make($datos['password']),
            ],
        ]);

        return redirect()->route('inscripcion.paso2');
    }

    // ──────────────────────────────────────────
    // PASO 2: Selección de carrera
    // ──────────────────────────────────────────

    public function paso2()
    {
        if (!session('inscripcion.paso1')) {
            return redirect()->route('inscripcion.paso1');
        }

        $carreras = Carrera::withCount('alumnos')->get()->map(function ($carrera) {
            $carrera->disponibles = max(0, $carrera->capacidad - $carrera->alumnos_count);
            return $carrera;
        });

        return view('inscripcion.paso2', compact('carreras'));
    }

    public function storePaso2(Request $request)
    {
        if (!session('inscripcion.paso1')) {
            return redirect()->route('inscripcion.paso1');
        }

        $datos = $request->validate([
            'carrera_id' => ['required', 'exists:Carreras,id'],
        ], [
            'carrera_id.required' => 'Debes seleccionar una carrera.',
        ]);

        // Verificar que aún haya cupo
        $carrera = Carrera::withCount('alumnos')->findOrFail($datos['carrera_id']);
        if ($carrera->alumnos_count >= $carrera->capacidad) {
            return back()->withErrors(['carrera_id' => 'La carrera seleccionada ya no tiene cupo disponible.']);
        }

        session(['inscripcion.paso2' => ['carrera_id' => $datos['carrera_id']]]);

        return redirect()->route('inscripcion.paso3');
    }

    // ──────────────────────────────────────────
    // PASO 3: Datos personales + archivos
    // ──────────────────────────────────────────

    public function paso3()
    {
        if (!session('inscripcion.paso1') || !session('inscripcion.paso2')) {
            return redirect()->route('inscripcion.paso1');
        }

        return view('inscripcion.paso3');
    }

    public function storePaso3(Request $request)
    {
        if (!session('inscripcion.paso1') || !session('inscripcion.paso2')) {
            return redirect()->route('inscripcion.paso1');
        }

        $datos = $request->validate([
            'nombres'              => ['required', 'string', 'max:100'],
            'apellidos'            => ['required', 'string', 'max:100'],
            'curp'                 => ['required', 'string', 'size:18', 'unique:Alumnos,curp'],
            'escuela_procedencia'  => ['required', 'string', 'max:200'],
            'foto'                 => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'certificado'          => ['required', 'file', 'mimes:pdf', 'max:5120'],
            'captcha'              => ['required'],
        ], [
            'curp.size'            => 'El CURP debe tener exactamente 18 caracteres.',
            'curp.unique'          => 'Este CURP ya está registrado.',
            'foto.required'        => 'La fotografía es obligatoria.',
            'foto.image'           => 'La fotografía debe ser una imagen (JPG, PNG).',
            'foto.max'             => 'La fotografía no debe superar 2 MB.',
            'certificado.required' => 'El certificado escolar es obligatorio.',
            'certificado.mimes'    => 'El certificado debe ser un archivo PDF.',
            'certificado.max'      => 'El certificado no debe superar 5 MB.',
        ]);

        // Validar captcha matemático
        if ((int) $request->captcha !== (int) session('inscripcion.captcha_respuesta')) {
            return back()->withErrors(['captcha' => 'Respuesta incorrecta. Intenta de nuevo.'])->withInput();
        }

        $p1 = session('inscripcion.paso1');
        $p2 = session('inscripcion.paso2');

        // Generar matrícula automática única: año + 6 dígitos
        do {
            $matricula = date('Y') . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
        } while (\App\Models\Alumno::where('matricula', $matricula)->exists());

        // Crear todos los registros en una transacción
        $alumnoId = \Illuminate\Support\Facades\DB::transaction(function () use ($datos, $matricula, $p1, $p2, $request) {

            $rolAlumno = \App\Models\Rol::where('nombre', 'Alumno')->firstOrFail();

            $usuario = Usuario::create([
                'rol_id'               => $rolAlumno->id,
                'correo_institucional' => $p1['correo_institucional'],
                'password_hash'        => $p1['password_hash'],
                'consentimiento_aviso' => now(),
            ]);

            $alumno = \App\Models\Alumno::create([
                'usuario_id'          => $usuario->id,
                'carrera_id'          => $p2['carrera_id'],
                'matricula'           => $matricula,
                'nombres'             => $datos['nombres'],
                'apellidos'           => $datos['apellidos'],
                'curp'                => strtoupper($datos['curp']),
                'escuela_procedencia' => $datos['escuela_procedencia'],
                'estatus'             => 'Activo',
            ]);

            // Guardar archivos definitivos directamente
            $rutaFoto = $request->file('foto')->storeAs(
                'alumnos/' . $alumno->id,
                'foto_' . time() . '.' . $request->file('foto')->extension(),
                'local'
            );
            $rutaCert = $request->file('certificado')->storeAs(
                'alumnos/' . $alumno->id,
                'certificado_' . time() . '.pdf',
                'local'
            );

            \App\Models\DocumentoAlumno::create([
                'alumno_id'       => $alumno->id,
                'tipo'            => 'foto',
                'ruta_archivo'    => $rutaFoto,
                'nombre_original' => $request->file('foto')->getClientOriginalName(),
            ]);

            \App\Models\DocumentoAlumno::create([
                'alumno_id'       => $alumno->id,
                'tipo'            => 'certificado',
                'ruta_archivo'    => $rutaCert,
                'nombre_original' => $request->file('certificado')->getClientOriginalName(),
            ]);

            return $alumno->id;
        });

        // Limpiar sesión de pasos y guardar solo el resultado
        session()->forget(['inscripcion.paso1', 'inscripcion.paso2', 'inscripcion.captcha_respuesta']);
        session(['inscripcion.resultado' => [
            'alumno_id'     => $alumnoId,
            'numero_cuenta' => $this->generarNumeroCuenta(),
        ]]);

        return redirect()->route('inscripcion.voucher');
    }

    // ──────────────────────────────────────────
    // PASO 4: Voucher (solo muestra, ya guardado)
    // ──────────────────────────────────────────

    public function voucher()
    {
        $resultado = session('inscripcion.resultado');

        if (!$resultado) {
            return redirect()->route('inscripcion.paso1');
        }

        $alumno       = \App\Models\Alumno::with(['usuario', 'carrera'])->findOrFail($resultado['alumno_id']);
        $carrera      = $alumno->carrera;
        $numeroCuenta = $resultado['numero_cuenta'];

        // Limpiar resultado para que no se pueda recargar
        session()->forget('inscripcion.resultado');

        return view('inscripcion.voucher', compact('alumno', 'carrera', 'numeroCuenta'));
    }

    // ──────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────

    public function captchaRefresh()
    {
        $a = rand(1, 20);
        $b = rand(1, 20);
        session(['inscripcion.captcha_respuesta' => $a + $b]);
        return response()->json(['pregunta' => "$a + $b"]);
    }

    private function generarNumeroCuenta(): string
    {
        return date('Y') . '-' . strtoupper(substr(uniqid(), -6));
    }
}
