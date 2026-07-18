@extends('layouts.app')

@section('title', 'Configuración de Checadores')

@section('content')
<div class="checadores-layout">
    <!-- Encabezado -->
    <div class="checadores-header-card">
        <div class="header-icon-box">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
            </svg>
        </div>
        <div class="header-text-box">
            <h1>Configuración de Checadores Biométricos</h1>
            <p>Administra los dispositivos biométricos de tu red local, ajusta las credenciales de conexión y habilita o deshabilita terminales.</p>
        </div>
    </div>

    <!-- Contenido en dos columnas (Listado y Formulario) -->
    <div class="checadores-grid">
        <!-- Columna Izquierda: Listado -->
        <div class="checadores-list-col">
            <div class="table-container-card">
                <div class="table-header-toolbar flex-between">
                    <h3 class="table-toolbar-title">Dispositivos Registrados</h3>
                    <div class="search-input-box compact">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="search-icon">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                        <input type="text" id="devicesSearch" onkeyup="filterDevicesTable()" placeholder="Buscar dispositivo o IP...">
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="premium-table" id="devicesTable">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Dirección IP</th>
                                <th>Puerto</th>
                                <th>Usuario</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($checadores as $ch)
                                <tr>
                                    <td class="font-semibold">{{ $ch->nombre }}</td>
                                    <td class="text-mono">{{ $ch->ip }}</td>
                                    <td>{{ $ch->puerto }}</td>
                                    <td>{{ $ch->usuario }}</td>
                                    <td>
                                        <span class="status-badge {{ $ch->activo ? 'badge-ontime' : 'badge-off' }}">
                                            {{ $ch->activo ? 'Activo' : 'Desactivado' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div style="display: flex; gap: 0.4rem;">
                                            <button type="button" class="btn-action-edit" onclick="openEditModal({{ json_encode($ch) }})">
                                                Editar
                                            </button>
                                            <form method="POST" action="{{ route('checadores.destroy', $ch->id) }}" onsubmit="return confirm('¿Seguro que deseas eliminar este checador?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn-action-delete">
                                                    Eliminar
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="no-records-row">No hay checadores registrados en el sistema.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Columna Derecha: Agregar Checador -->
        <div class="checadores-form-col">
            <div class="form-container-card">
                <h3 class="form-card-title">Registrar Nuevo Checador</h3>
                <form method="POST" action="{{ route('checadores.store') }}" class="premium-form">
                    @csrf
                    <div class="form-group">
                        <label for="nombre">Nombre del Checador:</label>
                        <input type="text" id="nombre" name="nombre" placeholder="Ej. Federalismo, Corporativo..." required class="form-control-crud">
                    </div>

                    <div class="form-group">
                        <label for="ip">Dirección IP:</label>
                        <input type="text" id="ip" name="ip" placeholder="Ej. 192.168.100.201" required class="form-control-crud">
                    </div>

                    <div class="form-row-crud">
                        <div class="form-group flex-1">
                            <label for="puerto">Puerto:</label>
                            <input type="number" id="puerto" name="puerto" value="80" required class="form-control-crud">
                        </div>
                        <div class="form-group flex-1">
                            <label for="usuario">Usuario:</label>
                            <input type="text" id="usuario" name="usuario" value="admin" required class="form-control-crud">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password">Contraseña (ISAPI):</label>
                        <input type="password" id="password" name="password" value="cga302630" required class="form-control-crud">
                    </div>

                    <div class="form-checkbox-group">
                        <label class="checkbox-container">
                            <input type="checkbox" id="activo" name="activo" value="1" checked>
                            <span class="checkmark"></span>
                            Dispositivo Habilitado (Activo)
                        </label>
                    </div>

                    <button type="submit" class="btn-primary-crud">
                        Guardar Checador
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- MODAL DE EDICIÓN DE CHECADOR -->
<div id="editChecadorModal" class="modal-overlay hidden">
    <div class="modal-content card animate-fade-in">
        <div class="modal-header">
            <h4>Editar Configuración de Checador</h4>
            <button type="button" class="btn-close-modal" onclick="closeEditModal()">&times;</button>
        </div>
        <form method="POST" id="editForm" action="">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="form-group">
                    <label for="edit_nombre">Nombre del Checador:</label>
                    <input type="text" id="edit_nombre" name="nombre" required class="form-control-modal">
                </div>

                <div class="form-group">
                    <label for="edit_ip">Dirección IP:</label>
                    <input type="text" id="edit_ip" name="ip" required class="form-control-modal">
                </div>

                <div class="form-row-crud" style="display: flex; gap: 1rem;">
                    <div class="form-group" style="flex: 1;">
                        <label for="edit_puerto">Puerto:</label>
                        <input type="number" id="edit_puerto" name="puerto" required class="form-control-modal">
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label for="edit_usuario">Usuario:</label>
                        <input type="text" id="edit_usuario" name="usuario" required class="form-control-modal">
                    </div>
                </div>

                <div class="form-group">
                    <label for="edit_password">Contraseña (ISAPI):</label>
                    <input type="password" id="edit_password" name="password" required class="form-control-modal">
                </div>

                <div class="form-checkbox-group" style="margin-top: 0.5rem;">
                    <label class="checkbox-container">
                        <input type="checkbox" id="edit_activo" name="activo" value="1">
                        <span class="checkmark"></span>
                        Dispositivo Habilitado (Activo)
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeEditModal()">Cancelar</button>
                <button type="submit" class="btn-primary-crud" style="padding: 0.5rem 1rem; font-size: 0.825rem; height: auto;">Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
<style>
    .checadores-layout {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
        animation: fadeIn 0.4s ease;
    }

    .checadores-header-card {
        background-color: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 1.5rem;
        display: flex;
        align-items: center;
        gap: 1.25rem;
        box-shadow: var(--shadow-sm);
    }

    .checadores-grid {
        display: grid;
        grid-template-columns: 1fr 340px;
        gap: 1.5rem;
        align-items: start;
    }

    @media (max-width: 992px) {
        .checadores-grid {
            grid-template-columns: 1fr;
        }
    }

    /* Columna Formulario */
    .form-container-card {
        background-color: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: var(--shadow-sm);
    }

    .form-card-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--text-main);
        margin-bottom: 1.25rem;
    }

    .premium-form {
        display: flex;
        flex-direction: column;
        gap: 1.1rem;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 0.4rem;
    }

    .form-group label {
        font-size: 0.8rem;
        font-weight: 600;
        color: var(--text-muted);
    }

    .form-control-crud {
        width: 100%;
        background-color: var(--bg-page);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        padding: 0.6rem 0.75rem;
        font-family: inherit;
        font-size: 0.875rem;
        color: var(--text-main);
        outline: none;
        transition: border-color var(--transition-speed) ease;
    }

    .form-control-crud:focus {
        border-color: var(--primary);
    }

    .form-row-crud {
        display: flex;
        gap: 1rem;
    }

    .flex-1 { flex: 1; }

    /* Checkbox */
    .form-checkbox-group {
        margin: 0.25rem 0;
    }

    .checkbox-container {
        display: flex;
        align-items: center;
        position: relative;
        padding-left: 1.75rem;
        cursor: pointer;
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--text-main);
        user-select: none;
    }

    .checkbox-container input {
        position: absolute;
        opacity: 0;
        cursor: pointer;
        height: 0;
        width: 0;
    }

    .checkmark {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        left: 0;
        height: 18px;
        width: 18px;
        background-color: var(--bg-page);
        border: 1px solid var(--border-color);
        border-radius: 4px;
        transition: all 0.2s ease;
    }

    .checkbox-container:hover input ~ .checkmark {
        border-color: var(--primary);
    }

    .checkbox-container input:checked ~ .checkmark {
        background-color: var(--primary);
        border-color: var(--primary);
    }

    .checkmark:after {
        content: "";
        position: absolute;
        display: none;
    }

    .checkbox-container input:checked ~ .checkmark:after {
        display: block;
    }

    .checkbox-container .checkmark:after {
        left: 6px;
        top: 2px;
        width: 5px;
        height: 10px;
        border: solid white;
        border-width: 0 2.5px 2.5px 0;
        transform: rotate(45deg);
    }

    .btn-primary-crud {
        background-color: var(--primary);
        color: white;
        border: none;
        border-radius: 8px;
        padding: 0.65rem 1rem;
        font-size: 0.875rem;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background-color var(--transition-speed) ease;
        height: 40px;
        width: 100%;
    }

    .btn-primary-crud:hover {
        background-color: var(--primary-hover);
    }

    /* Botones de acción listado */
    .btn-action-edit {
        background-color: rgba(37, 99, 235, 0.08);
        color: #2563eb;
        border: 1px solid rgba(37, 99, 235, 0.15);
        padding: 0.35rem 0.65rem;
        font-size: 0.725rem;
        font-weight: 600;
        border-radius: 6px;
        cursor: pointer;
        transition: all var(--transition-speed) ease;
    }

    .btn-action-edit:hover {
        background-color: #2563eb;
        color: white;
    }

    .text-mono {
        font-family: var(--font-mono, monospace);
        font-size: 0.8rem;
    }
