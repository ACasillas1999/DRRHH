@extends('layouts.app')

@section('title', 'Puestos de Trabajo')
@section('page_title', 'Puestos y Estructura Organizacional')

@push('styles')
<style>
    /* Tabs Navigation */
    .tabs-nav {
        display: flex;
        border-bottom: 2px solid var(--border-color);
        margin-bottom: 1.5rem;
        gap: 1rem;
    }

    .tab-link {
        padding: 0.75rem 1.25rem;
        font-weight: 600;
        font-size: 0.95rem;
        color: var(--text-muted);
        text-decoration: none;
        border-bottom: 3px solid transparent;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .tab-link:hover {
        color: var(--primary);
    }

    .tab-link.active {
        color: var(--primary);
        border-bottom-color: var(--primary);
    }

    .tab-pane {
        display: none;
    }

    .tab-pane.active {
        display: block;
    }

    /* Common Card & Form Styles */
    .section-card {
        background-color: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 20px;
        padding: 1.75rem;
        box-shadow: var(--shadow-sm);
        margin-bottom: 1.5rem;
    }

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
        transition: all 0.2s ease;
    }
    .btn-primary { background-color: var(--primary); color: white; }
    .btn-primary:hover { opacity: 0.9; }
    .btn-secundario { background-color: var(--bg-page); border: 1px solid var(--border-color); color: var(--text-main); }
    .btn-secundario:hover { background-color: var(--bg-card-hover); }

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
        grid-template-columns: 1fr auto;
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

    input[type="text"], select {
        width: 100%;
        padding: 0.5rem 0.65rem;
        border-radius: 8px;
        border: 1px solid var(--border-color);
        background-color: var(--bg-page);
        color: var(--text-main);
        font-family: inherit;
        font-size: 0.85rem;
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
        padding: 1rem 1.25rem;
        border-bottom: 1px solid var(--border-color);
        white-space: nowrap;
    }

    tbody td {
        padding: 0.85rem 1.25rem;
        border-bottom: 1px solid var(--border-color);
        font-size: 0.875rem;
        white-space: nowrap;
    }

    tbody tr:last-child td { border-bottom: none; }
    tbody tr:hover { background-color: var(--primary-light); }

    .acciones-cell {
        display: flex;
        gap: 0.75rem;
        align-items: center;
    }

    .ver-link { color: var(--primary); font-weight: 600; text-decoration: none; font-size: 0.85rem; cursor: pointer; }
    .ver-link:hover { text-decoration: underline; }

    .btn-eliminar-link {
        background: none;
        border: none;
        color: var(--baja);
        font-weight: 600;
        cursor: pointer;
        font-size: 0.85rem;
        padding: 0;
    }
    .btn-eliminar-link:hover { text-decoration: underline; }

    .status-msg { border-radius: 12px; padding: 1rem; margin-bottom: 1.5rem; font-weight: 600; }
    .status-success { border: 1px solid var(--activo); color: var(--activo); background-color: rgba(22, 163, 74, 0.05); }
    .status-error { border: 1px solid var(--baja); color: var(--baja); background-color: rgba(220, 38, 38, 0.05); }
    .empty-state { padding: 3rem; text-align: center; color: var(--text-muted); }

    /* Organigrama Hierarchy Styles */
    .org-tree {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .org-node {
        display: flex;
        align-items: center;
        padding: 0.75rem 1.25rem;
        border-radius: 12px;
        font-weight: 600;
        cursor: pointer;
        user-select: none;
        gap: 0.75rem;
        transition: all 0.2s ease;
    }

    .org-dep-node {
        background-color: var(--primary-light);
        color: var(--primary);
        border: 1px solid rgba(29, 78, 216, 0.15);
        font-size: 1.05rem;
    }
    .org-dep-node:hover {
        background-color: rgba(29, 78, 216, 0.08);
    }
    .org-dep-node.active {
        border-bottom-left-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }

    .org-sub-node {
        background-color: var(--bg-card);
        color: var(--text-main);
        border: 1px solid var(--border-color);
        font-size: 0.925rem;
    }
    .org-sub-node:hover {
        background-color: var(--bg-card-hover);
    }
    .org-sub-node.active {
        border-bottom-left-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }

    .org-puesto-node {
        background-color: var(--bg-page);
        color: var(--text-muted);
        border: 1px dashed var(--border-color);
        font-size: 0.85rem;
        cursor: default;
        padding: 0.5rem 1rem;
        margin-left: 2rem;
    }

    .org-dep-children {
        margin-left: 1.5rem;
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        padding-left: 0.75rem;
        border-left: 2px solid var(--border-color);
    }

    .org-sub-children {
        margin-left: 1.5rem;
        display: flex;
        flex-direction: column;
        gap: 0.35rem;
        padding-left: 0.75rem;
        border-left: 2px dashed var(--border-color);
    }

    .chevron-icon {
        width: 18px;
        height: 18px;
        transition: transform 0.2s ease;
        fill: none;
        stroke: currentColor;
        stroke-width: 2.5;
        stroke-linecap: round;
        stroke-linejoin: round;
    }
    .org-node.active .chevron-icon {
        transform: rotate(90deg);
    }

    .hidden {
        display: none !important;
    }

    /* Modal dialog overrides */
    .modal-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background-color: rgba(0, 0, 0, 0.5);
        align-items: center;
        justify-content: center;
        z-index: 200;
        padding: 1rem;
    }

    .modal-box {
        background-color: var(--bg-card);
        border-radius: 16px;
        padding: 1.75rem;
        max-width: 480px;
        width: 100%;
        border: 1px solid var(--border-color);
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .modal-box h3 {
        font-size: 1.15rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }

    .modal-box .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 0.75rem;
        margin-top: 1rem;
    }

    /* Tree Chart Corporate Structure */
    .tree-container {
        width: 100%;
        overflow-x: auto;
        padding: 2.5rem 1rem;
        background-color: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 20px;
        box-shadow: var(--shadow-sm);
        display: block;
        text-align: center;
        min-height: 500px;
    }

    .tree-root {
        display: inline-block;
        text-align: center;
        margin: 0 auto;
        padding: 0 3rem;
    }

    /* CSS tree connector lines */
    .tree-branch {
        display: flex;
        justify-content: center;
        position: relative;
        padding-top: 24px;
        margin: 0;
        list-style: none;
    }

    /* Vertical line from parent */
    .tree-branch::before {
        content: '';
        position: absolute;
        top: 0;
        left: 50%;
        width: 2px;
        height: 24px;
        background-color: var(--primary);
    }

    .tree-item {
        position: relative;
        padding: 24px 12px 0 12px;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    /* Horizontal connector lines */
    .tree-item::before, .tree-item::after {
        content: '';
        position: absolute;
        top: 0;
        right: 50%;
        width: 50%;
        height: 24px;
        border-top: 2px solid var(--primary);
    }
    .tree-item::after {
        right: auto;
        left: 50%;
        border-left: 2px solid var(--primary);
    }

    /* Remove connectors for single child */
    .tree-item:only-child::after, .tree-item:only-child::before {
        display: none;
    }
    .tree-item:only-child {
        padding-top: 0;
    }

    /* Remove connectors from edges */
    .tree-item:first-child::before, .tree-item:last-child::after {
        border: 0 none;
    }
    .tree-item:first-child::after {
        border-radius: 12px 0 0 0;
    }
    .tree-item:last-child::before {
        border-right: 2px solid var(--primary);
        border-radius: 0 12px 0 0;
    }

    /* Node Cards Styling */
    .tree-node {
        background-color: var(--bg-card);
        border: 2px solid var(--border-color);
        border-radius: 14px;
        padding: 0.85rem 1.25rem;
        box-shadow: var(--shadow-sm);
        text-align: center;
        min-width: 190px;
        max-width: 250px;
        transition: all 0.3s ease;
        z-index: 10;
        position: relative;
    }
    .tree-node:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-md);
        border-color: var(--primary);
    }

    .node-root {
        background: linear-gradient(135deg, var(--primary), #1e40af);
        color: white;
        border: none;
        font-weight: 800;
        font-size: 1.05rem;
        padding: 1rem 2rem;
        border-radius: 16px;
    }
    .node-root:hover {
        border-color: transparent;
    }

    .node-dept {
        border-color: var(--primary);
        background-color: var(--primary-light);
        color: var(--primary);
        font-weight: 700;
    }

    .node-subdept {
        border-color: var(--border-color);
        background-color: var(--bg-card);
        color: var(--text-main);
        text-align: left;
    }

    .node-title {
        font-weight: 700;
        font-size: 0.85rem;
        margin-bottom: 0.5rem;
        text-transform: uppercase;
        border-bottom: 1px solid var(--border-color);
        padding-bottom: 0.35rem;
        display: block;
        text-align: center;
    }

    .node-puestos-list {
        display: flex;
        flex-direction: column;
        gap: 0.35rem;
        margin-top: 0.5rem;
    }

    .node-puesto-item {
        background-color: var(--bg-page);
        border: 1px solid var(--border-color);
        border-radius: 6px;
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
        color: var(--text-muted);
        font-weight: 500;
        transition: all 0.2s ease;
    }
    .node-puesto-item:hover {
        background-color: var(--primary-light);
        color: var(--primary);
        border-color: var(--primary);
    }

        /* Vista de 3 columnas: Departamentos | Subdepartamentos | Puestos */
    .columnas-puestos {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 1rem;
        align-items: start;
    }

    .columna {
        background-color: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        box-shadow: var(--shadow-sm);
        overflow: hidden;
    }

    .columna-titulo {
        padding: 0.85rem 1.1rem;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--text-muted);
        border-bottom: 1px solid var(--border-color);
    }

    .columna-lista {
        display: flex;
        flex-direction: column;
        gap: 0.35rem;
        padding: 0.75rem;
        max-height: 65vh;
        overflow-y: auto;
    }

    .item-col {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 0.5rem;
        width: 100%;
        padding: 0.65rem 0.85rem;
        border: 1px solid transparent;
        border-radius: 10px;
        background: none;
        font-family: inherit;
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--text-main);
        text-align: left;
        cursor: pointer;
        transition: background-color 0.15s ease;
    }
    .item-col:hover { background-color: var(--bg-card-hover); }
    .item-col.active {
        background-color: var(--primary-light);
        color: var(--primary);
        border-color: rgba(29, 78, 216, 0.15);
    }

    .badge-col {
        flex-shrink: 0;
        font-size: 0.7rem;
        font-weight: 700;
        padding: 0.15rem 0.45rem;
        border-radius: 6px;
        background-color: var(--border-color);
        color: var(--text-muted);
    }
    .item-col.active .badge-col { background-color: var(--primary); color: white; }

    .item-puesto {
        display: flex;
        flex-direction: column;
        gap: 0.35rem;
        padding: 0.75rem 0.85rem;
        border: 1px solid var(--border-color);
        border-radius: 10px;
    }
    .item-puesto-nombre { font-weight: 600; font-size: 0.875rem; }
    .item-puesto-desc { font-size: 0.8rem; color: var(--text-muted); }

    .col-vacia { padding: 1.5rem 0.5rem; text-align: center; color: var(--text-muted); font-size: 0.85rem; }

    /* Tablet y celular: columnas una debajo de otra */
    @media (max-width: 900px) {
        .columnas-puestos { grid-template-columns: 1fr; }
        .columna-lista { max-height: none; }
    }

