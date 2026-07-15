@extends('layouts.app')

@section('title', 'Organigrama')
@section('page_title', 'Organigrama Corporativo')

@push('styles')
<style>
    .section-card {
        background-color: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 20px;
        padding: 1.75rem;
        box-shadow: var(--shadow-sm);
        margin-bottom: 1.5rem;
    }

    .status-msg { border-radius: 12px; padding: 1rem; margin-bottom: 1.5rem; font-weight: 600; }
    .status-success { border: 1px solid var(--activo); color: var(--activo); background-color: rgba(22, 163, 74, 0.05); }
    .status-error { border: 1px solid var(--baja); color: var(--baja); background-color: rgba(220, 38, 38, 0.05); }
    .empty-state { padding: 3rem; text-align: center; color: var(--text-muted); }

    .hidden { display: none !important; }

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
    .tree-item:only-child::after, .tree-item:only-child::before { display: none; }
    .tree-item:only-child { padding-top: 0; }

    /* Remove connectors from edges */
    .tree-item:first-child::before, .tree-item:last-child::after { border: 0 none; }
    .tree-item:first-child::after { border-radius: 12px 0 0 0; }
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
    .node-root:hover { border-color: transparent; }

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
</style>
@endpush

@section('content')
    @if (session('status'))
        <div class="status-msg status-success">{{ session('status') }}</div>
    @endif
    @if (session('error'))
        <div class="status-msg status-error">{{ session('error') }}</div>
    @endif

    <div class="section-card" style="padding-bottom: 0.5rem;">
        <h2 style="font-size: 1.15rem; font-weight:600; margin-bottom:0.5rem;">Organigrama Corporativo de Puestos</h2>
        <p style="color:var(--text-muted); font-size:0.875rem; margin-bottom:2rem;">Estructura jerárquica organizacional del Grupo Ascencio.</p>

        <div class="tree-container">
            <div class="tree-root">
                <!-- Root Node -->
                <div class="tree-node node-root">
                    <span>GRUPO ASCENCIO</span>
                </div>

                <!-- Department Branch -->
                <ul class="tree-branch">
                    @forelse($hierarchy as $dep => $subdeps)
                        <li class="tree-item">
                            <div class="tree-node node-dept">
                                <span>{{ $dep }}</span>
                            </div>
                            <ul class="tree-branch">
                                @foreach($subdeps as $sub => $puestosList)
                                    <li class="tree-item">
                                        <div class="tree-node node-subdept">
                                            <span class="node-title">{{ $sub }}</span>
                                            <div class="node-puestos-list">
                                                @foreach($puestosList as $puesto)
                                                    <div class="node-puesto-item" title="{{ $puesto->Descripcion ?: 'Sin descripción' }}">
                                                        {{ $puesto->Nombre_Puesto }}
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </li>
                    @empty
                        <li style="list-style: none;">
                            <p class="empty-state">No hay datos de estructura organizativa disponibles.</p>
                        </li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
@endsection
