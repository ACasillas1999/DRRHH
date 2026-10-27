@extends('layouts.app')

@section('title', 'Diario de Eventos')

@section('content')
<div class="diario-container">
    <!-- Encabezado de Página -->
    <div class="diario-header-card">
        <div class="header-icon-box">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                <line x1="16" y1="2" x2="16" y2="6"></line>
                <line x1="8" y1="2" x2="8" y2="6"></line>
                <line x1="3" y1="10" x2="21" y2="10"></line>
            </svg>
        </div>
        <div class="header-text-box">
            <h1>Diario de Recursos Humanos</h1>
            <p>Monitoreo en tiempo real de cumpleaños, aniversarios, vacaciones activas, vencimientos de contratos, evaluaciones e incapacidades.</p>
        </div>
    </div>

    <!-- Navegación por Pestañas (Tabs) -->
    <div class="diario-tabs">
        <button class="tab-btn active" onclick="switchTab('calendar-tab', this)">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                <line x1="16" y1="2" x2="16" y2="6"></line>
                <line x1="8" y1="2" x2="8" y2="6"></line>
                <line x1="3" y1="10" x2="21" y2="10"></line>
            </svg>
            Calendario Mensual
        </button>
        <button class="tab-btn" onclick="switchTab('list-tab', this)">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="8" y1="6" x2="21" y2="6"></line>
                <line x1="8" y1="12" x2="21" y2="12"></line>
                <line x1="8" y1="18" x2="21" y2="18"></line>
                <line x1="3" y1="6" x2="3.01" y2="6"></line>
                <line x1="3" y1="12" x2="3.01" y2="12"></line>
                <line x1="3" y1="18" x2="3.01" y2="18"></line>
            </svg>
            Eventos por Rango de Fechas
        </button>
    </div>

    <!-- CONTENIDO PESTAÑA 1: CALENDARIO -->
    <div id="calendar-tab" class="tab-content active">
        <div class="calendar-layout">
            <!-- Columna Izquierda: Contenedor FullCalendar -->
            <div class="calendar-main-card">
                <div id="calendar"></div>
            </div>

            <!-- Columna Derecha: Panel de Detalles del Día -->
            <div class="day-details-panel">
                <div class="details-panel-card">
                    <div class="details-panel-header">
                        <h3>Eventos del Día</h3>
                        <span id="selected-date-label" class="selected-date-pill">Cargando...</span>
                    </div>

                    <!-- Cuerpo de detalles dinámicos -->
                    <div id="day-details-content" class="details-panel-body">
                        <div class="empty-details-state">
                            <div class="empty-state-icon">📅</div>
                            <p>Haz clic en cualquier día o evento en el calendario para ver la información detallada.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- CONTENIDO PESTAÑA 2: LISTA POR RANGO -->
    <div id="list-tab" class="tab-content">
        <div class="list-filters-card">
            <form method="GET" action="{{ route('diario.index') }}" class="filters-form" id="range-filter-form">
                <input type="hidden" name="tab" value="list-tab">
                
                <div class="filter-group">
                    <label for="start_date">Desde:</label>
                    <input type="date" id="start_date" name="start_date" value="{{ $start_date }}" class="form-control">
                </div>

                <div class="filter-group">
                    <label for="end_date">Hasta:</label>
                    <input type="date" id="end_date" name="end_date" value="{{ $end_date }}" class="form-control">
                </div>

                <button type="submit" class="btn-filter-submit">
                    Filtrar Eventos
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
                    <input type="text" id="searchInput" onkeyup="filterRangeTable()" placeholder="Buscar en todos los campos (empleado, puesto, ubicación, tipo)...">
                </div>
            </div>

            <div class="table-responsive">
                <table id="eventsRangeTable" class="premium-table">
                    <thead>
                        <tr>
                            <th onclick="sortTable(0, 'text')" class="sortable">Tipo de Evento <span class="sort-indicator">&#x2195;</span></th>
                            <th onclick="sortTable(1, 'text')" class="sortable">Empleado <span class="sort-indicator">&#x2195;</span></th>
                            <th onclick="sortTable(2, 'date')" class="sortable">Fecha Evento <span class="sort-indicator">&#x2195;</span></th>
                            <th onclick="sortTable(3, 'date')" class="sortable">Fecha Ingreso <span class="sort-indicator">&#x2195;</span></th>
                            <th onclick="sortTable(4, 'text')" class="sortable">Ubicación Física <span class="sort-indicator">&#x2195;</span></th>
                            <th onclick="sortTable(5, 'text')" class="sortable">Puesto <span class="sort-indicator">&#x2195;</span></th>
                            <th>Detalles</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($listEvents as $event)
                            <tr>
                                <td>
                                    @php
                                        $pillClass = '';
                                        switch($event['tipo']) {
                                            case 'Cumpleaños': $pillClass = 'pill-birthday'; break;
                                            case 'Aniversario Laboral': $pillClass = 'pill-anniversary'; break;
                                            case 'Vacaciones': $pillClass = 'pill-vacation'; break;
                                            case 'Vencimiento de contrato': $pillClass = 'pill-contract'; break;
                                            case 'Evaluación': $pillClass = 'pill-evaluation'; break;
                                            case 'Incapacidad': $pillClass = 'pill-incapacity'; break;
                                        }
                                    @endphp
                                    <span class="event-type-pill {{ $pillClass }}">
                                        {{ $event['tipo'] }}
                                    </span>
                                </td>
                                <td class="font-semibold">{{ $event['nombre'] }} <span class="subtext">#{{ $event['n_empleado'] }}</span></td>
                                <td>
                                    @if(strpos($event['fecha_evento'], ' al ') !== false)
                                        <span class="range-date">{{ $event['fecha_evento'] }}</span>
                                    @else
                                        {{ \Illuminate\Support\Carbon::parse($event['fecha_evento'])->format('d/m/Y') }}
                                    @endif
                                </td>
                                <td>{{ $event['fecha_ingreso'] ? \Illuminate\Support\Carbon::parse($event['fecha_ingreso'])->format('d/m/Y') : '—' }}</td>
                                <td>{{ $event['fisica'] ?: '—' }}</td>
                                <td>{{ $event['puesto'] ?: '—' }}</td>
                                <td class="detalles-cell">{{ $event['detalles'] }}</td>
                                <td>
                                    <a href="{{ route('empleados.show', $event['n_empleado']) }}" class="btn-table-action">
                                        Ver Perfil
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="no-records-row">No hay eventos programados en este rango de fechas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    /* Estilos del Módulo Diario */
    .diario-container {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
        animation: fadeIn 0.4s ease;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Tarjeta del Encabezado */
    .diario-header-card {
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
        background: linear-gradient(135deg, var(--primary) 0%, #4f46e5 100%);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        box-shadow: 0 4px 12px rgba(29, 78, 216, 0.15);
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
    .diario-tabs {
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

    /* Contenido de Pestañas */
    .tab-content {
        display: none;
    }

    .tab-content.active {
        display: block;
        animation: fadeIn 0.3s ease;
    }

    /* ----- MAQUETADO DE CALENDARIO ----- */
    .calendar-layout {
        display: grid;
        grid-template-columns: minmax(0, 1fr);
        gap: 1.5rem;
        width: 100%;
    }

    @media (min-width: 1024px) {
        .calendar-layout {
            grid-template-columns: minmax(0, 1fr) 380px;
        }
    }

    .calendar-main-card {
        background-color: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: var(--shadow-sm);
        min-width: 0;
        width: 100%;
        overflow: hidden;
    }

    /* ----- SOBREESCRITURA DE FULLCALENDAR (PREMIUM LOOK) ----- */
    .fc {
        --fc-page-bg-color: var(--bg-card);
        --fc-neutral-bg-color: var(--bg-page);
        --fc-neutral-text-color: var(--text-muted);
        --fc-border-color: var(--border-color);
        --fc-button-text-color: var(--text-main);
        --fc-button-bg-color: var(--bg-card);
        --fc-button-border-color: var(--border-color);
        --fc-button-hover-bg-color: var(--bg-card-hover);
        --fc-button-hover-border-color: var(--border-color);
        --fc-button-active-bg-color: var(--primary);
        --fc-button-active-border-color: var(--primary);
        font-family: 'Outfit', sans-serif;
    }

    /* Botones de navegación */
    .fc .fc-button-primary {
        background-color: var(--bg-card) !important;
        border: 1px solid var(--border-color) !important;
        color: var(--text-main) !important;
        font-weight: 600 !important;
        font-size: 0.85rem !important;
        border-radius: 8px !important;
        padding: 0.4rem 0.8rem !important;
        transition: all var(--transition-speed) ease !important;
        text-transform: capitalize !important;
        box-shadow: var(--shadow-sm) !important;
    }

    .fc .fc-button-primary:hover {
        background-color: var(--primary-light) !important;
        border-color: var(--primary) !important;
        color: var(--primary) !important;
    }

    .fc .fc-button-primary:disabled {
        opacity: 0.5 !important;
        background-color: var(--bg-card) !important;
        color: var(--text-muted) !important;
    }

    .fc .fc-button-primary:not(:disabled).fc-button-active, 
    .fc .fc-button-primary:not(:disabled):active {
        background-color: var(--primary) !important;
        border-color: var(--primary) !important;
        color: white !important;
    }

    .fc .fc-toolbar-title {
        font-size: 1.25rem !important;
        font-weight: 700 !important;
        letter-spacing: -0.01em;
        color: var(--text-main);
    }

    /* Headers de la semana */
    .fc th {
        background-color: var(--bg-page);
        color: var(--text-muted) !important;
        padding: 0.5rem 0 !important;
        font-size: 0.8rem !important;
        font-weight: 600 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.05em !important;
        border-color: var(--border-color) !important;
    }

    .fc td {
        border-color: var(--border-color) !important;
    }

    /* Celdas de días */
    .fc .fc-daygrid-day-number {
        font-weight: 700;
        font-size: 0.9rem;
        color: var(--text-main);
        padding: 6px 10px !important;
        text-decoration: none !important;
    }

    .fc .fc-daygrid-day.fc-day-today {
        background-color: var(--primary-glow) !important;
    }

    .fc .fc-daygrid-day.fc-day-today .fc-daygrid-day-number {
        color: var(--primary) !important;
        background-color: var(--primary-light);
        border-radius: 4px;
    }

    /* Celda activa seleccionada */
    .fc-daygrid-day.active-day-cell {
        outline: 2px solid var(--primary) !important;
        outline-offset: -2px;
        background-color: var(--primary-glow) !important;
    }

    /* Eventos en celdas */
    .fc-event {
        border-radius: 6px !important;
        padding: 2px 6px !important;
        font-size: 0.75rem !important;
        font-weight: 600 !important;
        border: none !important;
        cursor: pointer !important;
        margin-bottom: 2px !important;
        box-shadow: var(--shadow-sm) !important;
        transition: transform 0.15s ease, filter 0.15s ease !important;
        color: white !important;
    }

    .fc-event:hover {
        transform: translateY(-1px) scale(1.02);
        filter: brightness(0.92);
    }

    /* Adaptar botones para que no peguen */
    .fc-header-toolbar {
        margin-bottom: 1.25rem !important;
        padding-bottom: 0.75rem;
        border-bottom: 1px solid var(--border-color);
    }

    /* ----- PANEL DE DETALLES DEL DÍA ----- */
    .day-details-panel {
        display: flex;
        flex-direction: column;
    }

    .details-panel-card {
        background-color: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: var(--shadow-sm);
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
        min-height: 400px;
        height: 100%;
        position: sticky;
        top: calc(var(--topbar-height) + 1.5rem);
        backdrop-filter: blur(8px);
    }

    .details-panel-header {
        display: flex;
        flex-direction: column;
        gap: 0.35rem;
        border-bottom: 1px solid var(--border-color);
        padding-bottom: 0.75rem;
    }

    .details-panel-header h3 {
        font-size: 1.15rem;
        font-weight: 700;
        letter-spacing: -0.01em;
    }

    .selected-date-pill {
        font-size: 0.75rem;
        font-weight: 600;
        background-color: var(--primary-light);
        color: var(--primary);
        padding: 0.25rem 0.65rem;
        border-radius: 9999px;
        align-self: flex-start;
    }

    .details-panel-body {
        flex-grow: 1;
        overflow-y: auto;
        max-height: 520px;
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        padding-right: 0.25rem;
    }

    /* Contenedor vacío */
    .empty-details-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        gap: 1rem;
        height: 250px;
        color: var(--text-muted);
    }

    .empty-state-icon {
        font-size: 2.5rem;
        filter: grayscale(0.2);
    }

    .empty-details-state p {
        font-size: 0.875rem;
        line-height: 1.5;
        max-width: 250px;
    }

    /* Spinner loader */
    .details-loading-box {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        height: 250px;
        gap: 0.75rem;
        color: var(--text-muted);
    }

    .spinner {
        width: 32px;
        height: 32px;
        border: 3px solid var(--border-color);
        border-top-color: var(--primary);
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    /* ----- TARJETAS DE EVENTOS ----- */
    .event-card {
        background-color: var(--bg-page);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 1rem;
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        box-shadow: var(--shadow-sm);
        transition: all var(--transition-speed) ease;
        border-left-width: 4px;
        animation: slideUp 0.25s ease-out forwards;
    }

    @keyframes slideUp {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .event-card:hover {
        transform: translateX(2px);
    }

    .event-card-header {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .event-icon {
        font-size: 1.15rem;
    }

    .event-card-header h4 {
        font-size: 0.9rem;
        font-weight: 700;
        color: var(--text-main);
    }

    .event-card-body {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .emp-name {
        font-size: 0.9rem;
        font-weight: 600;
        color: var(--text-main);
    }

    .emp-meta {
        font-size: 0.775rem;
        color: var(--text-muted);
    }

    .card-actions {
        display: flex;
        gap: 0.5rem;
        margin-top: 0.5rem;
        flex-wrap: wrap;
    }

    .flex-between {
        justify-content: space-between;
        align-items: center;
    }

    /* Botones y enlaces de la tarjeta */
    .btn-profile, .btn-doc, .btn-action-eval {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.4rem 0.75rem;
        font-size: 0.75rem;
        font-weight: 600;
        border-radius: 6px;
        text-decoration: none;
        cursor: pointer;
        transition: all var(--transition-speed) ease;
    }

    .btn-profile {
        background-color: var(--primary-light);
        color: var(--primary);
        border: 1px solid rgba(29, 78, 216, 0.15);
    }

    .btn-profile:hover {
        background-color: var(--primary);
        color: white;
    }

    .btn-doc {
        background-color: var(--bg-card);
        color: var(--text-main);
        border: 1px solid var(--border-color);
    }

    .btn-doc:hover {
        border-color: var(--primary);
        color: var(--primary);
    }

    .btn-doc.success {
        background-color: rgba(22, 163, 74, 0.1);
        color: #16a34a;
        border-color: rgba(22, 163, 74, 0.2);
    }

    .btn-doc.success:hover {
        background-color: #16a34a;
        color: white;
    }

    .no-doc-alert {
        font-size: 0.7rem;
        color: var(--baja);
        font-weight: 600;
        display: flex;
        align-items: center;
    }

    .btn-action-eval {
        background-color: #dc2626;
        color: white;
        border: none;
    }

    .btn-action-eval:hover {
        background-color: #b91c1c;
    }

    .badge-done {
        font-size: 0.75rem;
        font-weight: 600;
        color: #16a34a;
        background-color: rgba(22, 163, 74, 0.1);
        padding: 0.25rem 0.5rem;
        border-radius: 6px;
        border: 1px solid rgba(22, 163, 74, 0.2);
    }

    .bd-highlight {
        color: #16a34a;
        font-weight: 600;
        font-size: 0.75rem;
        background-color: #dcfce7;
        padding: 0.05rem 0.35rem;
        border-radius: 4px;
        margin-left: 0.25rem;
    }

    /* Colores por tipo de tarjeta */
    .holiday-card { border-left-color: #f97316; }
    .birthday-card { border-left-color: #22c55e; }
    .anniversary-card { border-left-color: #3b82f6; }
    .vacation-card { border-left-color: #eab308; }
    .contract-card { border-left-color: #f87171; }
    .evaluation-card { border-left-color: #14b8a6; }
    .incapacity-card { border-left-color: #a855f7; }

    /* Estilos especiales */
    .special-birthday {
        background: linear-gradient(135deg, var(--bg-page) 0%, rgba(34, 197, 94, 0.03) 100%);
        border: 1px solid rgba(34, 197, 94, 0.15);
        border-left: 4px solid #eab308;
    }

    .urgent {
        background-color: rgba(239, 68, 68, 0.02);
        border-color: rgba(239, 68, 68, 0.15);
        border-left: 4px solid #ef4444;
    }

    /* ----- PESTAÑA LISTA EVENTOS ----- */
    .list-filters-card {
        background-color: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 1.25rem;
        box-shadow: var(--shadow-sm);
        margin-bottom: 1rem;
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

    .btn-filter-submit {
        background-color: var(--primary);
        color: white;
        border: none;
        border-radius: 8px;
        padding: 0.55rem 1.25rem;
        font-size: 0.875rem;
        font-weight: 600;
        cursor: pointer;
        transition: background-color var(--transition-speed) ease;
        height: 38px;
    }

    .btn-filter-submit:hover {
        background-color: var(--primary-hover);
    }

    /* Toolbar de tabla */
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

    .search-input-box {
        position: relative;
        max-width: 480px;
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

    /* Estilos de Tabla */
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

    .premium-table th.sortable {
        cursor: pointer;
        user-select: none;
    }

    .premium-table th.sortable:hover {
        color: var(--primary);
        background-color: var(--bg-card-hover);
    }

    .sort-indicator {
        font-size: 0.75rem;
        margin-left: 0.25rem;
        color: var(--text-muted);
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

    .subtext {
        font-weight: normal;
        font-size: 0.75rem;
        color: var(--text-muted);
        margin-left: 0.15rem;
    }

    .range-date {
        font-weight: 500;
    }

    .detalles-cell {
        font-size: 0.8rem;
        color: var(--text-muted);
        max-width: 240px;
        white-space: normal;
        word-wrap: break-word;
    }

    .no-records-row {
        text-align: center;
        padding: 2.5rem !important;
        color: var(--text-muted);
        font-style: italic;
    }

    /* Pillas de tipos de eventos */
    .event-type-pill {
        display: inline-flex;
        align-items: center;
        padding: 0.25rem 0.65rem;
        border-radius: 9999px;
        font-size: 0.725rem;
        font-weight: 600;
    }

    .pill-birthday { background-color: #dcfce7; color: #16a34a; }
    .pill-anniversary { background-color: #dbeafe; color: #2563eb; }
    .pill-vacation { background-color: #fef9c3; color: #ca8a04; }
    .pill-contract { background-color: #fee2e2; color: #dc2626; }
    .pill-evaluation { background-color: #ccfbf1; color: #0d9488; }
    .pill-incapacity { background-color: #f3e8ff; color: #9333ea; }

    .btn-table-action {
        display: inline-flex;
        align-items: center;
        background-color: var(--bg-page);
        border: 1px solid var(--border-color);
        padding: 0.4rem 0.85rem;
        font-size: 0.775rem;
        font-weight: 600;
        border-radius: 6px;
        text-decoration: none;
        color: var(--text-main);
        transition: all var(--transition-speed) ease;
    }

    .btn-table-action:hover {
        border-color: var(--primary);
        color: var(--primary);
        background-color: var(--primary-light);
    }
</style>
@endpush

@push('scripts')
<!-- Cargar librería global de FullCalendar v6 -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
<script>
    // Variable global del calendario
    let calendar = null;

    // Switch de Pestañas (Tabs)
    function switchTab(tabId, buttonEl) {
        document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
        document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
        
        document.getElementById(tabId).classList.add('active');
        buttonEl.classList.add('active');

        // Sincronizar el query param en la URL de forma sutil
        const url = new URL(window.location.href);
        url.searchParams.set('tab', tabId);
        window.history.replaceState({}, '', url);

        // Si se cambia a la pestaña del calendario, refrescar para recalcular tamaños
        if (tabId === 'calendar-tab' && calendar) {
            calendar.updateSize();
        }
    }

    // Cargar información de eventos de un día mediante AJAX
    function loadDayInfo(day, month, year, dateStr) {
        const label = document.getElementById('selected-date-label');
        const contentBox = document.getElementById('day-details-content');

        // Mostrar cargando
        label.textContent = dateStr.split('-').reverse().join('/');
        contentBox.innerHTML = `
            <div class="details-loading-box">
                <div class="spinner"></div>
                <p>Cargando eventos del día...</p>
            </div>
        `;

        // Petición AJAX (Fetch)
        fetch(`{{ route('diario.dia-info') }}?day=${day}&month=${month}&year=${year}`)
            .then(response => {
                if (!response.ok) throw new Error('Error al obtener datos');
                return response.json();
            })
            .then(data => {
                label.textContent = data.date_formatted;
                renderDayDetails(data);
            })
            .catch(error => {
                console.error(error);
                contentBox.innerHTML = `
                    <div class="empty-details-state">
                        <div class="empty-state-icon">⚠️</div>
                        <p style="color: var(--baja);">Error al cargar los detalles. Intente de nuevo.</p>
                    </div>
                `;
            });
    }

    // Renderizar las tarjetas de eventos en el panel de detalles
    function renderDayDetails(data) {
        const contentBox = document.getElementById('day-details-content');
        let html = '';

        // Formateador de fechas helper
        const formatDate = (dateString) => {
            if (!dateString) return '—';
            const parts = dateString.split('-');
            if (parts.length !== 3) return dateString;
            return `${parts[2]}/${parts[1]}/${parts[0]}`;
        };

        let totalEventsCount = 0;

        // 1. Festivos
        if (data.holiday) {
            totalEventsCount++;
            html += `
                <div class="event-card holiday-card">
                    <div class="event-card-header">
                        <span class="event-icon">🎉</span>
                        <h4>Día Festivo Oficial</h4>
                    </div>
                    <div class="event-card-body">
                        <p class="emp-name" style="color: #ea580c;">${data.holiday}</p>
                        <p class="emp-meta">Feriado nacional de ley en México.</p>
                    </div>
                </div>
            `;
        }

        // 2. Cumpleaños
        if (data.birthdays && data.birthdays.length > 0) {
            data.birthdays.forEach(emp => {
                totalEventsCount++;
                html += `
                    <div class="event-card birthday-card">
                        <div class="event-card-header">
                            <span class="event-icon">🎂</span>
                            <h4>¡Feliz Cumpleaños!</h4>
                        </div>
                        <div class="event-card-body">
                            <p class="emp-name">${emp.Nombre_Completo}</p>
                            <p class="emp-meta">Empleado #${emp.N_Empleado} | ${emp.Edad} años cumplidos</p>
                            <p class="emp-meta"><strong>Nacimiento:</strong> ${formatDate(emp.Fecha_Nacimiento)}</p>
                            <div class="card-actions">
                                <a href="/empleados/${emp.N_Empleado}" class="btn-profile">Ver Perfil</a>
                            </div>
                        </div>
                    </div>
                `;
            });
        }

        // 3. Aniversarios Laborales
        if (data.anniversaries && data.anniversaries.length > 0) {
            data.anniversaries.forEach(emp => {
                totalEventsCount++;
                html += `
                    <div class="event-card anniversary-card">
                        <div class="event-card-header">
                            <span class="event-icon">🏆</span>
                            <h4>Aniversario de Ingreso</h4>
                        </div>
                        <div class="event-card-body">
                            <p class="emp-name">${emp.Nombre_Completo}</p>
                            <p class="emp-meta">Empleado #${emp.N_Empleado} | ${emp.years_completed} años en la empresa</p>
                            <p class="emp-meta"><strong>Fecha de Ingreso:</strong> ${formatDate(emp.Fecha_Ingreso)}</p>
                            <div class="card-actions">
                                <a href="/empleados/${emp.N_Empleado}" class="btn-profile">Ver Perfil</a>
                            </div>
                        </div>
                    </div>
                `;
            });
        }

        // 4. Vacaciones
        if (data.vacations && data.vacations.length > 0) {
            data.vacations.forEach(vac => {
                totalEventsCount++;
                const bdayBadge = vac.is_birthday ? '<span class="bd-highlight">🎂 Cumpleaños Hoy!</span>' : '';
                html += `
                    <div class="event-card vacation-card ${vac.is_birthday ? 'special-birthday' : ''}">
                        <div class="event-card-header">
                            <span class="event-icon">🏖️</span>
                            <h4>Vacaciones Aprobadas ${bdayBadge}</h4>
                        </div>
                        <div class="event-card-body">
                            <p class="emp-name">${vac.Nombre_Completo}</p>
                            <p class="emp-meta">Empleado #${vac.N_Empleado}</p>
                            <p class="emp-meta"><strong>Concepto:</strong> ${vac.Concepto}</p>
                            <p class="emp-meta"><strong>Rango:</strong> ${formatDate(vac.Fecha_Inicial)} al ${formatDate(vac.Fecha_Final)}</p>
                            <p class="emp-meta"><strong>Días Tomados:</strong> ${vac.Tomadas} | Saldo Restante: ${vac.Saldo}</p>
                            <div class="card-actions">
                                <a href="/empleados/${vac.N_Empleado}" class="btn-profile">Ver Perfil</a>
                            </div>
                        </div>
                    </div>
                `;
            });
        }

        // 5. Vencimientos de Contratos (Determinado)
        if (data.contracts && data.contracts.length > 0) {
            data.contracts.forEach(con => {
                totalEventsCount++;
                const isUrgent = con.days_left == 0;
                const docAction = con.ruta_doc_fallback
                    ? `<a href="${con.ruta_doc_fallback}" target="_blank" class="btn-doc success">Ver Contrato Indet.</a>`
                    : `<span class="no-doc-alert">⚠️ Sin Documento</span>`;
                html += `
                    <div class="event-card contract-card ${isUrgent ? 'urgent' : ''}">
                        <div class="event-card-header">
                            <span class="event-icon">${isUrgent ? '🚨' : '📄'}</span>
                            <h4>${isUrgent ? 'Vencimiento de Contrato Hoy' : `Contrato Vence en ${con.days_left} día(s)`}</h4>
                        </div>
                        <div class="event-card-body">
                            <p class="emp-name">${con.Nombre_Completo}</p>
                            <p class="emp-meta">Empleado #${con.N_Empleado}</p>
                            <p class="emp-meta"><strong>Puesto:</strong> ${con.Nombre_Puesto || '—'}</p>
                            <p class="emp-meta"><strong>Ubicación:</strong> ${con.Fisica || '—'}</p>
                            <p class="emp-meta"><strong>Fecha Vencimiento:</strong> ${formatDate(con.F_Vence)}</p>
                            <div class="card-actions flex-between">
                                <div style="display: flex; gap: 0.5rem;">
                                    <a href="/empleados/${con.N_Empleado}" class="btn-profile">Ver Perfil</a>
                                    ${docAction}
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });
        }

        // 6. Evaluaciones
        if (data.evaluations && data.evaluations.length > 0) {
            data.evaluations.forEach(ev => {
                totalEventsCount++;
                const isUrgent = ev.days_left == 0;
                
                let evalAction = '';
                if (ev.Ruta_Doc === 'Realizada') {
                    evalAction = `<span class="badge-done">✅ Realizada</span>`;
                } else {
                    evalAction = `
                        <button type="button" class="btn-action-eval" onclick="markEvaluationAsRealizada(${ev.ID}, this)">
                            Hecho
                        </button>
                    `;
                }

                html += `
                    <div class="event-card evaluation-card" id="eval-card-${ev.ID}">
                        <div class="event-card-header">
                            <span class="event-icon">🎓</span>
                            <h4>${isUrgent ? 'Evaluación Requerida Hoy' : `Evaluación en ${ev.days_left} día(s)`}</h4>
                        </div>
                        <div class="event-card-body">
                            <p class="emp-name">${ev.Nombre_Completo}</p>
                            <p class="emp-meta">Empleado #${ev.N_Empleado} | <strong>Evaluación:</strong> #${ev.evaluation_number}</p>
                            <p class="emp-meta"><strong>Puesto:</strong> ${ev.Nombre_Puesto || '—'}</p>
                            <p class="emp-meta"><strong>Ubicación:</strong> ${ev.Fisica || '—'}</p>
                            <p class="emp-meta"><strong>Vence Evaluación:</strong> ${formatDate(ev.F_Vence)}</p>
                            <div class="card-actions flex-between" id="eval-actions-${ev.ID}">
                                <a href="/empleados/${ev.N_Empleado}" class="btn-profile">Ver Perfil</a>
                                ${evalAction}
                            </div>
                        </div>
                    </div>
                `;
            });
        }

        // 7. Incapacidades
        if (data.incapacities && data.incapacities.length > 0) {
            data.incapacities.forEach(inc => {
                totalEventsCount++;
                const docAction = inc.Ruta_Documento
                    ? `<a href="${inc.Ruta_Documento}" target="_blank" class="btn-doc">Ver Documento</a>`
                    : '';
                html += `
                    <div class="event-card incapacity-card">
                        <div class="event-card-header">
                            <span class="event-icon">🏥</span>
                            <h4>Incapacidad Activa</h4>
                        </div>
                        <div class="event-card-body">
                            <p class="emp-name">${inc.Nombre_Completo}</p>
                            <p class="emp-meta">Empleado #${inc.N_Empleado}</p>
                            <p class="emp-meta"><strong>Motivo:</strong> ${inc.Descripcion || '—'}</p>
                            <p class="emp-meta"><strong>Vigencia:</strong> ${formatDate(inc.Fecha_Inicio)} al ${formatDate(inc.Fecha_Fin)}</p>
                            <p class="emp-meta"><strong>Días Totales:</strong> ${inc.Total_Dias}</p>
                            <div class="card-actions">
                                <a href="/empleados/${inc.N_Empleado}" class="btn-profile">Ver Perfil</a>
                                ${docAction}
                            </div>
                        </div>
                    </div>
                `;
            });
        }

        if (totalEventsCount === 0) {
            contentBox.innerHTML = `
                <div class="empty-details-state animate-fade-in">
                    <div class="empty-state-icon">🎉</div>
                    <p>No hay eventos extraordinarios programados para esta fecha.</p>
                </div>
            `;
        } else {
            contentBox.innerHTML = html;
        }
    }

    // AJAX POST para marcar evaluación como completada
    function markEvaluationAsRealizada(id, buttonEl) {
        if (!confirm('¿Estás seguro de que deseas marcar esta evaluación como realizada?')) {
            return;
        }

        buttonEl.disabled = true;
        buttonEl.textContent = '...';

        fetch(`/diario/evaluacion/${id}/realizar`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => {
            if (!response.ok) throw new Error('Error de comunicación');
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Cambiar el botón Hecho por el badge verde
                const actionsContainer = document.getElementById(`eval-actions-${id}`);
                if (actionsContainer) {
                    actionsContainer.querySelector('.btn-action-eval')?.remove();
                    
                    const doneSpan = document.createElement('span');
                    doneSpan.className = 'badge-done';
                    doneSpan.textContent = '✅ Realizada';
                    actionsContainer.appendChild(doneSpan);
                }
                
                // Refrescar el feed de FullCalendar para actualizar el evento (si corresponde)
                if (calendar) {
                    calendar.refetchEvents();
                }
            } else {
                alert('No se pudo actualizar: ' + (data.error || 'Intente de nuevo.'));
                buttonEl.disabled = false;
                buttonEl.textContent = 'Hecho';
            }
        })
        .catch(error => {
            console.error(error);
            alert('Error de conexión al marcar la evaluación.');
            buttonEl.disabled = false;
            buttonEl.textContent = 'Hecho';
        });
    }

    // ----- FILTRADO Y ORDENADO DE TABLA (TAB 2) -----
    
    // Filtrador de búsqueda de texto en tiempo real en la tabla
    function filterRangeTable() {
        let input = document.getElementById("searchInput").value.toLowerCase();
        let table = document.getElementById("eventsRangeTable");
        let rows = table.getElementsByTagName("tbody")[0].getElementsByTagName("tr");

        for (let i = 0; i < rows.length; i++) {
            let cells = rows[i].getElementsByTagName("td");
            let rowContainsFilter = false;

            for (let j = 0; j < cells.length; j++) {
                if (cells[j]) {
                    let cellText = cells[j].textContent || cells[j].innerText;
                    if (cellText.toLowerCase().includes(input)) {
                        rowContainsFilter = true;
                        break;
                    }
                }
            }

            if (rows[i].classList.contains('no-records-row') || rows[i].cells[0].classList.contains('no-records-row')) {
                continue;
            }

            rows[i].style.display = rowContainsFilter ? "" : "none";
        }
    }

    // Ordenamiento de tabla por columna
    function sortTable(columnIndex, type) {
        let table = document.getElementById("eventsRangeTable");
        let tbody = table.getElementsByTagName("tbody")[0];
        let rows = Array.from(tbody.rows);
        
        if (rows.length <= 1 && (rows[0]?.cells[0]?.classList.contains('no-records-row') || rows[0]?.cells[0]?.colSpan > 1)) {
            return;
        }

        let ascending = table.getAttribute('data-sort-dir') !== 'asc';

        rows.sort((rowA, rowB) => {
            let cellA = rowA.cells[columnIndex].textContent.trim();
            let cellB = rowB.cells[columnIndex].textContent.trim();

            if (type === 'date') {
                const cleanDate = (str) => {
                    const firstPart = str.split(' al ')[0];
                    const parts = firstPart.split('/');
                    if (parts.length === 3) {
                        return new Date(parts[2], parts[1] - 1, parts[0]).getTime();
                    }
                    return new Date(str).getTime() || 0;
                };
                return ascending ? cleanDate(cellA) - cleanDate(cellB) : cleanDate(cellB) - cleanDate(cellA);
            } else {
                return ascending ? cellA.localeCompare(cellB) : cellB.localeCompare(cellA);
            }
        });

        rows.forEach(row => tbody.appendChild(row));
        table.setAttribute('data-sort-dir', ascending ? 'asc' : 'desc');
    }

    // Inicializar FullCalendar y Tab Activa
    document.addEventListener('DOMContentLoaded', () => {
        // 1. Inicialización de FullCalendar
        const calendarEl = document.getElementById('calendar');
        calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'es',
            height: 'auto',
            // Celular/tablet: máximo 3 eventos por día + enlace "+N más"; sin esto un día con cientos
            // de eventos estira la página miles de pixeles. En PC se ve igual que antes (sin límite).
            dayMaxEvents: window.innerWidth <= 1024 ? 3 : false,
            firstDay: 1, // Lunes es primer día de la semana
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,dayGridWeek'
            },
            buttonText: {
                today: 'Hoy',
                month: 'Mes',
                week: 'Semana'
            },
            events: '{{ route("diario.eventos-feed") }}',
            
            // Selección de día
            dateClick: function(info) {
                const dateObj = new Date(info.dateStr + 'T00:00:00');
                const day = dateObj.getDate();
                const month = dateObj.getMonth() + 1;
                const year = dateObj.getFullYear();
                
                loadDayInfo(day, month, year, info.dateStr);
                
                // Resaltar día en la cuadrícula
                document.querySelectorAll('.fc-daygrid-day').forEach(el => el.classList.remove('active-day-cell'));
                info.dayEl.classList.add('active-day-cell');
            },

            // Selección de evento
            eventClick: function(info) {
                info.jsEvent.preventDefault();
                
                const dateStr = info.event.startStr.split('T')[0]; // YYYY-MM-DD
                const dateObj = new Date(dateStr + 'T00:00:00');
                const day = dateObj.getDate();
                const month = dateObj.getMonth() + 1;
                const year = dateObj.getFullYear();
                
                loadDayInfo(day, month, year, dateStr);
                
                // Resaltar celda del día correspondiente
                document.querySelectorAll('.fc-daygrid-day').forEach(el => el.classList.remove('active-day-cell'));
                const cell = info.el.closest('.fc-daygrid-day');
                if (cell) {
                    cell.classList.add('active-day-cell');
                }
            },

            // Cargar por defecto hoy tras renderizar
            viewDidMount: function(info) {
                setTimeout(() => {
                    const todayCell = document.querySelector('.fc-day-today');
                    if (todayCell) {
                        const dateStr = todayCell.getAttribute('data-date');
                        const dateObj = new Date(dateStr + 'T00:00:00');
                        loadDayInfo(dateObj.getDate(), dateObj.getMonth() + 1, dateObj.getFullYear(), dateStr);
                        todayCell.classList.add('active-day-cell');
                    }
                }, 150);
            }
        });
        calendar.render();

        // 2. Control de Tab inicial por URL
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
@endsection
