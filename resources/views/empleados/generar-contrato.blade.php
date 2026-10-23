{{-- Port de la pantalla de Generador_Docs del legacy (Determinado.php / GenerarDoc.php / Convenio.php).
     Mismo contenido que el legacy (las 4 consultas y "Descargar Contrato"), con el diseño de DRRHH. --}}
@extends('layouts.app')

@section('title', 'Generar ' . $titulo)
@section('page_title', 'Generador de Contratos')

@push('styles')
<style>
    .container { display: flex; flex-direction: column; gap: 1.5rem; max-width: 1000px; margin: 0 auto; width: 100%; }

    .card { background-color: var(--bg-card); border: 1px solid var(--border-color); border-radius: 20px; padding: 1.75rem; box-shadow: var(--shadow-sm); }
    h2 { font-size: 1.05rem; font-weight: 600; margin-bottom: 1rem; }

    /* Encabezado: tipo de contrato + acciones (equivale al <header> azul del legacy) */
    .encabezado { display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap; }
    .encabezado h1 { font-size: 1.4rem; font-weight: 700; margin: 0; }
    .encabezado p { font-size: 0.85rem; color: var(--text-muted); margin-top: 0.25rem; }
    .acciones { display: flex; gap: 0.75rem; flex-wrap: wrap; }

    /* Mensaje "No se encontraron datos" (mismo estilo que los errores del resto de la app) */
    .errores { background-color: rgba(220,38,38,0.1); border: 1px solid var(--baja); color: var(--baja); border-radius: 12px; padding: 1rem 1.25rem; font-size: 0.85rem; }

    /* Cada consulta se muestra en formato print_r, igual que el legacy (con scroll como su max-height: 300px) */
    .print-r { background-color: var(--bg-page); border: 1px solid var(--border-color); border-radius: 12px; padding: 1rem 1.25rem; font-family: Consolas, 'Courier New', monospace; font-size: 0.8rem; color: var(--text-main); max-height: 300px; overflow-y: auto; white-space: pre-wrap; word-wrap: break-word; margin: 0; }

    .btn { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.65rem 1.2rem; border-radius: 10px; font-weight: 600; font-size: 0.9rem; text-decoration: none; border: 1px solid transparent; cursor: pointer; }
    .btn-primary { background-color: var(--primary); color: white; }
    .btn-primary:hover { opacity: 0.9; }
    .btn-secundario { background-color: var(--bg-page); border: 1px solid var(--border-color); color: var(--text-main); }
</style>
@endpush

@section('content')
    <div class="container">
        {{-- Encabezado con el mismo título que el <h1> de cada generador del legacy --}}
        <div class="card encabezado">
            <div>
                <h1>{{ $titulo }}</h1>
                <p>Empleado {{ $n_empleado }}{{ !empty($personales['Nombre_Completo']) ? ' · ' . $personales['Nombre_Completo'] : '' }}</p>
            </div>
            <div class="acciones">
                {{-- "Volver" regresa al detalle del empleado (en el legacy iba a Detalles_Empleado.php) --}}
                <a href="{{ route('empleados.show', $n_empleado) }}" class="btn btn-secundario">Volver</a>
                {{-- Igual que el legacy: solo se puede descargar si las 4 consultas regresaron datos; abre en otra pestaña --}}
                @if($hayDatos)
                    <a href="{{ route($rutaDescarga, $n_empleado) }}" target="_blank" class="btn btn-primary">Descargar Contrato</a>
                @endif
            </div>
        </div>

        {{-- Mismo mensaje que el legacy cuando falta alguna de las 4 consultas --}}
        @unless($hayDatos)
            <div class="errores">No se encontraron datos para el empleado.</div>
        @endunless

        {{-- Las mismas 4 consultas y en el mismo orden que el legacy, con print_r (vacío si no hubo resultado) --}}
        @foreach(['Datos del empleado' => $personales, 'Datos laborales' => $laborales, 'Fechas laborales' => $fechas, 'Rutas de documentos' => $rutas] as $tituloBloque => $bloque)
            <div class="card">
                <h2>{{ $tituloBloque }}</h2>
                {{-- Fechas laborales es una lista (el legacy usa fetch_all), así que siempre imprime "Array ( )" aunque esté vacía --}}
                <pre class="print-r">{{ ($bloque || $tituloBloque === 'Fechas laborales') ? print_r($bloque, true) : '' }}</pre>
            </div>
        @endforeach
    </div>
@endsection
