@extends('layouts.app')

@section('title', 'Ubicaciones')
@section('page_title', 'Gestión de Ubicaciones Físicas')

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

    .acciones-cell { display: flex; gap: 0.75rem; align-items: center; }

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

    /* Modal */
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

    .modal-box h3 { font-size: 1.15rem; font-weight: 600; margin-bottom: 0.5rem; }

    .modal-box .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 0.75rem;
        margin-top: 1rem;
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
        <button class="btn btn-primary" onclick="openAddUbicacionModal()">+ Registrar Ubicación</button>
    </div>

    <div class="table-card">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Ubicación (Tienda)</th>
                    <th>Dirección</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($ubicaciones as $u)
                    <tr>
                        <td>{{ $u->ID }}</td>
                        <td><strong>{{ $u->Ubicacion }}</strong></td>
                        <td>{{ $u->Direccion }}</td>
                        <td>
                            <div class="acciones-cell">
                                <a class="ver-link" onclick="openEditUbicacionModal('{{ $u->ID }}', '{{ $u->Ubicacion }}', '{{ $u->Direccion }}')">Editar</a>
                                <form method="POST" action="{{ route('ubicaciones.destroy', $u->ID) }}" onsubmit="return confirm('¿Estás seguro de que deseas eliminar esta ubicación física?')" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-eliminar-link">Eliminar</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="empty-state">No se encontraron ubicaciones registradas.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- MODAL REGISTRAR UBICACION -->
    <div id="addUbicacionModal" class="modal-overlay">
        <div class="modal-box">
            <h3>Agregar Ubicación</h3>
            <form method="POST" action="{{ route('ubicaciones.store') }}">
                @csrf
                <div class="campo">
                    <label for="add_name">Ubicación (Nombre corto) *</label>
                    <input type="text" id="add_name" name="Ubicacion" required placeholder="Ej: BODEGA ESPAÑA">
                </div>
                <div class="campo" style="margin-top:0.75rem;">
                    <label for="add_dir">Dirección Completa *</label>
                    <input type="text" id="add_dir" name="Direccion" required placeholder="Calle, número, colonia, CP, ciudad...">
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Guardar</button>
                    <button type="button" class="btn btn-secundario" onclick="closeAddUbicacionModal()">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL EDITAR UBICACION -->
    <div id="editUbicacionModal" class="modal-overlay">
        <div class="modal-box">
            <h3>Editar Ubicación</h3>
            <form id="editUbicacionForm" method="POST" action="">
                @csrf
                @method('PUT')
                <div class="campo">
                    <label for="edit_name">Ubicación (Nombre) *</label>
                    <input type="text" id="edit_name" name="Ubicacion" required>
                </div>
                <div class="campo" style="margin-top:0.75rem;">
                    <label for="edit_dir">Dirección *</label>
                    <input type="text" id="edit_dir" name="Direccion" required>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                    <button type="button" class="btn btn-secundario" onclick="closeEditUbicacionModal()">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function openAddUbicacionModal() {
        document.getElementById('addUbicacionModal').style.display = 'flex';
    }
    function closeAddUbicacionModal() {
        document.getElementById('addUbicacionModal').style.display = 'none';
    }

    function openEditUbicacionModal(id, name, dir) {
        const form = document.getElementById('editUbicacionForm');
        form.action = `/ubicaciones/${id}`;
        document.getElementById('edit_name').value = name;
        document.getElementById('edit_dir').value = dir;
        document.getElementById('editUbicacionModal').style.display = 'flex';
    }
    function closeEditUbicacionModal() {
        document.getElementById('editUbicacionModal').style.display = 'none';
    }

    window.addEventListener('click', (e) => {
        if (e.target.classList.contains('modal-overlay')) {
            e.target.style.display = 'none';
        }
    });
</script>
@endpush
