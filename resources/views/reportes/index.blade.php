@extends('layouts.app')

@section('title', 'Reportes')
@section('page_title', 'Reportes y Estadísticas')

@push('styles')
<style>
    .status-msg { border-radius: 12px; padding: 1rem; margin-bottom: 1.5rem; font-weight: 600; }
    .status-success { border: 1px solid var(--activo); color: var(--activo); background-color: rgba(22, 163, 74, 0.05); }

    .subnav { display: flex; gap: 0.75rem; margin-bottom: 1.5rem; flex-wrap: wrap; }
    .btn { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.6rem 1.1rem; border-radius: 10px; font-weight: 600; font-size: 0.875rem; text-decoration: none; border: 1px solid transparent; cursor: pointer; }
    .btn-primary { background-color: var(--primary); color: white; }
    .btn-primary:hover { opacity: 0.9; }
    .btn-secundario { background-color: var(--bg-page); border: 1px solid var(--border-color); color: var(--text-main); }

    .chart-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(min(420px, 100%), 1fr)); gap: 1.5rem; margin-bottom: 1.5rem; min-width: 0; }
    .chart-card { background-color: var(--bg-card); border: 1px solid var(--border-color); border-radius: 20px; padding: 1.5rem; box-shadow: var(--shadow-sm); min-width: 0; }
    /* Celular: min(420px, 100%) arriba evita que la gráfica pida más ancho que la pantalla */
    @media (max-width: 640px) { .chart-card { padding: 1rem; } }
    .chart-card h2 { font-size: 1rem; font-weight: 600; margin-bottom: 1rem; }
    .chart-body { display: flex; gap: 1.25rem; align-items: center; min-width: 0; }
    .chart-body canvas { flex: 1; max-height: 280px; min-width: 0; }

    .table-mini-wrap { flex: 1; min-width: 0; overflow-x: auto; }
    .table-mini { border-collapse: collapse; font-size: 0.8rem; width: 100%; }
    .table-mini th, .table-mini td { border: 1px solid var(--border-color); padding: 0.4rem 0.6rem; text-align: center; white-space: nowrap; }
    .table-mini th { background-color: var(--bg-page); color: var(--text-muted); font-weight: 600; }

    .subnav-tabs { display: flex; gap: 0.5rem; flex-wrap: wrap; margin-bottom: 1.25rem; }
    .subnav-tab { padding: 0.5rem 1rem; border-radius: 8px; background-color: var(--bg-page); border: 1px solid var(--border-color); font-size: 0.8rem; font-weight: 600; color: var(--text-main); cursor: pointer; }
    .subnav-tab.active { background-color: var(--primary); border-color: var(--primary); color: white; }

    .empty-state { padding: 2rem; text-align: center; color: var(--text-muted); }
</style>
@endpush

