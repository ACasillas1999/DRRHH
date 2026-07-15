@extends('layouts.app')

@section('title', 'Exportar a Excel')
@section('page_title', 'Exportar Datos a Excel')

@push('styles')
<style>
    .back-link { display: inline-flex; align-items: center; gap: 0.4rem; color: var(--text-muted); text-decoration: none; font-size: 0.875rem; font-weight: 500; margin-bottom: 1rem; }
    .back-link:hover { color: var(--primary); }
    .section-card { background-color: var(--bg-card); border: 1px solid var(--border-color); border-radius: 20px; padding: 1.75rem; box-shadow: var(--shadow-sm); margin-bottom: 1.5rem; }
    .columnas-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 0.6rem; margin-bottom: 1.25rem; }
    .columna-item { display: flex; align-items: center; gap: 0.5rem; padding: 0.5rem 0.75rem; border-radius: 8px; background-color: var(--bg-page); border: 1px solid var(--border-color); font-size: 0.85rem; }
    .btn { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.6rem 1.1rem; border-radius: 10px; font-weight: 600; font-size: 0.875rem; text-decoration: none; border: 1px solid transparent; cursor: pointer; }
    .btn-primary { background-color: var(--primary); color: white; }
    .btn-secundario { background-color: var(--bg-page); border: 1px solid var(--border-color); color: var(--text-main); }
    .table-card { background-color: var(--bg-card); border: 1px solid var(--border-color); border-radius: 20px; box-shadow: var(--shadow-sm); overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; }
    thead th { text-align: left; font-size: 0.7rem; text-transform: uppercase; color: var(--text-muted); padding: 0.75rem 1rem; border-bottom: 1px solid var(--border-color); white-space: nowrap; }
    tbody td { padding: 0.6rem 1rem; border-bottom: 1px solid var(--border-color); font-size: 0.8rem; white-space: nowrap; }
    .empty-state { padding: 2rem; text-align: center; color: var(--text-muted); }
</style>
@endpush

@section('content')
    <a class="back-link" href="{{ route('reportes.index') }}">&larr; Volver a Reportes</a>

    <div class="section-card">
        <h2 style="margin-bottom:1rem; font-size:1.05rem;">Seleccione las Columnas</h2>
        <form method="POST" action="{{ route('reportes.exportar') }}" id="formExportar">
            @csrf
            <div class="columnas-grid">
                @foreach($columnasDisponibles as $col)
                    <label class="columna-item">
                        <input type="checkbox" name="columns[]" value="{{ $col }}" @checked(in_array($col, $columnasSeleccionadas))>
                        {{ $col }}
                    </label>
                @endforeach
            </div>
            <div style="display:flex; gap:0.75rem;">
                <button type="submit" class="btn btn-secundario">Mostrar Vista Previa</button>
                <button type="button" class="btn btn-primary" onclick="exportarExcel()">Exportar a Excel</button>
            </div>
        </form>
    </div>

    <div class="table-card">
        <table>
            <thead>
                <tr>
                    @foreach($columnasSeleccionadas as $col)
                        <th>{{ $col }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($previewData as $fila)
                    <tr>
                        @foreach($columnasSeleccionadas as $col)
                            <td>{{ $fila[$col] ?? '' }}</td>
                        @endforeach
                    </tr>
                @empty
                    <tr><td class="empty-state">Selecciona columnas y presiona "Mostrar Vista Previa".</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection

@push('scripts')
<script>
    function exportarExcel() {
        const checked = document.querySelectorAll('input[name="columns[]"]:checked');
        if (checked.length === 0) {
            alert('Selecciona al menos una columna para exportar.');
            return;
        }
        const form = document.getElementById('formExportar');
        form.action = "{{ route('reportes.exportarExcel') }}";
        form.submit();
        form.action = "{{ route('reportes.exportar') }}";
    }
</script>
@endpush
