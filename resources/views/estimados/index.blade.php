@extends('layouts.app')

@section('title', 'Empleados Estimados')
@section('page_title', 'Empleados Estimados por Sucursal')

@push('styles')
<style>
    .status-msg { border-radius: 12px; padding: 1rem; margin-bottom: 1.5rem; font-weight: 600; }
    .status-success { border: 1px solid var(--activo); color: var(--activo); background-color: rgba(22, 163, 74, 0.05); }
    .back-link { display: inline-flex; align-items: center; gap: 0.4rem; color: var(--text-muted); text-decoration: none; font-size: 0.875rem; font-weight: 500; margin-bottom: 1rem; }
    .back-link:hover { color: var(--primary); }
    .top-row { display: flex; justify-content: flex-end; margin-bottom: 1.5rem; }
    .btn { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.6rem 1.1rem; border-radius: 10px; font-weight: 600; font-size: 0.875rem; text-decoration: none; border: 1px solid transparent; cursor: pointer; }
    .btn-primary { background-color: var(--primary); color: white; }
    .btn-secundario { background-color: var(--bg-page); border: 1px solid var(--border-color); color: var(--text-main); }

    .section-card { background-color: var(--bg-card); border: 1px solid var(--border-color); border-radius: 20px; padding: 1.5rem; box-shadow: var(--shadow-sm); margin-bottom: 1.25rem; overflow-x: auto; /* la tabla se desliza dentro de la tarjeta en celular */ }
    .section-card h2 { font-size: 1rem; font-weight: 600; margin-bottom: 1rem; }

    table { width: 100%; border-collapse: collapse; }
    thead th { text-align: left; font-size: 0.7rem; text-transform: uppercase; color: var(--text-muted); padding: 0.65rem 0.85rem; border-bottom: 1px solid var(--border-color); }
    tbody td { padding: 0.55rem 0.85rem; border-bottom: 1px solid var(--border-color); font-size: 0.85rem; }
    tbody tr:last-child td { border-bottom: none; }
    .ver-link { color: var(--primary); font-weight: 600; text-decoration: none; font-size: 0.85rem; cursor: pointer; background: none; border: none; }
    .ver-link:hover { text-decoration: underline; }
    .btn-eliminar-link { background: none; border: none; color: var(--baja); font-weight: 600; cursor: pointer; font-size: 0.85rem; }
    .empty-state { padding: 2rem; text-align: center; color: var(--text-muted); }

    .modal-overlay { display: none; position: fixed; inset: 0; background-color: rgba(0,0,0,0.5); align-items: center; justify-content: center; z-index: 200; padding: 1rem; }
    .modal-box { background-color: var(--bg-card); border-radius: 16px; padding: 1.75rem; max-width: 420px; width: 100%; border: 1px solid var(--border-color); display: flex; flex-direction: column; gap: 1rem; }
    .modal-box h3 { font-size: 1.1rem; font-weight: 600; }
    .campo label { display: block; font-size: 0.75rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.03em; margin-bottom: 0.35rem; }
    .campo input, .campo select { width: 100%; padding: 0.5rem 0.65rem; border-radius: 8px; border: 1px solid var(--border-color); background-color: var(--bg-page); color: var(--text-main); font-family: inherit; font-size: 0.85rem; }
    .form-actions { display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 0.5rem; }
</style>
@endpush

