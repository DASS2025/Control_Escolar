<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SAES — Comprobante de Inscripción</title>
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
        .voucher-card {
            width: 100%;
            max-width: 680px;
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
            background-color: #0d6efd;
            z-index: 0;
        }
        .step-circle {
            width: 36px; height: 36px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: .85rem; font-weight: 700;
            border: 2px solid #0d6efd;
            background: #0d6efd; color: #fff;
            position: relative; z-index: 1;
        }
        .step-label { font-size: .7rem; color: #0d6efd; font-weight: 600; margin-top: .3rem; text-align: center; }

        /* ── Voucher imprimible ── */
        .voucher-print {
            border: 1px solid #dee2e6;
            border-radius: .75rem;
            padding: 1.75rem;
            background: #fff;
        }
        .voucher-header {
            border-bottom: 3px solid #1a2b4a;
            padding-bottom: 1rem;
            margin-bottom: 1.5rem;
        }
        .dato-row {
            display: flex;
            padding: .45rem 0;
            border-bottom: 1px dashed #dee2e6;
            font-size: .9rem;
        }
        .dato-row:last-child { border-bottom: none; }
        .dato-label {
            width: 210px;
            flex-shrink: 0;
            color: #6c757d;
            font-weight: 600;
        }
        .dato-valor { color: #212529; }

        .barcode-section {
            margin-top: 1.5rem;
            padding-top: 1.25rem;
            border-top: 2px dashed #dee2e6;
            text-align: center;
        }
        .num-cuenta {
            font-family: 'Courier New', monospace;
            font-size: 1.05rem;
            font-weight: 700;
            letter-spacing: .1em;
            color: #1a2b4a;
        }

        /* ── Impresión ── */
        @media print {
            body {
                background: #fff !important;
                padding: 0;
                display: block;
            }
            .no-print { display: none !important; }
            .voucher-card {
                box-shadow: none;
                border-radius: 0;
                max-width: 100%;
            }
            .card-body { padding: 0 !important; }
            .voucher-print {
                border: none;
                border-radius: 0;
                padding: 0;
            }
        }
    </style>
</head>
<body>
<div class="card voucher-card">
    <div class="card-body p-4 p-md-5">

        {{-- Stepper todos completados --}}
        <div class="stepper mb-4 no-print">
            @foreach(['Cuenta','Carrera','Datos','Comprobante'] as $paso)
            <div class="step-item">
                <div class="step-circle"><i class="bi bi-check-lg" style="font-size:.8rem"></i></div>
                <span class="step-label">{{ $paso }}</span>
            </div>
            @endforeach
        </div>

        {{-- Alerta de éxito --}}
        <div class="alert alert-success d-flex align-items-center gap-2 mb-4 no-print">
            <i class="bi bi-check-circle-fill fs-5"></i>
            <div>
                <strong>¡Inscripción completada!</strong>
                Tu registro fue guardado. Imprime o guarda este comprobante.
            </div>
        </div>

        {{-- ── VOUCHER IMPRIMIBLE ── --}}
        <div class="voucher-print" id="voucher">

            {{-- Encabezado --}}
            <div class="voucher-header d-flex justify-content-between align-items-center">
                <div>
                    <img src="{{ asset('images/univac-logo.jpeg') }}" alt="UNIVAC"
                         style="max-height:60px; max-width:180px; object-fit:contain">
                </div>
                <div class="text-end">
                    <div class="fw-bold" style="font-size:1.1rem; color:#1a2b4a">
                        Comprobante de Inscripción
                    </div>
                    <div class="text-muted small">
                        Fecha: {{ now()->format('d/m/Y H:i') }}
                    </div>
                    <div class="text-muted small">
                        Folio: <span class="fw-semibold text-dark">{{ strtoupper(substr(md5($alumno->id . $alumno->matricula), 0, 8)) }}</span>
                    </div>
                </div>
            </div>

            {{-- Datos del alumno --}}
            <div class="mb-3">
                <div class="fw-bold text-uppercase mb-2"
                     style="font-size:.7rem; letter-spacing:.08em; color:#6c757d">
                    Datos del alumno
                </div>

                <div class="dato-row">
                    <span class="dato-label">Nombre completo</span>
                    <span class="dato-valor fw-semibold">{{ $alumno->nombres }} {{ $alumno->apellidos }}</span>
                </div>
                <div class="dato-row">
                    <span class="dato-label">Matrícula</span>
                    <span class="dato-valor fw-bold" style="color:#0d6efd; font-size:1rem">{{ $alumno->matricula }}</span>
                </div>
                <div class="dato-row">
                    <span class="dato-label">CURP</span>
                    <span class="dato-valor" style="font-family:monospace">{{ $alumno->curp }}</span>
                </div>
                <div class="dato-row">
                    <span class="dato-label">Correo institucional</span>
                    <span class="dato-valor">{{ $alumno->usuario->correo_institucional }}</span>
                </div>
                <div class="dato-row">
                    <span class="dato-label">Escuela de procedencia</span>
                    <span class="dato-valor">{{ $alumno->escuela_procedencia }}</span>
                </div>
                <div class="dato-row">
                    <span class="dato-label">Estatus</span>
                    <span class="badge bg-success">{{ $alumno->estatus }}</span>
                </div>
            </div>

            {{-- Datos de la carrera --}}
            <div class="mb-3">
                <div class="fw-bold text-uppercase mb-2"
                     style="font-size:.7rem; letter-spacing:.08em; color:#6c757d">
                    Carrera inscrita
                </div>

                <div class="dato-row">
                    <span class="dato-label">Clave oficial</span>
                    <span class="dato-valor fw-semibold">{{ $carrera->clave_oficial }}</span>
                </div>
                <div class="dato-row">
                    <span class="dato-label">Nombre</span>
                    <span class="dato-valor">{{ $carrera->nombre }}</span>
                </div>
                <div class="dato-row">
                    <span class="dato-label">Total de créditos</span>
                    <span class="dato-valor">{{ $carrera->total_creditos }}</span>
                </div>
            </div>

            {{-- Número de cuenta + código de barras --}}
            <div class="barcode-section">
                <div class="text-muted small mb-1">Número de cuenta</div>
                <div class="num-cuenta mb-3">{{ $numeroCuenta }}</div>
                <svg id="barcode"></svg>
            </div>

        </div>{{-- fin voucher-print --}}

        {{-- Botones (no se imprimen) --}}
        <div class="d-flex justify-content-between align-items-center mt-4 no-print">
            <a href="{{ route('login') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-box-arrow-in-right me-1"></i>Ir al inicio de sesión
            </a>
            <button onclick="window.print()" class="btn btn-primary">
                <i class="bi bi-printer me-1"></i>Imprimir comprobante
            </button>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
<script>
    JsBarcode("#barcode", "{{ $numeroCuenta }}", {
        format:      "CODE128",
        width:       2,
        height:      60,
        displayValue: false,
        margin:      10,
        lineColor:   "#1a2b4a"
    });
</script>
</body>
</html>
