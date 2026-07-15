@extends('layouts.app')

@section('title', 'Ingresos y Renuncias')
@section('page_title', 'Reporte de Ingresos y Renuncias')

@push('styles')
<style>
    .filtros-card { background-color: var(--bg-card); border: 1px solid var(--border-color); border-radius: 16px; padding: 1.25rem; box-shadow: var(--shadow-sm); margin-bottom: 1.5rem; }
    .filtros-grid { display: flex; gap: 1rem; align-items: end; flex-wrap: wrap; }
    .campo label { display: block; font-size: 0.75rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.03em; margin-bottom: 0.35rem; }
    input[type="date"] { padding: 0.5rem 0.65rem; border-radius: 8px; border: 1px solid var(--border-color); background-color: var(--bg-page); color: var(--text-main); font-family: inherit; font-size: 0.85rem; }
    .btn { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.6rem 1.1rem; border-radius: 10px; font-weight: 600; font-size: 0.875rem; text-decoration: none; border: 1px solid transparent; cursor: pointer; }
    .btn-primary { background-color: var(--primary); color: white; }
    .btn-secundario { background-color: var(--bg-page); border: 1px solid var(--border-color); color: var(--text-main); }
    .back-link { display: inline-flex; align-items: center; gap: 0.4rem; color: var(--text-muted); text-decoration: none; font-size: 0.875rem; font-weight: 500; margin-bottom: 1rem; }
    .back-link:hover { color: var(--primary); }

    .chart-card { background-color: var(--bg-card); border: 1px solid var(--border-color); border-radius: 20px; padding: 1.5rem; box-shadow: var(--shadow-sm); margin-bottom: 1.5rem; }
    .chart-card h2 { font-size: 1rem; font-weight: 600; margin-bottom: 0.75rem; }
    .totales { display: flex; gap: 1.5rem; margin-bottom: 1rem; }
    .total-item { font-size: 0.9rem; }
    .total-item strong { color: var(--primary); }

    .subnav-tabs { display: flex; gap: 0.5rem; flex-wrap: wrap; margin-bottom: 1.25rem; }
    .subnav-tab { padding: 0.5rem 1rem; border-radius: 8px; background-color: var(--bg-page); border: 1px solid var(--border-color); font-size: 0.8rem; font-weight: 600; color: var(--text-main); cursor: pointer; }
    .subnav-tab.active { background-color: var(--primary); border-color: var(--primary); color: white; }
    .tab-pane { display: none; }
    .tab-pane.active { display: block; }
</style>
@endpush

@section('content')
    <a class="back-link" href="{{ route('reportes.index') }}">&larr; Volver a Reportes</a>

    <form class="filtros-card" method="GET" action="{{ route('reportes.ingresosRenuncias') }}">
        <div class="filtros-grid">
            <div class="campo">
                <label for="fecha_inicio">Fecha Inicio</label>
                <input type="date" name="fecha_inicio" id="fecha_inicio" value="{{ $fechaInicio }}">
            </div>
            <div class="campo">
                <label for="fecha_fin">Fecha Fin</label>
                <input type="date" name="fecha_fin" id="fecha_fin" value="{{ $fechaFin }}">
            </div>
            <div class="campo">
                <button type="submit" class="btn btn-primary">Actualizar</button>
            </div>
        </div>
    </form>

    <div class="chart-card">
        <h2>Gráfico Global de Contrataciones y Renuncias</h2>
        <div class="totales">
            <div class="total-item">Total Contrataciones: <strong>{{ array_sum($contratacionesGlobales) }}</strong></div>
            <div class="total-item">Total Renuncias: <strong>{{ array_sum($renunciasGlobales) }}</strong></div>
        </div>
        <canvas id="chartGlobal" height="90"></canvas>
    </div>

    <div class="chart-card">
        <h2>Por Sucursal</h2>
        @if($sucursales->count() > 0)
            <div class="subnav-tabs" id="sucursalTabs">
                @foreach($sucursales as $i => $fisica)
                    <div class="subnav-tab {{ $i === 0 ? 'active' : '' }}" onclick="mostrarTab({{ $i }})">{{ $fisica }}</div>
                @endforeach
            </div>
            @foreach($sucursales as $i => $fisica)
                <div class="tab-pane {{ $i === 0 ? 'active' : '' }}" id="tab{{ $i }}">
                    <div class="totales">
                        <div class="total-item">Contrataciones: <strong>{{ array_sum($contratacionesPorFecha[$fisica] ?? []) }}</strong></div>
                        <div class="total-item">Renuncias: <strong>{{ array_sum($renunciasPorFecha[$fisica] ?? []) }}</strong></div>
                    </div>
                    <canvas id="chart{{ $i }}" height="90"></canvas>
                </div>
            @endforeach
        @else
            <p>No hay datos de sucursales.</p>
        @endif
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const contratacionesPorFecha = @json($contratacionesPorFecha);
    const renunciasPorFecha = @json($renunciasPorFecha);
    const sucursales = @json($sucursales);

    function lineChart(canvasId, labels, dataA, dataB) {
        return new Chart(document.getElementById(canvasId), {
            type: 'line',
            data: {
                labels,
                datasets: [
                    { label: 'Contrataciones', data: dataA, borderColor: 'rgba(75,192,192,1)', backgroundColor: 'rgba(75,192,192,0.2)', tension: 0.1 },
                    { label: 'Renuncias', data: dataB, borderColor: 'rgba(255,99,132,1)', backgroundColor: 'rgba(255,99,132,0.2)', tension: 0.1 },
                ]
            },
            options: { responsive: true }
        });
    }

    // Global
    const fechasGlobales = [...new Set([
        ...Object.values(contratacionesPorFecha).flatMap(o => Object.keys(o)),
        ...Object.values(renunciasPorFecha).flatMap(o => Object.keys(o)),
    ])].sort();

    const contratacionesGlobales = fechasGlobales.map(f => sucursales.reduce((s, suc) => s + (contratacionesPorFecha[suc]?.[f] || 0), 0));
    const renunciasGlobales = fechasGlobales.map(f => sucursales.reduce((s, suc) => s + (renunciasPorFecha[suc]?.[f] || 0), 0));
    lineChart('chartGlobal', fechasGlobales, contratacionesGlobales, renunciasGlobales);

    // Por sucursal
    const chartsPorSucursal = [];
    sucursales.forEach((fisica, i) => {
        const cObj = contratacionesPorFecha[fisica] || {};
        const rObj = renunciasPorFecha[fisica] || {};
        const fechas = [...new Set([...Object.keys(cObj), ...Object.keys(rObj)])].sort();
        chartsPorSucursal[i] = lineChart('chart' + i, fechas, fechas.map(f => cObj[f] || 0), fechas.map(f => rObj[f] || 0));
    });

    function mostrarTab(i) {
        document.querySelectorAll('.tab-pane').forEach(el => el.classList.remove('active'));
        document.querySelectorAll('#sucursalTabs .subnav-tab').forEach(el => el.classList.remove('active'));
        document.getElementById('tab' + i).classList.add('active');
        document.querySelectorAll('#sucursalTabs .subnav-tab')[i].classList.add('active');
        // Chart.js dibuja mal en canvas ocultos (display:none) al crearse; forzar resize al mostrar la pestaña
        chartsPorSucursal[i]?.resize();
    }
</script>
@endpush
