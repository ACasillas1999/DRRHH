@extends('layouts.app')

@section('title', 'Roles y Permisos')
@section('page_title', 'Roles y Permisos')

@push('styles')
<style>
    .status-msg { border-radius: 12px; padding: 1rem; margin-bottom: 1.5rem; font-weight: 600; }
    .status-success { border: 1px solid var(--activo); color: var(--activo); background-color: rgba(22, 163, 74, 0.05); }
    .status-error { border: 1px solid var(--baja); color: var(--baja); background-color: rgba(220, 38, 38, 0.05); }

    .top-row { display: flex; justify-content: space-between; align-items: center; gap: 1rem; margin-bottom: 1.5rem; flex-wrap: wrap; }
    .top-row p { color: var(--text-muted); font-size: 0.875rem; }
    .btn { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.6rem 1.1rem; border-radius: 10px; font-weight: 600; font-size: 0.875rem; border: 1px solid transparent; cursor: pointer; font-family: inherit; }
    .btn-primary { background-color: var(--primary); color: white; }
    .btn-secundario { background-color: var(--bg-page); border: 1px solid var(--border-color); color: var(--text-main); }
    .btn-peligro { background-color: transparent; border: 1px solid var(--baja); color: var(--baja); }
    .btn-sm { padding: 0.4rem 0.75rem; font-size: 0.8rem; }

    .roles-layout { display: grid; grid-template-columns: 280px minmax(0, 1fr); gap: 1.25rem; align-items: start; }
    @media (max-width: 900px) { .roles-layout { grid-template-columns: minmax(0, 1fr); } }

    .roles-lista { display: flex; flex-direction: column; gap: 0.6rem; }
    .rol-card { background-color: var(--bg-card); border: 1px solid var(--border-color); border-radius: 14px; padding: 0.9rem 1rem; cursor: pointer; text-align: left; font-family: inherit; color: var(--text-main); width: 100%; transition: border-color var(--transition-speed); }
    .rol-card:hover { border-color: var(--primary); }
    .rol-card.activo { border-color: var(--primary); background-color: var(--primary-light); }
    .rol-card-top { display: flex; align-items: center; gap: 0.5rem; }
    .rol-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
    .rol-nombre { font-weight: 600; font-size: 0.95rem; }
    .rol-sistema { margin-left: auto; font-size: 0.65rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-muted); border: 1px solid var(--border-color); border-radius: 6px; padding: 0.1rem 0.4rem; }
    .rol-meta { font-size: 0.75rem; color: var(--text-muted); margin-top: 0.35rem; }

    .editor { background-color: var(--bg-card); border: 1px solid var(--border-color); border-radius: 20px; box-shadow: var(--shadow-sm); padding: 1.5rem; }
    .editor-panel { display: none; }
    .editor-panel.activo { display: block; }
    .editor-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; flex-wrap: wrap; margin-bottom: 1.5rem; }
    .editor-head h2 { font-size: 1.2rem; font-weight: 700; display: flex; align-items: center; gap: 0.5rem; }
    .editor-head p { color: var(--text-muted); font-size: 0.85rem; margin-top: 0.25rem; }
    .editor-acciones { display: flex; gap: 0.5rem; flex-wrap: wrap; }
    .editor-acciones form { display: inline; }

    .perm-seccion { margin-bottom: 1.75rem; }
    .perm-seccion h3 { font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: var(--text-muted); padding-bottom: 0.5rem; border-bottom: 1px solid var(--border-color); margin-bottom: 0.25rem; }
    .perm-row { display: flex; justify-content: space-between; align-items: center; gap: 1rem; padding: 0.7rem 0; border-bottom: 1px solid var(--border-color); }
    .perm-row:last-child { border-bottom: none; }
    .perm-label { font-weight: 600; font-size: 0.875rem; }
    .perm-desc { font-size: 0.75rem; color: var(--text-muted); margin-top: 0.15rem; }

    .perm-toggle { position: relative; width: 42px; height: 24px; border-radius: 9999px; border: none; cursor: pointer; flex-shrink: 0; background-color: var(--border-color); transition: background-color var(--transition-speed); }
    .perm-toggle::after { content: ''; position: absolute; top: 3px; left: 3px; width: 18px; height: 18px; border-radius: 50%; background: white; box-shadow: var(--shadow-sm); transition: left var(--transition-speed); }
    .perm-toggle.on { background-color: var(--primary); }
    .perm-toggle.on::after { left: 21px; }
    .perm-toggle:disabled { opacity: 0.5; cursor: wait; }

    .modal-overlay { display: none; position: fixed; inset: 0; background-color: rgba(0,0,0,0.5); align-items: center; justify-content: center; z-index: 200; padding: 1rem; }
    .modal-overlay.abierto { display: flex; }
    .modal-box { background-color: var(--bg-card); border-radius: 16px; padding: 1.75rem; max-width: 440px; width: 100%; border: 1px solid var(--border-color); display: flex; flex-direction: column; gap: 1rem; }
    .modal-box h3 { font-size: 1.1rem; font-weight: 600; }
    .campo label { display: block; font-size: 0.75rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.03em; margin-bottom: 0.35rem; }
    .campo input, .campo textarea { width: 100%; padding: 0.5rem 0.65rem; border-radius: 8px; border: 1px solid var(--border-color); background-color: var(--bg-page); color: var(--text-main); font-family: inherit; font-size: 0.85rem; box-sizing: border-box; }
    .campo input[type="color"] { height: 38px; padding: 0.2rem; cursor: pointer; }
    .campo small { display: block; color: var(--text-muted); font-size: 0.75rem; margin-top: 0.3rem; }
    .form-actions { display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 0.5rem; }

    .toast-rol { position: fixed; bottom: 24px; right: 24px; z-index: 9999; background-color: var(--bg-card); border: 1px solid var(--border-color); border-radius: 10px; padding: 0.7rem 1.1rem; font-size: 0.85rem; font-weight: 600; box-shadow: var(--shadow-lg); }
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
        <p>Los cambios en los interruptores se guardan al instante y aplican en la siguiente página que abra el usuario.</p>
        <button type="button" class="btn btn-primary" onclick="abrirModal('modalCrear')">+ Nuevo Rol</button>
    </div>

    @php $rolInicial = (int) request('rol', $roles->first()?->id); @endphp

    <div class="roles-layout">
        {{-- Lista de roles --}}
        <div class="roles-lista">
            @foreach ($roles as $role)
                <button type="button" class="rol-card {{ $role->id === $rolInicial ? 'activo' : '' }}" data-rol="{{ $role->id }}" onclick="seleccionarRol({{ $role->id }})">
                    <div class="rol-card-top">
                        <span class="rol-dot" style="background-color: {{ $role->color }}"></span>
                        <span class="rol-nombre">{{ $role->name }}</span>
                        @if ($role->is_system)<span class="rol-sistema">Sistema</span>@endif
                    </div>
                    <div class="rol-meta">{{ $role->usuarios_count }} usuario{{ $role->usuarios_count === 1 ? '' : 's' }}@if ($role->description) · {{ $role->description }}@endif</div>
                </button>
            @endforeach
        </div>

        {{-- Editor de permisos (un panel por rol, se muestra el seleccionado) --}}
        <div class="editor">
            @forelse ($roles as $role)
                <div class="editor-panel {{ $role->id === $rolInicial ? 'activo' : '' }}" data-panel="{{ $role->id }}">
                    <div class="editor-head">
                        <div>
                            <h2><span class="rol-dot" style="background-color: {{ $role->color }}"></span>{{ $role->name }}</h2>
                            <p>{{ $role->description ?: 'Sin descripción' }}</p>
                        </div>
                        <div class="editor-acciones">
                            <button type="button" class="btn btn-secundario btn-sm"
                                    onclick="abrirEditar(@js(['id' => $role->id, 'name' => $role->name, 'description' => $role->description, 'color' => $role->color, 'is_system' => $role->is_system]))">Editar</button>
                            <form method="POST" action="{{ route('roles.duplicate', $role) }}" data-confirm="Se creará una copia de &quot;{{ $role->name }}&quot; con los mismos permisos.">
                                @csrf
                                <button type="submit" class="btn btn-secundario btn-sm">Duplicar</button>
                            </form>
                            @unless ($role->is_system)
                                <form method="POST" action="{{ route('roles.destroy', $role) }}" data-confirm="¿Eliminar el rol &quot;{{ $role->name }}&quot;? No se puede deshacer." data-confirm-ok="Eliminar" data-confirm-peligro>
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-peligro btn-sm">Eliminar</button>
                                </form>
                            @endunless
                        </div>
                    </div>

                    @foreach ($catalogo as $section => $items)
                        <div class="perm-seccion">
                            <h3>{{ $section === 'modules' ? 'Acceso a módulos' : 'Acciones' }}</h3>
                            @foreach ($items as $key => $def)
                                @php $on = !empty($role->permissions[$section][$key]); @endphp
                                <div class="perm-row">
                                    <div>
                                        <div class="perm-label">{{ $def['label'] }}</div>
                                        <div class="perm-desc">{{ $def['desc'] }}</div>
                                    </div>
                                    <button type="button" class="perm-toggle {{ $on ? 'on' : '' }}"
                                            aria-pressed="{{ $on ? 'true' : 'false' }}" aria-label="{{ $def['label'] }}"
                                            onclick="togglePermiso(this, {{ $role->id }}, '{{ $section }}', '{{ $key }}')"></button>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            @empty
                <p style="color: var(--text-muted); text-align: center; padding: 2rem;">No hay roles. Corre <code>php artisan db:seed --class=RolesSeeder</code>.</p>
            @endforelse
        </div>
    </div>

    {{-- Modal crear --}}
    <div class="modal-overlay" id="modalCrear" onclick="if (event.target === this) cerrarModal('modalCrear')">
        <form class="modal-box" method="POST" action="{{ route('roles.store') }}">
            @csrf
            <h3>Nuevo Rol</h3>
            <div class="campo">
                <label for="crearNombre">Nombre</label>
                <input id="crearNombre" name="name" maxlength="50" required value="{{ old('name') }}">
                <small>Se crea sin permisos. Un rol nuevo no existe en el sistema legacy RH.</small>
            </div>
            <div class="campo">
                <label for="crearDesc">Descripción</label>
                <textarea id="crearDesc" name="description" maxlength="255" rows="2">{{ old('description') }}</textarea>
            </div>
            <div class="campo">
                <label for="crearColor">Color</label>
                <input id="crearColor" type="color" name="color" value="{{ old('color', '#1d4ed8') }}">
            </div>
            <div class="form-actions">
                <button type="button" class="btn btn-secundario" onclick="cerrarModal('modalCrear')">Cancelar</button>
                <button type="submit" class="btn btn-primary">Crear</button>
            </div>
        </form>
    </div>

    {{-- Modal editar --}}
    <div class="modal-overlay" id="modalEditar" onclick="if (event.target === this) cerrarModal('modalEditar')">
        <form class="modal-box" method="POST" id="formEditar">
            @csrf
            @method('PUT')
            <h3>Editar Rol</h3>
            <div class="campo">
                <label for="editarNombre">Nombre</label>
                <input id="editarNombre" name="name" maxlength="50" required>
                <small id="editarNombreAviso" style="display:none;">Rol de sistema: el legacy depende de este nombre, no se puede cambiar.</small>
            </div>
            <div class="campo">
                <label for="editarDesc">Descripción</label>
                <textarea id="editarDesc" name="description" maxlength="255" rows="2"></textarea>
            </div>
            <div class="campo">
                <label for="editarColor">Color</label>
                <input id="editarColor" type="color" name="color">
            </div>
            <div class="form-actions">
                <button type="button" class="btn btn-secundario" onclick="cerrarModal('modalEditar')">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar</button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script>
    const CSRF_ROLES = document.querySelector('meta[name="csrf-token"]').content;
    const URL_ROLES = @js(url('/roles'));

    function seleccionarRol(id) {
        document.querySelectorAll('.rol-card').forEach(c => c.classList.toggle('activo', c.dataset.rol == id));
        document.querySelectorAll('.editor-panel').forEach(p => p.classList.toggle('activo', p.dataset.panel == id));
        history.replaceState(null, '', '?rol=' + id);
    }

    function abrirModal(id) { document.getElementById(id).classList.add('abierto'); }
    function cerrarModal(id) { document.getElementById(id).classList.remove('abierto'); }

    function abrirEditar(rol) {
        const form = document.getElementById('formEditar');
        form.action = URL_ROLES + '/' + rol.id;
        const nombre = document.getElementById('editarNombre');
        nombre.value = rol.name;
        nombre.readOnly = rol.is_system;
        document.getElementById('editarNombreAviso').style.display = rol.is_system ? 'block' : 'none';
        document.getElementById('editarDesc').value = rol.description || '';
        document.getElementById('editarColor').value = rol.color || '#1d4ed8';
        abrirModal('modalEditar');
    }

    async function togglePermiso(btn, rolId, section, key) {
        const nuevo = !btn.classList.contains('on');
        btn.disabled = true;
        try {
            const res = await fetch(`${URL_ROLES}/${rolId}/permisos`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_ROLES },
                body: JSON.stringify({ permissions: { [section]: { [key]: nuevo } } }),
            });
            const data = await res.json();
            if (!res.ok || !data.ok) throw new Error(data.message || 'No se pudo guardar.');
            btn.classList.toggle('on', nuevo);
            btn.setAttribute('aria-pressed', nuevo ? 'true' : 'false');
            toastRol(nuevo ? 'Permiso activado' : 'Permiso desactivado', 'var(--activo)');
        } catch (e) {
            toastRol(e.message, 'var(--baja)');
        } finally {
            btn.disabled = false;
        }
    }

    function toastRol(msg, color) {
        const t = document.createElement('div');
        t.className = 'toast-rol';
        t.style.color = color;
        t.textContent = msg;
        document.body.appendChild(t);
        setTimeout(() => t.remove(), 2600);
    }

    @if ($errors->has('name') && !old('_method'))
        abrirModal('modalCrear');
    @endif
</script>
@endpush
