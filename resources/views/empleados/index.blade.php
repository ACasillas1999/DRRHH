@extends('layouts.app')

@section('title', 'Empleados')
@section('page_title', 'Listado de Empleados')

@push('styles')
<style>
    .top-row {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        margin-bottom: 1.5rem;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.6rem 1.1rem;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.875rem;
        text-decoration: none;
        border: 1px solid transparent;
        cursor: pointer;
    }
    .btn-primary { background-color: var(--primary); color: white; }
    .btn-primary:hover { opacity: 0.9; }

    .filtros-card {
        background-color: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 1.25rem;
        box-shadow: var(--shadow-sm);
        margin-bottom: 1.5rem;
    }

    .filtros-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 0.75rem;
        align-items: end;
    }

    .campo label {
        display: block;
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.03em;
        margin-bottom: 0.35rem;
    }

    select, input[type="text"] {
        width: 100%;
        padding: 0.5rem 0.65rem;
        border-radius: 8px;
        border: 1px solid var(--border-color);
        background-color: var(--bg-page);
        color: var(--text-main);
        font-family: inherit;
        font-size: 0.85rem;
    }

    .filtros-acciones {
        display: flex;
        gap: 0.5rem;
    }

    .btn-secundario {
        background-color: var(--bg-page);
        border: 1px solid var(--border-color);
        color: var(--text-main);
    }

    .table-card {
        background-color: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 20px;
        box-shadow: var(--shadow-sm);
        overflow-x: auto;
    }

    table { width: 100%; border-collapse: collapse; }

    thead th {
        text-align: left;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--text-muted);
        padding: 0.7rem 1rem; /* más compacto para que quepan más filas en pantalla */
        border-bottom: 1px solid var(--border-color);
        white-space: nowrap;
        cursor: pointer;
        user-select: none;
    }
    thead th:hover { color: var(--primary); }

    tbody td {
        padding: 0.5rem 1rem; /* filas compactas */
        border-bottom: 1px solid var(--border-color);
        font-size: 0.85rem;
        white-space: nowrap;
    }

    tbody tr:last-child td { border-bottom: none; }
    tbody tr:hover { background-color: var(--primary-light); }

    .estatus-activo { color: var(--activo); font-weight: 600; }
    .estatus-baja { color: var(--baja); font-weight: 600; }
    .estatus-pendiente { color: var(--pendiente); font-weight: 600; }

    .ver-link { color: var(--primary); font-weight: 600; text-decoration: none; font-size: 0.85rem; }
    .ver-link:hover { text-decoration: underline; }

    .empty-state { padding: 3rem; text-align: center; color: var(--text-muted); }

    /* Paginación en el navegador (ver script): Mostrar N, info y Anterior/Siguiente */
    .paginador {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: flex-end;
        gap: 0.75rem;
        margin-top: 1rem;
        font-size: 0.85rem;
        color: var(--text-muted);
    }
    .paginador select { width: auto; } /* el select general de esta vista es width: 100% */
    .paginador .btn:disabled { opacity: 0.4; cursor: not-allowed; }
</style>
@endpush

