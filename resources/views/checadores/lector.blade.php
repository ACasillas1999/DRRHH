@extends('layouts.app')

@section('title', 'Lector de Eventos en Vivo')

@section('content')
<div class="lector-layout">
    <!-- Encabezado -->
    <div class="lector-header-card">
        <div class="header-icon-box">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="16"></line>
                <line x1="8" y1="12" x2="16" y2="12"></line>
            </svg>
        </div>
        <div class="header-text-box">
            <h1>Lector de Eventos en Vivo (ISAPI)</h1>
            <p>Monitorea y depura en tiempo real las tramas de datos crudas enviadas por los checadores. Ideal para verificar lecturas de tarjetas y rostros.</p>
        </div>
    </div>

    <!-- Controles superiores -->
    <div class="control-filters-card">
        <div class="filters-grid" style="align-items: flex-end;">
            <div class="filter-group">
                <label for="lector_checador">Seleccionar Checador:</label>
                <select id="lector_checador" class="form-control">
                    @foreach($checadores as $ch)
                        <option value="{{ $ch->id }}">
                            {{ $ch->nombre }} ({{ $ch->ip }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="filter-group">
                <label for="lector_fecha">Fecha de Consulta:</label>
                <input type="date" id="lector_fecha" value="{{ date('Y-m-d') }}" class="form-control">
            </div>

            <div class="filter-group compact-input">
                <label for="lector_limit">Límite:</label>
                <select id="lector_limit" class="form-control" style="min-width: 90px;">
                    <option value="30">30</option>
                    <option value="50" selected>50</option>
                    <option value="100">100</option>
                    <option value="200">200</option>
                </select>
            </div>

            <div class="filter-group compact-input">
                <label for="lector_major" title="Código de evento principal (0 = Todos)">Major (Filtro):</label>
                <input type="number" id="lector_major" value="0" min="0" class="form-control" style="min-width: 80px;">
            </div>

            <div class="filter-group compact-input">
                <label for="lector_minor" title="Código de evento secundario (0 = Todos)">Minor (Filtro):</label>
                <input type="number" id="lector_minor" value="0" min="0" class="form-control" style="min-width: 80px;">
            </div>

            <button type="button" class="btn-primary-action" onclick="readRawEvents()">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                Iniciar Lectura
            </button>
        </div>
    </div>

    <!-- Alerta de error -->
    <div id="lector-error-alert" class="alert-box-error hidden">
        <div class="alert-icon">⚠️</div>
        <div class="alert-message">
            <strong>Error de conexión:</strong>
            <span id="lector-error-text"></span>
        </div>
    </div>

    <!-- Resultados -->
    <div class="table-container-card">
        <div class="table-header-toolbar flex-between">
            <h3 class="table-toolbar-title">Tramas de Eventos Recibidas</h3>
            <div class="search-input-box compact">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="search-icon">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                <input type="text" id="lectorSearch" onkeyup="filterLectorTable()" placeholder="Filtrar marcas locales...">
            </div>
        </div>

        <!-- Loader -->
        <div id="lector-loader" class="loader-container hidden">
            <div class="spinner"></div>
            <p>Conectando al biométrico y leyendo logs crudos vía ISAPI...</p>
            <span class="subtext">Esta llamada recupera las tramas crudas del log interno del dispositivo.</span>
        </div>

        <!-- Vacío -->
        <div id="lector-empty-state" class="empty-state-card">
            <div class="empty-state-icon">📡</div>
            <h4>Lectura en Vivo</h4>
            <p>Selecciona las condiciones y presiona "Iniciar Lectura" para capturar las respuestas directas del checador.</p>
        </div>

        <!-- Tabla -->
        <div class="table-responsive hidden" id="lector-table-box">
            <table class="premium-table" id="lectorTable">
                <thead>
                    <tr>
                        <th>Fecha y Hora</th>
                        <th>N_Emp (String)</th>
                        <th>Nombre Registrado</th>
                        <th>ID de Tarjeta</th>
                        <th>Modo Verificación</th>
                        <th>Major / Minor</th>
                        <th>Detalle de Evento</th>
                    </tr>
                </thead>
                <tbody id="lector-tbody">
                    <!-- Datos dinámicos -->
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .lector-layout {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
        animation: fadeIn 0.4s ease;
    }

    .lector-header-card {
        background-color: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 1.5rem;
        display: flex;
        align-items: center;
        gap: 1.25rem;
        box-shadow: var(--shadow-sm);
    }

    .compact-input input, .compact-input select {
        min-width: unset;
    }

    .text-mono {
        font-family: var(--font-mono, monospace);
        font-size: 0.8rem;
    }

    .badge-code {
        background-color: var(--bg-page);
        border: 1px solid var(--border-color);
        padding: 0.2rem 0.4rem;
        border-radius: 4px;
        font-weight: 700;
        font-size: 0.75rem;
    }

    .event-desc-highlight {
        font-weight: 600;
        color: var(--primary);
    }
</style>
@endpush

@push('scripts')
<script>
    // Mapeo básico de códigos comunes de eventos Hikvision
    const HIK_EVENT_TYPES = {
        '5_75': 'Autenticación Facial Exitosa',
        '5_76': 'Autenticación Facial Fallida',
        '5_1': 'Apertura de Puerta por Tarjeta',
        '5_2': 'Apertura de Puerta por Código/Password',
        '5_38': 'Huella dactilar pasó verificación',
        '5_39': 'Huella dactilar falló verificación',
        '0_0': 'Evento General / Sistema',
    };

    function readRawEvents() {
        const checadorId = document.getElementById('lector_checador').value;
        const fecha = document.getElementById('lector_fecha').value;
        const limit = document.getElementById('lector_limit').value;
        const major = document.getElementById('lector_major').value;
        const minor = document.getElementById('lector_minor').value;

        const loader = document.getElementById('lector-loader');
        const emptyState = document.getElementById('lector-empty-state');
        const tableBox = document.getElementById('lector-table-box');
        const errorAlert = document.getElementById('lector-error-alert');
        const tbody = document.getElementById('lector-tbody');

        if (!checadorId) {
            alert('Por favor selecciona un checador primero.');
            return;
        }

        // Resetear UI
        errorAlert.classList.add('hidden');
        tableBox.classList.add('hidden');
        emptyState.classList.add('hidden');
        loader.classList.remove('hidden');
        tbody.innerHTML = '';

        const url = `{{ route('checadores.lector.datos') }}?checador_id=${checadorId}&fecha=${fecha}&max_results=${limit}&major=${major}&minor=${minor}`;

        fetch(url)
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => { throw new Error(err.error || 'Error del servidor') });
                }
                return response.json();
            })
            .then(data => {
                loader.classList.add('hidden');
                
                const events = data.AcsEvent ? data.AcsEvent.InfoList : [];

                if (!events || events.length === 0) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="7" class="no-records-row">No se encontraron marcas de logs crudos para los filtros establecidos.</td>
                        </tr>
                    `;
                } else {
                    renderRawRows(events);
                }
                tableBox.classList.remove('hidden');
            })
            .catch(error => {
                loader.classList.add('hidden');
                document.getElementById('lector-error-text').textContent = error.message;
                errorAlert.classList.remove('hidden');
                emptyState.classList.remove('hidden');
            });
    }

    function renderRawRows(events) {
        const tbody = document.getElementById('lector-tbody');
        let html = '';

        events.forEach(ev => {
            const time = ev.time ? ev.time.replace('T', ' ').split('-')[0] : '—';
            const empNo = ev.employeeNoString || '—';
            const name = ev.name || '—';
            const card = ev.cardNo || '—';
            const mode = ev.currentVerifyMode || ev.verifyMode || '—';
            const codes = `${ev.major} / ${ev.minor}`;
            
            const eventKey = `${ev.major}_${ev.minor}`;
            const desc = HIK_EVENT_TYPES[eventKey] || 'Otro evento de acceso';

            html += `
                <tr>
                    <td class="text-mono">${time}</td>
                    <td class="font-semibold text-mono">${empNo}</td>
                    <td>${name}</td>
                    <td class="text-mono">${card}</td>
                    <td>
                        <span class="status-badge badge-off" style="text-transform: capitalize;">${mode}</span>
                    </td>
                    <td>
                        <span class="badge-code">${codes}</span>
                    </td>
                    <td>
                        <span class="event-desc-highlight">${desc}</span>
                        <span style="font-size: 0.75rem; color: var(--text-muted); display: block;">Serial: ${ev.serialNo || '—'}</span>
                    </td>
                </tr>
            `;
        });

        tbody.innerHTML = html;
    }

    function filterLectorTable() {
        const input = document.getElementById("lectorSearch").value.toLowerCase();
        const table = document.getElementById("lectorTable");
        const rows = table.getElementsByTagName("tbody")[0].getElementsByTagName("tr");

        for (let i = 0; i < rows.length; i++) {
            let rowContainsFilter = false;
            let cells = rows[i].getElementsByTagName("td");

            for (let j = 0; j < cells.length; j++) {
                if (cells[j]) {
                    let cellText = cells[j].textContent || cells[j].innerText;
                    if (cellText.toLowerCase().includes(input)) {
                        rowContainsFilter = true;
                        break;
                    }
                }
            }

            if (rows[i].cells[0].classList.contains('no-records-row')) continue;
            rows[i].style.display = rowContainsFilter ? "" : "none";
        }
    }
</script>
@endpush
