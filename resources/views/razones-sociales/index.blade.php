@extends('layouts.app')

@section('title', 'Razones Sociales')
@section('page_title', 'Razones Sociales')

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
        transition: all 0.2s ease;
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

    input[type="text"] {
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

    .ver-link { color: var(--primary); font-weight: 600; text-decoration: none; font-size: 0.85rem; }
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

    .empty-state { padding: 3rem; text-align: center; color: var(--text-muted); }
    
    .status-msg { border-radius: 12px; padding: 1rem; margin-bottom: 1.5rem; font-weight: 600; }
    .status-success { border: 1px solid var(--activo); color: var(--activo); background-color: rgba(22, 163, 74, 0.05); }
    .status-error { border: 1px solid var(--baja); color: var(--baja); background-color: rgba(220, 38, 38, 0.05); }

    .declaraciones-cell {
        max-width: 250px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        color: var(--text-muted);
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
        <a class="btn btn-primary" href="{{ route('razones-sociales.create') }}">+ Registrar Razón Social</a>
    </div>

    <!-- Filtros de Búsqueda -->
    <form class="filtros-card" method="GET" action="{{ route('razones-sociales.index') }}">
        <div class="filtros-grid">
            <div class="campo">
                <label for="busqueda">Buscar</label>
                <input type="text" name="busqueda" id="busqueda" value="{{ $busqueda ?? '' }}" placeholder="Buscar por Nomenclatura, Nombre IMSS, RFC o Representante...">
            </div>
            <div class="campo filtros-acciones">
                <button type="submit" class="btn btn-primary">Buscar</button>
                <a href="{{ route('razones-sociales.index') }}" class="btn btn-secundario">Limpiar</a>
            </div>
        </div>
    </form>

    <!-- Tabla de Razones Sociales -->
    <div class="table-card">
        <table>
            <thead>
                <tr>
                    <th>Nomenclatura</th>
                    <th>Razón Social IMSS</th>
                    <th>RFC Empresa</th>
                    <th>Registro Patronal</th>
                    <th>Representante Legal</th>
                    <th>Domicilio (Decl. F)</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($razonesSociales as $rs)
                    <tr>
                        <td><strong>{{ $rs->Nomenclatura }}</strong></td>
                        <td>{{ $rs->Razon_Social_IMSS }}</td>
                        <td><code>{{ $rs->RFC_Empresa }}</code></td>
                        <td>{{ $rs->Registro_Patronal }}</td>
                        <td>{{ $rs->Representante_Legal }}</td>
                        <td class="declaraciones-cell" title="{{ $rs->Declaraciones_F }}">{{ $rs->Declaraciones_F }}</td>
                        <td>
                            <div class="acciones-cell">
                                <a class="ver-link" href="{{ route('razones-sociales.edit', $rs->ID) }}">Editar</a>
                                <form method="POST" action="{{ route('razones-sociales.destroy', $rs->ID) }}" onsubmit="return confirm('¿Estás seguro de que deseas eliminar esta Razón Social? Esta acción no se puede deshacer.')" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-eliminar-link">Eliminar</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="empty-state">No se encontraron razones sociales registradas.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Paginación -->
    <div class="pagination-wrap">
        {{ $razonesSociales->appends(['busqueda' => $busqueda])->links() }}
    </div>
@endsection