@section('content')
    <a class="back-link" href="{{ route('reportes.index') }}">&larr; Volver a Reportes</a>

    @if (session('status'))
        <div class="status-msg status-success">{{ session('status') }}</div>
    @endif

    <div class="top-row">
        <button type="button" class="btn btn-primary" onclick="abrirModalCrear()">+ Agregar Estimado</button>
    </div>

    @forelse($estimadosPorSucursal as $sucursal => $items)
        <div class="section-card">
            <h2>{{ $sucursal }}</h2>
            <table>
                <thead>
                    <tr><th>Departamento</th><th>Puesto</th><th>Estimado</th><th>Acciones</th></tr>
                </thead>
                <tbody>
                    @foreach($items as $item)
                        <tr>
                            <td>{{ $item->Departamento }}</td>
                            <td>{{ $item->Puesto }}</td>
                            <td>{{ $item->Estimado_Empleados }}</td>
                            <td style="display:flex; gap:0.75rem;">
                                <button type="button" class="ver-link" onclick='abrirModalEditar(@json($item))'>Editar</button>
                                <form method="POST" action="{{ route('estimados.destroy', $item->id) }}" onsubmit="return confirm('¿Eliminar este estimado?')" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-eliminar-link">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @empty
        <div class="section-card">
            <p class="empty-state">No hay empleados estimados registrados.</p>
        </div>
    @endforelse

    {{-- Modal Crear --}}
    <div id="modalCrear" class="modal-overlay">
        <div class="modal-box">
            <h3>Agregar Empleado Estimado</h3>
            <form method="POST" action="{{ route('estimados.store') }}">
                @csrf
                <div class="campo">
                    <label>Sucursal</label>
                    <select name="Sucursal" required>
                        <option value="">Seleccione</option>
                        @foreach($sucursales as $s)
                            <option value="{{ $s }}">{{ $s }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="campo" style="margin-top:0.75rem;">
                    <label>Departamento</label>
                    <select name="Departamento" id="crearDepartamento" onchange="filtrarPuestos('crear')" required>
                        <option value="">Seleccione</option>
                        @foreach($puestos->pluck('Departamento')->unique() as $dep)
                            <option value="{{ $dep }}">{{ $dep }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="campo" style="margin-top:0.75rem;">
                    <label>Puesto</label>
                    <select name="Puesto" id="crearPuesto" required>
                        <option value="">Seleccione un departamento primero</option>
                    </select>
                </div>
                <div class="campo" style="margin-top:0.75rem;">
                    <label>Estimado de Empleados</label>
                    <input type="number" name="Estimado_Empleados" min="0" required>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Guardar</button>
                    <button type="button" class="btn btn-secundario" onclick="document.getElementById('modalCrear').style.display='none'">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Editar --}}
    <div id="modalEditar" class="modal-overlay">
        <div class="modal-box">
            <h3>Editar Empleado Estimado</h3>
            <form method="POST" id="formEditar" action="">
                @csrf
                @method('PUT')
                <div class="campo">
                    <label>Departamento</label>
                    <select name="Departamento" id="editarDepartamento" onchange="filtrarPuestos('editar')" required>
                        @foreach($puestos->pluck('Departamento')->unique() as $dep)
                            <option value="{{ $dep }}">{{ $dep }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="campo" style="margin-top:0.75rem;">
                    <label>Puesto</label>
                    <select name="Puesto" id="editarPuesto" required></select>
                </div>
                <div class="campo" style="margin-top:0.75rem;">
                    <label>Estimado de Empleados</label>
                    <input type="number" name="Estimado_Empleados" id="editarEstimado" min="0" required>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Guardar</button>
                    <button type="button" class="btn btn-secundario" onclick="document.getElementById('modalEditar').style.display='none'">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    const puestosPorDepartamento = @json($puestos->groupBy('Departamento')->map->pluck('Nombre_Puesto'));

    function filtrarPuestos(prefijo, puestoSeleccionado = null) {
        const departamento = document.getElementById(prefijo + 'Departamento').value;
        const puestoSelect = document.getElementById(prefijo + 'Puesto');
        const puestos = puestosPorDepartamento[departamento] || [];
        puestoSelect.innerHTML = puestos.map(p => `<option value="${p}" ${p === puestoSeleccionado ? 'selected' : ''}>${p}</option>`).join('');
    }

    function abrirModalCrear() {
        document.getElementById('modalCrear').style.display = 'flex';
    }

    function abrirModalEditar(item) {
        document.getElementById('formEditar').action = '{{ url('estimados') }}/' + item.id;
        document.getElementById('editarDepartamento').value = item.Departamento;
        filtrarPuestos('editar', item.Puesto);
        document.getElementById('editarEstimado').value = item.Estimado_Empleados;
        document.getElementById('modalEditar').style.display = 'flex';
    }
</script>
@endpush