</style>
@endpush

@push('scripts')
<script>
    // Editar Checador - Abrir Modal
    function openEditModal(checador) {
        const form = document.getElementById('editForm');
        form.action = `/checadores/${checador.id}`;

        document.getElementById('edit_nombre').value = checador.nombre;
        document.getElementById('edit_ip').value = checador.ip;
        document.getElementById('edit_puerto').value = checador.puerto;
        document.getElementById('edit_usuario').value = checador.usuario;
        document.getElementById('edit_password').value = checador.password;
        
        document.getElementById('edit_activo').checked = (checador.activo == 1);

        document.getElementById('editChecadorModal').classList.remove('hidden');
    }

    function closeEditModal() {
        document.getElementById('editChecadorModal').classList.add('hidden');
    }

    // Filtrar tabla localmente
    function filterDevicesTable() {
        const input = document.getElementById("devicesSearch").value.toLowerCase();
        const table = document.getElementById("devicesTable");
        const rows = table.getElementsByTagName("tbody")[0].getElementsByTagName("tr");

        for (let i = 0; i < rows.length; i++) {
            let nameCell = rows[i].getElementsByTagName("td")[0];
            let ipCell = rows[i].getElementsByTagName("td")[1];
            let rowContainsFilter = false;

            if (nameCell || ipCell) {
                let nameText = nameCell.textContent || nameCell.innerText;
                let ipText = ipCell.textContent || ipCell.innerText;
                
                if (nameText.toLowerCase().includes(input) || ipText.toLowerCase().includes(input)) {
                    rowContainsFilter = true;
                }
            }

            if (rows[i].cells[0].classList.contains('no-records-row')) continue;
            rows[i].style.display = rowContainsFilter ? "" : "none";
        }
    }
</script>
@endpush
