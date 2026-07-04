<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SAES — Inscripción / Paso 2</title>
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
            max-width: 700px;
            border: none;
            border-radius: 1rem;
            box-shadow: 0 20px 60px rgba(0,0,0,.3);
        }

        /* ── Stepper ── */
        .stepper { display: flex; align-items: center; justify-content: center; }
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
        .step-item.done::after { background-color: #0d6efd; }
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
        .step-item.done   .step-label { color: #0d6efd; font-weight: 600; }

        /* ── Tarjetas de carrera ── */
        .carrera-card {
            border: 2px solid #dee2e6;
            border-radius: .75rem;
            cursor: pointer;
            transition: border-color .15s, box-shadow .15s;
        }
        .carrera-card:hover:not(.disabled) { border-color: #0d6efd; box-shadow: 0 0 0 3px rgba(13,110,253,.1); }
        .carrera-card.selected { border-color: #0d6efd; background-color: #f0f5ff; box-shadow: 0 0 0 3px rgba(13,110,253,.15); }
        .carrera-card.disabled { opacity: .55; cursor: not-allowed; background-color: #f8f9fa; }

        .disponibilidad-bar {
            height: 6px;
            border-radius: 3px;
            background-color: #e9ecef;
            overflow: hidden;
        }
        .disponibilidad-bar .fill {
            height: 100%;
            border-radius: 3px;
            transition: width .3s;
        }
    </style>
</head>
<body>
<div class="card wizard-card">
    <div class="card-body p-4 p-md-5">

        {{-- Logo --}}
        <div class="text-center mb-4">
            <img src="{{ asset('images/univac-logo.jpeg') }}" alt="UNIVAC"
                 class="img-fluid mb-2" style="max-width: 200px;">
            <p class="text-muted small mb-0">Sistema de Administración Escolar</p>
        </div>

        {{-- Stepper --}}
        <div class="stepper mb-4">
            <div class="step-item done">
                <div class="step-circle"><i class="bi bi-check-lg" style="font-size:.8rem"></i></div>
                <span class="step-label">Cuenta</span>
            </div>
            <div class="step-item active">
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

        <h6 class="fw-bold mb-1">Selecciona tu carrera</h6>
        <p class="text-muted small mb-4">
            Elige la carrera en la que deseas inscribirte. Solo puedes seleccionar carreras con cupo disponible.
        </p>

        @if($errors->any())
            <div class="alert alert-danger py-2 small">
                <i class="bi bi-exclamation-circle me-1"></i>{{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('inscripcion.paso2.store') }}" id="formPaso2">
            @csrf

            <input type="hidden" name="carrera_id" id="carreraIdInput">

            <div class="row g-3 mb-4">
                @forelse($carreras as $carrera)
                    @php
                        $llena     = $carrera->disponibles <= 0;
                        $pctUsado  = $carrera->capacidad > 0
                                        ? min(100, round(($carrera->alumnos_count / $carrera->capacidad) * 100))
                                        : 100;
                        $colorFill = $pctUsado >= 90 ? '#dc3545' : ($pctUsado >= 60 ? '#fd7e14' : '#198754');
                    @endphp
                    <div class="col-12 col-md-6">
                        <div class="carrera-card p-3 h-100 {{ $llena ? 'disabled' : '' }}"
                             data-id="{{ $carrera->id }}"
                             onclick="{{ $llena ? '' : 'seleccionar(this)' }}">

                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <span class="badge bg-secondary text-uppercase mb-1" style="font-size:.65rem; letter-spacing:.05em">
                                        {{ $carrera->clave_oficial }}
                                    </span>
                                    <div class="fw-semibold" style="font-size:.95rem; line-height:1.3">
                                        {{ $carrera->nombre }}
                                    </div>
                                </div>
                                @if($llena)
                                    <span class="badge bg-danger ms-2 flex-shrink-0">LLENA</span>
                                @else
                                    <span class="badge bg-success ms-2 flex-shrink-0">
                                        {{ $carrera->disponibles }} lugar{{ $carrera->disponibles == 1 ? '' : 'es' }}
                                    </span>
                                @endif
                            </div>

                            <div class="text-muted small mb-2">
                                <i class="bi bi-award me-1"></i>{{ $carrera->total_creditos }} créditos
                            </div>

                            {{-- Barra de ocupación --}}
                            <div class="disponibilidad-bar mt-2">
                                <div class="fill" style="width: {{ $pctUsado }}%; background-color: {{ $colorFill }}"></div>
                            </div>
                            <div class="d-flex justify-content-between mt-1">
                                <span class="text-muted" style="font-size:.65rem">Ocupación</span>
                                <span class="text-muted" style="font-size:.65rem">
                                    {{ $carrera->alumnos_count }}/{{ $carrera->capacidad }}
                                </span>
                            </div>

                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <p class="text-muted text-center py-3">No hay carreras disponibles en este momento.</p>
                    </div>
                @endforelse
            </div>

            <div id="seleccionInfo" class="alert alert-primary py-2 small d-none">
                <i class="bi bi-check-circle me-1"></i>
                Carrera seleccionada: <strong id="seleccionNombre"></strong>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-2">
                <a href="{{ route('inscripcion.paso1') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i>Regresar
                </a>
                <button type="submit" id="btnContinuar" class="btn btn-primary" disabled>
                    Continuar <i class="bi bi-arrow-right ms-1"></i>
                </button>
            </div>
        </form>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function seleccionar(el) {
        // Quitar selección previa
        document.querySelectorAll('.carrera-card.selected').forEach(c => c.classList.remove('selected'));

        // Marcar la nueva
        el.classList.add('selected');

        const id     = el.dataset.id;
        const nombre = el.querySelector('.fw-semibold').textContent.trim();

        document.getElementById('carreraIdInput').value = id;
        document.getElementById('seleccionNombre').textContent = nombre;
        document.getElementById('seleccionInfo').classList.remove('d-none');
        document.getElementById('btnContinuar').disabled = false;
    }
</script>
</body>
</html>