@section('content')
    <div class="top-row">
        <a class="btn btn-primary" href="{{ route('empleados.crear') }}">+ Agregar Empleado</a>
    </div>

    <!-- Filtros de Búsqueda -->
    <form class="filtros-card" method="GET" action="{{ route('empleados.index') }}" id="formFiltros">
        <div class="filtros-grid">
            <div class="campo">
                <label for="sucursal">Razón Social</label>
                <select name="sucursal" id="sucursal" onchange="document.getElementById('formFiltros').submit()">
                    <option value="">Todas</option>
                    @foreach($sucursales as $s)
                        <option value="{{ $s }}" @selected(($filtros['sucursal'] ?? '') == $s)>{{ $s }}</option>
                    @endforeach
                </select>
            </div>
            <div class="campo">
                <label for="sucursalFisica">Sucursal Física</label>
                <select name="sucursalFisica" id="sucursalFisica" onchange="document.getElementById('formFiltros').submit()">
                    <option value="">Todas</option>
                    @foreach($sucursalesFisicas as $s)
                        <option value="{{ $s }}" @selected(($filtros['sucursalFisica'] ?? '') == $s)>{{ $s }}</option>
                    @endforeach
                </select>
            </div>
            <div class="campo">
                <label for="estado">Estado</label>
                <select name="estado" id="estado" onchange="document.getElementById('formFiltros').submit()">
                    <option value="">Todos</option>
                    @foreach($estados as $s)
                        <option value="{{ $s }}" @selected(($filtros['estado'] ?? '') == $s)>{{ $s }}</option>
                    @endforeach
                </select>
            </div>
            <div class="campo">
                <label for="departamento">Departamento</label>
                <select name="departamento" id="departamento" onchange="document.getElementById('formFiltros').submit()">
                    <option value="">Todos</option>
                    @foreach($departamentos as $d)
                        <option value="{{ $d }}" @selected(($filtros['departamento'] ?? '') == $d)>{{ $d }}</option>
                    @endforeach
                </select>
            </div>
            <div class="campo">
                <label for="puesto">Puesto</label>
                <select name="puesto" id="puesto" onchange="document.getElementById('formFiltros').submit()">
                    <option value="">Todos</option>
                    @foreach($puestos as $p)
                        <option value="{{ $p }}" @selected(($filtros['puesto'] ?? '') == $p)>{{ $p }}</option>
                    @endforeach
                </select>
            </div>
            <div class="campo">
                <label for="buscarTabla">Buscar</label>
                {{-- Como el legacy: busca en vivo en todas las columnas de la tabla (sin name, no se envía con los filtros) --}}
                <input type="text" id="buscarTabla" onkeyup="buscarEnTabla()" placeholder="Buscar en todos los campos...">
            </div>
            <div class="campo filtros-acciones">
                <button type="submit" class="btn btn-primary">Filtrar</button>
                <a href="{{ route('empleados.index') }}" class="btn btn-secundario">Limpiar</a>
            </div>
        </div>
    </form>

    <!-- Tabla de Empleados -->
    <div class="table-card">
        <table id="tablaEmpleados">
            <thead>
                <tr>
                    <th onclick="ordenarTabla(0, true)">N° Empleado</th>
                    <th onclick="ordenarTabla(1, false)">Nombre</th>
                    <th onclick="ordenarTabla(2, false)">Puesto</th>
                    <th onclick="ordenarTabla(3, false)">Departamento</th>
                    <th onclick="ordenarTabla(4, false)">Razón Social</th>
                    <th onclick="ordenarTabla(5, false)">Sucursal Física</th>
                    <th onclick="ordenarTabla(6, false)">Fecha Ingreso</th>
                    <th onclick="ordenarTabla(7, false)">Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($empleados as $empleado)
                    @php
                        $esPendiente = in_array($empleado->N_Empleado, $pendientes);
                        $claseEstatus = $esPendiente
                            ? 'estatus-pendiente'
                            : ($empleado->Estatus === 'ACTIVO' ? 'estatus-activo' : ($empleado->Estatus === 'BAJA' ? 'estatus-baja' : ''));
                    @endphp
                    <tr>
                        <td>{{ $empleado->N_Empleado }}</td>
                        <td>{{ $empleado->Nombre_Completo }}</td>
                        <td>{{ $empleado->Nombre_Puesto }}</td>
                        <td>{{ $empleado->Departamento }}</td>
                        <td>{{ $empleado->Nomeclatura }}</td>
                        <td>{{ $empleado->Fisica }}</td>
                        <td>{{ $empleado->Fecha_Ingreso }}</td>
                        <td class="{{ $claseEstatus }}">{{ $empleado->Estatus }}</td>
                        <td><a class="ver-link" href="{{ route('empleados.show', $empleado->N_Empleado) }}">Ver Detalles</a></td>
                    </tr>
                @empty
                    <tr class="fila-vacia">
                        <td colspan="9" class="empty-state">No se encontraron empleados con esos filtros.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Paginación en el navegador: se cargan todos los empleados (como el legacy) pero se muestran por páginas,
         así buscar y ordenar siguen abarcando a todos --}}
    <div class="paginador">
        <label for="porPagina">Mostrar</label>
        <select id="porPagina" onchange="cambiarPagina(0, true)">
            <option value="10">10</option>
            <option value="25" selected>25</option>
            <option value="50">50</option>
            <option value="100">100</option>
        </select>
        <span id="paginaInfo"></span>
        <button type="button" class="btn btn-secundario" id="btnAnterior" onclick="cambiarPagina(-1)">‹ Anterior</button>
        <button type="button" class="btn btn-secundario" id="btnSiguiente" onclick="cambiarPagina(1)">Siguiente ›</button>
    </div>
