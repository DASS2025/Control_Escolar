<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SAES — Inscripción / Paso 1</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #1a2b4a 0%, #2d4a7a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 0;
        }
        .wizard-card {
            width: 100%;
            max-width: 520px;
            border: none;
            border-radius: 1rem;
            box-shadow: 0 20px 60px rgba(0,0,0,.3);
        }

        /* ── Stepper ── */
        .stepper { display: flex; align-items: center; justify-content: center; gap: 0; }
        .step-item { display: flex; flex-direction: column; align-items: center; position: relative; flex: 1; }
        .step-item:not(:last-child)::after {
            content: '';
            position: absolute;
            top: 18px; left: calc(50% + 18px);
            width: calc(100% - 36px);
            height: 2px;
            background-color: #dee2e6;
            z-index: 0;
        }
        .step-item.done::after  { background-color: #0d6efd; }
        .step-circle {
            width: 36px; height: 36px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: .85rem; font-weight: 700;
            border: 2px solid #dee2e6;
            background: #fff; color: #adb5bd;
            position: relative; z-index: 1;
        }
        .step-item.active .step-circle { border-color: #0d6efd; background: #0d6efd; color: #fff; }
        .step-item.done   .step-circle { border-color: #0d6efd; background: #0d6efd; color: #fff; }
        .step-label { font-size: .7rem; color: #6c757d; margin-top: .3rem; text-align: center; }
        .step-item.active .step-label,
        .step-item.done   .step-label  { color: #0d6efd; font-weight: 600; }
    </style>
</head>
<body>
<div class="card wizard-card">
    <div class="card-body p-4 p-md-5">

        {{-- Logo --}}
        <div class="text-center mb-4">
            <img src="{{ asset('images/univac-logo.jpeg') }}" alt="UNIVAC"
                 class="img-fluid mb-2" style="max-width: 200px;">
            <p class="text-muted small mb-0">
                <i class="bi bi-mortarboard me-1 text-primary"></i>Plataforma de Inscripción en línea
            </p>
        </div>

        {{-- Stepper --}}
        <div class="stepper mb-4">
            <div class="step-item active">
                <div class="step-circle">1</div>
                <span class="step-label">Cuenta</span>
            </div>
            <div class="step-item">
                <div class="step-circle">2</div>
                <span class="step-label">Carrera</span>
            </div>
            <div class="step-item">
                <div class="step-circle">3</div>
                <span class="step-label">Datos</span>
            </div>
            <div class="step-item">
                <div class="step-circle">4</div>
                <span class="step-label">Voucher</span>
            </div>
        </div>

        <h6 class="fw-bold mb-1">Crear cuenta de acceso</h6>
        <p class="text-muted small mb-4">Usarás este correo y contraseña para ingresar al sistema una vez inscrito.</p>

        {{-- Errores globales --}}
        @if($errors->any())
            <div class="alert alert-danger py-2 small">
                <i class="bi bi-exclamation-circle me-1"></i>
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('inscripcion.paso1.store') }}">
            @csrf

            {{-- Correo --}}
            <div class="mb-3">
                <label for="correo_institucional" class="form-label fw-semibold">
                    Correo institucional <span class="text-danger">*</span>
                </label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input type="email"
                           id="correo_institucional"
                           name="correo_institucional"
                           class="form-control @error('correo_institucional') is-invalid @enderror"
                           value="{{ old('correo_institucional') }}"
                           placeholder="alumno@escuela.edu.mx"
                           required autofocus>
                    @error('correo_institucional')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- Contraseña --}}
            <div class="mb-3">
                <label for="password" class="form-label fw-semibold">
                    Contraseña <span class="text-danger">*</span>
                </label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password"
                           id="password"
                           name="password"
                           class="form-control @error('password') is-invalid @enderror"
                           placeholder="Mínimo 8 caracteres"
                           minlength="8"
                           required>
                    <button class="btn btn-outline-secondary" type="button" id="togglePassword" tabindex="-1">
                        <i class="bi bi-eye" id="eyeIcon"></i>
                    </button>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- Confirmar contraseña --}}
            <div class="mb-3">
                <label for="password_confirmation" class="form-label fw-semibold">
                    Confirmar contraseña <span class="text-danger">*</span>
                </label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                    <input type="password"
                           id="password_confirmation"
                           name="password_confirmation"
                           class="form-control"
                           placeholder="Repite tu contraseña"
                           minlength="8"
                           required>
                </div>
                <div id="matchFeedback" class="form-text"></div>
            </div>

            {{-- Aviso de privacidad --}}
            <div class="mb-4">
                <div class="form-check">
                    <input class="form-check-input @error('consentimiento') is-invalid @enderror"
                           type="checkbox"
                           id="consentimiento"
                           name="consentimiento"
                           value="1"
                           {{ old('consentimiento') ? 'checked' : '' }}
                           required>
                    <label class="form-check-label small" for="consentimiento">
                        He leído y acepto el
                        <a href="#" data-bs-toggle="modal" data-bs-target="#modalPrivacidad">
                            aviso de privacidad
                        </a>.
                        <span class="text-danger">*</span>
                    </label>
                    @error('consentimiento')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-primary">
                    Continuar <i class="bi bi-arrow-right ms-1"></i>
                </button>
            </div>
        </form>

        <div class="text-center mt-3">
            <small class="text-muted">
                ¿Ya tienes cuenta?
                <a href="{{ route('login') }}">Iniciar sesión</a>
            </small>
        </div>

    </div>
</div>

{{-- Modal aviso de privacidad --}}
<div class="modal fade" id="modalPrivacidad" tabindex="-1">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Aviso de Privacidad</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body small text-muted">
                <p>Los datos personales que proporciones en este formulario serán tratados con base en los principios
                de licitud, consentimiento, calidad, finalidad, lealtad, proporcionalidad y responsabilidad,
                conforme a la Ley Federal de Protección de Datos Personales en Posesión de los Particulares.</p>
                <p><strong>Finalidad:</strong> Registro y gestión del proceso de inscripción escolar.</p>
                <p><strong>Datos recabados:</strong> Nombre completo, CURP, correo electrónico, contraseña,
                escuela de procedencia, fotografía y certificado de estudios.</p>
                <p><strong>Responsable:</strong> Sistema de Administración Escolar (SAES).</p>
                <p>Tus datos no serán compartidos con terceros sin tu consentimiento expreso.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary btn-sm" data-bs-dismiss="modal"
                        onclick="document.getElementById('consentimiento').checked = true">
                    Acepto
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Toggle mostrar/ocultar contraseña
    document.getElementById('togglePassword').addEventListener('click', function () {
        const input = document.getElementById('password');
        const icon  = document.getElementById('eyeIcon');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('bi-eye', 'bi-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.replace('bi-eye-slash', 'bi-eye');
        }
    });

    // Verificación visual de coincidencia de contraseñas
    const pwd     = document.getElementById('password');
    const pwdConf = document.getElementById('password_confirmation');
    const feedback = document.getElementById('matchFeedback');

    function checkMatch() {
        if (pwdConf.value === '') { feedback.textContent = ''; return; }
        if (pwd.value === pwdConf.value) {
            feedback.textContent = '✓ Las contraseñas coinciden';
            feedback.className   = 'form-text text-success';
        } else {
            feedback.textContent = '✗ Las contraseñas no coinciden';
            feedback.className   = 'form-text text-danger';
        }
    }

    pwd.addEventListener('input', checkMatch);
    pwdConf.addEventListener('input', checkMatch);
</script>
</body>
</html>
