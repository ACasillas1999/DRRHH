@extends('layouts.app')

@section('title', 'Incidencias & Asistencia')

@section('content')
<div class="incidencias-container">
    <!-- Encabezado de Página -->
    <div class="incidencias-header-card">
        <div class="header-icon-box">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                <line x1="12" y1="9" x2="12" y2="13"></line>
                <line x1="12" y1="17" x2="12.01" y2="17"></line>
            </svg>
        </div>
        <div class="header-text-box">
            <h1>Control de Incidencias y Asistencias</h1>
            <p>Monitoreo y conciliación de marcas registradas en los 19 checadores biométricos Hikvision distribuidos en las sucursales.</p>
        </div>
    </div>

    <!-- Pestañas (Tabs) -->
    <div class="incidencias-tabs">
        <button class="tab-btn active" onclick="switchTab('conciliacion-tab', this)">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <polyline points="12 6 12 12 14 14"></polyline>
            </svg>
            Conciliación del Checador
        </button>
        <button class="tab-btn" onclick="switchTab('matriz-tab', this)">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                <line x1="9" y1="3" x2="9" y2="21"></line>
                <line x1="15" y1="3" x2="15" y2="21"></line>
                <line x1="3" y1="9" x2="21" y2="9"></line>
                <line x1="3" y1="15" x2="21" y2="15"></line>
            </svg>
            Matriz de Asistencia
        </button>
        <button class="tab-btn" onclick="switchTab('historial-tab', this)">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="8" y1="6" x2="21" y2="6"></line>
                <line x1="8" y1="12" x2="21" y2="12"></line>
                <line x1="8" y1="18" x2="21" y2="18"></line>
                <line x1="3" y1="6" x2="3.01" y2="6"></line>
                <line x1="3" y1="12" x2="3.01" y2="12"></line>
                <line x1="3" y1="18" x2="3.01" y2="18"></line>
            </svg>
            Historial de Incidencias
        </button>
    </div>

    <!-- PESTAÑA 1: CONCILIACIÓN DEL CHECADOR -->
    <div id="conciliacion-tab" class="tab-content active">
        <!-- Tarjeta de Controles de Consulta -->
        <div class="control-filters-card">
            <div class="filters-grid">
                <div class="filter-group">
                    <label for="search-date">Fecha de Consulta:</label>
                    <input type="date" id="search-date" value="{{ date('Y-m-d') }}" class="form-control">
                </div>

                <div class="filter-group">
                    <label for="biometric-select">Seleccionar Checador:</label>
                    <select id="biometric-select" class="form-control">
                        <option value="all">-- Todos los Checadores (Consolidar) --</option>
                        @foreach($devices as $name => $ip)
                            <option value="{{ $ip }}" {{ $name === 'Federalismo' ? 'selected' : '' }}>
                                {{ $name }} ({{ $ip }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <button type="button" class="btn-primary-action" onclick="fetchBiometricData()">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"></path>
                    </svg>
                    Consultar Checador
                </button>
            </div>
        </div>

        <!-- Alerta de Error de Conexión (Oculta por defecto) -->
        <div id="connection-error-alert" class="alert-box-error hidden">
            <div class="alert-icon">⚠️</div>
            <div class="alert-message">
                <strong>Error de Conexión:</strong>
                <span id="error-message-text"></span>
            </div>
        </div>

        <!-- Tabla de Resultados de la Conciliación -->
        <div class="table-container-card">
            <div class="table-header-toolbar flex-between">
                <h3 class="table-toolbar-title">Asistencias del Día</h3>
                <div class="search-input-box compact">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="search-icon">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                    <input type="text" id="conciliatorSearch" onkeyup="filterConciliatorTable()" placeholder="Buscar empleado o sucursal...">
                </div>
            </div>

            <!-- Loader de Checadas -->
            <div id="biometric-loader" class="loader-container hidden">
                <div class="spinner"></div>
                <p>Conectando al biométrico y conciliando asistencias...</p>
                <span class="subtext">Esto puede tomar hasta 4 segundos según el estado de la red.</span>
            </div>

            <!-- Estado Inicial Vacío -->
            <div id="biometric-empty-state" class="empty-state-card">
                <div class="empty-state-icon">🕒</div>
                <h4>Consulta de Asistencias</h4>
                <p>Selecciona la fecha y el checador biométrico arriba y haz clic en "Consultar Checador" para procesar el día.</p>
            </div>

            <!-- Tabla de datos -->
            <div class="table-responsive hidden" id="biometric-table-box">
                <table class="premium-table" id="conciliatorTable">
                    <thead>
                        <tr>
                            <th>N_Emp</th>
                            <th>Empleado</th>
                            <th>Puesto</th>
                            <th>Sucursal</th>
                            <th>Horario</th>
                            <th>1ª Checada</th>
                            <th>Última Checada</th>
                            <th>Estado</th>
                            <th>Acciones Rápidas</th>
                        </tr>
                    </thead>
                    <tbody id="biometric-tbody">
                        <!-- Carga dinámica vía AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- PESTAÑA 2: MATRIZ DE ASISTENCIA -->
    <div id="matriz-tab" class="tab-content">
        <div class="control-filters-card">
            <form method="GET" action="{{ route('incidencias.index') }}" class="filters-form" id="matrixFilterForm">
                <input type="hidden" name="tab" value="matriz-tab">
                
                <div class="filters-grid" style="align-items: flex-end;">
                    <div class="filter-group">
                        <label for="matrix_start_date">Fecha de Inicio:</label>
                        <input type="date" id="matrix_start_date" name="matrix_start_date" value="{{ $matrix_start_date }}" class="form-control">
                    </div>

                    <div class="filter-group">
                        <label for="matrix_end_date">Fecha de Fin:</label>
                        <input type="date" id="matrix_end_date" name="matrix_end_date" value="{{ $matrix_end_date }}" class="form-control">
                    </div>

                    <div class="filter-group">
                        <label for="matrix_departamento">Departamento:</label>
                        <select id="matrix_departamento" name="matrix_departamento" class="form-control">
                            <option value="">-- Todos --</option>
                            @foreach($departamentosList as $dep)
                                <option value="{{ $dep }}" {{ $sel_departamento == $dep ? 'selected' : '' }}>{{ $dep }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="matrix_sucursal">Sucursal (Física):</label>
                        <select id="matrix_sucursal" name="matrix_sucursal" class="form-control">
                            <option value="">-- Todas --</option>
                            @foreach($sucursalesList as $suc)
                                <option value="{{ $suc }}" {{ $sel_sucursal == $suc ? 'selected' : '' }}>{{ $suc }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="matrix_puesto">Puesto:</label>
                        <select id="matrix_puesto" name="matrix_puesto" class="form-control">
                            <option value="">-- Todos --</option>
                            @foreach($puestosList as $pue)
                                <option value="{{ $pue }}" {{ $sel_puesto == $pue ? 'selected' : '' }}>{{ $pue }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div style="display: flex; gap: 0.5rem; align-items: flex-end;">
                        <button type="submit" class="btn-primary-action">
                            Consultar
                        </button>
                        <button type="button" class="btn-excel-export" onclick="exportMatrixToExcel()">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                <polyline points="14 2 14 8 20 8"></polyline>
                                <line x1="16" y1="13" x2="8" y2="13"></line>
                                <line x1="16" y1="17" x2="8" y2="17"></line>
                                <polyline points="10 9 9 9 8 9"></polyline>
                            </svg>
                            Exportar a Excel
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div class="table-container-card">
            <div class="table-header-toolbar flex-between">
                <h3 class="table-toolbar-title">Cuadrícula de Asistencia</h3>
                <div class="search-input-box compact">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="search-icon">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                    <input type="text" id="matrixSearch" onkeyup="filterMatrixTable()" placeholder="Buscar empleado...">
                </div>
            </div>

            <div class="table-responsive" style="max-height: 600px; overflow: auto;">
                <table class="premium-table matrix-grid-table" id="matrixGridTable">
                    <thead>
                        <tr>
                            <th class="sticky-col first-header"># EMP</th>
                            <th class="sticky-col second-header">Nombre Completo</th>
                            <th>Razón Social</th>
                            <th>Física</th>
                            <th>Horario</th>
                            <th>KPI's</th>
                            @foreach($matrixDates as $dateStr)
                                <th class="date-header-cell" title="{{ $dateStr }}">
                                    {{ \Carbon\Carbon::parse($dateStr)->format('d') }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($matrixGrid as $row)
                            <tr>
                                <td class="sticky-col first-cell font-semibold">{{ $row['n_empleado'] }}</td>
                                <td class="sticky-col second-cell font-semibold text-main">{{ $row['nombre'] }}</td>
                                <td>{{ $row['razon_social'] }}</td>
                                <td>{{ $row['sucursal'] }}</td>
                                <td style="font-size: 0.8rem; white-space: nowrap;">{{ $row['horario'] }}</td>
                                <td class="font-semibold text-center">{{ $row['kpi'] }}</td>
                                @foreach($matrixDates as $dateStr)
                                    @php $code = $row['days'][$dateStr] ?? ''; @endphp
                                    <td class="text-center matrix-cell-code">
                                        @if($code === 'R')
                                            <span class="matrix-badge m-retardo" title="Retardo">R</span>
                                        @elseif($code === 'F')
                                            <span class="matrix-badge m-falta" title="Falta">F</span>
                                        @elseif($code === 'J')
                                            <span class="matrix-badge m-justificado" title="Justificado">J</span>
                                        @elseif($code === 'V')
                                            <span class="matrix-badge m-vacacion" title="Vacaciones">V</span>
                                        @elseif($code === 'I')
                                            <span class="matrix-badge m-incapacity" title="Incapacidad">I</span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ 6 + count($matrixDates) }}" class="no-records-row">No hay registros que coincidan con los filtros establecidos.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- PESTAÑA 2: HISTORIAL DE INCIDENCIAS -->
    <div id="historial-tab" class="tab-content">
        <div class="control-filters-card">
            <form method="GET" action="{{ route('incidencias.index') }}" class="filters-form">
                <input type="hidden" name="tab" value="historial-tab">
                
                <div class="filter-group">
                    <label for="start_date">Desde:</label>
                    <input type="date" id="start_date" name="start_date" value="{{ $start_date }}" class="form-control">
                </div>

                <div class="filter-group">
                    <label for="end_date">Hasta:</label>
                    <input type="date" id="end_date" name="end_date" value="{{ $end_date }}" class="form-control">
                </div>

                <button type="submit" class="btn-filter-submit">
                    Filtrar Incidencias
                </button>
            </form>
        </div>

        <div class="table-container-card">
            <div class="table-header-toolbar">
                <div class="search-input-box">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="search-icon">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                    <input type="text" id="historialSearch" onkeyup="filterHistorialTable()" placeholder="Buscar por empleado, puesto, tipo de incidencia...">
                </div>
            </div>

            <div class="table-responsive">
                <table class="premium-table" id="historialTable">
                    <thead>
                        <tr>
                            <th>N_Emp</th>
                            <th>Empleado</th>
                            <th>Puesto</th>
                            <th>Sucursal</th>
                            <th>Fecha</th>
                            <th>Incidencia</th>
                            <th>Fecha de Registro</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($savedIncidents as $inc)
                            <tr id="incident-row-{{ $inc->id }}">
                                <td>{{ $inc->N_Empleado }}</td>
                                <td class="font-semibold">{{ $inc->personal->Nombre_Completo }}</td>
                                <td>{{ $inc->personal->laboral->puesto->Nombre_Puesto ?? '—' }}</td>
                                <td>{{ $inc->personal->laboral->Fisica ?? '—' }}</td>
                                <td>{{ \Carbon\Carbon::parse($inc->Fecha)->format('d/m/Y') }}</td>
                                <td>
                                    <span class="event-type-pill {{ $inc->Tipo_Incidencia === 'Retardo' ? 'pill-retardo' : 'pill-falta' }}">
                                        {{ $inc->Tipo_Incidencia }}
                                    </span>
                                </td>
                                <td>{{ \Carbon\Carbon::parse($inc->Fecha_Creacion)->format('d/m/Y H:i') }}</td>
                                <td>
                                    <button type="button" class="btn-action-delete" onclick="openDeleteModal({{ $inc->id }})">
                                        Eliminar
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="no-records-row">No hay incidencias registradas en este rango de fechas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- MODAL PARA INGRESAR JUSTIFICACIÓN DE ELIMINACIÓN -->
<div id="deleteIncidentModal" class="modal-overlay hidden">
    <div class="modal-content card animate-fade-in">
        <div class="modal-header">
            <h4>Justificación de Eliminación</h4>
            <button type="button" class="btn-close-modal" onclick="closeDeleteModal()">&times;</button>
        </div>
        <div class="modal-body">
            <p class="modal-desc">Por favor ingresa la justificación obligatoria para anular esta incidencia.</p>
            <input type="hidden" id="delete-incident-id">
            
            <div class="form-group-modal">
                <label for="delete-justification">Motivo / Justificación:</label>
                <textarea id="delete-justification" rows="3" placeholder="Ej. El empleado justificó el retardo con pase médico o aviso de transporte..." class="form-control-modal"></textarea>
                <span class="error-msg-modal hidden" id="justification-error">La justificación debe tener al menos 4 caracteres.</span>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-secondary" onclick="closeDeleteModal()">Cancelar</button>
            <button type="button" class="btn-danger" onclick="submitDeleteIncident()">Confirmar Eliminación</button>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* Estilos del Módulo de Incidencias */
    .incidencias-container {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
        animation: fadeIn 0.4s ease;
    }

    /* Sticky columns para la matriz de asistencia */
    .matrix-grid-table {
        position: relative;
    }

    .matrix-grid-table th.sticky-col {
        position: sticky;
        left: 0;
        z-index: 10;
        background-color: var(--bg-page);
        border-right: 1px solid var(--border-color);
    }

    .matrix-grid-table td.sticky-col {
        position: sticky;
        left: 0;
        z-index: 8;
        background-color: var(--bg-card);
        border-right: 1px solid var(--border-color);
    }

    .matrix-grid-table th.first-header, 
    .matrix-grid-table td.first-cell {
        left: 0;
        min-width: 80px;
        z-index: 11; /* mayor que second-cell para superponerse */
    }

    .matrix-grid-table th.second-header, 
    .matrix-grid-table td.second-cell {
        left: 80px;
        min-width: 240px;
        box-shadow: 2px 0 5px rgba(0, 0, 0, 0.05);
    }

    /* Badges de la matriz */
    .matrix-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 20px;
        height: 20px;
        border-radius: 4px;
        font-size: 0.7rem;
        font-weight: 800;
        line-height: 1;
    }

    .m-retardo { background-color: #fef9c3; color: #ca8a04; border: 1px solid rgba(202, 138, 4, 0.2); }
    .m-falta { background-color: #fee2e2; color: #dc2626; border: 1px solid rgba(220, 38, 38, 0.2); }
    .m-justificado { background-color: #dcfce7; color: #16a34a; border: 1px solid rgba(22, 163, 74, 0.2); }
    .m-vacacion { background-color: #dbeafe; color: #2563eb; border: 1px solid rgba(37, 99, 235, 0.2); }
    .m-incapacity { background-color: #f3e8ff; color: #9333ea; border: 1px solid rgba(147, 51, 234, 0.2); }

    .date-header-cell {
        text-align: center;
        min-width: 34px;
        padding: 0.5rem 0.25rem !important;
        font-size: 0.725rem !important;
    }

    .matrix-cell-code {
        padding: 0.35rem 0.25rem !important;
    }

    .btn-excel-export {
        background-color: #16a34a;
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

    .btn-excel-export:hover {
        background-color: #15803d;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

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

    /* Pestañas */
    .incidencias-tabs {
        display: flex;
        gap: 0.5rem;
        border-bottom: 1px solid var(--border-color);
        padding-bottom: 1px;
    }

    .tab-btn {
        background: none;
        border: none;
        padding: 0.85rem 1.25rem;
        font-size: 0.9rem;
        font-weight: 600;
        color: var(--text-muted);
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        border-bottom: 2px solid transparent;
        transition: all var(--transition-speed) ease;
    }

    .tab-btn svg {
        color: var(--text-muted);
        transition: color var(--transition-speed) ease;
    }

    .tab-btn:hover {
        color: var(--primary);
    }

    .tab-btn:hover svg {
        color: var(--primary);
    }

    .tab-btn.active {
        color: var(--primary);
        border-bottom-color: var(--primary);
    }

    .tab-btn.active svg {
        color: var(--primary);
    }

    .tab-content {
        display: none;
    }

    .tab-content.active {
        display: block;
        animation: fadeIn 0.3s ease;
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

    .filters-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 1.25rem;
        align-items: flex-end;
    }

    .filters-form {
        display: flex;
        flex-wrap: wrap;
        gap: 1.25rem;
        align-items: flex-end;
    }

    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 0.4rem;
    }

    .filter-group label {
        font-size: 0.8rem;
        font-weight: 600;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.02em;
    }

    .form-control {
        background-color: var(--bg-page);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        padding: 0.5rem 0.75rem;
        font-family: inherit;
        font-size: 0.875rem;
        color: var(--text-main);
        outline: none;
        min-width: 180px;
        transition: border-color var(--transition-speed) ease;
    }

    .form-control:focus {
        border-color: var(--primary);
    }

    /* Botones principales */
    .btn-primary-action, .btn-filter-submit {
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

    .btn-primary-action:hover, .btn-filter-submit:hover {
        background-color: var(--primary-hover);
    }

    /* Caja de Alerta */
    .alert-box-error {
        background-color: rgba(220, 38, 38, 0.05);
        border: 1px solid rgba(220, 38, 38, 0.15);
        color: var(--baja);
        padding: 1rem;
        border-radius: 12px;
        display: flex;
        gap: 0.75rem;
        align-items: center;
        margin-bottom: 1rem;
        animation: fadeIn 0.3s ease;
    }

    .alert-box-warning {
        background-color: rgba(217, 119, 6, 0.05);
        border: 1px solid rgba(217, 119, 6, 0.15);
        color: #b45309;
        padding: 1rem;
        border-radius: 12px;
        display: flex;
        gap: 0.75rem;
        align-items: center;
        margin-bottom: 1rem;
        animation: fadeIn 0.3s ease;
    }

    .alert-icon {
        font-size: 1.25rem;
    }

    .alert-message {
        font-size: 0.85rem;
        line-height: 1.4;
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

    /* Loader */
    .loader-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 4rem 1rem;
        gap: 0.75rem;
        color: var(--text-muted);
        text-align: center;
    }

    .spinner {
        width: 36px;
        height: 36px;
        border: 3px solid var(--border-color);
        border-top-color: var(--primary);
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
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

    .empty-state-card p {
        font-size: 0.875rem;
        max-width: 320px;
    }

    /* Badges de Estado */
    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.25rem 0.65rem;
        border-radius: 9999px;
        font-size: 0.725rem;
        font-weight: 700;
    }

    .badge-ontime { background-color: rgba(22, 163, 74, 0.1); color: var(--activo); }
    .badge-retardo { background-color: rgba(217, 119, 6, 0.1); color: var(--pendiente); }
    .badge-falta { background-color: rgba(220, 38, 38, 0.1); color: var(--baja); }
    .badge-vacation { background-color: rgba(59, 130, 246, 0.1); color: #2563eb; }
    .badge-incapacity { background-color: rgba(168, 85, 247, 0.1); color: #9333ea; }
    .badge-off { background-color: var(--bg-page); border: 1px solid var(--border-color); color: var(--text-muted); }

    /* Pillas de incidencias */
    .event-type-pill {
        display: inline-flex;
        align-items: center;
        padding: 0.25rem 0.65rem;
        border-radius: 9999px;
        font-size: 0.725rem;
        font-weight: 700;
    }

    .pill-retardo { background-color: #fef9c3; color: #ca8a04; }
    .pill-falta { background-color: #fee2e2; color: #dc2626; }

    /* Botones de acción */
    .btn-action-retardo, .btn-action-falta, .btn-action-justificado, .btn-action-delete {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.35rem 0.65rem;
        font-size: 0.725rem;
        font-weight: 600;
        border-radius: 6px;
        cursor: pointer;
        border: 1px solid transparent;
        transition: all var(--transition-speed) ease;
    }

    .btn-action-retardo {
        background-color: rgba(217, 119, 6, 0.08);
        color: var(--pendiente);
        border-color: rgba(217, 119, 6, 0.15);
    }
    .btn-action-retardo:hover {
        background-color: var(--pendiente);
        color: white;
    }

    .btn-action-falta {
        background-color: rgba(220, 38, 38, 0.08);
        color: var(--baja);
        border-color: rgba(220, 38, 38, 0.15);
    }
    .btn-action-falta:hover {
        background-color: var(--baja);
        color: white;
    }

    .btn-action-justificado {
        background-color: var(--bg-page);
        color: var(--text-muted);
        border-color: var(--border-color);
    }
    .btn-action-justificado:hover {
        border-color: var(--primary);
        color: var(--primary);
        background-color: var(--primary-light);
    }

    .btn-action-delete {
        background-color: rgba(220, 38, 38, 0.08);
        color: var(--baja);
        border: 1px solid rgba(220, 38, 38, 0.15);
    }

    .btn-action-delete:hover {
        background-color: var(--baja);
        color: white;
    }

    .badge-logged {
        font-size: 0.725rem;
        font-weight: 700;
        color: var(--activo);
        background-color: rgba(22, 163, 74, 0.08);
        padding: 0.25rem 0.5rem;
        border-radius: 6px;
        border: 1px solid rgba(22, 163, 74, 0.15);
    }

    /* ----- MODAL STYLES ----- */
    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(4px);
        z-index: 1000;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1.5rem;
        transition: opacity var(--transition-speed) ease;
    }

    .modal-content {
        max-width: 480px;
        width: 100%;
        background-color: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        box-shadow: var(--shadow-lg);
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }

    .modal-header {
        padding: 1.25rem;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .modal-header h4 {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--text-main);
    }

    .btn-close-modal {
        background: none;
        border: none;
        font-size: 1.5rem;
        color: var(--text-muted);
        cursor: pointer;
        line-height: 1;
    }

    .btn-close-modal:hover {
        color: var(--baja);
    }

    .modal-body {
        padding: 1.25rem;
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .modal-desc {
        font-size: 0.85rem;
        color: var(--text-muted);
    }

    .form-group-modal {
        display: flex;
        flex-direction: column;
        gap: 0.4rem;
    }

    .form-group-modal label {
        font-size: 0.8rem;
        font-weight: 600;
        color: var(--text-muted);
    }

    .form-control-modal {
        width: 100%;
        background-color: var(--bg-page);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        padding: 0.6rem 0.75rem;
        font-family: inherit;
        font-size: 0.875rem;
        color: var(--text-main);
        outline: none;
        resize: vertical;
        transition: border-color var(--transition-speed) ease;
    }

    .form-control-modal:focus {
        border-color: var(--primary);
    }

    .error-msg-modal {
        font-size: 0.75rem;
        color: var(--baja);
        font-weight: 500;
        margin-top: 0.15rem;
    }

    .modal-footer {
        padding: 1rem 1.25rem;
        border-top: 1px solid var(--border-color);
        display: flex;
        justify-content: flex-end;
        gap: 0.75rem;
    }

    .btn-secondary {
        background-color: var(--bg-page);
        border: 1px solid var(--border-color);
        color: var(--text-main);
        padding: 0.5rem 1rem;
        border-radius: 8px;
        font-size: 0.825rem;
        font-weight: 600;
        cursor: pointer;
        transition: all var(--transition-speed) ease;
    }

    .btn-secondary:hover {
        background-color: var(--bg-card-hover);
    }

    .btn-danger {
        background-color: var(--baja);
        color: white;
        border: none;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        font-size: 0.825rem;
        font-weight: 600;
        cursor: pointer;
        transition: background-color var(--transition-speed) ease;
    }

    .btn-danger:hover {
        background-color: #b91c1c;
    }

    .hidden {
        display: none !important;
    }
</style>
@endpush

@push('scripts')
<script>
    // Conmutar pestañas (Tabs)
    function switchTab(tabId, buttonEl) {
        document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
        document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
        
        document.getElementById(tabId).classList.add('active');
        buttonEl.classList.add('active');

        const url = new URL(window.location.href);
        url.searchParams.set('tab', tabId);
        window.history.replaceState({}, '', url);
    }

    // --- PESTAÑA 1: CONCILIADOR DE CHECADOR BIOMÉTRICO ---

    function fetchBiometricData() {
        const fecha = document.getElementById('search-date').value;
        const ip = document.getElementById('biometric-select').value;
        
        const loader = document.getElementById('biometric-loader');
        const emptyState = document.getElementById('biometric-empty-state');
        const tableBox = document.getElementById('biometric-table-box');
        const errorAlert = document.getElementById('connection-error-alert');
        const tbody = document.getElementById('biometric-tbody');

        // Resetear UI
        errorAlert.classList.add('hidden');
        tableBox.classList.add('hidden');
        emptyState.classList.add('hidden');
        loader.classList.remove('hidden');
        tbody.innerHTML = '';

        if (ip === 'all') {
            const select = document.getElementById('biometric-select');
            const devices = [];
            for (let i = 2; i < select.options.length; i++) { // Skip "all" and first option
                const opt = select.options[i];
                const name = opt.text.split(' ')[0];
                devices.push({ name: name, ip: opt.value });
            }
            
            const loaderText = loader.querySelector('p');
            const loaderSubtext = loader.querySelector('.subtext');
            loaderText.textContent = `Consultando y consolidando los ${devices.length} checadores...`;
            loaderSubtext.textContent = 'Esto se realiza en paralelo. Espere unos segundos...';
            
            const promises = devices.map(dev => {
                return fetch(`{{ route('incidencias.biometrico') }}?fecha=${fecha}&ip=${dev.ip}`)
                    .then(response => {
                        if (!response.ok) {
                            return response.json().then(err => { throw new Error(err.error || 'Offline') });
                        }
                        return response.json();
                    })
                    .then(data => ({ name: dev.name, ip: dev.ip, status: 'ok', data }))
                    .catch(err => ({ name: dev.name, ip: dev.ip, status: 'error', error: err.message }));
            });
            
            Promise.all(promises)
                .then(results => {
                    loader.classList.add('hidden');
                    loaderText.textContent = 'Conectando al biométrico y conciliando asistencias...';
                    loaderSubtext.textContent = 'Esto puede tomar hasta 4 segundos según el estado de la red.';
                    
                    const success = results.filter(r => r.status === 'ok');
                    const failed = results.filter(r => r.status === 'error');
                    
                    if (success.length === 0) {
                        const errMsgs = failed.map(f => `${f.name}: ${f.error}`).join(' | ');
                        throw new Error('Todos los checadores están fuera de línea o fallaron: ' + errMsgs);
                    }
                    
                    const consolidatedData = consolidateDevicesData(success);
                    
                    if (failed.length > 0) {
                        const failedNames = failed.map(f => `${f.name} (${f.ip})`).join(', ');
                        errorAlert.className = 'alert-box-warning';
                        document.getElementById('error-message-text').innerHTML = `<strong>Conexión parcial:</strong> Se procesó la asistencia de ${success.length} de los ${devices.length} checadores. <br>Dispositivos fuera de línea: <span style="font-size: 0.8rem; color: #b45309;">${failedNames}</span>`;
                        errorAlert.classList.remove('hidden');
                    } else {
                        errorAlert.classList.add('hidden');
                    }
                    
                    if (consolidatedData.length === 0) {
                        tbody.innerHTML = `
                            <tr>
                                <td colspan="9" class="no-records-row">No hay empleados registrados en el sistema.</td>
                            </tr>
                        `;
                    } else {
                        renderConciliationRows(consolidatedData, fecha);
                    }
                    tableBox.classList.remove('hidden');
                })
                .catch(error => {
                    loader.classList.add('hidden');
                    loaderText.textContent = 'Conectando al biométrico y conciliando asistencias...';
                    loaderSubtext.textContent = 'Esto puede tomar hasta 4 segundos según el estado de la red.';
                    
                    document.getElementById('error-message-text').textContent = error.message;
                    errorAlert.className = 'alert-box-error';
                    errorAlert.classList.remove('hidden');
                    emptyState.classList.remove('hidden');
                });
            return;
        }

        fetch(`{{ route('incidencias.biometrico') }}?fecha=${fecha}&ip=${ip}`)
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => { throw new Error(err.error || 'Fallo del servidor') });
                }
                return response.json();
            })
            .then(data => {
                loader.classList.add('hidden');
                
                if (data.length === 0) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="9" class="no-records-row">No hay empleados registrados en el sistema.</td>
                        </tr>
                    `;
                } else {
                    renderConciliationRows(data, fecha);
                }
                tableBox.classList.remove('hidden');
            })
            .catch(error => {
                loader.classList.add('hidden');
                document.getElementById('error-message-text').textContent = error.message;
                errorAlert.className = 'alert-box-error';
                errorAlert.classList.remove('hidden');
                emptyState.classList.remove('hidden');
            });
    }

    function consolidateDevicesData(successResults) {
        const employeesMap = {};
        
        successResults.forEach(result => {
            result.data.forEach(emp => {
                const nEmp = emp.n_empleado;
                if (!employeesMap[nEmp]) {
                    employeesMap[nEmp] = [];
                }
                employeesMap[nEmp].push(emp);
            });
        });
        
        function timeToSeconds(timeStr) {
            if (!timeStr || timeStr === '—' || timeStr === 'Sin salida') return null;
            const parts = timeStr.trim().split(':');
            if (parts.length < 2) return null;
            const hours = parseInt(parts[0], 10);
            const minutes = parseInt(parts[1], 10);
            const seconds = parts.length > 2 ? parseInt(parts[2], 10) : 0;
            return hours * 3600 + minutes * 60 + seconds;
        }
        
        function formatTime(secs) {
            const h = Math.floor(secs / 3600);
            const m = Math.floor((secs % 3600) / 60);
            const pad = (num) => String(num).padStart(2, '0');
            return `${pad(h)}:${pad(m)}`;
        }
        
        const consolidatedList = [];
        
        Object.keys(employeesMap).forEach(nEmp => {
            const records = employeesMap[nEmp];
            const firstRec = records[0];
            
            const consolidated = {
                n_empleado: firstRec.n_empleado,
                nombre: firstRec.nombre,
                puesto: firstRec.puesto,
                sucursal: 'Consolidado',
                horario: firstRec.horario,
                primera_checada: '—',
                ultima_checada: '—',
                estado: 'Falta',
                sugiere_incidencia: null,
                comentarios: 'No se registran marcas del empleado.',
                id_incidencia_guardada: firstRec.id_incidencia_guardada
            };
            
            let minEntrySecs = null;
            let maxExitSecs = null;
            let minEntryTimeStr = null;
            let maxExitTimeStr = null;
            const sucursalesSet = new Set();
            
            records.forEach(rec => {
                if (rec.primera_checada !== '—') {
                    sucursalesSet.add(rec.sucursal);
                }
                
                const pSecs = timeToSeconds(rec.primera_checada);
                if (pSecs !== null) {
                    if (minEntrySecs === null || pSecs < minEntrySecs) {
                        minEntrySecs = pSecs;
                        minEntryTimeStr = rec.primera_checada;
                    }
                }
                
                const uSecs = timeToSeconds(rec.ultima_checada);
                if (uSecs !== null) {
                    if (maxExitSecs === null || uSecs > maxExitSecs) {
                        maxExitSecs = uSecs;
                        maxExitTimeStr = rec.ultima_checada;
                    }
                }
            });
            
            if (sucursalesSet.size > 0) {
                consolidated.sucursal = Array.from(sucursalesSet).join(', ');
            } else {
                consolidated.sucursal = firstRec.sucursal;
            }
            
            if (minEntryTimeStr !== null) {
                consolidated.primera_checada = minEntryTimeStr;
                if (maxExitTimeStr !== null && maxExitTimeStr !== minEntryTimeStr) {
                    consolidated.ultima_checada = maxExitTimeStr;
                } else {
                    consolidated.ultima_checada = 'Sin salida';
                }
                
                let shiftStartSecs = null;
                const match = firstRec.horario.match(/^(\d{2}):(\d{2})/);
                if (match) {
                    shiftStartSecs = parseInt(match[1], 10) * 3600 + parseInt(match[2], 10) * 60;
                }
                
                if (shiftStartSecs !== null) {
                    const entryLimitSecs = shiftStartSecs + 10 * 60;
                    if (minEntrySecs > entryLimitSecs) {
                        consolidated.estado = 'Retardo';
                        consolidated.sugiere_incidencia = 'Retardo';
                        consolidated.comentarios = `Entró a las ${minEntryTimeStr} (Horario límite de entrada: ${formatTime(entryLimitSecs)}).`;
                    } else {
                        consolidated.estado = 'A tiempo';
                        consolidated.sugiere_incidencia = null;
                        consolidated.comentarios = `Entró a las ${minEntryTimeStr} (Horario entrada: ${formatTime(shiftStartSecs)}).`;
                    }
                } else {
                    consolidated.estado = 'A tiempo';
                    consolidated.sugiere_incidencia = null;
                    consolidated.comentarios = `Entró a las ${minEntryTimeStr}.`;
                }
            } else {
                consolidated.estado = firstRec.estado;
                consolidated.sugiere_incidencia = firstRec.sugiere_incidencia;
                consolidated.comentarios = firstRec.comentarios;
            }
            
            consolidatedList.push(consolidated);
        });
        
        return consolidatedList;
    }

    function renderConciliationRows(data, fecha) {
        const tbody = document.getElementById('biometric-tbody');
        let html = '';

        data.forEach(row => {
            let badgeClass = 'badge-off';
            switch (row.estado) {
                case 'A tiempo': badgeClass = 'badge-ontime'; break;
                case 'Retardo': badgeClass = 'badge-retardo'; break;
                case 'Falta': badgeClass = 'badge-falta'; break;
                case 'Vacaciones': badgeClass = 'badge-vacation'; break;
                case 'Incapacidad': badgeClass = 'badge-incapacity'; break;
                case 'Día de descanso': badgeClass = 'badge-off'; break;
            }

            let actionHtml = '—';
            
            // Si tiene sugerencia de incidencia y no está guardada previamente
            if (row.sugiere_incidencia && !row.id_incidencia_guardada) {
                const btnClass = row.sugiere_incidencia === 'Retardo' ? 'btn-action-retardo' : 'btn-action-falta';
                actionHtml = `
                    <div style="display: flex; gap: 0.4rem;" id="action-cell-${row.n_empleado}">
                        <button type="button" class="${btnClass}" onclick="logIncidence(${row.n_empleado}, '${fecha}', '${row.sugiere_incidencia}', this)">
                            Guardar ${row.sugiere_incidencia}
                        </button>
                        <button type="button" class="btn-action-justificado" onclick="logIncidence(${row.n_empleado}, '${fecha}', 'Justificado', this)">
                            Justificar
                        </button>
                    </div>
                `;
            } else if (row.id_incidencia_guardada) {
                actionHtml = `<span class="badge-logged">Registrada (BD)</span>`;
            }

            html += `
                <tr id="conciliator-row-${row.n_empleado}">
                    <td>${row.n_empleado}</td>
                    <td class="font-semibold">${row.nombre}</td>
                    <td>${row.puesto}</td>
                    <td>${row.sucursal}</td>
                    <td style="font-size: 0.8rem; color: var(--text-muted);">${row.horario}</td>
                    <td>${row.primera_checada}</td>
                    <td>${row.ultima_checada}</td>
                    <td>
                        <span class="status-badge ${badgeClass}" id="status-badge-${row.n_empleado}">
                            ${row.estado}
                        </span>
                    </td>
                    <td>${actionHtml}</td>
                </tr>
            `;
        });

        tbody.innerHTML = html;
    }

    // Registrar incidencia via AJAX
    function logIncidence(nEmpleado, fecha, tipoIncidencia, btnEl) {
        btnEl.disabled = true;
        btnEl.textContent = '...';

        fetch(`{{ route('incidencias.guardar') }}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                n_empleado: nEmpleado,
                fecha: fecha,
                tipo_incidencia: tipoIncidencia
            })
        })
        .then(response => {
            if (!response.ok) throw new Error('Error al registrar');
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Actualizar interfaz en tiempo real
                const actionCell = document.getElementById(`action-cell-${nEmpleado}`);
                if (actionCell) {
                    actionCell.parentNode.innerHTML = `<span class="badge-logged">Registrada (BD)</span>`;
                }

                // Cambiar el badge de estado
                const statusBadge = document.getElementById(`status-badge-${nEmpleado}`);
                if (statusBadge) {
                    statusBadge.className = `status-badge ${tipoIncidencia === 'Retardo' ? 'badge-retardo' : (tipoIncidencia === 'Falta' ? 'badge-falta' : 'badge-off')}`;
                    statusBadge.textContent = tipoIncidencia;
                }
            } else {
                alert('No se pudo guardar la incidencia.');
                btnEl.disabled = false;
                btnEl.textContent = 'Guardar';
            }
        })
        .catch(error => {
            console.error(error);
            alert('Error de conexión al registrar la incidencia.');
            btnEl.disabled = false;
            btnEl.textContent = 'Guardar';
        });
    }

    // Filtrar tabla conciliación localmente en tiempo real
    function filterConciliatorTable() {
        const input = document.getElementById("conciliatorSearch").value.toLowerCase();
        const table = document.getElementById("conciliatorTable");
        const rows = table.getElementsByTagName("tbody")[0].getElementsByTagName("tr");

        for (let i = 0; i < rows.length; i++) {
            let cells = rows[i].getElementsByTagName("td");
            let rowContainsFilter = false;

            // Buscador en columnas (ID, Nombre, Puesto, Sucursal)
            for (let j = 0; j < 4; j++) {
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

    // --- PESTAÑA 2: HISTORIAL DE INCIDENCIAS ---

    // Filtrar tabla historial localmente en tiempo real
    function filterHistorialTable() {
        const input = document.getElementById("historialSearch").value.toLowerCase();
        const table = document.getElementById("historialTable");
        const rows = table.getElementsByTagName("tbody")[0].getElementsByTagName("tr");

        for (let i = 0; i < rows.length; i++) {
            let cells = rows[i].getElementsByTagName("td");
            let rowContainsFilter = false;

            // Buscador en todas las celdas
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

    // --- LÓGICA DE MODAL DE ELIMINACIÓN DE INCIDENCIAS ---

    function openDeleteModal(id) {
        document.getElementById('delete-incident-id').value = id;
        document.getElementById('delete-justification').value = '';
        document.getElementById('justification-error').classList.add('hidden');
        document.getElementById('deleteIncidentModal').classList.remove('hidden');
    }

    function closeDeleteModal() {
        document.getElementById('deleteIncidentModal').classList.add('hidden');
    }

    function submitDeleteIncident() {
        const id = document.getElementById('delete-incident-id').value;
        const justification = document.getElementById('delete-justification').value.trim();
        const errorMsg = document.getElementById('justification-error');

        if (justification.length < 4) {
            errorMsg.classList.remove('hidden');
            return;
        }

        errorMsg.classList.add('hidden');

        fetch(`/incidencias/${id}/desactivar`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                justificacion: justification
            })
        })
        .then(response => {
            if (!response.ok) throw new Error('Error al desactivar');
            return response.json();
        })
        .then(data => {
            if (data.success) {
                closeDeleteModal();
                // Remover la fila de la tabla en tiempo real con animación
                const row = document.getElementById(`incident-row-${id}`);
                if (row) {
                    row.style.transition = 'all 0.3s ease';
                    row.style.opacity = '0';
                    setTimeout(() => {
                        row.remove();
                        // Si ya no quedan filas en la tabla, mostrar el aviso de vacío
                        const tbody = document.querySelector('#historialTable tbody');
                        if (tbody.querySelectorAll('tr').length === 0) {
                            tbody.innerHTML = `
                                <tr>
                                    <td colspan="8" class="no-records-row">No hay incidencias registradas en este rango de fechas.</td>
                                </tr>
                            `;
                        }
                    }, 300);
                }
            } else {
                alert('No se pudo eliminar la incidencia.');
            }
        })
        .catch(error => {
            console.error(error);
            alert('Error de conexión al eliminar la incidencia.');
        });
    }

    // Filtrar tabla matriz localmente en tiempo real
    function filterMatrixTable() {
        const input = document.getElementById("matrixSearch").value.toLowerCase();
        const table = document.getElementById("matrixGridTable");
        const rows = table.getElementsByTagName("tbody")[0].getElementsByTagName("tr");

        for (let i = 0; i < rows.length; i++) {
            let cells = rows[i].getElementsByTagName("td");
            let rowContainsFilter = false;

            // Buscar en las celdas principales (N_Emp y Nombre Completo)
            for (let j = 0; j < 2; j++) {
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

    // Exportar matriz a Excel
    function exportMatrixToExcel() {
        const table = document.getElementById("matrixGridTable");
        
        // Clonar la tabla para no modificar la original en pantalla
        const tableClone = table.cloneNode(true);
        
        // Quitar las clases 'sticky-col' y otros estilos para que Excel lo interprete plano y limpio
        tableClone.querySelectorAll('.sticky-col').forEach(el => {
            el.style.position = 'static';
            el.style.backgroundColor = 'transparent';
        });

        // Convertir la tabla HTML a un archivo Excel descargable
        const html = tableClone.outerHTML;
        
        // Usar tipo MIME compatible con XLS de Excel
        const url = 'data:application/vnd.ms-excel;charset=utf-8,\uFEFF' + encodeURIComponent(html);
        
        const downloadLink = document.createElement("a");
        document.body.appendChild(downloadLink);
        
        const fechaInicio = document.getElementById("matrix_start_date").value;
        const fechaFin = document.getElementById("matrix_end_date").value;
        
        downloadLink.href = url;
        downloadLink.download = `Matriz_Asistencias_${fechaInicio}_al_${fechaFin}.xls`;
        downloadLink.click();
        document.body.removeChild(downloadLink);
    }

    // Inicializar Tab activa por URL al cargar la página
    document.addEventListener('DOMContentLoaded', () => {
        const urlParams = new URLSearchParams(window.location.search);
        const tabParam = urlParams.get('tab');
        if (tabParam) {
            const btn = document.querySelector(`.tab-btn[onclick*="${tabParam}"]`);
            if (btn) {
                btn.click();
            }
        }
    });
</script>
@endpush
