@extends('layouts.app')

@section('title', 'Historial de Cambios')

@section('content')
<div class="historial-container">
    <!-- Encabezado de Página -->
    <div class="incidencias-header-card">
        <div class="header-icon-box">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <polyline points="12 6 12 12 16 14"></polyline>
            </svg>
        </div>
        <div class="header-text-box">
            <h1>Historial de Cambios</h1>
            <p>Bitácora de acciones realizadas por los usuarios del sistema sobre los registros de empleados.</p>
        </div>
    </div>

    <!-- Filtros -->
    <div class="control-filters-card">
        <form method="GET" action="{{ route('historial.index') }}" class="filters-form">
            <div class="filter-group">
                <label for="fecha">Fecha:</label>
                <input type="date" id="fecha" name="fecha" value="{{ $fecha }}" class="form-control">
            </div>
            <div class="filter-group">
                <label for="usuario">Usuario:</label>
                <input type="text" id="usuario" name="usuario" value="{{ $usuario }}" placeholder="Filtrar por usuario..." class="form-control">
            </div>
            <div class="filter-group">
                <label for="empleado">Empleado:</label>
                <input type="text" id="empleado" name="empleado" value="{{ $empleado }}" placeholder="Filtrar por empleado..." class="form-control">
            </div>
            <div class="filter-group">
                <label for="cambio">Cambio:</label>
                <input type="text" id="cambio" name="cambio" value="{{ $cambio }}" placeholder="Filtrar por cambio..." class="form-control">
            </div>
            <button type="submit" class="btn-filter-submit">Filtrar</button>
            @if($fecha || $usuario || $empleado || $cambio)
                <a href="{{ route('historial.index') }}" class="btn-filter-submit" style="background-color: var(--text-muted);">Limpiar</a>
            @endif
        </form>
    </div>

    <!-- Tabla -->
    <div class="table-container-card">
        <div class="table-responsive">
            @if($historial->isEmpty())
                <div class="empty-state-card">
                    <div class="empty-state-icon">🗂️</div>
                    <h4>No hay cambios registrados</h4>
                </div>
            @else
                <table class="premium-table">
                    <thead>
                        <tr>
                            <th>Fecha y Hora</th>
                            <th>Usuario</th>
                            <th>Empleado</th>
                            <th>Cambio</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($historial as $h)
                            <tr>
                                <td>{{ \Illuminate\Support\Carbon::parse($h->Fecha_Hora)->format('d/m/Y H:i') }}</td>
                                <td>{{ $h->usuario->Nombre ?? 'Sistema' }}</td>
                                <td>{{ $h->personal->Nombre_Completo ?? '—' }}</td>
                                <td>{{ $h->Cambio }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div style="padding: 1rem 1.25rem;">
                    {{ $historial->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<style>
    .historial-container {
        display: flex;
        flex-direction: column;
        gap: 1rem;
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
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        height: 38px;
        text-decoration: none;
        transition: background-color var(--transition-speed) ease;
    }

    .btn-filter-submit:hover {
        background-color: var(--primary-hover);
    }

    /* Cabecera del Panel (compartido con otras páginas portadas) */
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

    .control-filters-card {
        background-color: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 1.25rem;
        box-shadow: var(--shadow-sm);
    }

    .table-container-card {
        background-color: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        box-shadow: var(--shadow-sm);
        overflow: hidden;
    }

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
</style>
@endsection