@section('content')
    @if (session('status'))
        <div class="status-msg status-success">{{ session('status') }}</div>
    @endif

    <div class="subnav">
        <a href="{{ route('estimados.index') }}" class="btn btn-secundario">Actualizar Estimados</a>
        <a href="{{ route('reportes.ingresosRenuncias') }}" class="btn btn-secundario">Reporte Ingresos/Renuncias</a>
        <a href="{{ route('reportes.exportar') }}" class="btn btn-secundario">Exportar a Excel</a>
    </div>

    <div class="chart-grid">
        <div class="chart-card">
            <h2>Empleados Activos por Puesto</h2>
            <div class="chart-body">
                <canvas id="chartPuesto"></canvas>
            </div>
        </div>

        <div class="chart-card">
            <h2>Empleados por Estatus</h2>
            <div class="chart-body">
                <canvas id="chartEstatus"></canvas>
            </div>
        </div>

        <div class="chart-card">
            <h2>Empleados Activos por Razón Social</h2>
            <div class="chart-body">
                <canvas id="chartNomenclatura"></canvas>
            </div>
        </div>

        <div class="chart-card">
            <h2>Empleados Activos por Departamento</h2>
            <div class="chart-body">
                <canvas id="chartDepartamento"></canvas>
            </div>
        </div>
    </div>

    <div class="chart-card" style="margin-bottom:1.5rem;">
        <h2>Comparativa Actuales vs Estimados por Sucursal</h2>
        @if(count($comparativaPorSucursal) > 0)
            <div class="subnav-tabs" id="sucursalTabs">
                @foreach($comparativaPorSucursal as $sucursal => $datos)
                    <div class="subnav-tab {{ $loop->first ? 'active' : '' }}" onclick="mostrarSucursal('{{ $sucursal }}', this)">{{ $sucursal }}</div>
                @endforeach
            </div>
            <div class="chart-body">
                <canvas id="chartComparativa"></canvas>
                <div class="table-mini-wrap">
                    <table class="table-mini" id="tablaComparativa">
                        <thead><tr><th>Puesto</th><th>Actuales</th><th>Estimados</th><th>Dif.</th></tr></thead>
                        <tbody id="tablaComparativaBody"></tbody>
                    </table>
                </div>
            </div>
        @else
            <p class="empty-state">No hay empleados estimados registrados. <a href="{{ route('estimados.index') }}">Agregar estimados</a></p>
        @endif
    </div>

    <div class="chart-card">
        <h2>Comparativa Global Actuales vs Estimados por Puesto</h2>
        @if(count($comparativaGlobalPorPuesto) > 0)
            <div class="chart-body">
                <canvas id="chartComparativaGlobal"></canvas>
                <div class="table-mini-wrap">
                    <table class="table-mini">
                        <thead><tr><th>Puesto</th><th>Actuales</th><th>Estimados</th><th>Dif.</th></tr></thead>
                        <tbody>
                            @foreach($comparativaGlobalPorPuesto as $c)
                                <tr>
                                    <td>{{ $c['puesto'] }}</td>
                                    <td>{{ $c['actuales'] }}</td>
                                    <td>{{ $c['estimados'] }}</td>
                                    <td>{{ $c['diferencia'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <p class="empty-state">No hay empleados estimados registrados.</p>
        @endif
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const porPuesto = @json($porPuesto);
    const porEstatus = @json($porEstatus);
    const porNomenclatura = @json($porNomenclatura);
    const porDepartamento = @json($porDepartamento);
    const comparativaPorSucursal = @json($comparativaPorSucursal);

    function crearBarra(id, labels, data, color) {
        return new Chart(document.getElementById(id), {
            type: 'bar',
            data: { labels, datasets: [{ data, backgroundColor: color }] },
            options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
        });
    }

    crearBarra('chartPuesto', Object.keys(porPuesto), Object.values(porPuesto), 'rgba(29, 78, 216, 0.6)');
    crearBarra('chartEstatus', Object.keys(porEstatus), Object.values(porEstatus), 'rgba(220, 38, 38, 0.6)');
    crearBarra('chartNomenclatura', Object.keys(porNomenclatura), Object.values(porNomenclatura), 'rgba(217, 119, 6, 0.6)');
    crearBarra('chartDepartamento', Object.keys(porDepartamento), Object.values(porDepartamento), 'rgba(22, 163, 74, 0.6)');

    let chartComparativa = null;
    function mostrarSucursal(sucursal, el) {
        document.querySelectorAll('#sucursalTabs .subnav-tab').forEach(t => t.classList.remove('active'));
        el.classList.add('active');

        const datos = comparativaPorSucursal[sucursal] || [];
        const labels = datos.map(d => d.puesto);
        const actuales = datos.map(d => d.actuales);
        const estimados = datos.map(d => d.estimados);

        if (chartComparativa) chartComparativa.destroy();
        chartComparativa = new Chart(document.getElementById('chartComparativa'), {
            type: 'bar',
            data: {
                labels,
                datasets: [
                    { label: 'Actuales', data: actuales, backgroundColor: 'rgba(29, 78, 216, 0.6)' },
                    { label: 'Estimados', data: estimados, backgroundColor: 'rgba(147, 51, 234, 0.6)' },
                ]
            },
            options: { scales: { y: { beginAtZero: true } } }
        });

        const tbody = document.getElementById('tablaComparativaBody');
        tbody.innerHTML = datos.map(d => `<tr><td>${d.puesto}</td><td>${d.actuales}</td><td>${d.estimados}</td><td>${d.diferencia}</td></tr>`).join('');
    }

    const primeraSucursal = Object.keys(comparativaPorSucursal)[0];
    if (primeraSucursal) mostrarSucursal(primeraSucursal, document.querySelector('#sucursalTabs .subnav-tab'));

    @if(count($comparativaGlobalPorPuesto) > 0)
    new Chart(document.getElementById('chartComparativaGlobal'), {
        type: 'bar',
        data: {
            labels: @json(array_column($comparativaGlobalPorPuesto, 'puesto')),
            datasets: [
                { label: 'Actuales', data: @json(array_column($comparativaGlobalPorPuesto, 'actuales')), backgroundColor: 'rgba(29, 78, 216, 0.6)' },
                { label: 'Estimados', data: @json(array_column($comparativaGlobalPorPuesto, 'estimados')), backgroundColor: 'rgba(147, 51, 234, 0.6)' },
            ]
        },
        options: { scales: { y: { beginAtZero: true } } }
    });
    @endif
</script>
@endpush
