@extends('layouts.app')

@section('title', 'Reclutamiento')

@section('content')
<div class="reclutamiento-container">
    <!-- Encabezado de Página -->
    <div class="incidencias-header-card">
        <div class="header-icon-box">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                <path d="M11 8v6"></path>
                <path d="M8 11h6"></path>
            </svg>
        </div>
        <div class="header-text-box">
            <h1>Gestión de Reclutamiento</h1>
            <p>Comparativa de plantilla estimada vs actual y seguimiento de candidatos en proceso de selección.</p>
        </div>
    </div>

    <!-- Resumen de Estados -->
    <div class="control-filters-card">
        <div class="chips-row">
            @foreach($estados as $estado)
                <span class="chip" style="background-color: {{ $estadoColores[$estado] }};">{{ $estado }}: {{ $contadorEstados[$estado] ?? 0 }}</span>
            @endforeach
            <span class="chip chip-total">📋 Total: {{ $totalCandidatos }}</span>
        </div>
        <div class="chips-row" style="margin-top: 0.6rem;">
            <span class="chip chip-nuevos">✨ Nuevos hoy: {{ $nuevosHoy }}</span>
            <span class="chip chip-nuevos">📅 Esta semana: {{ $nuevosSemana }}</span>
            <span class="chip chip-nuevos">📆 Este mes: {{ $nuevosMes }}</span>
        </div>
    </div>

    <!-- Gráfica -->
    <div class="table-container-card" style="padding: 1.25rem; margin-bottom: 1rem;">
        <div class="table-toolbar-title" style="margin-bottom: 0.75rem;">📈 Nuevos candidatos esta semana</div>
        <canvas id="graficoCandidatos" height="70"></canvas>
    </div>

    <div class="reclutamiento-grid">
        <!-- Comparativa -->
        <div class="table-container-card">
            <div class="table-header-toolbar">
                <div class="table-toolbar-title">Comparativa Estimada vs Actual</div>
                <div class="search-input-box compact" style="margin-top: 0.75rem;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="search-icon">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                    <input type="text" id="buscarComparativa" onkeyup="filtrarTabla('buscarComparativa', 'tablaComparativa')" placeholder="Buscar en comparativa...">
                </div>
            </div>
            <div class="table-responsive">
                <table class="premium-table" id="tablaComparativa">
                    <thead>
                        <tr>
                            <th onclick="ordenarTabla('tablaComparativa', 0, false)">Ubicación ⇅</th>
                            <th onclick="ordenarTabla('tablaComparativa', 1, false)">Puesto ⇅</th>
                            <th onclick="ordenarTabla('tablaComparativa', 2, true)">Estimado ⇅</th>
                            <th onclick="ordenarTabla('tablaComparativa', 3, true)">Actual ⇅</th>
                            <th onclick="ordenarTabla('tablaComparativa', 4, true)">Diferencia ⇅</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($comparativa as $row)
                            <tr>
                                <td>{{ $row->sucursal }}</td>
                                <td>{{ $row->puesto }}</td>
                                <td>{{ $row->estimado }}</td>
                                <td>{{ $row->actual }}</td>
                                <td>
                                    {{ $row->diferencia }}
                                    @if($row->diferencia < 0)
                                        <span style="color: var(--baja);">⚠</span>
                                    @elseif($row->diferencia > 0)
                                        <span style="color: var(--activo);">✔</span>
                                    @else
                                        <span style="color: var(--text-muted);">=</span>
                                    @endif
                                </td>
                                <td>
                                    <button type="button" class="btn-primary-action" style="padding: 0.4rem 0.8rem; font-size: 0.8rem; height: auto;"
                                        onclick="abrirModalNuevo('{{ addslashes($row->puesto) }}', '{{ addslashes($row->sucursal) }}')">
                                        ➕ Agregar
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="no-records-row">No hay datos de comparativa registrados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div style="padding: 1rem 1.25rem;">
                {{ $comparativa->links() }}
            </div>
        </div>

        <!-- Candidatos -->
        <div class="table-container-card">
            <div class="table-header-toolbar">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                    <div class="table-toolbar-title">Candidatos en Proceso</div>
                    <button type="button" class="btn-primary-action" style="background-color: #28a745;" onclick="exportarExcelCandidatos()">📥 Exportar a Excel</button>
                </div>
                <div class="search-input-box compact" style="margin-top: 0.75rem;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="search-icon">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                    <input type="text" id="buscarCandidatos" onkeyup="filtrarTabla('buscarCandidatos', 'tablaCandidatos')" placeholder="Buscar en candidatos...">
                </div>
            </div>
            <div class="table-responsive">
                @if($candidatos->isEmpty())
                    <div class="empty-state-card">
                        <div class="empty-state-icon">🗂️</div>
                        <h4>No hay candidatos en proceso</h4>
                    </div>
                @else
                    <table class="premium-table" id="tablaCandidatos">
                        <thead>
                            <tr>
                                <th onclick="ordenarTabla('tablaCandidatos', 0, false)">Nombre ⇅</th>
                                <th onclick="ordenarTabla('tablaCandidatos', 1, false)">Ubicación ⇅</th>
                                <th onclick="ordenarTabla('tablaCandidatos', 2, false)">Puesto ⇅</th>
                                <th onclick="ordenarTabla('tablaCandidatos', 3, false)">Reclutador ⇅</th>
                                <th onclick="ordenarTabla('tablaCandidatos', 4, false)">Fecha Postulación ⇅</th>
                                <th onclick="ordenarTabla('tablaCandidatos', 5, false)">Estado ⇅</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($candidatos as $c)
                                <tr>
                                    <td class="font-semibold">{{ $c->nombre }}</td>
                                    <td>{{ $c->Fisica }}</td>
                                    <td>{{ $c->Puesto }}</td>
                                    <td>{{ $c->Reclutador }}</td>
                                    <td>{{ \Carbon\Carbon::parse($c->fecha_postulacion)->format('d/m/Y') }}</td>
                                    <td>
                                        <span class="status-badge" style="background-color: {{ $estadoColores[$c->estado] }}22; color: {{ $estadoColores[$c->estado] }};" title="Último cambio por: {{ $c->ultimoHistorial->usuario->Nombre ?? 'Sin registro' }}">
                                            {{ $c->estado }}
                                        </span>
                                    </td>
                                    @php
                                        $detalle = collect($c)->only(['id','nombre','correo','telefono','Fisica','Puesto','Reclutador','fecha_postulacion','estado','Motivo_rechazo','Medio_Reclutamiento','notas','cv'])->toArray();
                                        $detalle['ultimo_usuario'] = $c->ultimoHistorial->usuario->Nombre ?? null;
                                    @endphp
                                    <td>
                                        <button type="button" class="btn-primary-action" style="padding: 0.4rem 0.8rem; font-size: 0.8rem; height: auto;"
                                            onclick='abrirModalDetalle(@json($detalle, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT))'>
                                            Ver Detalles
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div style="padding: 1rem 1.25rem;">
                        {{ $candidatos->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal Nuevo Candidato -->
<div id="modalNuevoCandidato" class="modal-overlay hidden">
    <div class="modal-box">
        <span class="modal-close" onclick="cerrarModalNuevo()">✕</span>
        <h2>Registrar Nuevo Candidato</h2>
        <form id="formNuevoCandidato" onsubmit="return guardarNuevoCandidato();">
            <div class="form-group">
                <label>Nombre:</label>
                <input type="text" id="nuevo_nombre" required>
            </div>
            <div class="form-group">
                <label>Correo:</label>
                <input type="email" id="nuevo_correo">
            </div>
            <div class="form-group">
                <label>Teléfono:</label>
                <input type="text" id="nuevo_telefono">
            </div>
            <div class="form-group">
                <label>Sucursal:</label>
                <input type="text" id="nuevo_sucursal" readonly>
            </div>
            <div class="form-group">
                <label>Puesto:</label>
                <input type="text" id="nuevo_puesto" readonly>
            </div>
            <div class="form-group">
                <label>Reclutador:</label>
                <select id="nuevo_reclutador" required>
                    <option value="">Seleccione un reclutador</option>
                    @foreach($reclutadores as $nombre)
                        <option value="{{ $nombre }}">{{ $nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Medio de Reclutamiento:</label>
                <select id="nuevo_medio" required>
                    <option value="">Seleccione un medio</option>
                    <option value="LinkedIn">LinkedIn</option>
                    <option value="Facebook">Facebook</option>
                    <option value="Computrabajo">Computrabajo</option>
                    <option value="Indeed">Indeed</option>
                    <option value="Recomendación">Recomendación</option>
                    <option value="Otros">Otros</option>
                </select>
            </div>
            <div class="form-group">
                <label>CV (PDF):</label>
                <input type="file" id="nuevo_cv" accept=".pdf">
            </div>
            <button type="submit" class="btn-primary-action" style="width: 100%; justify-content: center;">Guardar Candidato</button>
        </form>
    </div>
</div>

<!-- Modal Detalles del Candidato -->
<div id="modalCandidato" class="modal-overlay hidden">
    <div class="modal-box">
        <span class="modal-close" onclick="cerrarModal()">✕</span>
        <h2>Detalles del Candidato</h2>
        <form id="formCandidato" onsubmit="return guardarCandidato();">
            <input type="hidden" id="modal_id">
            <div class="form-group">
                <label>Ubicación:</label>
                <input type="text" id="modal_sucursal" disabled>
            </div>
            <div class="form-group">
                <label>Puesto:</label>
                <input type="text" id="modal_puesto" disabled>
            </div>
            <div class="form-group">
                <label>Reclutador:</label>
                <input type="text" id="modal_reclutador" disabled>
            </div>
            <div class="form-group">
                <label>Nombre:</label>
                <input type="text" id="modal_nombre" disabled>
            </div>
            <div class="form-group">
                <label>Correo:</label>
                <input type="email" id="modal_correo" disabled>
            </div>
            <div class="form-group">
                <label>Teléfono:</label>
                <input type="text" id="modal_telefono" disabled>
            </div>
            <div class="form-group">
                <label>Estado:</label>
                <select id="modal_estado">
                    @foreach($estados as $estado)
                        <option value="{{ $estado }}">{{ $estado }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Medio de Reclutamiento:</label>
                <input type="text" id="modal_medio_reclutamiento" disabled>
            </div>
            <div class="form-group">
                <label>Motivo de Rechazo:</label>
                <select id="modal_motivo_rechazo">
                    <option value="">Seleccione un motivo</option>
                    <option value="No cumple requisitos">No cumple requisitos</option>
                    <option value="No se presentó a la entrevista">No se presentó a la entrevista</option>
                    <option value="Rechazo del candidato">Rechazo del candidato</option>
                    <option value="Otros">Otros</option>
                </select>
            </div>
            <div class="form-group">
                <label>Notas:</label>
                <textarea id="modal_notas" rows="4"></textarea>
            </div>
            <div class="form-group" id="modal_cv_section"></div>
            <div class="form-group" id="modal_historial" style="font-size: 0.8rem;"></div>
            <button type="submit" class="btn-primary-action" style="width: 100%; justify-content: center;">Guardar Cambios</button>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.sheetjs.com/xlsx-0.20.0/package/dist/xlsx.full.min.js"></script>

<script>
    function filtrarTabla(inputId, tablaId) {
        const filtro = document.getElementById(inputId).value.toLowerCase();
        const filas = document.querySelectorAll('#' + tablaId + ' tbody tr');
        filas.forEach(fila => {
            fila.style.display = fila.innerText.toLowerCase().includes(filtro) ? '' : 'none';
        });
    }

    function ordenarTabla(tablaId, columnIndex, esNumerico) {
        const tabla = document.getElementById(tablaId);
        const filas = Array.from(tabla.tBodies[0].rows);
        const asc = tabla.getAttribute('data-sort') !== 'asc';

        filas.sort((a, b) => {
            const cellA = a.cells[columnIndex].textContent.trim();
            const cellB = b.cells[columnIndex].textContent.trim();
            if (esNumerico) {
                return asc ? parseFloat(cellA) - parseFloat(cellB) : parseFloat(cellB) - parseFloat(cellA);
            }
            return asc ? cellA.localeCompare(cellB) : cellB.localeCompare(cellA);
        });

        filas.forEach(fila => tabla.tBodies[0].appendChild(fila));
        tabla.setAttribute('data-sort', asc ? 'asc' : 'desc');
    }

    function exportarExcelCandidatos() {
        const tabla = document.getElementById('tablaCandidatos');
        if (!tabla) return;
        const wb = XLSX.utils.book_new();
        const ws = XLSX.utils.table_to_sheet(tabla);
        XLSX.utils.book_append_sheet(wb, ws, 'Candidatos');
        XLSX.writeFile(wb, 'Candidatos.xlsx');
    }

    // --- Modal Nuevo Candidato ---
    function abrirModalNuevo(puesto, sucursal) {
        document.getElementById('formNuevoCandidato').reset();
        document.getElementById('nuevo_puesto').value = puesto;
        document.getElementById('nuevo_sucursal').value = sucursal;
        document.getElementById('modalNuevoCandidato').classList.remove('hidden');
    }

    function cerrarModalNuevo() {
        document.getElementById('modalNuevoCandidato').classList.add('hidden');
    }

    function guardarNuevoCandidato() {
        const formData = new FormData();
        formData.append('nombre', document.getElementById('nuevo_nombre').value);
        formData.append('correo', document.getElementById('nuevo_correo').value);
        formData.append('telefono', document.getElementById('nuevo_telefono').value);
        formData.append('sucursal', document.getElementById('nuevo_sucursal').value);
        formData.append('puesto', document.getElementById('nuevo_puesto').value);
        formData.append('reclutador', document.getElementById('nuevo_reclutador').value);
        formData.append('medio_reclutamiento', document.getElementById('nuevo_medio').value);

        const archivoCV = document.getElementById('nuevo_cv');
        if (archivoCV.files.length > 0) {
            formData.append('cv', archivoCV.files[0]);
        }

        fetch('{{ route("reclutamiento.store") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert(data.mensaje);
                location.reload();
            } else {
                alert('Error: ' + (data.error || 'desconocido'));
            }
        })
        .catch(() => alert('Error inesperado al guardar.'));

        return false;
    }

    // --- Modal Detalles Candidato ---
    function abrirModalDetalle(candidato) {
        document.getElementById('modal_id').value = candidato.id;
        document.getElementById('modal_nombre').value = candidato.nombre;
        document.getElementById('modal_correo').value = candidato.correo || '';
        document.getElementById('modal_telefono').value = candidato.telefono || '';
        document.getElementById('modal_estado').value = candidato.estado;
        document.getElementById('modal_sucursal').value = candidato.Fisica || 'No especificado';
        document.getElementById('modal_puesto').value = candidato.Puesto || 'No especificado';
        document.getElementById('modal_reclutador').value = candidato.Reclutador || 'No asignado';
        document.getElementById('modal_medio_reclutamiento').value = candidato.Medio_Reclutamiento || '';
        document.getElementById('modal_motivo_rechazo').value = candidato.Motivo_rechazo || '';
        document.getElementById('modal_notas').value = candidato.notas || '';

        if (candidato.cv) {
            document.getElementById('modal_cv_section').innerHTML = `<label>CV:</label><br><a href="${candidato.cv}" target="_blank">Ver CV</a>`;
        } else {
            document.getElementById('modal_cv_section').innerHTML = `<label>Subir CV:</label><input type="file" id="modal_cv_file" accept=".pdf">`;
        }

        fetch(`/reclutamiento/${candidato.id}/historial`)
            .then(res => res.text())
            .then(html => {
                const ultimoUsuario = candidato.ultimo_usuario || 'Sin registro';
                document.getElementById('modal_historial').innerHTML =
                    `<div style="margin-bottom:8px; font-weight:bold; color: var(--primary);">Último cambio realizado por: ${ultimoUsuario}</div>` + html;
            });

        document.getElementById('modalCandidato').classList.remove('hidden');
    }

    function cerrarModal() {
        document.getElementById('modalCandidato').classList.add('hidden');
    }

    function guardarCandidato() {
        const id = document.getElementById('modal_id').value;
        const formData = new FormData();
        formData.append('estado', document.getElementById('modal_estado').value);
        formData.append('motivo_rechazo', document.getElementById('modal_motivo_rechazo').value);
        formData.append('notas', document.getElementById('modal_notas').value);

        const archivoCV = document.getElementById('modal_cv_file');
        if (archivoCV && archivoCV.files.length > 0) {
            formData.append('cv', archivoCV.files[0]);
        }

        fetch(`/reclutamiento/${id}`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert(data.mensaje);
                location.reload();
            } else {
                alert('Error: ' + (data.error || 'desconocido'));
            }
        })
        .catch(() => alert('Error inesperado al guardar.'));

        return false;
    }

    // --- Gráfica semanal ---
    const ctxReclutamiento = document.getElementById('graficoCandidatos').getContext('2d');
    new Chart(ctxReclutamiento, {
        type: 'line',
        data: {
            labels: @json($diasSemana),
            datasets: [
                {
                    label: '🆕 Nuevos candidatos',
                    data: @json($valoresSemana),
                    borderColor: '#005996',
                    backgroundColor: 'rgba(0,89,150,0.1)',
                    fill: true,
                    tension: 0.3
                },
                @foreach($estadoDatasets as $ds)
                {
                    label: '{{ $ds['label'] }}',
                    data: @json($ds['data']),
                    borderColor: '{{ $ds['borderColor'] }}',
                    backgroundColor: '{{ $ds['backgroundColor'] }}',
                    fill: false,
                    tension: 0.3
                },
                @endforeach
            ]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'top', labels: { boxWidth: 12 } } },
            scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
        }
    });