</style>
@endpush

@section('content')
    @if (session('status'))
        <div class="status-msg status-success">{{ session('status') }}</div>
    @endif
    @if (session('error'))
        <div class="status-msg status-error">{{ session('error') }}</div>
    @endif

    <div class="top-row">
        <a class="btn btn-primary" href="{{ route('puestos.create') }}">+ Registrar Puesto</a>
    </div>

    <form class="filtros-card" method="GET" action="{{ route('puestos.index') }}">
        <div class="filtros-grid">
            <div class="campo">
                <label for="busqueda">Buscar Puesto</label>
                <input type="text" name="busqueda" id="busqueda" value="{{ $busqueda ?? '' }}" placeholder="Nombre del puesto, departamento o subdepartamento...">
            </div>
            <div class="campo filtros-acciones">
                <button type="submit" class="btn btn-primary">Buscar</button>
                <a href="{{ route('puestos.index') }}" class="btn btn-secundario">Limpiar</a>
            </div>
        </div>
    </form>

       @if(empty($puestosGrouped))
        <div class="section-card">
            <p class="empty-state">No se encontraron puestos de trabajo registrados.</p>
        </div>
    @else
        <div class="columnas-puestos">
            <!-- Columna 1: Departamentos -->
            <div class="columna col-dep">
                <div class="columna-titulo">Departamentos</div>
                <div class="columna-lista">
                    @foreach($puestosGrouped as $departamento => $subdepartamentosList)
                        <button type="button" class="item-col" data-dep="{{ $loop->index }}" onclick="seleccionarDepartamento('{{ $loop->index }}')">
                            <span>{{ $departamento }}</span>
                            <span class="badge-col">{{ array_sum(array_map('count', $subdepartamentosList)) }}</span>
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Columna 2: Subdepartamentos del departamento seleccionado -->
            <div class="columna col-sub">
                <div class="columna-titulo">Subdepartamentos</div>
                <div class="columna-lista">
                    <p class="col-vacia">Selecciona un departamento</p>
                    @foreach($puestosGrouped as $departamento => $subdepartamentosList)
                        @php $d = $loop->index; @endphp
                        @foreach($subdepartamentosList as $subdepartamento => $puestosList)
                            <button type="button" class="item-col hidden" data-dep="{{ $d }}" data-sub="{{ $d }}-{{ $loop->index }}" onclick="seleccionarSubdepartamento('{{ $d }}-{{ $loop->index }}')">
                                <span>{{ $subdepartamento }}</span>
                                <span class="badge-col">{{ count($puestosList) }}</span>
                            </button>
                        @endforeach
                    @endforeach
                </div>
            </div>

            <!-- Columna 3: Puestos del subdepartamento seleccionado -->
            <div class="columna col-puestos">
                <div class="columna-titulo">Puestos</div>
                <div class="columna-lista">
                    <p class="col-vacia">Selecciona un subdepartamento</p>
                    @foreach($puestosGrouped as $departamento => $subdepartamentosList)
                        @php $d = $loop->index; @endphp
                        @foreach($subdepartamentosList as $subdepartamento => $puestosList)
                            @php $s = $d . '-' . $loop->index; @endphp
                            @foreach($puestosList as $p)
                                <div class="item-puesto hidden" data-sub="{{ $s }}">
                                    <span class="item-puesto-nombre">{{ $p->Nombre_Puesto }}</span>
                                    <span class="item-puesto-desc">{{ $p->Descripcion ?: 'Sin descripción' }}</span>
                                    <div class="acciones-cell">
                                        <a class="ver-link" href="{{ route('puestos.edit', $p->ID) }}">Editar</a>
                                        <form method="POST" action="{{ route('puestos.destroy', $p->ID) }}" onsubmit="return confirm('¿Estás seguro de que deseas eliminar este puesto de trabajo?')" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-eliminar-link">Eliminar</button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        @endforeach
                    @endforeach
                </div>
            </div>
        </div>
    @endif

