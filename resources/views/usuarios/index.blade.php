@extends('layouts.app')

@section('title', 'Usuarios')
@section('page_title', 'Usuarios del Sistema')

@push('styles')
<style>
    .status-msg { border-radius: 12px; padding: 1rem; margin-bottom: 1.5rem; font-weight: 600; }
    .status-success { border: 1px solid var(--activo); color: var(--activo); background-color: rgba(22, 163, 74, 0.05); }
    .status-error { border: 1px solid var(--baja); color: var(--baja); background-color: rgba(220, 38, 38, 0.05); }

    .top-row { display: flex; justify-content: flex-end; margin-bottom: 1.5rem; }
    .btn { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.6rem 1.1rem; border-radius: 10px; font-weight: 600; font-size: 0.875rem; text-decoration: none; border: 1px solid transparent; cursor: pointer; }
    .btn-primary { background-color: var(--primary); color: white; }
    .btn-secundario { background-color: var(--bg-page); border: 1px solid var(--border-color); color: var(--text-main); }

    .table-card { background-color: var(--bg-card); border: 1px solid var(--border-color); border-radius: 20px; box-shadow: var(--shadow-sm); overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; }
    thead th { text-align: left; font-size: 0.7rem; text-transform: uppercase; color: var(--text-muted); padding: 0.85rem 1rem; border-bottom: 1px solid var(--border-color); white-space: nowrap; }
    tbody td { padding: 0.65rem 1rem; border-bottom: 1px solid var(--border-color); font-size: 0.85rem; white-space: nowrap; }
    tbody tr:last-child td { border-bottom: none; }
    .ver-link { color: var(--primary); font-weight: 600; text-decoration: none; font-size: 0.85rem; cursor: pointer; background: none; border: none; }
    .ver-link:hover { text-decoration: underline; }
    .empty-state { padding: 2rem; text-align: center; color: var(--text-muted); }

    .rol-badge { display: inline-block; padding: 0.25rem 0.65rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; background-color: var(--primary-light); color: var(--primary); }

    .modal-overlay { display: none; position: fixed; inset: 0; background-color: rgba(0,0,0,0.5); align-items: center; justify-content: center; z-index: 200; padding: 1rem; }
    .modal-box { background-color: var(--bg-card); border-radius: 16px; padding: 1.75rem; max-width: 460px; width: 100%; border: 1px solid var(--border-color); display: flex; flex-direction: column; gap: 1rem; max-height: 90vh; overflow-y: auto; }
    .modal-box h3 { font-size: 1.1rem; font-weight: 600; }
    .campo label { display: block; font-size: 0.75rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.03em; margin-bottom: 0.35rem; }
    .campo input, .campo select { width: 100%; padding: 0.5rem 0.65rem; border-radius: 8px; border: 1px solid var(--border-color); background-color: var(--bg-page); color: var(--text-main); font-family: inherit; font-size: 0.85rem; }
    .form-actions { display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 0.5rem; }
    .errores { background-color: rgba(220,38,38,0.05); border: 1px solid var(--baja); color: var(--baja); border-radius: 10px; padding: 0.75rem 1rem; font-size: 0.8rem; }
</style>
@endpush