</script>

<style>
    /* Cabecera del Panel */
    .incidencias-header-card {
        background-color: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 1.5rem;
        display: flex;
        align-items: center;
        gap: 1.25rem;
        box-shadow: var(--shadow-sm);
    }

    .header-icon-box {
        width: 56px;
        height: 56px;
        background: linear-gradient(135deg, var(--baja) 0%, #a855f7 100%);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        box-shadow: 0 4px 12px rgba(220, 38, 38, 0.15);
    }

    .header-text-box h1 {
        font-size: 1.4rem;
        font-weight: 700;
        letter-spacing: -0.02em;
        margin-bottom: 0.25rem;
    }

    .header-text-box p {
        font-size: 0.875rem;
        color: var(--text-muted);
    }

    /* Tarjeta de Controles */
    .control-filters-card {
        background-color: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 1.25rem;
        box-shadow: var(--shadow-sm);
        margin-bottom: 1rem;
    }

    /* Botones principales */
    .btn-primary-action {
        background-color: var(--primary);
        color: white;
        border: none;
        border-radius: 8px;
        padding: 0.55rem 1.25rem;
        font-size: 0.875rem;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        height: 38px;
        transition: background-color var(--transition-speed) ease;
    }

    .btn-primary-action:hover {
        background-color: var(--primary-hover);
    }

    /* Contenedor de Tabla */
    .table-container-card {
        background-color: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        box-shadow: var(--shadow-sm);
        overflow: hidden;
    }

    .table-header-toolbar {
        padding: 1.25rem;
        border-bottom: 1px solid var(--border-color);
    }

    .table-toolbar-title {
        font-size: 1.05rem;
        font-weight: 700;
        color: var(--text-main);
    }

    .search-input-box {
        position: relative;
        width: 100%;
        max-width: 480px;
    }

    .search-input-box.compact {
        max-width: 320px;
    }

    .search-icon {
        position: absolute;
        left: 0.75rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-muted);
        pointer-events: none;
    }

    .search-input-box input {
        width: 100%;
        background-color: var(--bg-page);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        padding: 0.5rem 1rem 0.5rem 2.25rem;
        font-family: inherit;
        font-size: 0.85rem;
        color: var(--text-main);
        outline: none;
        transition: border-color var(--transition-speed) ease;
    }

    .search-input-box input:focus {
        border-color: var(--primary);
    }

    /* Tabla */
    .table-responsive {
        width: 100%;
        overflow-x: auto;
    }

    .premium-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
        font-size: 0.875rem;
    }

    .premium-table th {
        background-color: var(--bg-page);
        color: var(--text-muted);
        font-weight: 600;
        padding: 0.85rem 1.25rem;
        border-bottom: 1px solid var(--border-color);
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }

    .premium-table td {
        padding: 0.85rem 1.25rem;
        border-bottom: 1px solid var(--border-color);
        vertical-align: middle;
        color: var(--text-main);
    }

    .premium-table tr:last-child td {
        border-bottom: none;
    }

    .premium-table tr:hover td {
        background-color: var(--bg-card-hover);
    }

    .font-semibold {
        font-weight: 600;
    }

    .no-records-row {
        text-align: center;
        padding: 3rem !important;
        color: var(--text-muted);
        font-style: italic;
    }

    /* Caja de estado vacío */
    .empty-state-card {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        padding: 4rem 1rem;
        gap: 0.5rem;
        color: var(--text-muted);
    }

    .empty-state-icon {
        font-size: 2.5rem;
    }

    .empty-state-card h4 {
        font-size: 1.05rem;
        font-weight: 700;
        color: var(--text-main);
    }

    .reclutamiento-container {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .chips-row {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .chip {
        padding: 0.4rem 0.85rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 700;
        color: #fff;
        background-color: var(--text-muted);
        white-space: nowrap;
    }

    .chip-total { background-color: #6c757d; }
    .chip-nuevos { background-color: var(--primary); }

    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.25rem 0.65rem;
        border-radius: 9999px;
        font-size: 0.725rem;
        font-weight: 700;
    }

    .reclutamiento-grid {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .premium-table th { cursor: pointer; white-space: nowrap; }

    /* Modales */
    .modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(4px);
        z-index: 999;
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 20px;
    }

    .modal-overlay.hidden { display: none; }

    .modal-box {
        background: var(--bg-card);
        border-radius: 12px;
        padding: 1.5rem;
        width: 100%;
        max-width: 480px;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.25);
        position: relative;
    }

    .modal-box h2 {
        font-size: 1.1rem;
        margin-bottom: 1rem;
        color: var(--text-main);
    }

    .modal-close {
        position: absolute;
        top: 0.75rem;
        right: 1rem;
        cursor: pointer;
        color: var(--text-muted);
        font-weight: bold;
    }

    #formNuevoCandidato, #formCandidato {
        display: flex;
        flex-direction: column;
        gap: 0.85rem;
    }

    #formNuevoCandidato .form-group, #formCandidato .form-group {
        display: flex;
        flex-direction: column;
        gap: 0.3rem;
    }

    #formNuevoCandidato label, #formCandidato label {
        font-size: 0.8rem;
        font-weight: 600;
        color: var(--text-muted);
    }

    #formNuevoCandidato input, #formNuevoCandidato select,
    #formCandidato input, #formCandidato select, #formCandidato textarea {
        background-color: var(--bg-page);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        padding: 0.5rem 0.75rem;
        font-family: inherit;
        font-size: 0.875rem;
        color: var(--text-main);
    }

    #modal_historial {
        background-color: var(--bg-page);
        padding: 0.6rem;
        border-radius: 8px;
        border: 1px solid var(--border-color);
        max-height: 150px;
        overflow-y: auto;
    }
</style>
@endsection
