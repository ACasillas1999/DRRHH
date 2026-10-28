@extends('layouts.app')

@section('content')
<div class="incidencias-container">
    <!-- Cabecera del Panel -->
    <div class="incidencias-header-card animate-fade-in">
        <div class="header-icon-box">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="20" x2="18" y2="10"></line>
                <line x1="12" y1="20" x2="12" y2="4"></line>
                <line x1="6" y1="20" x2="6" y2="14"></line>
            </svg>
        </div>
        <div class="header-text-box">
            <h1>Reportes de Asistencia</h1>
            <p>Consulta métricas de puntualidad, faltas de marcas y estadísticas de asistencia de los empleados.</p>
        </div>
    </div>

    <!-- Pestañas (Tabs) -->
    <div class="incidencias-tabs">
        <button class="tab-btn active" onclick="switchTab('rsc-tab', this)">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="12"></line>
                <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
            Sin Checar / Incidencias del Día
        </button>
        <button class="tab-btn" onclick="switchTab('ranking-tab', this)">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                <line x1="9" y1="3" x2="9" y2="21"></line>
                <line x1="15" y1="3" x2="15" y2="21"></line>
                <line x1="3" y1="9" x2="21" y2="9"></line>
                <line x1="3" y1="15" x2="21" y2="15"></line>
            </svg>
            Ranking de Puntualidad
        </button>
    </div>

    <!-- PESTAÑA 1: EMPLEADOS SIN CHECAR -->
    <div id="rsc-tab" class="tab-content active">
        <div class="control-filters-card">
            <div class="table-toolbar-title" style="margin-bottom: 0.75rem;">Empleados sin marca en un día específico</div>
            <div class="filters-grid" style="align-items: flex-end;">
                <div class="filter-group">
                    <label for="rsc_fecha">Fecha:</label>
                    <input type="date" id="rsc_fecha" value="{{ date('Y-m-d') }}" class="form-control">
                </div>
                <div class="filter-group">
                    <label for="rsc_departamento">Departamento:</label>
                    <select id="rsc_departamento" class="form-control">
                        <option value="">-- Todos --</option>
                        @foreach($departamentosList as $dep)
                            <option value="{{ $dep }}">{{ $dep }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-group">
                    <label for="rsc_sucursal">Sucursal:</label>
                    <select id="rsc_sucursal" class="form-control">
                        <option value="">-- Todas --</option>
                        @foreach($sucursalesList as $suc)
                            <option value="{{ $suc }}">{{ $suc }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-group">
                    <label for="rsc_puesto">Puesto:</label>
                    <select id="rsc_puesto" class="form-control">
                        <option value="">-- Todos --</option>
                        @foreach($puestosList as $pu)
                            <option value="{{ $pu }}">{{ $pu }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="button" class="btn-primary-action" onclick="fetchReporteSinChecar()">Consultar</button>
            </div>
        </div>

        <div class="control-filters-card" id="rsc-resumen" style="display: none;">
            <div class="chips-row-reportes">
                <span class="chip-reporte chip-falta">Falta completa: <b id="rsc-count-falta">0</b></span>
                <span class="chip-reporte chip-entrada">Sin entrada: <b id="rsc-count-entrada">0</b></span>
                <span class="chip-reporte chip-salida">Sin salida: <b id="rsc-count-salida">0</b></span>
                <span class="chip-reporte chip-sinconciliar">Sin conciliar: <b id="rsc-count-sinconciliar">0</b></span>
            </div>
        </div>

        <div class="table-container-card">
            <div class="table-header-toolbar">
                <div class="search-input-box compact">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="search-icon">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                    <input type="text" id="rscSearch" onkeyup="filterReportTable('rscSearch', 'rscTable')" placeholder="Buscar empleado, sucursal o puesto...">
                </div>
            </div>
            <div class="table-responsive">
                <table class="premium-table" id="rscTable">
                    <thead>
                        <tr>
                            <th onclick="sortTable('rscTable', 0, true)" style="cursor: pointer; user-select: none;" title="Ordenar por número de empleado">N_Emp <span class="sort-icon">⇅</span></th>
                            <th onclick="sortTable('rscTable', 1, false)" style="cursor: pointer; user-select: none;" title="Ordenar por empleado">Empleado <span class="sort-icon">⇅</span></th>
                            <th onclick="sortTable('rscTable', 2, false)" style="cursor: pointer; user-select: none;" title="Ordenar por departamento">Departamento <span class="sort-icon">⇅</span></th>
                            <th onclick="sortTable('rscTable', 3, false)" style="cursor: pointer; user-select: none;" title="Ordenar por puesto">Puesto <span class="sort-icon">⇅</span></th>
                            <th onclick="sortTable('rscTable', 4, false)" style="cursor: pointer; user-select: none;" title="Ordenar por sucursal">Sucursal <span class="sort-icon">⇅</span></th>
                            <th onclick="sortTable('rscTable', 5, false)" style="cursor: pointer; user-select: none;" title="Ordenar por categoría">Categoría <span class="sort-icon">⇅</span></th>
                        </tr>
                    </thead>
                    <tbody id="rsc-tbody">
                        <tr><td colspan="6" class="no-records-row">Selecciona una fecha y da clic en "Consultar".</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- PESTAÑA 2: RANKING -->
    <div id="ranking-tab" class="tab-content">
        <div class="control-filters-card">
            <div class="table-toolbar-title" style="margin-bottom: 0.75rem;">Puntualidad por departamento y ranking de empleados</div>
            <div class="filters-grid" style="align-items: flex-end;">
                <div class="filter-group">
                    <label for="rp_start_date">Desde:</label>
                    <input type="date" id="rp_start_date" value="{{ \Carbon\Carbon::now()->startOfMonth()->toDateString() }}" class="form-control">
                </div>
                <div class="filter-group">
                    <label for="rp_end_date">Hasta:</label>
                    <input type="date" id="rp_end_date" value="{{ \Carbon\Carbon::now()->toDateString() }}" class="form-control">
                </div>
                <div class="filter-group">
                    <label for="rp_departamento">Departamento:</label>
                    <select id="rp_departamento" class="form-control">
                        <option value="">-- Todos --</option>
                        @foreach($departamentosList as $dep)
                            <option value="{{ $dep }}">{{ $dep }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="button" class="btn-primary-action" onclick="fetchReportePuntualidad()">Consultar</button>
            </div>
        </div>

        <!-- Tarjetas de Resumen (KPIs) -->
        <div id="rp-kpi-summary-row" style="display: none; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 1.5rem; margin-top: 1.25rem;">
            <div class="control-filters-card" style="margin-bottom: 0; padding: 1rem; display: flex; align-items: center; gap: 1rem; border-left: 4px solid var(--primary); box-shadow: var(--shadow-sm);">
                <div style="background-color: var(--primary-light); color: var(--primary); padding: 0.65rem; border-radius: 10px; display: inline-flex; font-size: 1.25rem;">👥</div>
                <div>
                    <h5 style="margin: 0; font-size: 0.725rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">Empleados Evaluados</h5>
                    <div id="kpi-rp-totales" style="font-size: 1.25rem; font-weight: 800; color: var(--text-main); margin-top: 0.15rem;">0</div>
                </div>
            </div>
            <div class="control-filters-card" style="margin-bottom: 0; padding: 1rem; display: flex; align-items: center; gap: 1rem; border-left: 4px solid #0ea5e9; box-shadow: var(--shadow-sm);">
                <div style="background-color: #e0f2fe; color: #0284c7; padding: 0.65rem; border-radius: 10px; display: inline-flex; font-size: 1.25rem;">📈</div>
                <div>
                    <h5 style="margin: 0; font-size: 0.725rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">Promedio Puntualidad</h5>
                    <div id="kpi-rp-promedio" style="font-size: 1.25rem; font-weight: 800; color: var(--text-main); margin-top: 0.15rem;">0%</div>
                </div>
            </div>
            <div class="control-filters-card" style="margin-bottom: 0; padding: 1rem; display: flex; align-items: center; gap: 1rem; border-left: 4px solid var(--activo); box-shadow: var(--shadow-sm);">
                <div style="background-color: rgba(22, 163, 74, 0.08); color: var(--activo); padding: 0.65rem; border-radius: 10px; display: inline-flex; font-size: 1.25rem;">🏆</div>
                <div>
                    <h5 style="margin: 0; font-size: 0.725rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">Puntualidad Excelente</h5>
                    <div id="kpi-rp-excelentes" style="font-size: 1.25rem; font-weight: 800; color: var(--activo); margin-top: 0.15rem;">0</div>
                </div>
            </div>
            <div class="control-filters-card" style="margin-bottom: 0; padding: 1rem; display: flex; align-items: center; gap: 1rem; border-left: 4px solid var(--baja); box-shadow: var(--shadow-sm);">
                <div style="background-color: rgba(220, 38, 38, 0.08); color: var(--baja); padding: 0.65rem; border-radius: 10px; display: inline-flex; font-size: 1.25rem;">⚠️</div>
                <div>
                    <h5 style="margin: 0; font-size: 0.725rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">Casos Críticos</h5>
                    <div id="kpi-rp-criticos" style="font-size: 1.25rem; font-weight: 800; color: var(--baja); margin-top: 0.15rem;">0</div>
                </div>
            </div>
        </div>

        <div class="table-container-card" style="padding: 1.25rem; margin-bottom: 1.5rem; box-shadow: var(--shadow-sm);">
            <div class="table-toolbar-title" style="margin-bottom: 0.75rem;">% Puntualidad por departamento</div>
            <canvas id="graficoPuntualidadDepto" height="80"></canvas>
        </div>

        <div class="table-container-card">
            <div class="table-header-toolbar">
                <div class="search-input-box compact">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="search-icon">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                    <input type="text" id="rankingSearch" onkeyup="filterRankingTable()" placeholder="Buscar empleado o departamento...">
                </div>
            </div>
            <div class="table-responsive">
                <table class="premium-table" id="rankingTable">
                    <thead>
                        <tr>
                            <th onclick="sortTable('rankingTable', 0, false)" style="cursor: pointer; user-select: none;" title="Ordenar por empleado">Empleado <span class="sort-icon">⇅</span></th>
                            <th onclick="sortTable('rankingTable', 1, false)" style="cursor: pointer; user-select: none;" title="Ordenar por departamento">Departamento <span class="sort-icon">⇅</span></th>
                            <th onclick="sortTable('rankingTable', 2, false)" style="cursor: pointer; user-select: none;" title="Ordenar por hora de entrada promedio">Hora Prom. Entrada <span class="sort-icon">⇅</span></th>
                            <th onclick="sortTable('rankingTable', 3, true)" style="cursor: pointer; user-select: none;" title="Ordenar por minutos promedio de retardo">Min. Prom. Tarde <span class="sort-icon">⇅</span></th>
                            <th onclick="sortTable('rankingTable', 4, true)" style="cursor: pointer; user-select: none;" title="Ordenar por cantidad a tiempo">A Tiempo <span class="sort-icon">⇅</span></th>
                            <th onclick="sortTable('rankingTable', 5, true)" style="cursor: pointer; user-select: none;" title="Ordenar por cantidad de retardos">Retardos <span class="sort-icon">⇅</span></th>
                            <th onclick="sortTable('rankingTable', 6, true)" style="cursor: pointer; user-select: none;" title="Ordenar por cantidad de faltas">Faltas <span class="sort-icon">⇅</span></th>
                            <th onclick="sortTable('rankingTable', 7, true)" style="cursor: pointer; user-select: none;" title="Ordenar por porcentaje de puntualidad">% Puntualidad <span class="sort-icon">⇅</span></th>
                        </tr>
                    </thead>
                    <tbody id="ranking-tbody">
                        <tr><td colspan="8" class="no-records-row">Selecciona un rango de fechas y da clic en "Consultar".</td></tr>
                    </tbody>
                </table>
            </div>
            <!-- Pagination Controls -->
            <div id="ranking-pagination" style="display: none; padding: 1rem; border-top: 1px solid var(--border-color); align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
                <div style="font-size: 0.85rem; color: var(--text-muted); display: inline-flex; align-items: center; gap: 0.25rem;">
                    Mostrando <span id="ranking-p-range" style="font-weight: 700; color: var(--text-main);">0-0</span> de <span id="ranking-p-total" style="font-weight: 700; color: var(--text-main);">0</span> registros
                </div>
                <div style="display: flex; align-items: center; gap: 1rem; margin-left: auto;">
                    <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; color: var(--text-muted);">
                        <span>Mostrar:</span>
                        <select id="ranking-page-size" onchange="changeRankingPageSize()" style="padding: 0.35rem 0.5rem; border: 1px solid var(--border-color); border-radius: 6px; background-color: var(--bg-card); color: var(--text-main); font-family: inherit; font-size: 0.825rem; outline: none; cursor: pointer;">
                            <option value="10">10</option>
                            <option value="25" selected>25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                            <option value="all">Todos</option>
                        </select>
                    </div>
                    <div style="display: flex; gap: 0.25rem;">
                        <button type="button" id="ranking-btn-prev" onclick="changeRankingPage(-1)" class="btn-secondary" style="padding: 0.4rem 0.75rem; font-size: 0.8rem; height: auto;">
                            &larr; Anterior
                        </button>
                        <button type="button" id="ranking-btn-next" onclick="changeRankingPage(1)" class="btn-secondary" style="padding: 0.4rem 0.75rem; font-size: 0.8rem; height: auto;">
                            Siguiente &rarr;
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* Estilos del Módulo de Reportes de Asistencia */
    .incidencias-container {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
        animation: fadeIn 0.4s ease;
    }

    .sort-icon {
        font-size: 0.85rem;
        opacity: 0.6;
        margin-left: 4px;
        user-select: none;
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
        background: linear-gradient(135deg, var(--primary) 0%, #a855f7 100%);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
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
    .badge-off { background-color: var(--bg-page); border: 1px solid var(--border-color); color: var(--text-muted); }

    /* Chips de reportes */
    .chips-row-reportes {
        display: flex;
        flex-wrap: wrap;
        gap: 0.6rem;
    }

    .chip-reporte {
        padding: 0.4rem 0.85rem;
        border-radius: 9999px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .chip-falta { background-color: rgba(220, 38, 38, 0.1); color: var(--baja); }
    .chip-entrada { background-color: rgba(217, 119, 6, 0.1); color: var(--pendiente); }
    .chip-salida { background-color: rgba(217, 119, 6, 0.1); color: var(--pendiente); }
    .chip-sinconciliar { background-color: var(--bg-page); border: 1px solid var(--border-color); color: var(--text-muted); }

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
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

        // Ambos reportes son puro DB (no tocan el checador), se cargan solos al entrar.
        if (tabId === 'rsc-tab') {
            fetchReporteSinChecar();
        } else if (tabId === 'ranking-tab') {
            fetchReportePuntualidad();
        }
    }

    // --- FILTRAR TABLAS LOCALMENTE ---
    function filterReportTable(inputId, tableId) {
        const filtro = document.getElementById(inputId).value.toLowerCase();
        const filas = document.querySelectorAll('#' + tableId + ' tbody tr');
        filas.forEach(fila => {
            fila.style.display = fila.innerText.toLowerCase().includes(filtro) ? '' : 'none';
        });
    }

    // --- REPORTE: SIN CHECAR ---
    const CATEGORIA_LABELS = {
        falta_completa: { label: 'Falta completa', clase: 'chip-falta' },
        sin_entrada: { label: 'Sin entrada', clase: 'chip-entrada' },
        sin_salida: { label: 'Sin salida', clase: 'chip-salida' },
        sin_conciliar: { label: 'Sin conciliar', clase: 'chip-sinconciliar' },
    };

    function fetchReporteSinChecar() {
        const fecha = document.getElementById('rsc_fecha').value;
        const departamento = document.getElementById('rsc_departamento').value;
        const sucursal = document.getElementById('rsc_sucursal').value;
        const puesto = document.getElementById('rsc_puesto').value;
        const tbody = document.getElementById('rsc-tbody');

        tbody.innerHTML = '<tr><td colspan="6" class="no-records-row">Consultando...</td></tr>';

        const params = new URLSearchParams({ fecha, departamento, sucursal, puesto });

        fetch(`{{ route('incidencias.reporteSinChecar') }}?${params.toString()}`)
            .then(res => res.json())
            .then(data => {
                document.getElementById('rsc-resumen').style.display = 'block';
                document.getElementById('rsc-count-falta').textContent = data.falta_completa.length;
                document.getElementById('rsc-count-entrada').textContent = data.sin_entrada.length;
                document.getElementById('rsc-count-salida').textContent = data.sin_salida.length;
                document.getElementById('rsc-count-sinconciliar').textContent = data.sin_conciliar.length;

                const filas = [
                    ...data.falta_completa.map(f => ({ ...f, categoria: 'falta_completa' })),
                    ...data.sin_entrada.map(f => ({ ...f, categoria: 'sin_entrada' })),
                    ...data.sin_salida.map(f => ({ ...f, categoria: 'sin_salida' })),
                    ...data.sin_conciliar.map(f => ({ ...f, categoria: 'sin_conciliar' })),
                ];

                if (filas.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="6" class="no-records-row">Todos los empleados tienen marca completa ese día.</td></tr>';
                    return;
                }

                tbody.innerHTML = filas.map(f => {
                    const cat = CATEGORIA_LABELS[f.categoria];
                    return `<tr>
                        <td>${f.n_empleado}</td>
                        <td class="font-semibold">${f.nombre}</td>
                        <td>${f.departamento || '—'}</td>
                        <td>${f.puesto || '—'}</td>
                        <td>${f.sucursal || '—'}</td>
                        <td><span class="chip-reporte ${cat.clase}">${cat.label}</span></td>
                    </tr>`;
                }).join('');
            })
            .catch(() => {
                tbody.innerHTML = '<tr><td colspan="6" class="no-records-row">Error al consultar el reporte.</td></tr>';
            });
    }

    // --- REPORTE: RANKING Y KPIs ---
    let graficoPuntualidadDeptoChart = null;
    let rankingDataActual = [];
    let rankingFilteredData = [];
    let rankingPageSize = 25;
    let rankingCurrentPage = 1;

    function fetchReportePuntualidad() {
        const startDate = document.getElementById('rp_start_date').value;
        const endDate = document.getElementById('rp_end_date').value;
        const departamento = document.getElementById('rp_departamento').value;
        const tbody = document.getElementById('ranking-tbody');

        tbody.innerHTML = '<tr><td colspan="8" class="no-records-row">Consultando...</td></tr>';
        document.getElementById('rp-kpi-summary-row').style.display = 'none';

        const params = new URLSearchParams({ start_date: startDate, end_date: endDate, departamento });

        fetch(`{{ route('incidencias.reportePuntualidad') }}?${params.toString()}`)
            .then(res => res.json())
            .then(data => {
                rankingDataActual = data.ranking;
                rankingFilteredData = [...rankingDataActual];
                rankingCurrentPage = 1;
                
                // Calcular y mostrar KPIs
                calculatePuntualidadKPIs(rankingDataActual);
                document.getElementById('rp-kpi-summary-row').style.display = 'grid';

                // Resetear buscador
                document.getElementById('rankingSearch').value = '';

                renderRankingTable();
                renderGraficoPuntualidadDepto(data.por_departamento);
            })
            .catch(() => {
                tbody.innerHTML = '<tr><td colspan="8" class="no-records-row">Error al consultar el reporte.</td></tr>';
                document.getElementById('rp-kpi-summary-row').style.display = 'none';
            });
    }

    function calculatePuntualidadKPIs(ranking) {
        const total = ranking.length;
        let sumPuntualidad = 0;
        let excelentes = 0;
        let criticos = 0;
        let validPuntualidadCount = 0;

        ranking.forEach(r => {
            const p = r.porcentaje_puntualidad;
            if (p !== null && p !== undefined) {
                sumPuntualidad += p;
                validPuntualidadCount++;
                if (p >= 95) excelentes++;
                if (p < 80) criticos++;
            } else {
                criticos++;
            }
        });

        const promGral = validPuntualidadCount > 0 ? Math.round(sumPuntualidad / validPuntualidadCount) : 0;

        document.getElementById('kpi-rp-totales').textContent = total;
        document.getElementById('kpi-rp-promedio').textContent = promGral + '%';
        document.getElementById('kpi-rp-excelentes').textContent = excelentes;
        document.getElementById('kpi-rp-criticos').textContent = criticos;
    }

    function renderRankingTable() {
        const tbody = document.getElementById('ranking-tbody');
        const paginationContainer = document.getElementById('ranking-pagination');
        
        if (rankingFilteredData.length === 0) {
            tbody.innerHTML = '<tr><td colspan="8" class="no-records-row">Sin datos para el rango seleccionado o filtro de búsqueda.</td></tr>';
            paginationContainer.style.display = 'none';
            return;
        }

        paginationContainer.style.display = 'flex';
        
        let displayData = rankingFilteredData;
        const totalRecords = rankingFilteredData.length;
        let start = 0;
        let end = totalRecords;

        if (rankingPageSize !== 'all') {
            const size = parseInt(rankingPageSize, 10);
            const maxPages = Math.ceil(totalRecords / size);
            if (rankingCurrentPage > maxPages) rankingCurrentPage = maxPages;
            if (rankingCurrentPage < 1) rankingCurrentPage = 1;
            
            start = (rankingCurrentPage - 1) * size;
            end = Math.min(start + size, totalRecords);
            displayData = rankingFilteredData.slice(start, end);
        }

        tbody.innerHTML = displayData.map(r => {
            const p = r.porcentaje_puntualidad;
            let badgeClass = 'badge-off';
            if (p !== null && p !== undefined) {
                if (p >= 95) badgeClass = 'badge-ontime';
                else if (p >= 80) badgeClass = 'badge-retardo';
                else badgeClass = 'badge-falta';
            }
            const pStr = p !== null && p !== undefined ? `${p}%` : '—';
            
            return `
                <tr>
                    <td class="font-semibold">${r.nombre}</td>
                    <td>${r.departamento || '—'}</td>
                    <td>${r.hora_promedio_entrada || '—'}</td>
                    <td>${r.minutos_promedio_tarde ?? '—'}</td>
                    <td>${r.a_tiempo}</td>
                    <td>${r.retardos}</td>
                    <td>${r.faltas}</td>
                    <td><span class="status-badge ${badgeClass}">${pStr}</span></td>
                </tr>
            `;
        }).join('');

        document.getElementById('ranking-p-range').textContent = `${totalRecords === 0 ? 0 : start + 1}-${end}`;
        document.getElementById('ranking-p-total').textContent = totalRecords;

        const size = rankingPageSize === 'all' ? totalRecords : parseInt(rankingPageSize, 10);
        document.getElementById('ranking-btn-prev').disabled = rankingCurrentPage === 1 || rankingPageSize === 'all';
        document.getElementById('ranking-btn-next').disabled = rankingCurrentPage >= Math.ceil(totalRecords / size) || rankingPageSize === 'all';
    }

    function filterRankingTable() {
        const query = document.getElementById('rankingSearch').value.toLowerCase().trim();
        if (!query) {
            rankingFilteredData = [...rankingDataActual];
        } else {
            rankingFilteredData = rankingDataActual.filter(r => {
                return (r.nombre && r.nombre.toLowerCase().includes(query)) ||
                       (r.departamento && r.departamento.toLowerCase().includes(query));
            });
        }
        rankingCurrentPage = 1;
        renderRankingTable();
    }

    // Navegar y cambiar páginas
    function changeRankingPage(direction) {
        rankingCurrentPage += direction;
        renderRankingTable();
    }

    function changeRankingPageSize() {
        rankingPageSize = document.getElementById('ranking-page-size').value;
        rankingCurrentPage = 1;
        renderRankingTable();
    }

    function renderGraficoPuntualidadDepto(porDepartamento) {
        const ctx = document.getElementById('graficoPuntualidadDepto').getContext('2d');

        if (graficoPuntualidadDeptoChart) {
            graficoPuntualidadDeptoChart.destroy();
        }

        graficoPuntualidadDeptoChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: porDepartamento.map(d => d.departamento),
                datasets: [{
                    label: '% Puntualidad',
                    data: porDepartamento.map(d => d.porcentaje_puntualidad),
                    backgroundColor: '#005996',
                    borderRadius: 6,
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, max: 100, ticks: { callback: v => v + '%' } } }
            }
        });
    }

    // Inicializar Tab activa por URL al cargar la página
    document.addEventListener('DOMContentLoaded', () => {
        const urlParams = new URLSearchParams(window.location.search);
        const tabParam = urlParams.get('tab') || 'rsc-tab';
        const btn = document.querySelector(`.tab-btn[onclick*="${tabParam}"]`);
        if (btn) {
            btn.click();
        }
    });

    // Función de ordenación genérica para cualquier tabla
    function sortTable(tableId, columnIndex, esNumerico, esFecha = false) {
        const table = document.getElementById(tableId);
        if (!table) return;
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        
        // Ignorar fila vacía o de carga/sin registros
        if (rows.length === 1 && (rows[0].querySelector('.no-records-row') || rows[0].textContent.includes('Consultando...') || rows[0].textContent.includes('cargando...'))) {
            return;
        }

        const currentSortedCol = table.getAttribute('data-sort-col');
        let asc = table.getAttribute('data-sort-dir') !== 'asc';
        
        if (currentSortedCol !== String(columnIndex)) {
            asc = true;
        }

        rows.sort((a, b) => {
            if (a.cells.length <= columnIndex || b.cells.length <= columnIndex) return 0;
            const cellA = a.cells[columnIndex].textContent.trim();
            const cellB = b.cells[columnIndex].textContent.trim();

            if (esNumerico) {
                const cleanA = cellA.replace(/[^\d.-]/g, '');
                const cleanB = cellB.replace(/[^\d.-]/g, '');
                const numA = parseFloat(cleanA);
                const numB = parseFloat(cleanB);
                
                const valA = isNaN(numA) ? -Infinity : numA;
                const valB = isNaN(numB) ? -Infinity : numB;
                return asc ? valA - valB : valB - valA;
            } else if (esFecha) {
                const parseDate = (str) => {
                    if (!str || str === '—' || str === '-') return new Date(0);
                    if (str.includes('/')) {
                        const parts = str.split(' ')[0].split('/');
                        if (parts.length === 3) {
                            const timePart = str.split(' ')[1] || '00:00';
                            const timeParts = timePart.split(':');
                            return new Date(parts[2], parts[1] - 1, parts[0], timeParts[0] || 0, timeParts[1] || 0);
                        }
                    }
                    return new Date(str);
                };
                const dateA = parseDate(cellA);
                const dateB = parseDate(cellB);
                return asc ? dateA - dateB : dateB - dateA;
            } else {
                return asc ? cellA.localeCompare(cellB, 'es', { sensitivity: 'base' }) : cellB.localeCompare(cellA, 'es', { sensitivity: 'base' });
            }
        });

        // Limpiar iconos anteriores en los headers de esta tabla
        const headers = table.querySelectorAll('thead th');
        headers.forEach(h => {
            const icon = h.querySelector('.sort-icon');
            if (icon) icon.textContent = '⇅';
        });

        // Actualizar el icono de la columna ordenada
        const activeHeader = headers[columnIndex];
        if (activeHeader) {
            const activeIcon = activeHeader.querySelector('.sort-icon');
            if (activeIcon) {
                activeIcon.textContent = asc ? ' ▲' : ' ▼';
            }
        }

        // Reinsertar las filas en el orden correcto
        rows.forEach(row => tbody.appendChild(row));

        // Guardar estado del ordenamiento
        table.setAttribute('data-sort-dir', asc ? 'asc' : 'desc');
        table.setAttribute('data-sort-col', columnIndex);
    }
</script>
@endpush