@endsection

@push('scripts')
<script>
        // Columna 2: muestra solo los subdepartamentos del departamento elegido
    function seleccionarDepartamento(dep) {
        document.querySelectorAll('.col-dep .item-col').forEach(el => el.classList.toggle('active', el.dataset.dep === dep));

        let primero = null;
        document.querySelectorAll('.col-sub .item-col').forEach(el => {
            const visible = el.dataset.dep === dep;
            el.classList.toggle('hidden', !visible);
            if (visible && !primero) primero = el;
        });
        document.querySelector('.col-sub .col-vacia').classList.add('hidden');

        // Selecciona el primer subdepartamento para que la columna 3 no quede vacía
        if (primero) seleccionarSubdepartamento(primero.dataset.sub);
    }

    // Columna 3: muestra solo los puestos del subdepartamento elegido
    function seleccionarSubdepartamento(sub) {
        document.querySelectorAll('.col-sub .item-col').forEach(el => el.classList.toggle('active', el.dataset.sub === sub));
        document.querySelectorAll('.col-puestos .item-puesto').forEach(el => el.classList.toggle('hidden', el.dataset.sub !== sub));
        document.querySelector('.col-puestos .col-vacia').classList.add('hidden');
    }

    // Al cargar la página abre el primer departamento
    document.addEventListener('DOMContentLoaded', () => {
        const primero = document.querySelector('.col-dep .item-col');
        if (primero) seleccionarDepartamento(primero.dataset.dep);
    });

</script>
@endpush