@endsection

@push('scripts')
<script>
    // Paginación en el navegador. Las filas que coinciden con la búsqueda se reparten en páginas
    let paginaActual = 1;

    function aplicarPaginacion() {
        const texto = document.getElementById('buscarTabla').value.toLowerCase();
        const porPagina = parseInt(document.getElementById('porPagina').value, 10);
        const filas = Array.from(document.querySelectorAll('#tablaEmpleados tbody tr:not(.fila-vacia)'));

        // Port de filterTable() del legacy: coincide si el texto está en cualquier columna
        const coinciden = filas.filter(fila => fila.textContent.toLowerCase().includes(texto));
        const totalPaginas = Math.max(1, Math.ceil(coinciden.length / porPagina));
        paginaActual = Math.min(Math.max(paginaActual, 1), totalPaginas);

        // Se ocultan todas y solo se muestran las de la página actual
        const inicio = (paginaActual - 1) * porPagina;
        filas.forEach(fila => fila.style.display = 'none');
        coinciden.slice(inicio, inicio + porPagina).forEach(fila => fila.style.display = '');

        document.getElementById('paginaInfo').textContent =
            `Página ${paginaActual} de ${totalPaginas} · ${coinciden.length} empleados`;
        document.getElementById('btnAnterior').disabled = paginaActual === 1;
        document.getElementById('btnSiguiente').disabled = paginaActual === totalPaginas;
    }

    // delta: -1 anterior, 1 siguiente. reiniciar: volver a la página 1 (al buscar o cambiar "Mostrar")
    function cambiarPagina(delta, reiniciar = false) {
        paginaActual = reiniciar ? 1 : paginaActual + delta;
        aplicarPaginacion();
    }

    // Lo llama el input de búsqueda (onkeyup)
    function buscarEnTabla() {
        cambiarPagina(0, true);
    }

    function ordenarTabla(columna, esNumerico) {
        const tabla = document.getElementById('tablaEmpleados');
        const filas = Array.from(tabla.tBodies[0].rows);
        const asc = tabla.getAttribute('data-sort') !== 'asc';

        filas.sort((a, b) => {
            const ca = a.cells[columna].textContent.trim();
            const cb = b.cells[columna].textContent.trim();
            if (esNumerico) return asc ? parseFloat(ca) - parseFloat(cb) : parseFloat(cb) - parseFloat(ca);
            return asc ? ca.localeCompare(cb) : cb.localeCompare(ca);
        });

        filas.forEach(fila => tabla.tBodies[0].appendChild(fila));
        tabla.setAttribute('data-sort', asc ? 'asc' : 'desc');

        // Después de ordenar se vuelve a la página 1 con el nuevo orden
        cambiarPagina(0, true);
    }

    // Primera página al cargar
    aplicarPaginacion();
</script>
@endpush