@section('content')
    @if (session('status'))
        <div class="status-msg status-success">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="status-msg status-error">
            <ul style="margin-left:1.25rem;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="top-row">
        <button type="button" class="btn btn-primary" onclick="abrirModalCrear()">+ Agregar Usuario</button>
    </div>

    <div class="table-card">
        <table>
            <thead>
                <tr><th>Usuario</th><th>Nombre</th><th>Rol</th><th>Correo</th><th>Teléfono</th><th>Acciones</th></tr>
            </thead>
            <tbody>
                @forelse($usuarios as $u)
                    <tr>
                        <td>{{ $u->Username }}</td>
                        <td>{{ $u->Nombre }}</td>
                        <td><span class="rol-badge">{{ $u->Rol }}</span></td>
                        <td>{{ $u->Correo }}</td>
                        <td>{{ $u->Telefono }}</td>
                        <td>
                            <button type="button" class="ver-link" onclick='abrirModalEditar(@json($u))'>Editar</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="empty-state">No hay usuarios registrados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Modal Crear --}}
    <div id="modalCrear" class="modal-overlay">
        <div class="modal-box">
            <h3>Agregar Usuario</h3>
            <form method="POST" action="{{ route('usuarios.store') }}">
                @csrf
                <div class="campo"><label>Nombre de Usuario</label><input type="text" name="username" required></div>
                <div class="campo" style="margin-top:0.75rem;"><label>Nombre</label><input type="text" name="nombre" required></div>
                <div class="campo" style="margin-top:0.75rem;"><label>Correo</label><input type="email" name="correo" required></div>
                <div class="campo" style="margin-top:0.75rem;"><label>Teléfono</label><input type="text" name="telefono"></div>
                <div class="campo" style="margin-top:0.75rem;">
                    <label>Rol</label>
                    <select name="rol" required>
                        @foreach($roles as $r)
                            <option value="{{ $r }}">{{ $r }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="campo" style="margin-top:0.75rem;">
                    <label>Departamento</label>
                    <select name="departamento" id="crearDepartamento" onchange="cargarResponsables('crear')">
                        <option value="">Seleccione un departamento</option>
                        @foreach($departamentos as $dep)
                            <option value="{{ $dep }}">{{ $dep }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="campo" style="margin-top:0.75rem;">
                    <label>Responsable</label>
                    <select name="responsable" id="crearResponsable" disabled>
                        <option value="">Seleccione un departamento primero</option>
                    </select>
                </div>
                <div class="campo" style="margin-top:0.75rem;"><label>Contraseña</label><input type="password" name="password" required></div>
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
            <h3>Editar Usuario</h3>
            <form method="POST" id="formEditar" action="">
                @csrf
                @method('PUT')
                <div class="campo"><label>Nombre de Usuario</label><input type="text" name="username" id="editarUsername" required></div>
                <div class="campo" style="margin-top:0.75rem;"><label>Nombre</label><input type="text" name="nombre" id="editarNombre" required></div>
                <div class="campo" style="margin-top:0.75rem;"><label>Correo</label><input type="email" name="correo" id="editarCorreo" required></div>
                <div class="campo" style="margin-top:0.75rem;"><label>Teléfono</label><input type="text" name="telefono" id="editarTelefono"></div>
                <div class="campo" style="margin-top:0.75rem;">
                    <label>Rol</label>
                    <select name="rol" id="editarRol" required>
                        @foreach($roles as $r)
                            <option value="{{ $r }}">{{ $r }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="campo" style="margin-top:0.75rem;">
                    <label>Departamento</label>
                    <select name="departamento" id="editarDepartamento" onchange="cargarResponsables('editar')">
                        <option value="">Seleccione un departamento</option>
                        @foreach($departamentos as $dep)
                            <option value="{{ $dep }}">{{ $dep }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="campo" style="margin-top:0.75rem;">
                    <label>Responsable</label>
                    <select name="responsable" id="editarResponsable">
                        <option value="">Seleccione un departamento primero</option>
                    </select>
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
    function abrirModalCrear() {
        document.getElementById('modalCrear').style.display = 'flex';
    }

    function cargarResponsables(prefijo, responsableSeleccionado = null) {
        const departamento = document.getElementById(prefijo + 'Departamento').value;
        const responsableSelect = document.getElementById(prefijo + 'Responsable');

        if (!departamento) {
            responsableSelect.innerHTML = '<option value="">Seleccione un departamento primero</option>';
            responsableSelect.disabled = true;
            return;
        }

        responsableSelect.innerHTML = '<option value="">Cargando...</option>';

        fetch(`{{ route('api.usuarios.responsables') }}?departamento=${encodeURIComponent(departamento)}`)
            .then(r => r.json())
            .then(data => {
                if (data.length === 0) {
                    responsableSelect.innerHTML = '<option value="">No hay responsables disponibles</option>';
                    responsableSelect.disabled = true;
                    return;
                }
                responsableSelect.innerHTML = '<option value="">Seleccione un responsable</option>' +
                    data.map(r => `<option value="${r.ID}" ${String(r.ID) === String(responsableSeleccionado) ? 'selected' : ''}>${r.Nombre_Completo} - ${r.SubDepartamento} (${r.Fisica})</option>`).join('');
                responsableSelect.disabled = false;
            })
            .catch(() => {
                responsableSelect.innerHTML = '<option value="">Error al cargar responsables</option>';
                responsableSelect.disabled = true;
            });
    }

    function abrirModalEditar(usuario) {
        document.getElementById('formEditar').action = '{{ url('usuarios') }}/' + usuario.ID;
        document.getElementById('editarUsername').value = usuario.Username;
        document.getElementById('editarNombre').value = usuario.Nombre;
        document.getElementById('editarCorreo').value = usuario.Correo;
        document.getElementById('editarTelefono').value = usuario.Telefono || '';
        document.getElementById('editarRol').value = usuario.Rol;
        document.getElementById('editarDepartamento').value = usuario.Departamento || '';
        if (usuario.Departamento) {
            cargarResponsables('editar', usuario.Responsable);
        } else {
            document.getElementById('editarResponsable').innerHTML = '<option value="">Seleccione un departamento primero</option>';
        }
        document.getElementById('modalEditar').style.display = 'flex';
    }
</script>
@endpush
