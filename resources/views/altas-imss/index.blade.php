@extends('layouts.app')

@section('title', 'Altas y Bajas IMSS')
@section('page_title', 'Altas y Bajas IMSS')

@push('styles')
<style>
    .status-msg { border-radius: 12px; padding: 1rem; margin-bottom: 1.5rem; font-weight: 600; }
    .status-success { border: 1px solid var(--activo); color: var(--activo); background-color: rgba(22, 163, 74, 0.05); }
    .status-error { border: 1px solid var(--baja); color: var(--baja); background-color: rgba(220, 38, 38, 0.05); }

    .filtros-card {
        background-color: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 1.25rem;
        box-shadow: var(--shadow-sm);
        margin-bottom: 1.5rem;
    }
    .filtros-grid { display: grid; grid-template-columns: 1fr auto; gap: 0.75rem; align-items: end; }
    .campo label { display: block; font-size: 0.75rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.03em; margin-bottom: 0.35rem; }
    input[type="text"], input[type="date"], select {
        width: 100%; padding: 0.5rem 0.65rem; border-radius: 8px; border: 1px solid var(--border-color);
        background-color: var(--bg-page); color: var(--text-main); font-family: inherit; font-size: 0.85rem;
    }

    .btn { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.6rem 1.1rem; border-radius: 10px; font-weight: 600; font-size: 0.875rem; text-decoration: none; border: 1px solid transparent; cursor: pointer; }
    .btn-primary { background-color: var(--primary); color: white; }
    .btn-primary:hover { opacity: 0.9; }
    .btn-secundario { background-color: var(--bg-page); border: 1px solid var(--border-color); color: var(--text-main); }
    .btn-sm { padding: 0.4rem 0.7rem; font-size: 0.75rem; }

    .table-card { background-color: var(--bg-card); border: 1px solid var(--border-color); border-radius: 20px; box-shadow: var(--shadow-sm); overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; }
    thead th { text-align: left; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-muted); padding: 0.85rem 1rem; border-bottom: 1px solid var(--border-color); white-space: nowrap; }
    tbody td { padding: 0.65rem 1rem; border-bottom: 1px solid var(--border-color); font-size: 0.82rem; white-space: nowrap; }
    tbody tr:last-child td { border-bottom: none; }
    tbody tr:hover { background-color: var(--primary-light); }

    .ver-link { color: var(--primary); font-weight: 600; text-decoration: none; font-size: 0.85rem; }
    .ver-link:hover { text-decoration: underline; }
    .empty-state { padding: 3rem; text-align: center; color: var(--text-muted); }

    .accion-inline { display: flex; gap: 0.4rem; align-items: center; }
    .accion-inline input { width: 150px; }

    .estatus-activo { color: var(--activo); font-weight: 600; }
    .estatus-baja { color: var(--baja); font-weight: 600; }
</style>
@endpush

@section('content')
    @if (session('status'))
        <div class="status-msg status-success">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="status-msg status-error">{{ $errors->first() }}</div>
    @endif

    <form class="filtros-card" method="GET" action="{{ route('altas-imss.index') }}">
        <div class="filtros-grid">
            <div class="campo">
                <label for="busqueda">Buscar</label>
                <input type="text" name="busqueda" id="busqueda" value="{{ $busqueda ?? '' }}" placeholder="Número o nombre de empleado...">
            </div>
            <div class="campo" style="display:flex; gap:0.5rem;">
                <button type="submit" class="btn btn-primary">Buscar</button>
                <a href="{{ route('altas-imss.index') }}" class="btn btn-secundario">Limpiar</a>
            </div>
        </div>
    </form>

    <div class="table-card">
        <table>
            <thead>
                <tr>
                    <th>N° Empleado</th>
                    <th>Estatus</th>
                    <th>Nombre Completo</th>
                    <th>Fecha Ingreso</th>
                    <th>Fecha IMSS</th>
                    <th>Documento</th>
                    <th>Puesto</th>
                    <th>N° IMSS</th>
                    <th>Razón Social</th>
                    <th>Ubicación Física</th>
                    <th>Función</th>
                </tr>
            </thead>
            <tbody>
                @forelse($registros as $r)
                    @php
                        $esActivo = $r->Estatus === 'ACTIVO';
                        $fechaMostrar = $esActivo ? $r->Fecha_Alta_IMSS : $r->Fecha_Baja_IMSS;
                    @endphp
                    <tr>
                        <td>{{ $r->N_Empleado }}</td>
                        <td class="{{ $esActivo ? 'estatus-activo' : 'estatus-baja' }}">{{ $r->Estatus }}</td>
                        <td>{{ $r->Nombre_Completo }}</td>
                        <td>{{ $r->Fecha_Ingreso ?: '—' }}</td>
                        <td>
                            @if($fechaMostrar)
                                {{ $fechaMostrar }}
                            @else
                                <form class="accion-inline" method="POST" action="{{ $esActivo ? route('altas-imss.fechaAlta', $r->N_Empleado) : route('altas-imss.fechaBaja', $r->N_Empleado) }}" onsubmit="return confirm('¿Confirmas actualizar esta fecha?')">
                                    @csrf
                                    <input type="date" name="{{ $esActivo ? 'fecha_alta_imss' : 'fecha_baja_imss' }}" required>
                                    <button type="submit" class="btn btn-primary btn-sm">Guardar</button>
                                </form>
                            @endif
                        </td>
                        <td>
                            @if($r->ruta_documento)
                                <a class="ver-link" href="{{ $r->ruta_documento }}" target="_blank">Ver Documento</a>
                            @else
                                <form class="accion-inline" method="POST" action="{{ route('altas-imss.documento', $r->N_Empleado) }}" enctype="multipart/form-data" onsubmit="return confirm('¿Confirmas subir este documento?')">
                                    @csrf
                                    <input type="hidden" name="tipo" value="{{ $esActivo ? 'activo' : 'baja' }}">
                                    <input type="file" name="documento" accept=".pdf,.doc,.docx" required style="width:140px;">
                                    <button type="submit" class="btn btn-primary btn-sm">Subir</button>
                                </form>
                            @endif
                        </td>
                        <td>{{ $r->Nombre_Puesto }}</td>
                        <td>{{ $r->IMSS }}</td>
                        <td>{{ $r->Nomeclatura }}</td>
                        <td>{{ $r->Fisica }}</td>
                        <td><a class="ver-link" href="{{ route('empleados.show', $r->N_Empleado) }}">Ver Detalles</a></td>
                    </tr>
                @empty
                    <tr><td colspan="11" class="empty-state">No hay registros pendientes de alta/baja IMSS.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination-wrap">
        {{ $registros->links() }}
    </div>
@endsection
