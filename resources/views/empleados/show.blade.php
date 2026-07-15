@extends('layouts.app')

@section('title', $empleado->Nombre_Completo)
@section('page_title', 'Expediente del Empleado')

@push('styles')
<style>
    .container { display: flex; flex-direction: column; gap: 1.5rem; }

    .card { background-color: var(--bg-card); border: 1px solid var(--border-color); border-radius: 20px; padding: 1.75rem; box-shadow: var(--shadow-sm); }

    .perfil { display: flex; align-items: center; gap: 1.5rem; flex-wrap: wrap; }
    .perfil-foto { width: 90px; height: 90px; border-radius: 50%; object-fit: cover; background-color: var(--bg-card-hover); border: 1px solid var(--border-color); }
    .perfil-info h1 { font-size: 1.6rem; font-weight: 600; }
    .perfil-info p { color: var(--text-muted); font-size: 0.9rem; margin-top: 0.15rem; }

    .badge { display: inline-block; padding: 0.3rem 0.8rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.03em; margin-top: 0.5rem; }
    .badge-activo { background-color: rgba(22,163,74,0.12); color: var(--activo); }
    .badge-baja { background-color: rgba(220,38,38,0.12); color: var(--baja); }

    .baja-info { margin-top: 0.75rem; font-size: 0.85rem; color: var(--text-muted); }

    h2 { font-size: 1.1rem; font-weight: 600; margin-bottom: 1rem; }

    .contacto-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.5rem; }
    .contacto-grid ul { list-style: none; }
    .contacto-grid li { font-size: 0.875rem; margin-top: 0.35rem; color: var(--text-main); }
    .contacto-grid .titulo { font-weight: 700; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.03em; color: var(--text-muted); }

    .tab-buttons { display: flex; gap: 0.5rem; border-bottom: 1px solid var(--border-color); margin-bottom: 1.25rem; }
    .tab-button { padding: 0.6rem 1rem; font-size: 0.875rem; font-weight: 600; color: var(--text-muted); cursor: pointer; border-bottom: 2px solid transparent; }
    .tab-button.active { color: var(--primary); border-bottom-color: var(--primary); }
    .tab-content { display: none; }
    .tab-content.active { display: block; }

    .campos-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; }
    .campo label { display: block; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.03em; color: var(--text-muted); margin-bottom: 0.25rem; }
    .campo .valor { font-size: 0.9rem; padding: 0.5rem 0.65rem; background-color: var(--bg-page); border: 1px solid var(--border-color); border-radius: 8px; min-height: 2.2rem; }

    table { width: 100%; border-collapse: collapse; }
    thead th { text-align: left; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-muted); padding: 0.75rem; border-bottom: 1px solid var(--border-color); white-space: nowrap; }
    tbody td { padding: 0.65rem 0.75rem; border-bottom: 1px solid var(--border-color); font-size: 0.85rem; white-space: nowrap; }
    tbody tr:last-child td { border-bottom: none; }
    .table-wrap { overflow-x: auto; }
    .vacio { color: var(--text-muted); font-size: 0.85rem; padding: 1rem 0; }

    .resumen-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 1rem; margin-bottom: 1.25rem; }
    .resumen-item { background-color: var(--bg-page); border: 1px solid var(--border-color); border-radius: 12px; padding: 0.75rem 1rem; }
    .resumen-item .label { font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.03em; }
    .resumen-item .val { font-size: 1.1rem; font-weight: 600; margin-top: 0.15rem; }

    .btn-baja { align-self: flex-start; background-color: var(--baja); color: white; border: none; padding: 0.6rem 1.1rem; border-radius: 10px; font-weight: 600; font-size: 0.85rem; cursor: pointer; }
    .btn-baja:hover { opacity: 0.9; }

    .modal-overlay { display: none; position: fixed; inset: 0; background-color: rgba(0,0,0,0.5); align-items: center; justify-content: center; z-index: 200; padding: 1rem; }
    .modal-box { background-color: var(--bg-card); border-radius: 16px; padding: 1.75rem; max-width: 420px; width: 100%; border: 1px solid var(--border-color); }
    .modal-box h2 { font-size: 1.1rem; margin-bottom: 0.25rem; }
    .modal-box select, .modal-box input[type="date"] { width: 100%; padding: 0.5rem 0.65rem; border-radius: 8px; border: 1px solid var(--border-color); background-color: var(--bg-page); color: var(--text-main); font-family: inherit; font-size: 0.85rem; }
    .btn-primary-modal { background-color: var(--primary); color: white; border: none; padding: 0.6rem 1.1rem; border-radius: 10px; font-weight: 600; font-size: 0.85rem; cursor: pointer; }
    .btn-cancelar { background-color: var(--bg-page); border: 1px solid var(--border-color); color: var(--text-main); padding: 0.6rem 1.1rem; border-radius: 10px; font-weight: 600; font-size: 0.85rem; cursor: pointer; }

    a.doc-link { color: var(--primary); text-decoration: none; font-weight: 600; font-size: 0.85rem; }
    a.doc-link:hover { text-decoration: underline; }

    .doc-gen-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 0.75rem; }
    .doc-btn { display: inline-flex; align-items: center; justify-content: center; padding: 0.6rem 1rem; border-radius: 10px; background-color: var(--primary-light); color: var(--primary); font-weight: 600; font-size: 0.85rem; text-decoration: none; border: none; cursor: pointer; }
    .doc-btn:hover { opacity: 0.85; }
    .doc-summary { cursor: pointer; font-weight: 600; font-size: 0.9rem; color: var(--text-main); }
    .doc-inline-form { display: flex; gap: 0.6rem; margin-top: 0.75rem; flex-wrap: wrap; }
    .doc-inline-form input, .doc-inline-form select { padding: 0.5rem 0.65rem; border-radius: 8px; border: 1px solid var(--border-color); background-color: var(--bg-page); color: var(--text-main); font-family: inherit; font-size: 0.85rem; flex: 1; min-width: 160px; }
    .doc-inline-form-wrap { display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); }
