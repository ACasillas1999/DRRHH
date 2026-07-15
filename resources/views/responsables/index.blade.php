@extends('layouts.app')

@section('title', 'Responsables')
@section('page_title', 'Gestión de Responsables')

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
</style>
@endpush

@section('content')
    @if (session('status'))
        <div class="status-msg status-success">{{ session('status') }}</div>
    @endif
    @if (session('error'))
        <div class="status-msg status-error">{{ session('error') }}</div>
    @endif

    <!-- Formulario de Asignación -->
    <div class="section-card">
        <h2 style="font-size: 1.1rem; font-weight:600; margin-bottom:1.25rem;">Asignar Nuevo Responsable</h2>
        <form method="POST" action="{{ route('responsables.store') }}" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)) auto; gap:1rem; align-items:end;">
            @csrf
            <div class="campo">
                <label for="resp_dep">Departamento</label>
                <select id="resp_dep" name="departamento" required onchange="cargarSubdepartamentos()">
                    <option value="">Seleccione Departamento</option>
                    @foreach($departamentosList as $d)
                        <option value="{{ $d }}">{{ $d }}</option>
                    @endforeach
                </select>
            </div>
            <div class="campo">
                <label for="resp_sub">SubDepartamento</label>
                <select id="resp_sub" name="subdepartamento" required onchange="cargarEmpleados()" disabled>
                    <option value="">Seleccione Subdepartamento</option>
                </select>
            </div>
            <div class="campo">
                <label for="resp_res">Responsable</label>
                <select id="resp_res" name="responsable" required disabled>
                    <option value="">Seleccione Responsable</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary" style="margin-bottom:0.1rem; height: 2.3rem;">Guardar Responsable</button>
        </form>
    </div>

    <!-- Listado de Responsables -->
    <div class="table-card">
        <table>
            <thead>
                <tr>
                    <th>Departamento</th>
                    <th>SubDepartamento</th>
                    <th>N° Empleado</th>
                    <th>Responsable</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($responsables as $r)
                    <tr>
                        <td>{{ $r->Departamento }}</td>
                        <td>{{ $r->SubDepartamento }}</td>
                        <td>{{ $r->N_Empleado }}</td>
                        <td><strong>{{ $r->Nombre_Completo }}</strong></td>
                        <td>
                            <form method="POST" action="{{ route('responsables.destroy', $r->ID) }}" onsubmit="return confirm('¿Estás seguro de que deseas eliminar esta asignación de responsable?')" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-eliminar-link">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="empty-state">No se encontraron responsables registrados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection

@push('scripts')
<script>
    function cargarSubdepartamentos() {
        const dep = document.getElementById('resp_dep').value;
        const subSelect = document.getElementById('resp_sub');
        const resSelect = document.getElementById('resp_res');

        subSelect.innerHTML = '<option value="">Seleccione Subdepartamento</option>';
        resSelect.innerHTML = '<option value="">Seleccione Responsable</option>';
        subSelect.disabled = true;
        resSelect.disabled = true;

        if (!dep) return;

        fetch(`/api/puestos/subdepartamentos?departamento=${encodeURIComponent(dep)}`)
            .then(res => res.json())
            .then(data => {
                subSelect.innerHTML += '<option value="JEFE DE DEPARTAMENTO">JEFE DE DEPARTAMENTO</option>';
                data.forEach(sub => {
                    subSelect.innerHTML += `<option value="${sub}">${sub}</option>`;
                });
                subSelect.disabled = false;
            });
    }

    function cargarEmpleados() {
        const dep = document.getElementById('resp_dep').value;
        const sub = document.getElementById('resp_sub').value;
        const resSelect = document.getElementById('resp_res');

        resSelect.innerHTML = '<option value="">Seleccione Responsable</option>';
        resSelect.disabled = true;

        if (!dep || !sub) return;

        fetch(`/api/puestos/empleados?departamento=${encodeURIComponent(dep)}&subdepartamento=${encodeURIComponent(sub)}`)
            .then(res => res.json())
            .then(data => {
                data.forEach(emp => {
                    resSelect.innerHTML += `<option value="${emp.N_Empleado}">${emp.Nombre_Completo}</option>`;
                });
                resSelect.disabled = false;
            });
    }
</script>
@endpush
