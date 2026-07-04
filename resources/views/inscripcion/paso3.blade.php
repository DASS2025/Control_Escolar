<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SAES — Inscripción / Paso 3</title>
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
            max-width: 640px;
            border: none;
            border-radius: 1rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, .3);
        }

        /* ── Stepper ── */
        .stepper {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .step-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            flex: 1;
        }

        .step-item:not(:last-child)::after {
            content: '';
            position: absolute;
            top: 18px;
            left: calc(50% + 18px);
            width: calc(100% - 36px);
            height: 2px;
            background-color: #dee2e6;
            z-index: 0;
        }

        .step-item.done::after {
            background-color: #0d6efd;
        }

        .step-circle {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .85rem;
            font-weight: 700;
            border: 2px solid #dee2e6;
            background: #fff;
            color: #adb5bd;
            position: relative;
            z-index: 1;
        }

        .step-item.active .step-circle {
            border-color: #0d6efd;
            background: #0d6efd;
            color: #fff;
        }

        .step-item.done .step-circle {
            border-color: #0d6efd;
            background: #0d6efd;
            color: #fff;
        }

        .step-label {
            font-size: .7rem;
            color: #6c757d;
            margin-top: .3rem;
            text-align: center;
        }

        .step-item.active .step-label,
        .step-item.done .step-label {
            color: #0d6efd;
            font-weight: 600;
        }

        /* ── Upload zones ── */
        .upload-zone {
            border: 2px dashed #ced4da;
            border-radius: .75rem;
            padding: 1.25rem;
            text-align: center;
            cursor: pointer;
            transition: border-color .15s, background .15s;
        }

        .upload-zone:hover {
            border-color: #0d6efd;
            background-color: #f0f5ff;
        }

        .upload-zone.has-file {
            border-color: #198754;
            background-color: #f0faf4;
        }

        .upload-zone input[type="file"] {
            display: none;
        }

        #fotoPreview {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #0d6efd;
            display: none;
            margin: 0 auto .5rem;
        }

        /* ── Captcha ── */
        .captcha-box {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: .5rem;
            padding: .75rem 1rem;
            font-size: 1.3rem;
            font-weight: 700;
            letter-spacing: .1em;
            text-align: center;
            color: #1a2b4a;
            user-select: none;
            min-width: 110px;
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
                <div class="step-item done">
                    <div class="step-circle"><i class="bi bi-check-lg" style="font-size:.8rem"></i></div>
                    <span class="step-label">Carrera</span>
                </div>
                <div class="step-item active">
                    <div class="step-circle">3</div>
                    <span class="step-label">Datos</span>
                </div>
                <div class="step-item">
                    <div class="step-circle">4</div>
                    <span class="step-label">Voucher</span>
                </div>
            </div>

            <h6 class="fw-bold mb-1">Datos personales y documentos</h6>
            <p class="text-muted small mb-4">Completa tu información personal y sube los documentos requeridos.</p>

            @if($errors->any())
            <div class="alert alert-danger py-2 small">
                <i class="bi bi-exclamation-circle me-1"></i>{{ $errors->first() }}
            </div>
            @endif

            <form method="POST" action="{{ route('inscripcion.paso3.store') }}"
                enctype="multipart/form-data" id="formPaso3" novalidate>
                @csrf

                {{-- ── Datos personales ── --}}
                <p class="fw-semibold text-muted mb-2" style="font-size:.75rem; text-transform:uppercase; letter-spacing:.06em">
                    Datos personales
                </p>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="nombres" class="form-label fw-semibold">
                            Nombres <span class="text-danger">*</span>
                        </label>
                        <input type="text" id="nombres" name="nombres"
                            class="form-control @error('nombres') is-invalid @enderror"
                            value="{{ old('nombres') }}" maxlength="100" required>
                        @error('nombres')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label for="apellidos" class="form-label fw-semibold">
                            Apellidos <span class="text-danger">*</span>
                        </label>
                        <input type="text" id="apellidos" name="apellidos"
                            class="form-control @error('apellidos') is-invalid @enderror"
                            value="{{ old('apellidos') }}" maxlength="100" required>
                        @error('apellidos')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label for="curp" class="form-label fw-semibold">
                            CURP <span class="text-danger">*</span>
                        </label>
                        <input type="text" id="curp" name="curp"
                            class="form-control text-uppercase @error('curp') is-invalid @enderror"
                            value="{{ old('curp') }}" maxlength="18" minlength="18"
                            placeholder="18 caracteres" required>
                        <div id="curpFeedback" class="form-text"></div>
                        @error('curp')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12">
                        <label for="escuela_procedencia" class="form-label fw-semibold">
                            Escuela de procedencia <span class="text-danger">*</span>
                        </label>
                        <input type="text" id="escuela_procedencia" name="escuela_procedencia"
                            class="form-control @error('escuela_procedencia') is-invalid @enderror"
                            value="{{ old('escuela_procedencia') }}" maxlength="200"
                            placeholder="Nombre del bachillerato o preparatoria de origen" required>
                        @error('escuela_procedencia')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <hr class="my-4">

                {{-- ── Documentos ── --}}
                <p class="fw-semibold text-muted mb-3" style="font-size:.75rem; text-transform:uppercase; letter-spacing:.06em">
                    Documentos requeridos
                </p>

                <div class="row g-3 mb-4">

                    {{-- Fotografía --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold d-block">
                            Fotografía <span class="text-danger">*</span><br>
                            <span class="text-muted fw-normal" style="font-size:.75rem">JPG/PNG · máx. 2 MB</span>
                        </label>
                        <div class="upload-zone @error('foto') border-danger @enderror" id="fotoZone">
                            <img id="fotoPreview" src="" alt="Vista previa">
                            <i class="bi bi-person-bounding-box text-muted" id="fotoIcon" style="font-size:2rem"></i>
                            <p class="text-muted small mb-0 mt-2" id="fotoLabel">Selecciona una opción</p>
                            <div class="d-flex gap-2 justify-content-center mt-3">
                                <button type="button" class="btn btn-outline-secondary btn-sm"
                                    onclick="document.getElementById('foto').click()">
                                    <i class="bi bi-folder2-open me-1"></i>Archivo
                                </button>
                                <button type="button" class="btn btn-outline-primary btn-sm"
                                    onclick="abrirCamara()">
                                    <i class="bi bi-camera me-1"></i>Cámara
                                </button>
                            </div>
                            <input type="file" id="foto" name="foto"
                                accept="image/jpeg,image/png" style="display:none">
                        </div>
                        @error('foto')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    {{-- Certificado PDF --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold d-block">
                            Certificado escolar <span class="text-danger">*</span><br>
                            <span class="text-muted fw-normal" style="font-size:.75rem">PDF · máx. 5 MB</span>
                        </label>
                        <div class="upload-zone @error('certificado') border-danger @enderror"
                            id="certZone" onclick="document.getElementById('certificado').click()">
                            <i class="bi bi-file-earmark-pdf text-muted" id="certIcon" style="font-size:2rem"></i>
                            <p class="text-muted small mb-0 mt-2" id="certLabel">Haz clic para seleccionar</p>
                            <input type="file" id="certificado" name="certificado"
                                accept="application/pdf" style="display:none" required>
                        </div>
                        @error('certificado')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                </div>{{-- fin row documentos --}}

                <hr class="my-4">

                {{-- ── Captcha matemático ── --}}
                <p class="fw-semibold text-muted mb-3" style="font-size:.75rem; text-transform:uppercase; letter-spacing:.06em">
                    Verificación de seguridad
                </p>

                <div class="d-flex align-items-center gap-3 mb-4 flex-wrap">
                    <div class="captcha-box" id="captchaPregunta">
                        <span class="spinner-border spinner-border-sm text-secondary"></span>
                    </div>
                    <span class="fs-5 fw-bold text-muted">=</span>
                    <div style="width:120px">
                        <input type="number" id="captcha" name="captcha"
                            class="form-control text-center @error('captcha') is-invalid @enderror"
                            placeholder="?" required min="0">
                        @error('captcha')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <button type="button" class="btn btn-outline-secondary btn-sm"
                        onclick="cargarCaptcha()" title="Nueva pregunta">
                        <i class="bi bi-arrow-clockwise"></i>
                    </button>
                </div>

                <div class="d-flex justify-content-between align-items-center">
                    <a href="{{ route('inscripcion.paso2') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left me-1"></i>Regresar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        Finalizar registro <i class="bi bi-arrow-right ms-1"></i>
                    </button>
                </div>

            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // ── CURP: mayúsculas automáticas y validación de formato ──
        const curpInput = document.getElementById('curp');
        const curpFeedback = document.getElementById('curpFeedback');
        const curpRegex = /^[A-Z]{1}[AEIOU]{1}[A-Z]{2}\d{2}(0[1-9]|1[0-2])(0[1-9]|[12]\d|3[01])[HM]{1}[A-Z]{2}[B-DF-HJ-NP-TV-Z]{3}[A-Z\d]{1}\d{1}$/;

        curpInput.addEventListener('input', function() {
            this.value = this.value.toUpperCase();
            if (this.value.length === 18) {
                if (curpRegex.test(this.value)) {
                    curpFeedback.textContent = '✓ Formato válido';
                    curpFeedback.className = 'form-text text-success';
                } else {
                    curpFeedback.textContent = '✗ Formato inválido';
                    curpFeedback.className = 'form-text text-danger';
                }
            } else {
                curpFeedback.textContent = this.value.length + '/18 caracteres';
                curpFeedback.className = 'form-text text-muted';
            }
        });

        // ── Vista previa fotografía ──
        document.getElementById('foto').addEventListener('change', function() {
            const file = this.files[0];
            if (!file) return;

            const zone = document.getElementById('fotoZone');
            const preview = document.getElementById('fotoPreview');
            const icon = document.getElementById('fotoIcon');
            const label = document.getElementById('fotoLabel');

            const reader = new FileReader();
            reader.onload = e => {
                preview.src = e.target.result;
                preview.style.display = 'block';
                icon.style.display = 'none';
                label.textContent = file.name;
                zone.classList.add('has-file');
            };
            reader.readAsDataURL(file);
        });

        // ── Nombre del PDF seleccionado ──
        document.getElementById('certificado').addEventListener('change', function() {
            const file = this.files[0];
            const zone = document.getElementById('certZone');
            const icon = document.getElementById('certIcon');
            const label = document.getElementById('certLabel');

            if (file) {
                icon.className = 'bi bi-file-earmark-check text-success';
                icon.style.fontSize = '2rem';
                label.textContent = file.name;
                zone.classList.add('has-file');
            }
        });

        let streamCamara = null;
        let fotoCapturada = null; // guarda el Blob de la foto tomada

        function abrirCamara() {
            navigator.mediaDevices.getUserMedia({
                    video: {
                        facingMode: 'user'
                    },
                    audio: false
                })
                .then(stream => {
                    streamCamara = stream;
                    document.getElementById('camaraVideo').srcObject = stream;
                    new bootstrap.Modal(document.getElementById('modalCamara')).show();
                })
                .catch(() => {
                    alert('No se pudo acceder a la cámara. Verifica los permisos del navegador.');
                });
        }

        function cerrarCamara() {
            if (streamCamara) {
                streamCamara.getTracks().forEach(t => t.stop());
                streamCamara = null;
            }
            bootstrap.Modal.getInstance(document.getElementById('modalCamara'))?.hide();
        }

        function capturarFoto() {
            const video = document.getElementById('camaraVideo');
            const canvas = document.getElementById('camaraCanvas');
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            canvas.getContext('2d').drawImage(video, 0, 0);

            canvas.toBlob(blob => {
                fotoCapturada = blob;

                // Mostrar vista previa
                const preview = document.getElementById('fotoPreview');
                preview.src = canvas.toDataURL('image/jpeg');
                preview.style.display = 'block';
                document.getElementById('fotoIcon').style.display = 'none';
                document.getElementById('fotoLabel').textContent = 'foto_camara.jpg';
                document.getElementById('fotoZone').classList.add('has-file');

                cerrarCamara();
            }, 'image/jpeg', 0.9);
        }

        // Interceptar el submit para incluir la foto capturada si aplica
        document.getElementById('formPaso3').addEventListener('submit', function(e) {
            if (fotoCapturada) {
                try {
                    const dt = new DataTransfer();
                    dt.items.add(new File([fotoCapturada], 'foto_camara.jpg', {
                        type: 'image/jpeg'
                    }));
                    document.getElementById('foto').files = dt.files;
                    // El form continúa enviándose normalmente
                } catch (err) {
                    e.preventDefault();
                    const form = new FormData(this);
                    form.set('foto', fotoCapturada, 'foto_camara.jpg');
                    fetch(this.action, {
                            method: 'POST',
                            body: form,
                            redirect: 'manual'
                        })
                        .then(() => {
                            window.location.href = '{{ route("inscripcion.voucher") }}';
                        })
                        .catch(() => alert('Error al enviar el formulario. Intenta de nuevo.'));
                }
            }
        });

        // ── Captcha ──
        function cargarCaptcha() {
            document.getElementById('captchaPregunta').innerHTML =
                '<span class="spinner-border spinner-border-sm text-secondary"></span>';
            document.getElementById('captcha').value = '';

            fetch('{{ route("inscripcion.captcha.refresh") }}')
                .then(r => r.json())
                .then(data => {
                    document.getElementById('captchaPregunta').textContent = data.pregunta + ' =';
                })
                .catch(() => {
                    document.getElementById('captchaPregunta').textContent = '? + ?';
                });
        }

        // Cargar captcha al abrir la página
        cargarCaptcha();
    </script>
    <div class="modal fade" id="modalCamara" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title">Tomar fotografía</h6>
                    <button type="button" class="btn-close" onclick="cerrarCamara()"></button>
                </div>
                <div class="modal-body text-center p-2">
                    <video id="camaraVideo" autoplay playsinline
                        style="width:100%; border-radius:.5rem; max-height:320px; object-fit:cover"></video>
                    <canvas id="camaraCanvas" style="display:none"></canvas>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-primary" onclick="capturarFoto()">
                        <i class="bi bi-camera-fill me-1"></i>Capturar
                    </button>
                </div>
            </div>
        </div>
    </div>
</body>

</html>