</style>
@endpush

@section('content')
    <div class="container">

        @if (session('status'))
            <div class="card" style="border-color: var(--activo); color: var(--activo); font-weight: 600;">{{ session('status') }}</div>
        @endif

        {{-- Perfil --}}
        <div class="card perfil">
            <form id="formFoto" action="{{ route('empleados.foto', $empleado->N_Empleado) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <label for="fotoInput" style="cursor:pointer;" title="Cambiar foto">
                    <img class="perfil-foto" src="{{ $rutaDocumento->Ruta_Foto_Empleado ?? '/Img/avatar.png' }}" alt="Foto de perfil" onerror="this.style.visibility='hidden'">
                </label>
                <input id="fotoInput" type="file" name="foto_perfil" accept="image/*" style="display:none" onchange="document.getElementById('formFoto').submit()">
            </form>
            <div class="perfil-info" style="flex: 1;">
                <h1>{{ $empleado->Nombre_Completo }}</h1>
                <p>{{ $laboral->puesto->Departamento ?? 'Sin departamento' }} &middot; {{ $laboral->puesto->Nombre_Puesto ?? 'Sin puesto' }}</p>
                <p>N° Empleado: {{ $empleado->N_Empleado }}</p>
                <span class="badge {{ $laboral->Estatus === 'ACTIVO' ? 'badge-activo' : 'badge-baja' }}">{{ $laboral->Estatus }}</span>

                @php
                    $ultimaFecha = $fechasLaborales->first();
                @endphp
                @if ($laboral->Estatus === 'BAJA')
                    <div class="baja-info">
                        @if ($ultimaFecha && $ultimaFecha->comentario)
                            <div><strong>Comentario:</strong> {{ $ultimaFecha->comentario }}</div>
                        @endif
                        @if ($ultimaFecha && $ultimaFecha->recontratable)
                            <div><strong>Recontratable:</strong> {{ $ultimaFecha->recontratable }}</div>
                        @endif
                    </div>
                @endif
            </div>

            @if ($laboral->Estatus === 'ACTIVO')
                <button type="button" class="btn-baja" onclick="document.getElementById('modalBaja').style.display='flex'">Dar de Baja</button>
            @endif
        </div>

        @if ($laboral->Estatus === 'ACTIVO')
            <div id="modalBaja" class="modal-overlay">
                <div class="modal-box">
                    <h2>Dar de Baja al Empleado</h2>
                    <form method="POST" action="{{ route('empleados.baja', $empleado->N_Empleado) }}">
                        @csrf
                        <div class="campo" style="margin-top:1rem">
                            <label for="fecha_baja">Fecha de Baja</label>
                            <input type="date" id="fecha_baja" name="fecha_baja" required>
                        </div>
                        <div class="campo" style="margin-top:0.75rem">
                            <label for="motivo_baja">Motivo de Baja</label>
                            <select id="motivo_baja" name="motivo_baja" required>
                                <option value="">Seleccione un motivo</option>
                                <option value="Renuncia">Renuncia</option>
                                <option value="Defuncion">Defunción</option>
                                <option value="No Renovacion">No Renovación</option>
                                <option value="Ausentismo">Ausentismo</option>
                                <option value="Desvinculacion">Desvinculación</option>
                                <option value="Solicitud de pension">Solicitud de pensión</option>
                                <option value="Incapacidad Total y/o Invalidez">Incapacidad Total y/o Invalidez</option>
                            </select>
                        </div>
                        <div class="campo" style="margin-top:0.75rem">
                            <label for="comentarios">Comentarios</label>
                            <textarea id="comentarios" name="comentarios" maxlength="500" rows="3" style="width:100%; padding:0.5rem; border-radius:8px; border:1px solid var(--border-color); background-color:var(--bg-page); color:var(--text-main); font-family:inherit;"></textarea>
                        </div>
                        <div class="campo" style="margin-top:0.75rem">
                            <label for="recontatable">¿Es recontratable?</label>
                            <select id="recontatable" name="recontratable">
                                <option value="Si">Sí</option>
                                <option value="No">No</option>
                            </select>
                        </div>
                        <div style="display:flex; gap:0.75rem; margin-top:1.25rem;">
                            <button type="submit" class="btn-primary-modal">Confirmar Baja</button>
                            <button type="button" class="btn-cancelar" onclick="document.getElementById('modalBaja').style.display='none'">Cancelar</button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        {{-- Contacto --}}
        <div class="card">
            <h2>Contacto</h2>
            <div class="contacto-grid">
                <ul>
                    <li class="titulo">Personal</li>
                    <li>{{ $empleado->Celular ?: 'Sin celular' }}</li>
                    <li>{{ $empleado->Correo_Electronico ?: 'Sin correo' }}</li>
                </ul>
                <ul>
                    <li class="titulo">Empresarial</li>
                    <li>{{ $laboral->Telefono_Empresarial ?: 'Sin teléfono' }}</li>
                    <li>{{ $laboral->Correo_Empresarial ?: 'Sin correo' }}</li>
                    <li>{{ $laboral->Extension ?: 'Sin extensión' }}</li>
                </ul>
                <ul>
                    <li class="titulo">Team Building</li>
                    <li>Reclutador: {{ $laboral->Reclutador ?: 'N/D' }}</li>
                    <li>Entrevista: {{ $laboral->Entrevista ?: 'N/D' }}</li>
                    <li>Asesor de Ingreso: {{ $laboral->Asesor_Ingreso ?: 'N/D' }}</li>
                </ul>
            </div>
        </div>

        {{-- Tabs Datos Personales / Laborales --}}
        <div class="card">
            <div class="tab-buttons" style="justify-content: space-between; align-items: center;">
                <div style="display:flex;">
                    <div class="tab-button active" data-tab="tabPersonales">Datos Personales</div>
                    <div class="tab-button" data-tab="tabLaborales">Datos Laborales</div>
                </div>
            </div>

            <div id="tabPersonales" class="tab-content active">
                <div style="text-align:right; margin-bottom:0.75rem;">
                    <a href="{{ route('empleados.personales.edit', $empleado->N_Empleado) }}" class="ver-link">Editar</a>
                </div>
                <div class="campos-grid">
                    <div class="campo"><label>IMSS</label><div class="valor">{{ $empleado->IMSS ?: '—' }}</div></div>
                    <div class="campo"><label>CURP</label><div class="valor">{{ $empleado->CURP ?: '—' }}</div></div>
                    <div class="campo"><label>RFC</label><div class="valor">{{ $empleado->RFC ?: '—' }}</div></div>
                    <div class="campo"><label>Sexo</label><div class="valor">{{ $empleado->Sexo ?: '—' }}</div></div>
                    <div class="campo"><label>Fecha Nacimiento</label><div class="valor">{{ $empleado->Fecha_Nacimiento ?: '—' }}</div></div>
                    <div class="campo"><label>Edad</label><div class="valor">{{ $empleado->Edad ?: '—' }}</div></div>
                    <div class="campo"><label>Ciudad</label><div class="valor">{{ $empleado->Ciudad ?: '—' }}</div></div>
                    <div class="campo"><label>Estado</label><div class="valor">{{ $empleado->Estado ?: '—' }}</div></div>
                    <div class="campo"><label>Domicilio</label><div class="valor">{{ $empleado->Domicilio ?: '—' }}</div></div>
                    <div class="campo"><label>Estado Civil</label><div class="valor">{{ $empleado->Estado_Civil ?: '—' }}</div></div>
                    <div class="campo"><label>Teléfono</label><div class="valor">{{ $empleado->Telefono ?: '—' }}</div></div>
                </div>

                <h2 style="margin-top:1.5rem">Beneficiario</h2>
                <div class="campos-grid">
                    <div class="campo"><label>Contacto de Emergencia</label><div class="valor">{{ $empleado->Contacto_Emergencia ?: '—' }}</div></div>
                    <div class="campo"><label>Teléfono de Emergencia</label><div class="valor">{{ $empleado->Tel_Emergencia ?: '—' }}</div></div>
                    <div class="campo"><label>Parentesco</label><div class="valor">{{ $empleado->Parentesco_B ?: '—' }}</div></div>
                    <div class="campo"><label>Nombre Completo</label><div class="valor">{{ $empleado->Nombre_Completo_B ?: '—' }}</div></div>
                    <div class="campo"><label>Domicilio</label><div class="valor">{{ $empleado->Domicilio_B ?: '—' }}</div></div>
                    <div class="campo"><label>Teléfono</label><div class="valor">{{ $empleado->Telefono_B ?: '—' }}</div></div>
                    <div class="campo"><label>Fecha Nacimiento</label><div class="valor">{{ $empleado->F_NAC_B ?: '—' }}</div></div>
                </div>
            </div>

            <div id="tabLaborales" class="tab-content">
                <div style="text-align:right; margin-bottom:0.75rem;">
                    <a href="{{ route('empleados.laborales.edit', $laboral->N_Empleado) }}" class="ver-link">Editar</a>
                </div>
                <div class="campos-grid">
                    <div class="campo"><label>Departamento</label><div class="valor">{{ $laboral->puesto->Departamento ?? '—' }}</div></div>
                    <div class="campo"><label>Puesto</label><div class="valor">{{ $laboral->puesto->Nombre_Puesto ?? '—' }}</div></div>
                    <div class="campo"><label>Nomenclatura</label><div class="valor">{{ $laboral->Nomeclatura ?: '—' }}</div></div>
                    <div class="campo"><label>Física</label><div class="valor">{{ $laboral->Fisica ?: '—' }}</div></div>
                    <div class="campo"><label>Salario Diario Bruto</label><div class="valor">{{ $laboral->Salario_Diario ?: '—' }}</div></div>
                    <div class="campo"><label>Sueldo Bruto</label><div class="valor">{{ $laboral->Sueldo ?: '—' }}</div></div>
                    <div class="campo"><label>Salario Diario Neto</label><div class="valor">{{ $laboral->Salario_Diario_Neto ?: '—' }}</div></div>
                    <div class="campo"><label>Sueldo Neto</label><div class="valor">{{ $laboral->Sueldo_Neto ?: '—' }}</div></div>
                    <div class="campo"><label>Compensación</label><div class="valor">{{ $laboral->Hora_Comida ?: '—' }}</div></div>
                    <div class="campo"><label>Efectivo</label><div class="valor">{{ $laboral->Efectivo ?: '—' }}</div></div>
                    <div class="campo"><label>Comisión</label><div class="valor">{{ $laboral->Comision ?: '—' }}</div></div>
                    <div class="campo"><label>KPI</label><div class="valor">{{ $laboral->KPI ?: '—' }}</div></div>
                    <div class="campo"><label>Horario</label><div class="valor">{{ $laboral->Horario ?: '—' }}</div></div>
                </div>
            </div>
        </div>

        {{-- Fechas Laborales --}}
        <div class="card">
            <h2>Fechas Laborales</h2>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Fecha Ingreso</th><th>Fecha Egreso</th><th>Fecha Alta IMSS</th><th>Fecha Baja IMSS</th></tr></thead>
                    <tbody>
                        @forelse($fechasLaborales as $fecha)
                            <tr>
                                <td>{{ $fecha->Fecha_Ingreso ?: '—' }}</td>
                                <td>{{ $fecha->Fecha_Egreso ?: '—' }}</td>
                                <td>{{ $fecha->Fecha_Alta_IMSS ?: '—' }}</td>
                                <td>{{ $fecha->Fecha_Baja_IMSS ?: '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="vacio">No se encontraron fechas laborales.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Documentos IMSS --}}
        <div class="card">
            <h2>Documentos IMSS</h2>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Tipo</th><th>Fecha</th><th>Documento</th></tr></thead>
                    <tbody>
                        @forelse($documentosImss as $doc)
                            <tr>
                                <td>{{ $doc->tipo ?: '—' }}</td>
                                <td>{{ $doc->fecha ?? $doc->fecha_subida ?? '—' }}</td>
                                <td>
                                    @if($doc->ruta_documento)
                                        <a class="doc-link" href="{{ $doc->ruta_documento }}" target="_blank">Ver documento</a>
                                    @else
                                        Sin documento
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="vacio">No se encontraron documentos IMSS.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Historial de Asignaciones --}}
        <div class="card">
            <h2>Historial de Asignaciones</h2>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Producto</th><th>Cantidad</th><th>Fecha Asignación</th><th>Fecha Renovación</th><th>Documento</th></tr></thead>
                    <tbody>
                        @forelse($asignaciones as $asignacion)
                            <tr>
                                <td>{{ $asignacion->producto->Nombre ?? '—' }}</td>
                                <td>{{ $asignacion->cantidad ?: '—' }}</td>
                                <td>{{ $asignacion->Fecha_Asignacion ?: '—' }}</td>
                                <td>{{ $asignacion->Fecha_Renovacion ?: '—' }}</td>
                                <td>
                                    @if($asignacion->Ruta_Documento)
                                        <a class="doc-link" href="{{ $asignacion->Ruta_Documento }}" target="_blank">Ver documento</a>
                                    @else
                                        Sin documento
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="vacio">No hay asignaciones registradas.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Historial de Pagos --}}
        <div class="card">
            <h2>Historial de Pagos</h2>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Producto</th><th>N° Pago</th><th>Monto Mensual</th><th>Fecha</th><th>Estatus</th></tr></thead>
                    <tbody>
                        @forelse($pagos as $pago)
                            <tr>
                                <td>{{ $pago->producto->Nombre ?? '—' }}</td>
                                <td>{{ $pago->Numero_Pago ?: '—' }}</td>
                                <td>{{ $pago->Monto_Mensual ?: '—' }}</td>
                                <td>{{ $pago->Fecha_Inicio ?: '—' }}</td>
                                <td>{{ $pago->estatus ?: '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="vacio">No hay pagos registrados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Resumen Vacaciones --}}
        @php
            $primeraFecha = $fechasLaborales->sortBy('Fecha_Ingreso')->first();
        @endphp
        <div class="card">
            <h2>Vacaciones</h2>
            <div class="resumen-grid">
                <div class="resumen-item"><div class="label">Aniversario Laboral</div><div class="val">{{ $primeraFecha->Fecha_Ingreso ?? '—' }}</div></div>
                <div class="resumen-item"><div class="label">Física</div><div class="val">{{ $laboral->Fisica ?: '—' }}</div></div>
                <div class="resumen-item"><div class="label">Responsable</div><div class="val">{{ $responsable ?: '—' }}</div></div>
                <div class="resumen-item"><div class="label">Saldo Inicial</div><div class="val">{{ $laboral->Dias_Vacaciones_Iniciales ?? '—' }}</div></div>
                <div class="resumen-item"><div class="label">Saldo Actual</div><div class="val">{{ $laboral->Dias_Vacaciones_Disponibles ?? '—' }}</div></div>
            </div>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Concepto</th><th>Estatus</th><th>Fecha Inicial</th><th>Fecha Final</th><th>Tomadas</th><th>Saldo</th></tr></thead>
                    <tbody>
                        @forelse($vacacionesHistorial as $vac)
                            <tr>
                                <td>{{ $vac->Concepto ?: '—' }}</td>
                                <td>{{ $vac->Estatus ?: '—' }}</td>
                                <td>{{ $vac->Fecha_Inicial ?: '—' }}</td>
                                <td>{{ $vac->Fecha_Final ?: '—' }}</td>
                                <td>{{ $vac->Tomadas ?: '—' }}</td>
                                <td>{{ $vac->Saldo ?: '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="vacio">Sin solicitudes de vacaciones.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Resumen Incapacidades --}}
        <div class="card">
            <h2>Incapacidades</h2>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Descripción</th><th>Fecha Inicio</th><th>Fecha Fin</th><th>Total Días</th><th>Documento</th></tr></thead>
                    <tbody>
                        @forelse($incapacidades as $inc)
                            <tr>
                                <td>{{ $inc->Descripcion ?: '—' }}</td>
                                <td>{{ $inc->Fecha_Inicio ?: '—' }}</td>
                                <td>{{ $inc->Fecha_Fin ?: '—' }}</td>
                                <td>{{ $inc->Total_Dias ?: '—' }}</td>
                                <td>
                                    @if($inc->Ruta_Documento)
                                        <a class="doc-link" href="{{ $inc->Ruta_Documento }}" target="_blank">Ver documento</a>
                                    @else
                                        Sin documento
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="vacio">Sin incapacidades registradas.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Documentos generados --}}
        <div class="card">
            <h2>Generar Documentos</h2>
            <div class="doc-gen-grid">
                <a class="doc-btn" href="{{ route('empleados.documentos.cartaOferta', $empleado->N_Empleado) }}">Carta Oferta</a>
                <a class="doc-btn" href="{{ route('empleados.documentos.cartaConfidencialidad', $empleado->N_Empleado) }}">Carta Confidencialidad</a>
                <a class="doc-btn" href="{{ route('empleados.documentos.politicaPuntualidad', $empleado->N_Empleado) }}">Política Puntualidad</a>
                <a class="doc-btn" href="{{ route('empleados.contratos.determinado', $empleado->N_Empleado) }}">Contrato Determinado</a>
                <a class="doc-btn" href="{{ route('empleados.contratos.indeterminado', $empleado->N_Empleado) }}">Contrato Indeterminado</a>
                <a class="doc-btn" href="{{ route('empleados.contratos.convenio', $empleado->N_Empleado) }}">Convenio Confidencialidad</a>
            </div>

            <details style="margin-top:1.25rem;">
                <summary class="doc-summary">Ficha Nómina</summary>
                <form method="GET" action="{{ route('empleados.documentos.fichaNomina', $empleado->N_Empleado) }}" class="doc-inline-form">
                    <input type="text" name="Autoriza" placeholder="Autoriza (nombre)">
                    <button type="submit" class="doc-btn">Generar</button>
                </form>
            </details>

            <details style="margin-top:0.75rem;">
                <summary class="doc-summary">Folio Baja</summary>
                <form method="GET" action="{{ route('empleados.documentos.folioBaja', $empleado->N_Empleado) }}" class="doc-inline-form doc-inline-form-wrap">
                    <input type="text" name="dias_laborados" placeholder="Días laborados">
                    <input type="text" name="Concepto_Adicional" placeholder="Concepto adicional">
                    <input type="text" name="Incidencias_Descontar" placeholder="Incidencias a descontar">
                    <input type="text" name="Incidencias_Hrs" placeholder="Horas de incidencias">
                    <input type="text" name="Importe" placeholder="Importe">
                    <select name="motivo_baja">
                        <option value="">Motivo de baja</option>
                        <option value="Renuncia">Renuncia</option>
                        <option value="Defuncion">Defunción</option>
                        <option value="No Renovacion">No Renovación</option>
                        <option value="Ausentismo">Ausentismo</option>
                        <option value="Desvinculacion">Desvinculación</option>
                        <option value="Solicitud de pension">Solicitud de pensión</option>
                        <option value="Incapacidad Total y/o Invalidez">Incapacidad Total y/o Invalidez</option>
                    </select>
                    <input type="text" name="observaciones" placeholder="Observaciones" style="grid-column: 1/-1;">
                    <button type="submit" class="doc-btn" style="grid-column: 1/-1;">Generar</button>
                </form>
            </details>
        </div>

        {{-- Documentos subidos --}}
        <div class="card">
            <h2>Documentos Subidos</h2>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Nombre</th><th>Fecha</th><th>Documento</th></tr></thead>
                    <tbody>
                        @forelse($documentosGenerados as $doc)
                            <tr>
                                <td>{{ $doc->Nombre_Documento }}</td>
                                <td>{{ $doc->Fecha_Subida }}</td>
                                <td><a class="doc-link" href="{{ $doc->Ruta_Documento }}" target="_blank">Ver documento</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="vacio">Sin documentos subidos.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <form method="POST" action="{{ route('empleados.documentos.store', $empleado->N_Empleado) }}" enctype="multipart/form-data" class="doc-inline-form" style="margin-top:1rem;">
                @csrf
                <input type="text" name="nombre_documento" placeholder="Nombre del documento" required>
                <input type="file" name="documento" required>
                <button type="submit" class="doc-btn">Subir Documento</button>
            </form>
            @error('documento')
                <p style="color: var(--baja); font-size: 0.8rem; margin-top: 0.5rem;">{{ $message }}</p>
            @enderror
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('.tab-button').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.tab-button').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
            btn.classList.add('active');
            document.getElementById(btn.dataset.tab).classList.add('active');
        });
    });
</script>
@endpush
