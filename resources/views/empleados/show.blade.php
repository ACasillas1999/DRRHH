@extends('layouts.app')

@section('title', 'Expediente: ' . $empleado->Nombre_Completo)
@section('page_title', 'Expediente del Empleado')

@push('styles')
<!-- FullCalendar CSS -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css" rel="stylesheet" />
<style>
    /* Estilos de la nueva distribución */
    .show-layout-container {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
        animation: fadeIn 0.4s ease;
    }

    /* Banner de Perfil Principal */
    .employee-banner {
        background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
        color: white;
        border-radius: 20px;
        padding: 2.25rem 2rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1.5rem;
        flex-wrap: wrap;
        box-shadow: var(--shadow-md);
        position: relative;
    }

    .banner-left {
        display: flex;
        align-items: center;
        gap: 1.5rem;
    }

    .back-btn-float {
        position: absolute;
        top: 1rem;
        left: 1.25rem;
        background-color: rgba(255, 255, 255, 0.15);
        color: white;
        border: 1px solid rgba(255, 255, 255, 0.25);
        padding: 0.35rem 0.75rem;
        border-radius: 8px;
        font-size: 0.75rem;
        font-weight: 600;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 0.35rem;
        transition: all 0.2s ease;
    }

    .back-btn-float:hover {
        background-color: rgba(255, 255, 255, 0.25);
        transform: translateX(-2px);
    }

    .foto-container {
        position: relative;
        cursor: pointer;
    }

    .banner-foto {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        object-fit: cover;
        background-color: rgba(255,255,255,0.1);
        border: 3px solid rgba(255, 255, 255, 0.9);
        box-shadow: var(--shadow-md);
        transition: transform 0.2s ease;
    }

    .foto-container:hover .banner-foto {
        transform: scale(1.03);
    }

    .default-avatar-svg {
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: rgba(255, 255, 255, 0.2);
        color: rgba(255, 255, 255, 0.9);
    }

    .default-avatar-svg svg {
        width: 55px;
        height: 55px;
    }

    .banner-text h1 {
        font-size: 1.8rem;
        font-weight: 700;
        letter-spacing: -0.01em;
        text-transform: uppercase;
        margin-bottom: 0.25rem;
    }

    .banner-text p {
        font-size: 0.95rem;
        opacity: 0.9;
        margin-bottom: 0.35rem;
        text-transform: uppercase;
        font-weight: 500;
        letter-spacing: 0.02em;
    }

    .banner-text .emp-num-tag {
        font-size: 0.8rem;
        background-color: rgba(255, 255, 255, 0.15);
        padding: 0.25rem 0.5rem;
        border-radius: 6px;
        display: inline-block;
        font-weight: 600;
    }

    .banner-right {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 0.75rem;
    }

    .badge-banner {
        display: inline-block;
        padding: 0.4rem 1rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .badge-banner-activo {
        background-color: #22c55e;
        color: white;
    }

    .badge-banner-baja {
        background-color: #ef4444;
        color: white;
    }

    .btn-baja {
        background-color: #ef4444;
        color: white;
        border: none;
        padding: 0.55rem 1.25rem;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.85rem;
        cursor: pointer;
        transition: opacity 0.2s ease;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .btn-baja:hover {
        opacity: 0.9;
    }

    /* Fila de Contacto (Tres Columnas) */
    .contact-row-card {
        background-color: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 1.5rem 1.75rem;
        box-shadow: var(--shadow-sm);
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 2rem;
    }

    @media (max-width: 768px) {
        .contact-row-card {
            grid-template-columns: 1fr;
            gap: 1.25rem;
        }
    }

    .contact-col h3 {
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--text-muted);
        border-bottom: 2px solid var(--border-color);
        padding-bottom: 0.35rem;
        margin-bottom: 0.75rem;
    }

    .contact-col p {
        font-size: 0.875rem;
        margin-top: 0.35rem;
        color: var(--text-main);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .contact-col p strong {
        color: var(--text-muted);
        font-weight: 500;
    }

    /* Layout en Columnas Izquierda/Derecha */
    .show-grid {
        display: grid;
        grid-template-columns: 1.15fr 0.85fr;
        gap: 1.5rem;
        align-items: start;
    }

    @media (max-width: 1100px) {
        .show-grid {
            grid-template-columns: 1fr;
        }
    }

    /* Estructuras de Tarjeta Generales */
    .detail-card {
        background-color: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: var(--shadow-sm);
        margin-bottom: 1.5rem;
    }

    .detail-card h2 {
        font-size: 1.05rem;
        font-weight: 600;
        color: var(--text-main);
        border-bottom: 1px solid var(--border-color);
        padding-bottom: 0.5rem;
        margin-bottom: 1rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    /* Pestañas Datos Personales / Laborales */
    .tabs-header {
        display: flex;
        border: 1px solid var(--border-color);
        border-radius: 12px;
        overflow: hidden;
        margin-bottom: 1.25rem;
        background-color: var(--bg-page);
        padding: 4px;
        gap: 4px;
    }

    .tab-btn {
        flex: 1;
        padding: 0.6rem;
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--text-muted);
        cursor: pointer;
        background: none;
        border: none;
        border-radius: 8px;
        text-align: center;
        transition: all 0.2s ease;
    }

    .tab-btn.active {
        background-color: var(--primary);
        color: white;
        box-shadow: var(--shadow-sm);
    }

    .tab-pane {
        display: none;
    }

    .tab-pane.active {
        display: block;
    }

    /* Filas de Información Vertical */
    .vertical-details {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .detail-row {
        display: grid;
        grid-template-columns: 190px 1fr;
        align-items: center;
        gap: 1rem;
        border-bottom: 1px solid var(--primary-glow);
        padding-bottom: 0.4rem;
    }

    @media (max-width: 500px) {
        .detail-row {
            grid-template-columns: 1fr;
            gap: 0.25rem;
        }
    }

    .detail-label {
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: var(--text-muted);
    }

    .detail-value {
        font-size: 0.875rem;
        color: var(--text-main);
        padding: 0.35rem 0.65rem;
        background-color: var(--bg-page);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        min-height: 1.85rem;
        display: flex;
        align-items: center;
    }

    .btn-update-section {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        background-color: var(--primary);
        color: white;
        border: none;
        padding: 0.65rem 1.2rem;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.85rem;
        text-decoration: none;
        margin-top: 1.25rem;
        transition: opacity 0.2s ease;
        align-self: flex-start;
    }

    .btn-update-section:hover {
        opacity: 0.9;
    }

    /* Tablas Premium */
    .table-wrap {
        overflow-x: auto;
        border-radius: 10px;
        border: 1px solid var(--border-color);
        background-color: var(--bg-card);
    }

    .premium-table {
        width: 100%;
        border-collapse: collapse;
    }

    .premium-table thead th {
        text-align: left;
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--text-muted);
        padding: 0.75rem;
        background-color: var(--bg-page);
        border-bottom: 1px solid var(--border-color);
        white-space: nowrap;
    }

    .premium-table tbody td {
        padding: 0.65rem 0.75rem;
        border-bottom: 1px solid var(--border-color);
        font-size: 0.85rem;
        color: var(--text-main);
        white-space: nowrap;
    }

    .premium-table tbody tr:last-child td {
        border-bottom: none;
    }

    .vacio-row {
        color: var(--text-muted);
        font-size: 0.85rem;
        text-align: center;
        padding: 1.25rem !important;
    }

    /* Acciones y Enlaces */
    .doc-link {
        color: var(--primary);
        text-decoration: none;
        font-weight: 600;
        font-size: 0.85rem;
    }

    .doc-link:hover {
        text-decoration: underline;
    }

    .action-btn-group {
        display: flex;
        gap: 0.5rem;
        align-items: center;
    }

    .action-btn {
        padding: 0.4rem 0.75rem;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
        text-decoration: none;
        cursor: pointer;
        border: 1px solid transparent;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        transition: all 0.2s ease;
    }

    .action-btn-generate {
        background-color: var(--primary-light);
        color: var(--primary);
    }

    .action-btn-generate:hover {
        background-color: var(--primary);
        color: white;
    }

    .action-btn-upload {
        background-color: var(--bg-page);
        border-color: var(--border-color);
        color: var(--text-main);
    }

    .action-btn-upload:hover {
        background-color: var(--bg-card-hover);
        border-color: var(--text-muted);
    }

    .action-btn-download {
        background-color: rgba(22, 163, 74, 0.1);
        color: #16a34a;
    }

    .action-btn-download:hover {
        background-color: #16a34a;
        color: white;
    }

    .action-btn-edit {
        background-color: rgba(217, 119, 6, 0.1);
        color: #d97706;
    }

    .action-btn-edit:hover {
        background-color: #d97706;
        color: white;
    }

    /* Botones de Documentos de Historial de Pagos */
    .pagos-btn-row {
        display: flex;
        gap: 0.75rem;
        margin-top: 1rem;
        flex-wrap: wrap;
    }

    .btn-pago-doc {
        padding: 0.5rem 1rem;
        background-color: var(--primary-light);
        color: var(--primary);
        border: 1px solid rgba(29, 78, 216, 0.15);
        font-size: 0.8rem;
        font-weight: 600;
        text-decoration: none;
        border-radius: 8px;
        transition: all 0.2s ease;
    }

    .btn-pago-doc:hover {
        background-color: var(--primary);
        color: white;
        box-shadow: var(--shadow-sm);
    }

    /* Estilos del Calendario */
    #employee-calendar {
        background-color: var(--bg-card);
        font-family: inherit;
    }

    .fc .fc-toolbar-title {
        font-size: 1rem !important;
        font-weight: 600 !important;
    }

    .fc .fc-button {
        padding: 0.25rem 0.5rem !important;
        font-size: 0.75rem !important;
        font-weight: 600 !important;
        border-radius: 6px !important;
    }

    .fc-theme-standard td, .fc-theme-standard th {
        border-color: var(--border-color) !important;
    }

    .fc .fc-col-header-cell-cushion {
        font-size: 0.7rem !important;
        text-transform: uppercase !important;
        font-weight: 700 !important;
        color: var(--text-muted) !important;
    }

    .fc .fc-daygrid-day-number {
        font-size: 0.75rem !important;
        font-weight: 500 !important;
        color: var(--text-main) !important;
    }

    .fc .fc-event {
        font-size: 0.7rem !important;
        padding: 1px 3px !important;
        border-radius: 4px !important;
        font-weight: 500 !important;
        border: none !important;
    }

    /* Ventanas Modales */
    .modal-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background-color: rgba(15, 23, 42, 0.6);
        backdrop-filter: blur(4px);
        align-items: center;
        justify-content: center;
        z-index: 200;
        padding: 1rem;
    }

    .modal-box {
        background-color: var(--bg-card);
        border-radius: 16px;
        padding: 1.75rem;
        max-width: 450px;
        width: 100%;
        border: 1px solid var(--border-color);
        box-shadow: var(--shadow-lg);
        animation: modalFadeIn 0.3s ease;
    }

    @keyframes modalFadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .modal-box h2 {
        font-size: 1.15rem;
        margin-bottom: 0.5rem;
        color: var(--text-main);
    }

    .modal-box select, .modal-box input[type="date"], .modal-box input[type="text"], .modal-box input[type="file"] {
        width: 100%;
        padding: 0.55rem 0.75rem;
        border-radius: 8px;
        border: 1px solid var(--border-color);
        background-color: var(--bg-page);
        color: var(--text-main);
        font-family: inherit;
        font-size: 0.85rem;
        margin-top: 0.25rem;
    }

    .btn-primary-modal {
        background-color: var(--primary);
        color: white;
        border: none;
        padding: 0.6rem 1.1rem;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.85rem;
        cursor: pointer;
        transition: opacity 0.2s ease;
    }

    .btn-primary-modal:hover {
        opacity: 0.9;
    }

    .btn-cancelar {
        background-color: var(--bg-page);
        border: 1px solid var(--border-color);
        color: var(--text-main);
        padding: 0.6rem 1.1rem;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.85rem;
        cursor: pointer;
        transition: background-color 0.2s ease;
    }

    .btn-cancelar:hover {
        background-color: var(--bg-card-hover);
    }

    /* Modal de Vista Previa de Documento (Pantalla Dividida) */
    .preview-modal-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background-color: rgba(15, 23, 42, 0.65);
        backdrop-filter: blur(6px);
        align-items: center;
        justify-content: center;
        z-index: 250;
        padding: 1.5rem;
    }

    .preview-modal-box {
        background-color: var(--bg-card);
        border-radius: 20px;
        border: 1px solid var(--border-color);
        box-shadow: var(--shadow-lg);
        width: 100%;
        max-width: 1200px;
        height: 90vh;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        animation: modalFadeIn 0.3s ease;
    }

    .preview-modal-header {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        justify-content: space-between;
        align-items: center;
        background-color: var(--bg-card);
    }

    .preview-modal-header h2 {
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--text-main);
        margin: 0;
    }

    .preview-modal-body {
        display: grid;
        grid-template-columns: 380px 1fr;
        flex: 1;
        overflow: hidden;
    }

    /* Columna de Formulario (Izquierda) */
    .preview-form-col {
        padding: 1.5rem;
        border-right: 1px solid var(--border-color);
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
        background-color: var(--bg-page);
    }

    /* Columna de Hoja de Papel (Derecha) */
    .preview-sheet-col {
        padding: 2.5rem;
        overflow-y: auto;
        background-color: #e2e8f0;
        display: flex;
        justify-content: center;
        align-items: flex-start;
    }

    .paper-sheet {
        background-color: white;
        color: #334155;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
        width: 100%;
        max-width: 800px;
        min-height: 1000px;
        padding: 4rem 3.5rem;
        font-family: 'Inter', 'Outfit', 'Georgia', serif;
        font-size: 0.875rem;
        line-height: 1.6;
        border-radius: 4px;
        position: relative;
        box-sizing: border-box;
    }

    .paper-sheet h1, .paper-sheet h2, .paper-sheet h3 {
        color: #0f172a;
        font-weight: 700;
        text-align: center;
        margin-bottom: 1.5rem;
    }

    /* Highlight style for real-time tags */
    .preview-tag {
        background-color: rgba(59, 130, 246, 0.08);
        color: #1d4ed8;
        padding: 2px 6px;
        border-radius: 4px;
        font-weight: 600;
        border-bottom: 1px dashed #3b82f6;
        display: inline-block;
        min-width: 20px;
        transition: all 0.2s ease;
    }

    .preview-tag.updated {
        background-color: rgba(34, 197, 94, 0.12);
        color: #15803d;
        border-bottom-color: #22c55e;
    }

    .preview-form-col .campo {
        display: flex;
        flex-direction: column;
        gap: 0.35rem;
    }

    .preview-form-col input, .preview-form-col select, .preview-form-col textarea {
        border-radius: 8px !important;
        border: 1px solid var(--border-color) !important;
        padding: 0.6rem 0.75rem !important;
        background-color: var(--bg-card) !important;
        color: var(--text-main) !important;
        outline: none !important;
        box-sizing: border-box !important;
        width: 100% !important;
        font-family: inherit !important;
        font-size: 0.875rem !important;
        transition: border-color 0.2s ease !important;
    }

    .preview-form-col input:focus, .preview-form-col select:focus, .preview-form-col textarea:focus {
        border-color: var(--primary) !important;
    }
</style>
@endpush

@section('content')
<div class="show-layout-container">

    @if (session('status'))
        <div class="detail-card" style="border-left: 5px solid #22c55e; color: #16a34a; font-weight: 600; padding: 1rem 1.5rem;">
            {{ session('status') }}
        </div>
    @endif

    
    {{-- Muestra el primer error de validación (ej. extensión no permitida al subir un contrato) --}}
    @if ($errors->any())
        <div class="detail-card" style="border-left: 5px solid #ef4444; color: #dc2626; font-weight: 600; padding: 1rem 1.5rem;">
            {{ $errors->first() }}
        </div>
    @endif


    {{-- 1. ENCABEZADO TIPO BANNER --}}
    <div class="employee-banner">
        <a href="{{ route('empleados.index') }}" class="back-btn-float">
            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            Volver
        </a>

        <div class="banner-left">
            <div class="foto-container" onclick="openPhotoModal()" title="Ver o cambiar foto de perfil">
                @if(!empty($rutaDocumento?->Ruta_Foto_Empleado))
                    <img id="bannerProfileImg" class="banner-foto" src="{{ \App\Support\LegacyFile::url($rutaDocumento->Ruta_Foto_Empleado) }}" alt="Foto de perfil" onerror="handleImageError(this, 'bannerDefaultAvatar')">
                    <div id="bannerDefaultAvatar" class="banner-foto default-avatar-svg" style="display:none;">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                            <path fill-rule="evenodd" d="M7.5 6a4.5 4.5 0 119 0 4.5 4.5 0 01-9 0zM3.751 20.105a8.25 8.25 0 0116.498 0 .75.75 0 01-.437.695A18.683 18.683 0 0112 22.5c-2.786 0-5.433-.608-7.812-1.7a.75.75 0 01-.437-.695z" clip-rule="evenodd" />
                        </svg>
                    </div>
                @else
                    <div class="banner-foto default-avatar-svg">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                            <path fill-rule="evenodd" d="M7.5 6a4.5 4.5 0 119 0 4.5 4.5 0 01-9 0zM3.751 20.105a8.25 8.25 0 0116.498 0 .75.75 0 01-.437.695A18.683 18.683 0 0112 22.5c-2.786 0-5.433-.608-7.812-1.7a.75.75 0 01-.437-.695z" clip-rule="evenodd" />
                        </svg>
                    </div>
                @endif
            </div>
            <form id="formFoto" action="{{ route('empleados.foto', $empleado->N_Empleado) }}" method="POST" enctype="multipart/form-data" style="display:none;">
                @csrf
                <input id="fotoInput" type="file" name="foto_perfil" accept="image/*" onchange="document.getElementById('formFoto').submit()">
            </form>
            <div class="banner-text">
                <h1>{{ $empleado->Nombre_Completo }}</h1>
                <p>{{ $laboral->puesto->Departamento ?? 'Sin Departamento' }} — {{ $laboral->puesto->Nombre_Puesto ?? 'Sin Puesto' }}</p>
                <span class="emp-num-tag">N° Empleado: {{ $empleado->N_Empleado }}</span>
            </div>
        </div>

        <div class="banner-right">
            <span class="badge-banner {{ $laboral->Estatus === 'ACTIVO' ? 'badge-banner-activo' : 'badge-banner-baja' }}">
                {{ $laboral->Estatus }}
            </span>

            @if ($laboral->Estatus === 'ACTIVO')
                <button type="button" class="btn-baja" onclick="document.getElementById('modalBaja').style.display='flex'">
                    Dar de Baja
                </button>
            @endif
        </div>

        @if ($laboral->Estatus === 'BAJA')
            @php
                $ultimaFecha = $fechasLaborales->first();
            @endphp
            <div style="width: 100%; border-top: 1px solid rgba(255,255,255,0.2); margin-top: 1rem; padding-top: 0.75rem; font-size: 0.85rem; opacity: 0.9;">
                @if ($ultimaFecha && $ultimaFecha->Fecha_Egreso)
                    <span><strong>Fecha de Egreso:</strong> {{ \Illuminate\Support\Carbon::parse($ultimaFecha->Fecha_Egreso)->format('d/m/Y') }} &middot; </span>
                @endif
                @if ($ultimaFecha && $ultimaFecha->motivo_baja)
                    <span><strong>Motivo:</strong> {{ $ultimaFecha->motivo_baja }} &middot; </span>
                @endif
                @if ($ultimaFecha && $ultimaFecha->recontratable)
                    <span><strong>Recontratable:</strong> {{ $ultimaFecha->recontratable }} &middot; </span>
                @endif
                @if ($ultimaFecha && $ultimaFecha->comentario)
                    <span><strong>Comentarios:</strong> {{ $ultimaFecha->comentario }}</span>
                @endif
            </div>
        @endif
    </div>

    {{-- 2. FILA DE CONTACTO (TRES COLUMNARIOS) --}}
    <div class="contact-row-card">
        <div class="contact-col">
            <h3>Personal</h3>
            <p><strong>Celular:</strong> {{ $empleado->Celular ?: 'Sin Celular' }}</p>
            <p><strong>Correo:</strong> {{ $empleado->Correo_Electronico ?: 'Sin Correo' }}</p>
        </div>
        <div class="contact-col">
            <h3>Empresarial</h3>
            <p><strong>Teléfono:</strong> {{ $laboral->Telefono_Empresarial ?: 'Sin Teléfono' }}</p>
            <p><strong>Correo:</strong> {{ $laboral->Correo_Empresarial ?: 'Sin Correo' }}</p>
            <p><strong>Extensión:</strong> {{ $laboral->Extension ?: 'Sin Extensión' }}</p>
        </div>
        <div class="contact-col">
            <h3>Team Building</h3>
            <p><strong>Reclutador:</strong> {{ $laboral->Reclutador ?: 'N/D' }}</p>
            <p><strong>Entrevista Operativa:</strong> {{ $laboral->Entrevista ?: 'N/D' }}</p>
            <p><strong>Asesor de Ingreso:</strong> {{ $laboral->Asesor_Ingreso ?: 'N/D' }}</p>
        </div>
    </div>

    {{-- 3. GRID PRINCIPAL (DOS COLUMNAS) --}}
    <div class="show-grid">

        {{-- COLUMNA IZQUIERDA --}}
        <div class="left-column">

            <div class="detail-card" style="padding-top: 0.5rem;">
                <div class="tabs-header">
                    <button class="tab-btn active" onclick="switchSectionTab('secPersonales', this)">Datos Personales</button>
                    <button class="tab-btn" onclick="switchSectionTab('secLaborales', this)">Datos Laborales</button>
                    <button class="tab-btn" onclick="switchSectionTab('secDocPersonales', this)">Documentos Personales</button>
                    <button class="tab-btn" onclick="switchSectionTab('secDocLaborales', this)">Documentos Laborales</button>
                </div>

                {{-- Sección: Personales --}}
                <div id="secPersonales" class="tab-pane active">
                    <div class="vertical-details">
                        <div class="detail-row"><span class="detail-label">Número Empleado:</span><span class="detail-value">{{ $empleado->N_Empleado }}</span></div>
                        <div class="detail-row"><span class="detail-label">Estatus:</span><span class="detail-value">{{ $laboral->Estatus }}</span></div>
                        <div class="detail-row"><span class="detail-label">Nombre Completo:</span><span class="detail-value">{{ $empleado->Nombre_Completo }}</span></div>
                        <div class="detail-row"><span class="detail-label">IMSS:</span><span class="detail-value">{{ $empleado->IMSS ?: '—' }}</span></div>
                        <div class="detail-row"><span class="detail-label">CURP:</span><span class="detail-value">{{ $empleado->CURP ?: '—' }}</span></div>
                        <div class="detail-row"><span class="detail-label">RFC:</span><span class="detail-value">{{ $empleado->RFC ?: '—' }}</span></div>
                        <div class="detail-row"><span class="detail-label">Sexo:</span><span class="detail-value">{{ $empleado->Sexo ?: '—' }}</span></div>
                        <div class="detail-row"><span class="detail-label">Fecha Nacimiento:</span><span class="detail-value">{{ $empleado->Fecha_Nacimiento ? \Illuminate\Support\Carbon::parse($empleado->Fecha_Nacimiento)->format('d/m/Y') : '—' }}</span></div>
                        <div class="detail-row"><span class="detail-label">Edad:</span><span class="detail-value">{{ $empleado->Edad ?: '—' }}</span></div>
                        <div class="detail-row"><span class="detail-label">Ciudad:</span><span class="detail-value">{{ $empleado->Ciudad ?: '—' }}</span></div>
                        <div class="detail-row"><span class="detail-label">Estado:</span><span class="detail-value">{{ $empleado->Estado ?: '—' }}</span></div>
                        <div class="detail-row"><span class="detail-label">Domicilio:</span><span class="detail-value">{{ $empleado->Domicilio ?: '—' }}</span></div>
                        <div class="detail-row"><span class="detail-label">Estado Civil:</span><span class="detail-value">{{ $empleado->Estado_Civil ?: '—' }}</span></div>
                        <div class="detail-row"><span class="detail-label">Teléfono:</span><span class="detail-value">{{ $empleado->Telefono ?: '—' }}</span></div>
                        <div class="detail-row"><span class="detail-label">Celular:</span><span class="detail-value">{{ $empleado->Celular ?: '—' }}</span></div>
                        <div class="detail-row"><span class="detail-label">Correo Electrónico:</span><span class="detail-value">{{ $empleado->Correo_Electronico ?: '—' }}</span></div>

                        <h3 style="font-size: 0.95rem; font-weight: 600; margin: 1.5rem 0 0.75rem 0; border-bottom: 1px solid var(--border-color); padding-bottom: 0.35rem;">Beneficiario</h3>
                        <div class="detail-row"><span class="detail-label">Contacto de Emergencia:</span><span class="detail-value">{{ $empleado->Contacto_Emergencia ?: '—' }}</span></div>
                        <div class="detail-row"><span class="detail-label">Teléfono de Emergencia:</span><span class="detail-value">{{ $empleado->Tel_Emergencia ?: '—' }}</span></div>
                        <div class="detail-row"><span class="detail-label">Parentesco:</span><span class="detail-value">{{ $empleado->Parentesco_B ?: '—' }}</span></div>
                        <div class="detail-row"><span class="detail-label">Nombre Completo:</span><span class="detail-value">{{ $empleado->Nombre_Completo_B ?: '—' }}</span></div>
                        <div class="detail-row"><span class="detail-label">Domicilio:</span><span class="detail-value">{{ $empleado->Domicilio_B ?: '—' }}</span></div>
                        <div class="detail-row"><span class="detail-label">Teléfono:</span><span class="detail-value">{{ $empleado->Telefono_B ?: '—' }}</span></div>
                        <div class="detail-row"><span class="detail-label">Fecha Nacimiento:</span><span class="detail-value">{{ $empleado->F_NAC_B ? \Illuminate\Support\Carbon::parse($empleado->F_NAC_B)->format('d/m/Y') : '—' }}</span></div>
                    </div>
                    <div style="display: flex;">
                        <a href="{{ route('empleados.personales.edit', $empleado->N_Empleado) }}" class="btn-update-section">
                            Actualizar Personales
                        </a>
                    </div>
                </div>

                {{-- Sección: Laborales --}}
                <div id="secLaborales" class="tab-pane">
                    <div class="vertical-details">
                        <div class="detail-row"><span class="detail-label">Departamento:</span><span class="detail-value">{{ $laboral->puesto->Departamento ?? '—' }}</span></div>
                        <div class="detail-row"><span class="detail-label">Puesto:</span><span class="detail-value">{{ $laboral->puesto->Nombre_Puesto ?? '—' }}</span></div>
                        <div class="detail-row"><span class="detail-label">Nomenclatura:</span><span class="detail-value">{{ $laboral->Nomeclatura ?: '—' }}</span></div>
                        <div class="detail-row"><span class="detail-label">Física:</span><span class="detail-value">{{ $laboral->Fisica ?: '—' }}</span></div>
                        <div class="detail-row"><span class="detail-label">Salario Diario Bruto:</span><span class="detail-value">{{ is_numeric($laboral->Salario_Diario) ? '$' . number_format($laboral->Salario_Diario, 2) : ($laboral->Salario_Diario ?: '—') }}</span></div>
                        <div class="detail-row"><span class="detail-label">Sueldo Bruto:</span><span class="detail-value">{{ is_numeric($laboral->Sueldo) ? '$' . number_format($laboral->Sueldo, 2) : ($laboral->Sueldo ?: '—') }}</span></div>
                        <div class="detail-row"><span class="detail-label">Salario Diario Neto:</span><span class="detail-value">{{ is_numeric($laboral->Salario_Diario_Neto) ? '$' . number_format($laboral->Salario_Diario_Neto, 2) : ($laboral->Salario_Diario_Neto ?: '—') }}</span></div>
                        <div class="detail-row"><span class="detail-label">Sueldo Neto:</span><span class="detail-value">{{ is_numeric($laboral->Sueldo_Neto) ? '$' . number_format($laboral->Sueldo_Neto, 2) : ($laboral->Sueldo_Neto ?: '—') }}</span></div>
                        <div class="detail-row"><span class="detail-label">Compensación:</span><span class="detail-value">{{-- Compensación (columna Hora_Comida): se muestra tal cual como el legacy, incluido el 0; solo vacío/null muestra — --}}{{ ($laboral->Hora_Comida ?? '') !== '' ? $laboral->Hora_Comida : '—' }}</span></div>
                        <div class="detail-row"><span class="detail-label">Efectivo:</span><span class="detail-value">{{ is_numeric($laboral->Efectivo) ? '$' . number_format($laboral->Efectivo, 2) : ($laboral->Efectivo ?: '—') }}</span></div>
                        <div class="detail-row"><span class="detail-label">Comisión:</span><span class="detail-value">{{ is_numeric($laboral->Comision) ? '$' . number_format($laboral->Comision, 2) : ($laboral->Comision ?: '—') }}</span></div>
                        <div class="detail-row"><span class="detail-label">KPI:</span><span class="detail-value">{{ is_numeric($laboral->KPI) ? '$' . number_format($laboral->KPI, 2) : ($laboral->KPI ?: '—') }}</span></div>
                        <div class="detail-row"><span class="detail-label">Horario:</span><span class="detail-value">{{ $laboral->Horario ?: '—' }}</span></div>
                    </div>
                    <div style="display: flex;">
                        <a href="{{ route('empleados.laborales.edit', $laboral->N_Empleado) }}" class="btn-update-section">
                            Actualizar Laborales
                        </a>
                    </div>
                </div>

                {{-- Sección: Documentos Personales --}}
                <div id="secDocPersonales" class="tab-pane">
                    <div class="table-wrap">
                        <table class="premium-table">
                            <thead>
                                <tr>
                                    <th>Documento</th>
                                    <th>Estado</th>
                                    <th>Fecha de Subida</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($documentosEmpleado as $doc)
                                    <tr>
                                        <td class="font-semibold">{{ $doc->Tipo_Documento }}</td>
                                        <td>
                                            @if($doc->Ruta_Documento)
                                                <span class="badge-banner badge-banner-activo" style="padding: 0.25rem 0.6rem; font-size: 0.7rem;">Entregado</span>
                                            @else
                                                <span class="badge-banner badge-banner-baja" style="padding: 0.25rem 0.6rem; font-size: 0.7rem; background-color: #94a3b8;">Pendiente</span>
                                            @endif
                                        </td>
                                        <td>{{ $doc->Fecha_Subida ? \Illuminate\Support\Carbon::parse($doc->Fecha_Subida)->format('d/m/Y H:i') : '—' }}</td>
                                        <td>
                                            <div class="action-btn-group">
                                                @if($doc->Ruta_Documento)
                                                    <a class="action-btn action-btn-download" href="{{ $doc->Ruta_Documento }}" target="_blank" title="Ver documento">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                                            <circle cx="12" cy="12" r="3"></circle>
                                                        </svg>
                                                    </a>
                                                @endif
                                                <button type="button" class="action-btn action-btn-upload" onclick="openExpedienteUploadModal('personal', {{ $doc->ID }}, '{{ $doc->Tipo_Documento }}')" title="Subir archivo">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                                        <polyline points="17 8 12 3 7 8"></polyline>
                                                        <line x1="12" y1="3" x2="12" y2="15"></line>
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="vacio-row">No se encontraron documentos personales registrados.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Sección: Documentos Laborales --}}
                <div id="secDocLaborales" class="tab-pane">
                    <div class="table-wrap">
                        <table class="premium-table">
                            <thead>
                                <tr>
                                    <th>Documento</th>
                                    <th>Estado</th>
                                    <th>Fecha de Subida</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($documentosLaborales as $doc)
                                    <tr>
                                        <td class="font-semibold">{{ $doc->Tipo_Documento }}</td>
                                        <td>
                                            @if($doc->Ruta_Documento)
                                                <span class="badge-banner badge-banner-activo" style="padding: 0.25rem 0.6rem; font-size: 0.7rem;">Entregado</span>
                                            @else
                                                <span class="badge-banner badge-banner-baja" style="padding: 0.25rem 0.6rem; font-size: 0.7rem; background-color: #94a3b8;">Pendiente</span>
                                            @endif
                                        </td>
                                        <td>{{ $doc->Fecha_Subida ? \Illuminate\Support\Carbon::parse($doc->Fecha_Subida)->format('d/m/Y H:i') : '—' }}</td>
                                        <td>
                                            <div class="action-btn-group">
                                                @if($doc->Ruta_Documento)
                                                    <a class="action-btn action-btn-download" href="{{ $doc->Ruta_Documento }}" target="_blank" title="Ver documento">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                                            <circle cx="12" cy="12" r="3"></circle>
                                                        </svg>
                                                    </a>
                                                @endif
                                                <button type="button" class="action-btn action-btn-upload" onclick="openExpedienteUploadModal('laboral', {{ $doc->ID }}, '{{ $doc->Tipo_Documento }}')" title="Subir archivo">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                                        <polyline points="17 8 12 3 7 8"></polyline>
                                                        <line x1="12" y1="3" x2="12" y2="15"></line>
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="vacio-row">No se encontraron documentos laborales registrados.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Ficha Nómina --}}
            <div class="detail-card">
                <h2>Ficha Nómina</h2>
                <div class="table-wrap">
                    <table class="premium-table">
                        <thead>
                            <tr>
                                <th>Tipo de Documento</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="font-semibold">Ficha Nómina</td>
                                <td>
                                    <div class="action-btn-group">
                                        <button type="button" class="action-btn action-btn-generate" onclick="openDocumentPreview('ficha_nomina', 'Ficha Nómina', '{{ route('empleados.documentos.fichaNomina', $empleado->N_Empleado) }}')">
                                            Generar Ficha
                                        </button>
                                        <button type="button" class="action-btn action-btn-upload" onclick="openUploadModal('Ficha Nomina')">
                                            Subir Documento
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Vacaciones --}}
            @php
                $primeraFecha = $fechasLaborales->sortBy('Fecha_Ingreso')->first();
            @endphp
            <div class="detail-card">
                <h2>Vacaciones</h2>
                <div class="table-wrap" style="margin-bottom: 1.25rem;">
                    <table class="premium-table">
                        <thead>
                            <tr>
                                <th>N° Emp</th>
                                <th>Nombre Completo</th>
                                <th>Aniversario Laboral</th>
                                <th>Física</th>
                                <th>Saldo Inicial</th>
                                <th>Saldo Actual</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>{{ $empleado->N_Empleado }}</td>
                                <td class="font-semibold">{{ $empleado->Nombre_Completo }}</td>
                                <td>{{ $primeraFecha->Fecha_Ingreso ?? '—' }}</td>
                                <td>{{ $laboral->Fisica ?: '—' }}</td>
                                <td><strong>{{ $laboral->Dias_Vacaciones_Iniciales ?? '0' }}</strong></td>
                                <td><strong style="color: var(--primary);">{{ $laboral->Dias_Vacaciones_Disponibles ?? '0' }}</strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <h3 style="font-size: 0.9rem; font-weight: 600; color: var(--text-muted); margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 0.03em;">Solicitudes</h3>
                <div class="table-wrap">
                    <table class="premium-table">
                        <thead>
                            <tr>
                                <th>Concepto</th>
                                <th>Estatus</th>
                                <th>Fecha Inicial</th>
                                <th>Fecha Final</th>
                                <th>Tomadas</th>
                                <th>Saldo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($vacacionesHistorial as $vac)
                                <tr>
                                    <td>{{ $vac->Concepto ?: '—' }}</td>
                                    <td>
                                        <span class="status-badge {{ $vac->Estatus === 'APROBADO' ? 'badge-on' : 'badge-off' }}">
                                            {{ $vac->Estatus }}
                                        </span>
                                    </td>
                                    <td>{{ $vac->Fecha_Inicial ?: '—' }}</td>
                                    <td>{{ $vac->Fecha_Final ?: '—' }}</td>
                                    <td>{{ $vac->Tomadas ?: '—' }}</td>
                                    <td>{{ $vac->Saldo ?: '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="vacio-row">No se encontraron solicitudes de vacaciones.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Incapacidades --}}
            <div class="detail-card">
                <h2>Incapacidades</h2>
                <div class="table-wrap">
                    <table class="premium-table">
                        <thead>
                            <tr>
                                <th>Descripción</th>
                                <th>Fecha Inicio</th>
                                <th>Fecha Fin</th>
                                <th>Total Días</th>
                                <th>Documento</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($incapacidades as $inc)
                                <tr>
                                    <td class="font-semibold">{{ $inc->Descripcion ?: '—' }}</td>
                                    <td>{{ $inc->Fecha_Inicio ?: '—' }}</td>
                                    <td>{{ $inc->Fecha_Fin ?: '—' }}</td>
                                    <td>{{ $inc->Total_Dias ?: '—' }}</td>
                                    <td>
                                        @if($inc->Ruta_Documento)
                                            <a class="doc-link" href="{{ $inc->Ruta_Documento }}" target="_blank">Ver documento</a>
                                        @else
                                            Sin documento
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="vacio-row">Sin incapacidades registradas.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Historial de cambios --}}
            <div class="detail-card">
                <h2>Historial de Cambios</h2>
                <div class="table-wrap">
                    <table class="premium-table">
                        <thead>
                            <tr>
                                <th>Fecha / Hora</th>
                                <th>Acción / Cambio</th>
                                <th>Usuario</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($historial as $h)
                                <tr>
                                    <td class="text-mono" style="font-size: 0.75rem;">{{ $h->Fecha_Hora }}</td>
                                    <td style="white-space: normal;">{{ $h->Cambio }}</td>
                                    <td class="font-semibold">{{ $h->usuario->Nombre ?? 'Sistema' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="vacio-row">No se encontraron registros en el historial de cambios.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        {{-- COLUMNA DERECHA --}}
        <div class="right-column">

            {{-- Calendario --}}
            <div class="detail-card" style="padding: 1.25rem;">
                <h2>Calendario de Eventos</h2>
                <div id="employee-calendar" style="min-height: 350px;"></div>
            </div>

            {{-- Fechas Laborales --}}
            <div class="detail-card">
                <h2>Fechas Laborales</h2>
                <div class="table-wrap">
                    <table class="premium-table">
                        <thead>
                            <tr>
                                <th>Fecha Ingreso</th>
                                <th>Fecha Egreso</th>
                                <th>Fecha Alta IMSS</th>
                                <th>Fecha Baja IMSS</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($fechasLaborales as $fecha)
                                <tr>
                                    <td>{{ $fecha->Fecha_Ingreso ?: '—' }}</td>
                                    <td>{{ $fecha->Fecha_Egreso ?: '—' }}</td>
                                    <td>{{ $fecha->Fecha_Alta_IMSS ?: '—' }}</td>
                                    <td>{{ $fecha->Fecha_Baja_IMSS ?: '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="vacio-row">No se encontraron fechas laborales.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Documentos IMSS --}}
            <div class="detail-card">
                <h2>Documentos IMSS</h2>
                <div class="table-wrap">
                    <table class="premium-table">
                        <thead>
                            <tr>
                                <th>Tipo de Movimiento</th>
                                <th>Fecha</th>
                                <th>Documento</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($documentosImss as $doc)
                                <tr>
                                    <td class="font-semibold">{{ $doc->tipo ?: '—' }}</td>
                                    <td>{{ $doc->fecha ?? $doc->fecha_subida ?? '—' }}</td>
                                    <td>
                                        @if($doc->ruta_documento)
                                            <a class="doc-link" href="{{ $doc->ruta_documento }}" target="_blank">Ver documento</a>
                                        @else
                                            <span style="color: var(--text-muted);">Sin documento</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="vacio-row">No se encontraron documentos IMSS.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Historial de Asignaciones --}}
            <div class="detail-card">
                <h2>Historial de Asignaciones</h2>
                <div class="table-wrap">
                    <table class="premium-table">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Cantidad</th>
                                <th>Fecha Asignación</th>
                                <th>Documento</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($asignaciones as $asignacion)
                                <tr>
                                    <td class="font-semibold">{{ $asignacion->producto->Nombre ?? '—' }}</td>
                                    <td>{{ $asignacion->cantidad ?: '—' }}</td>
                                    <td>{{ $asignacion->Fecha_Asignacion ?: '—' }}</td>
                                    <td>
                                        @if($asignacion->Ruta_Documento)
                                            <a class="doc-link" href="{{ $asignacion->Ruta_Documento }}" target="_blank">Ver documento</a>
                                        @else
                                            Sin documento
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="vacio-row">No hay asignaciones registradas.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Historial de Pagos --}}
            <div class="detail-card">
                <h2>Historial de Pagos</h2>
                <div class="table-wrap">
                    <table class="premium-table">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Monto</th>
                                <th>Fecha Inicio</th>
                                <th>Estatus</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pagos as $pago)
                                <tr>
                                    <td class="font-semibold">{{ $pago->producto->Nombre ?? '—' }}</td>
                                    <td>{{ is_numeric($pago->Monto_Mensual) ? '$' . number_format($pago->Monto_Mensual, 2) : ($pago->Monto_Mensual ?: '—') }}</td>
                                    <td>{{ $pago->Fecha_Inicio ?: '—' }}</td>
                                    <td>{{ $pago->estatus ?: '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="vacio-row">No hay pagos registrados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Botones de Documentos de Historial de Pagos --}}
                <div class="pagos-btn-row">
                    <button type="button" class="btn-pago-doc" onclick="switchSectionTab('secDocPersonales', document.querySelectorAll('.tab-btn')[2]); window.scrollTo({top: document.getElementById('secDocPersonales').offsetTop - 100, behavior: 'smooth'});">
                        Documentos Personales
                    </button>
                    <button type="button" class="btn-pago-doc" onclick="switchSectionTab('secDocLaborales', document.querySelectorAll('.tab-btn')[3]); window.scrollTo({top: document.getElementById('secDocLaborales').offsetTop - 100, behavior: 'smooth'});">
                        Documentos Laborales
                    </button>
                </div>
            </div>

            {{-- Generación de Documentos Individuales --}}
            <div class="detail-card">
                <h2>Carta Oferta</h2>
                <table class="premium-table">
                    <tbody>
                        <tr>
                            <td class="font-semibold">Carta Oferta</td>
                            <td>
                                <div class="action-btn-group">
                                    <button type="button" class="action-btn action-btn-generate" onclick="openDocumentPreview('carta_oferta', 'Carta Oferta', '{{ route('empleados.documentos.cartaOferta', $empleado->N_Empleado) }}')">
                                        Generar Carta
                                    </button>
                                    <button type="button" class="action-btn action-btn-upload" onclick="openUploadModal('Carta Oferta')">
                                        Subir Documento
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="detail-card">
                <h2>Carta de Confidencialidad</h2>
                <table class="premium-table">
                    <tbody>
                        <tr>
                            <td class="font-semibold">Carta Confidencialidad</td>
                            <td>
                                <div class="action-btn-group">
                                    <button type="button" class="action-btn action-btn-generate" onclick="openDocumentPreview('carta_confidencialidad', 'Carta de Confidencialidad', '{{ route('empleados.documentos.cartaConfidencialidad', $empleado->N_Empleado) }}')">
                                        Generar Carta
                                    </button>
                                    <button type="button" class="action-btn action-btn-upload" onclick="openUploadModal('Carta Confidencialidad')">
                                        Subir Documento
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="detail-card">
                <h2>Política de Puntualidad y Asistencia</h2>
                <table class="premium-table">
                    <tbody>
                        <tr>
                            <td class="font-semibold">Política Puntualidad</td>
                            <td>
                                <div class="action-btn-group">
                                    <button type="button" class="action-btn action-btn-generate" onclick="openDocumentPreview('politica_puntualidad', 'Reglamento de Puntualidad y Asistencia', '{{ route('empleados.documentos.politicaPuntualidad', $empleado->N_Empleado) }}')">
                                        Generar Documento
                                    </button>
                                    <button type="button" class="action-btn action-btn-upload" onclick="openUploadModal('Politica Puntualidad')">
                                        Subir Documento
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Contratos del Empleado --}}
            <div class="detail-card">
                <h2>Contratos del Empleado</h2>
                <div class="table-wrap" style="margin-bottom: 1rem;">
                    <table class="premium-table">
                        <thead>
                            <tr>
                                {{-- Columna ID como en el legacy --}}
                                <th>ID</th>
                                <th>Tipo de Contrato</th>
                                <th>Fecha Inicio</th>
                                <th>Fecha Vence</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                // Botón "Generar" por tipo de contrato (el legacy usa Determinado.php, GenerarDoc.php y Convenio.php)
                                $generarContrato = [
                                    'Determinado' => ['contrato_determinado', 'Contrato Determinado', route('empleados.contratos.determinado', $empleado->N_Empleado)],
                                    'Indeterminado' => ['contrato_indeterminado', 'Contrato Indeterminado', route('empleados.contratos.indeterminado', $empleado->N_Empleado)],
                                    'Convenio' => ['convenio', 'Convenio de Confidencialidad y Fidelidad', route('empleados.contratos.convenio', $empleado->N_Empleado)],
                                ];
                            @endphp
                            @forelse($contratos as $con)
                                <tr>
                                    <td>{{ $con->ID }}</td>
                                    <td class="font-semibold">{{ $con->Tipo_de_Contrato }}</td>
                                    <td>{{ $con->F_Inicio ? \Illuminate\Support\Carbon::parse($con->F_Inicio)->format('d/m/Y') : '—' }}</td>
                                    <td>{{ $con->F_Vence ? \Illuminate\Support\Carbon::parse($con->F_Vence)->format('d/m/Y') : 'Indeterminado' }}</td>
                                    <td>
                                        <div class="action-btn-group">
                                            @if($con->Ruta_Doc)
                                                {{-- Igual que el legacy: si ya tiene archivo, SOLO se puede descargar --}}
                                                <a class="action-btn action-btn-download" href="{{ \App\Support\LegacyFile::url($con->Ruta_Doc) }}" target="_blank" title="Descargar contrato">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                                        <polyline points="7 10 12 15 17 10"></polyline>
                                                        <line x1="12" y1="15" x2="12" y2="3"></line>
                                                    </svg>
                                                </a>
                                            @elseif(isset($generarContrato[$con->Tipo_de_Contrato]))
                                                {{-- Sin archivo y es Determinado/Indeterminado/Convenio: Generar + Subir --}}
                                                @php([$docKey, $docTitulo, $docRuta] = $generarContrato[$con->Tipo_de_Contrato])
                                                <button type="button" class="action-btn action-btn-generate" onclick="openDocumentPreview('{{ $docKey }}', '{{ $docTitulo }}', '{{ $docRuta }}')" title="Generar contrato">
                                                    Generar
                                                </button>
                                                {{-- Sube el archivo a ESTE contrato (por su ID), no como documento suelto --}}
                                                <button type="button" class="action-btn action-btn-upload" onclick="openContratoModal({{ $con->ID }}, '{{ $con->Tipo_de_Contrato }}')" title="Subir contrato firmado">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                                        <polyline points="17 8 12 3 7 8"></polyline>
                                                        <line x1="12" y1="3" x2="12" y2="15"></line>
                                                    </svg>
                                                </button>
                                            @elseif($con->Tipo_de_Contrato === 'Evaluacion')
                                                {{-- Evaluación sin archivo: mismos 3 formularios de Google que el legacy (no se sube nada) --}}
                                                <div style="display:flex; flex-direction:column; gap:0.25rem; font-size:0.75rem;">
                                                    <a href="https://forms.gle/jhvSzRgQGyYMVyqL7" target="_blank" rel="noopener">Evaluación 2do contrato</a>
                                                    <a href="https://forms.gle/z1qVW8EJ2n3qDbVi8" target="_blank" rel="noopener">Evaluación 3er contrato</a>
                                                    <a href="https://forms.gle/fr9UpKFwCLwNPReg7" target="_blank" rel="noopener">Evaluación Contrato de planta</a>
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    {{-- 5 columnas ahora que se agregó ID --}}
                                    <td colspan="5" class="vacio-row">No se encontraron contratos registrados.</td>
                                </tr>
                            @endforelse

                        </tbody>
                    </table>
                </div>

                {{-- Generar Contratos --}}
                <h3 style="font-size: 0.8rem; font-weight: 700; color: var(--text-muted); margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 0.03em;">Generar Plantillas de Contrato</h3>
                <div class="pagos-btn-row">
                    <button type="button" class="btn-pago-doc" onclick="openDocumentPreview('contrato_determinado', 'Contrato Determinado', '{{ route('empleados.contratos.determinado', $empleado->N_Empleado) }}')">Contrato Determinado</button>
                    <button type="button" class="btn-pago-doc" onclick="openDocumentPreview('contrato_indeterminado', 'Contrato Indeterminado', '{{ route('empleados.contratos.indeterminado', $empleado->N_Empleado) }}')">Contrato Indeterminado</button>
                    <button type="button" class="btn-pago-doc" onclick="openDocumentPreview('convenio', 'Convenio de Confidencialidad y Fidelidad', '{{ route('empleados.contratos.convenio', $empleado->N_Empleado) }}')">Convenio Confidencialidad</button>
                </div>
            </div>

            {{-- Documentos subidos (Ficha nómina / Folio baja adicional) --}}
            <div class="detail-card">
                <h2>Documentos Adicionales Generados / Subidos</h2>
                <div class="table-wrap" style="margin-bottom: 1.25rem;">
                    <table class="premium-table">
                        <thead>
                            <tr>
                                <th>Nombre del Documento</th>
                                <th>Fecha de Subida</th>
                                <th>Enlace</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($documentosGenerados as $dg)
                                <tr>
                                    <td class="font-semibold">{{ $dg->Nombre_Documento }}</td>
                                    <td>{{ $dg->Fecha_Subida }}</td>
                                    <td>
                                        <a class="doc-link" href="{{ $dg->Ruta_Documento }}" target="_blank">Ver documento</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="vacio-row">No hay documentos adicionales subidos.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div style="margin-top: 1rem; display: flex;">
                    <button type="button" class="action-btn action-btn-generate" style="justify-content: center; padding: 0.6rem 1.2rem; font-weight: 600; font-size: 0.8rem;" onclick="openDocumentPreview('folio_baja', 'Folio de Baja / Solicitud Finiquito', '{{ route('empleados.documentos.folioBaja', $empleado->N_Empleado) }}')">
                        Generar Folio de Baja
                    </button>
                </div>
            </div>

        </div>

    </div>

</div>

{{-- MODAL: DAR DE BAJA --}}
@if ($laboral->Estatus === 'ACTIVO')
    <div id="modalBaja" class="modal-overlay">
        <div class="modal-box">
            <h2>Dar de Baja al Empleado</h2>
            <form method="POST" action="{{ route('empleados.baja', $empleado->N_Empleado) }}">
                @csrf
                <div class="campo" style="margin-top:1rem">
                    <label for="fecha_baja" class="detail-label">Fecha de Baja</label>
                    <input type="date" id="fecha_baja" name="fecha_baja" required>
                </div>
                <div class="campo" style="margin-top:0.75rem">
                    <label for="motivo_baja" class="detail-label">Motivo de Baja</label>
                    <select id="motivo_baja" name="motivo_baja" required>
                        <option value="">Seleccione un motivo</option>
                        <option value="Renuncia">Renuncia</option>
                        <option value="Defuncion">Defunción</option>
                        <option value="No Renovacion">No Renovación</option>
                        <option value="Ausentismo">Ausentismo</option>
                        <option value="Desvinculacion">Desvinculación</option>
                        <option value="Solicitud de pension">Solicitud de pensión</option>
                        <option value="Incapacidad Total y/o Invalidez">Incapacidad Total y/o Invalidez</option>
                    </select>
                </div>
                <div class="campo" style="margin-top:0.75rem">
                    <label for="comentarios" class="detail-label">Comentarios</label>
                    <textarea id="comentarios" name="comentarios" maxlength="500" rows="3" style="width:100%; padding:0.5rem; border-radius:8px; border:1px solid var(--border-color); background-color:var(--bg-page); color:var(--text-main); font-family:inherit; font-size:0.85rem; margin-top:0.25rem;"></textarea>
                </div>
                <div class="campo" style="margin-top:0.75rem">
                    <label for="recontratable" class="detail-label">¿Es recontratable?</label>
                    <select id="recontratable" name="recontratable">
                        <option value="Si">Sí</option>
                        <option value="No">No</option>
                    </select>
                </div>
                <div style="display:flex; gap:0.75rem; margin-top:1.5rem; justify-content: flex-end;">
                    <button type="button" class="btn-cancelar" onclick="document.getElementById('modalBaja').style.display='none'">Cancelar</button>
                    <button type="submit" class="btn-primary-modal" style="background-color: #ef4444;">Confirmar Baja</button>
                </div>
            </form>
        </div>
    </div>
@endif

{{-- MODAL: SUBIR DOCUMENTO (PRE-RELLENADO DINÁMICAMENTE) --}}
<div id="uploadModal" class="modal-overlay">
    <div class="modal-box">
        <h2 id="uploadModalTitle">Subir Documento</h2>
        <form method="POST" action="{{ route('empleados.documentos.store', $empleado->N_Empleado) }}" enctype="multipart/form-data">
            @csrf
            <div class="campo" style="margin-top:1rem">
                <label for="nombre_documento" class="detail-label">Nombre del Documento</label>
                <input type="text" id="nombre_documento" name="nombre_documento" readonly required style="background-color: var(--bg-page); cursor: not-allowed;">
            </div>
            <div class="campo" style="margin-top:1rem">
                <label for="documento" class="detail-label">Seleccionar Archivo (PDF, Imagen, Word)</label>
                <input type="file" id="documento" name="documento" required>
            </div>
            <div style="display:flex; gap:0.75rem; margin-top:1.5rem; justify-content: flex-end;">
                <button type="button" class="btn-cancelar" onclick="closeUploadModal()">Cancelar</button>
                <button type="submit" class="btn-primary-modal">Subir Documento</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL: SUBIR CONTRATO FIRMADO (port de procesar_contrato.php) --}}
<div id="contratoUploadModal" class="modal-overlay">
    <div class="modal-box">
        {{-- El título se llena desde openContratoModal() con el tipo y el ID del contrato --}}
        <h2 id="contratoUploadTitle">Subir Contrato</h2>
        <form method="POST" action="{{ route('empleados.contratos.subir', $empleado->N_Empleado) }}" enctype="multipart/form-data">
            @csrf
            {{-- ID del contrato a actualizar; lo llena openContratoModal() --}}
            <input type="hidden" id="contrato_id" name="contrato_id">
            <div class="campo" style="margin-top:1rem">
                {{-- Mismas extensiones que acepta el legacy --}}
                <label for="archivo_contrato" class="detail-label">Seleccionar Archivo (PDF, DOC, DOCX, TXT)</label>
                <input type="file" id="archivo_contrato" name="archivo" accept=".pdf,.doc,.docx,.txt" required>
            </div>
            <div style="display:flex; gap:0.75rem; margin-top:1.5rem; justify-content: flex-end;">
                <button type="button" class="btn-cancelar" onclick="closeContratoModal()">Cancelar</button>
                <button type="submit" class="btn-primary-modal">Subir Contrato</button>
            </div>
        </form>
    </div>
</div>



{{-- MODAL: SUBIR DOCUMENTO DE EXPEDIENTE (PERSONAL / LABORAL) --}}
<div id="expedienteUploadModal" class="modal-overlay">
    <div class="modal-box">
        <h2 id="expedienteUploadModalTitle">Subir Documento de Expediente</h2>
        <form id="expedienteUploadForm" method="POST" action="" enctype="multipart/form-data">
            @csrf
            <input type="hidden" id="expediente_documento_id" name="documento_id">
            <div class="campo" style="margin-top:1rem">
                <label for="expediente_nombre_documento" class="detail-label">Nombre del Documento</label>
                <input type="text" id="expediente_nombre_documento" readonly required style="background-color: var(--bg-page); cursor: not-allowed;">
            </div>
            <div class="campo" style="margin-top:1rem">
                <label for="expediente_documento" class="detail-label">Seleccionar Archivo (PDF, Imagen, Word)</label>
                <input type="file" id="expediente_documento" name="documento" required>
            </div>
            <div style="display:flex; gap:0.75rem; margin-top:1.5rem; justify-content: flex-end;">
                <button type="button" class="btn-cancelar" onclick="closeExpedienteUploadModal()">Cancelar</button>
                <button type="submit" class="btn-primary-modal">Subir Documento</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL: VER / CAMBIAR FOTO DE PERFIL --}}
<div id="photoModal" class="modal-overlay">
    <div class="modal-box" style="max-width: 400px; text-align: center;">
        <h2>Foto de Perfil</h2>
        <div style="margin: 1.5rem 0; display: flex; justify-content: center;">
            @if(!empty($rutaDocumento?->Ruta_Foto_Empleado))
                <img id="modalProfileImg" src="{{ \App\Support\LegacyFile::url($rutaDocumento->Ruta_Foto_Empleado) }}" alt="Foto de perfil grande" style="width: 200px; height: 200px; border-radius: 50%; object-fit: cover; border: 3px solid var(--border-color); box-shadow: var(--shadow-md);" onerror="handleImageError(this, 'modalDefaultAvatar')">
                <div id="modalDefaultAvatar" class="default-avatar-svg" style="width: 200px; height: 200px; border-radius: 50%; background-color: var(--bg-page); color: var(--text-muted); border: 3px solid var(--border-color); display: none; align-items: center; justify-content: center;">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" style="width: 100px; height: 100px;">
                        <path fill-rule="evenodd" d="M7.5 6a4.5 4.5 0 119 0 4.5 4.5 0 01-9 0zM3.751 20.105a8.25 8.25 0 0116.498 0 .75.75 0 01-.437.695A18.683 18.683 0 0112 22.5c-2.786 0-5.433-.608-7.812-1.7a.75.75 0 01-.437-.695z" clip-rule="evenodd" />
                    </svg>
                </div>
            @else
                <div class="default-avatar-svg" style="width: 200px; height: 200px; border-radius: 50%; background-color: var(--bg-page); color: var(--text-muted); border: 3px solid var(--border-color); display: flex; align-items: center; justify-content: center;">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" style="width: 100px; height: 100px;">
                        <path fill-rule="evenodd" d="M7.5 6a4.5 4.5 0 119 0 4.5 4.5 0 01-9 0zM3.751 20.105a8.25 8.25 0 0116.498 0 .75.75 0 01-.437.695A18.683 18.683 0 0112 22.5c-2.786 0-5.433-.608-7.812-1.7a.75.75 0 01-.437-.695z" clip-rule="evenodd" />
                    </svg>
                </div>
            @endif
        </div>
        <div style="display: flex; flex-direction: column; gap: 0.75rem;">
            <button type="button" class="btn-primary-modal" onclick="document.getElementById('fotoInput').click(); closePhotoModal();">
                Subir Nueva Foto
            </button>
            <button type="button" class="btn-cancelar" onclick="closePhotoModal()">
                Cerrar
            </button>
        </div>
    </div>
</div>

{{-- MODAL UNIFICADO: VISTA PREVIA Y EDICIÓN EN TIEMPO REAL --}}
<div id="documentPreviewModal" class="preview-modal-overlay">
    <div class="preview-modal-box">
        <div class="preview-modal-header">
            <h2 id="previewModalTitle">Vista Previa del Documento</h2>
            <button type="button" class="btn-cancelar" style="padding: 0.25rem 0.5rem; font-size: 1.15rem; border: none; background: none;" onclick="closeDocumentPreview()">&times;</button>
        </div>
        <div class="preview-modal-body">
            <!-- Formulario a la Izquierda -->
            <form id="previewForm" class="preview-form-col" method="GET" action="" onsubmit="closeDocumentPreview()">
                <div style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 0.5rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;">
                    * Modifica los campos a continuación para actualizar el machote a la derecha en tiempo real.
                </div>

                <!-- Inputs para Ficha Nómina -->
                <div id="form-group-ficha_nomina" class="preview-form-group" style="display: none;">
                    <div class="campo">
                        <label for="input_Autoriza" class="detail-label">Quién Autoriza</label>
                        <input type="text" id="input_Autoriza" name="Autoriza" value="Recursos Humanos">
                    </div>
                </div>

                <!-- Inputs para Carta Oferta -->
                <div id="form-group-carta_oferta" class="preview-form-group" style="display: none;">
                    <div class="campo">
                        <label for="input_Sueldo" class="detail-label">Ingreso Mensual Bruto</label>
                        <input type="text" id="input_Sueldo" name="Sueldo" value="{{ $laboral->Sueldo }}">
                    </div>
                    <div class="campo" style="margin-top: 0.75rem;">
                        <label for="input_Sueldo_Neto" class="detail-label">Ingreso Mensual Neto</label>
                        <input type="text" id="input_Sueldo_Neto" name="Sueldo_Neto" value="{{ $laboral->Sueldo_Neto }}">
                    </div>
                    <div class="campo" style="margin-top: 0.75rem;">
                        <label for="input_Efectivo" class="detail-label">Compensación</label>
                        <input type="text" id="input_Efectivo" name="Efectivo" value="{{ $laboral->Efectivo }}">
                    </div>
                    <div class="campo" style="margin-top: 0.75rem;">
                        <label for="input_Hora_Comida" class="detail-label">Apoyo Horario</label>
                        <input type="text" id="input_Hora_Comida" name="Hora_Comida" value="{{ $laboral->Hora_Comida }}">
                    </div>
                    <div class="campo" style="margin-top: 0.75rem;">
                        <label for="input_Comision" class="detail-label">Bono Garantía</label>
                        <input type="text" id="input_Comision" name="Comision" value="{{ $laboral->Comision }}">
                    </div>
                    <div class="campo" style="margin-top: 0.75rem;">
                        <label for="input_KPI" class="detail-label">KPI</label>
                        <input type="text" id="input_KPI" name="KPI" value="{{ $laboral->KPI }}">
                    </div>
                </div>

                <!-- Inputs para Folio de Baja -->
                <div id="form-group-folio_baja" class="preview-form-group" style="display: none;">
                    <div class="campo">
                        <label for="input_motivo_baja" class="detail-label">Motivo de Baja</label>
                        <select id="input_motivo_baja" name="motivo_baja">
                            <option value="">Seleccione un motivo</option>
                            <option value="Renuncia" {{ ($fechasLaborales->first()->motivo_baja ?? '') === 'Renuncia' ? 'selected' : '' }}>Renuncia</option>
                            <option value="Defuncion" {{ ($fechasLaborales->first()->motivo_baja ?? '') === 'Defuncion' ? 'selected' : '' }}>Defunción</option>
                            <option value="No Renovacion" {{ ($fechasLaborales->first()->motivo_baja ?? '') === 'No Renovacion' ? 'selected' : '' }}>No Renovación</option>
                            <option value="Ausentismo" {{ ($fechasLaborales->first()->motivo_baja ?? '') === 'Ausentismo' ? 'selected' : '' }}>Ausentismo</option>
                            <option value="Desvinculacion" {{ ($fechasLaborales->first()->motivo_baja ?? '') === 'Desvinculacion' ? 'selected' : '' }}>Desvinculación</option>
                            <option value="Solicitud de pension" {{ ($fechasLaborales->first()->motivo_baja ?? '') === 'Solicitud de pension' ? 'selected' : '' }}>Solicitud de pensión</option>
                            <option value="Incapacidad Total y/o Invalidez" {{ ($fechasLaborales->first()->motivo_baja ?? '') === 'Incapacidad Total y/o Invalidez' ? 'selected' : '' }}>Incapacidad Total/Invalidez</option>
                        </select>
                    </div>
                    <div class="campo" style="margin-top: 0.75rem;">
                        <label for="input_Dias_laborados" class="detail-label">Días Laborados en quincena</label>
                        <input type="text" id="input_Dias_laborados" name="dias_laborados" value="">
                    </div>
                    <div class="campo" style="margin-top: 0.75rem;">
                        <label for="input_Concepto_Adicional" class="detail-label">Concepto Adicional</label>
                        <input type="text" id="input_Concepto_Adicional" name="Concepto_Adicional" value="">
                    </div>
                    <div class="campo" style="margin-top: 0.75rem;">
                        <label for="input_Descontar_Incidencias" class="detail-label">Descontar Incidencias</label>
                        <input type="text" id="input_Descontar_Incidencias" name="Incidencias_Descontar" value="">
                    </div>
                    <div class="campo" style="margin-top: 0.75rem;">
                        <label for="input_Incidencia_Hrs" class="detail-label">Horas Incidencias</label>
                        <input type="text" id="input_Incidencia_Hrs" name="Incidencias_Hrs" value="">
                    </div>
                    <div class="campo" style="margin-top: 0.75rem;">
                        <label for="input_Importe" class="detail-label">Importe a pagar</label>
                        <input type="text" id="input_Importe" name="Importe" value="">
                    </div>
                    <div class="campo" style="margin-top: 0.75rem;">
                        <label for="input_Observaciones" class="detail-label">Observaciones</label>
                        <textarea id="input_Observaciones" name="observaciones" rows="3" style="width: 100%; border-radius: 8px; border: 1px solid var(--border-color); background-color: var(--bg-page); color: var(--text-main); font-family: inherit; font-size: 0.85rem; padding: 0.4rem;"></textarea>
                    </div>
                </div>

                <!-- No hay inputs específicos para contratos/cartas informativas ya que son 100% autogenerados, pero se muestra nota -->
                <div id="form-group-empty" class="preview-form-group" style="display: none;">
                    <p style="font-size: 0.85rem; color: var(--text-muted);">Este documento se genera de forma directa con la información almacenada en el expediente. No requiere datos adicionales.</p>
                </div>

                <div style="margin-top: auto; display: flex; flex-direction: column; gap: 0.75rem; border-top: 1px solid var(--border-color); padding-top: 1rem;">
                    <button type="submit" id="btnSubmitPreview" class="btn-primary-modal" style="width: 100%;">Descargar Documento</button>
                    <button type="button" class="btn-cancelar" style="width: 100%;" onclick="closeDocumentPreview()">Cancelar</button>
                </div>
            </form>

            <!-- Hoja de Papel a la Derecha -->
            <div class="preview-sheet-col">
                <div class="paper-sheet" id="paperContainer">
                    <!-- Contenido dinámico inyectado por JS -->
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- FullCalendar JS -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
<script>
    // Cambio de Pestañas Datos Personales / Laborales
    function switchSectionTab(tabId, btn) {
        // Desactivar todas las pestañas y botones de esta sección
        const parentCard = btn.closest('.detail-card');
        parentCard.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        parentCard.querySelectorAll('.tab-pane').forEach(c => c.classList.remove('active'));
        
        // Activar pestaña seleccionada
        btn.classList.add('active');
        document.getElementById(tabId).classList.add('active');
    }

    // Modal de Subida de Documentos Prefiltrada
    function openUploadModal(docType) {
        document.getElementById('nombre_documento').value = docType;
        document.getElementById('uploadModal').style.display = 'flex';
    }

    // Cerrar el modal de subida
    function closeUploadModal() {
        document.getElementById('uploadModal').style.display = 'none';
        document.getElementById('documento').value = ''; // Limpiar archivo seleccionado
    }

    
    // Abre el modal de contrato y guarda qué contrato (ID) se va a actualizar
    function openContratoModal(contratoId, tipo) {
        document.getElementById('contrato_id').value = contratoId;
        document.getElementById('contratoUploadTitle').textContent = 'Subir Contrato ' + tipo + ' (ID ' + contratoId + ')';
        document.getElementById('contratoUploadModal').style.display = 'flex';
    }

    // Cierra el modal de contrato y limpia el archivo seleccionado
    function closeContratoModal() {
        document.getElementById('contratoUploadModal').style.display = 'none';
        document.getElementById('archivo_contrato').value = '';
    }



    // Ficha Nómina generation handled by openDocumentPreview

    // Inicializar Calendario de Eventos Filtrado por Empleado
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('employee-calendar');
        if (calendarEl) {
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'es',
                firstDay: 1, // Lunes
                height: 380,
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth'
                },
                events: `{{ route('diario.eventos-feed') }}?n_empleado={{ $empleado->N_Empleado }}`,
                eventClick: function(info) {
                    info.jsEvent.preventDefault();

                    const modalId = 'calendar-event-detail-modal';
                    let existing = document.getElementById(modalId);
                    if (existing) existing.remove();

                    const modal = document.createElement('div');
                    modal.id = modalId;
                    modal.style.position = 'fixed';
                    modal.style.inset = '0';
                    modal.style.backgroundColor = 'rgba(15, 23, 42, 0.65)';
                    modal.style.backdropFilter = 'blur(4px)';
                    modal.style.display = 'flex';
                    modal.style.alignItems = 'center';
                    modal.style.justifyContent = 'center';
                    modal.style.zIndex = '350';
                    modal.style.padding = '1.5rem';

                    const details = info.event.extendedProps.detalles || 'Sin detalles adicionales disponibles.';
                    const type = info.event.extendedProps.tipo || 'Evento';
                    const color = info.event.backgroundColor || '#3b82f6';

                    modal.innerHTML = `
                        <div style="background-color: var(--bg-card); border-radius: 16px; border: 1px solid var(--border-color); box-shadow: var(--shadow-lg); width: 100%; max-width: 450px; overflow: hidden; animation: modalFadeIn 0.25s ease;">
                            <div style="padding: 1.25rem; border-bottom: 1px solid var(--border-color); background-color: ${color}; color: white; font-weight: 600; display: flex; align-items: center; justify-content: space-between;">
                                <span style="font-size: 0.95rem; font-family: 'Outfit', sans-serif;">Detalles del Evento</span>
                                <button type="button" style="background: none; border: none; color: white; font-size: 1.25rem; cursor: pointer; padding: 0 0.5rem; line-height: 1;" onclick="this.closest('#${modalId}').remove()">&times;</button>
                            </div>
                            <div style="padding: 1.5rem; display: flex; flex-direction: column; gap: 1rem; font-family: 'Inter', sans-serif; text-align: left;">
                                <div>
                                    <strong style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Evento:</strong>
                                    <div style="font-size: 1rem; font-weight: 600; color: var(--text-main); margin-top: 0.15rem;">${info.event.title}</div>
                                </div>
                                <div>
                                    <strong style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Categoría:</strong>
                                    <div style="font-size: 0.85rem; color: var(--text-main); margin-top: 0.15rem; font-weight: 500;">${type}</div>
                                </div>
                                <div>
                                    <strong style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Detalles:</strong>
                                    <div style="font-size: 0.85rem; color: var(--text-main); line-height: 1.5; margin-top: 0.25rem; background-color: var(--bg-page); padding: 0.75rem; border-radius: 8px; border: 1px solid var(--border-color); max-height: 180px; overflow-y: auto;">${details}</div>
                                </div>
                            </div>
                            <div style="padding: 1rem; border-top: 1px solid var(--border-color); display: flex; justify-content: flex-end; background-color: var(--bg-page);">
                                <button type="button" class="btn-cancelar" style="padding: 0.5rem 1.2rem; font-size: 0.8rem; border-radius: 8px;" onclick="this.closest('#${modalId}').remove()">Cerrar</button>
                            </div>
                        </div>
                    `;

                    modal.addEventListener('click', function(e) {
                        if (e.target === modal) {
                            modal.remove();
                        }
                    });

                    document.body.appendChild(modal);
                }
            });
            calendar.render();
        }
    });

    // Fallback si la imagen de perfil da error 404
    function handleImageError(imgEl, fallbackId = 'bannerDefaultAvatar') {
        imgEl.style.display = 'none';
        const fallback = document.getElementById(fallbackId);
        if (fallback) {
            fallback.style.display = 'flex';
        }
    }

    // Modal de Foto de Perfil
    function openPhotoModal() {
        document.getElementById('photoModal').style.display = 'flex';
    }

    function closePhotoModal() {
        document.getElementById('photoModal').style.display = 'none';
    }

    // Modal de Subida de Expediente
    function openExpedienteUploadModal(group, id, name) {
        const form = document.getElementById('expedienteUploadForm');
        document.getElementById('expediente_documento_id').value = id;
        document.getElementById('expediente_nombre_documento').value = name;
        
        if (group === 'personal') {
            form.action = `{{ route('empleados.documentos.subirPersonal', $empleado->N_Empleado) }}`;
        } else {
            form.action = `{{ route('empleados.documentos.subirLaboral', $empleado->N_Empleado) }}`;
        }
        
        document.getElementById('expedienteUploadModal').style.display = 'flex';
    }

    function closeExpedienteUploadModal() {
        document.getElementById('expedienteUploadModal').style.display = 'none';
        document.getElementById('expediente_documento').value = '';
    }

    // --- SISTEMA UNIFICADO DE VISTA PREVIA ---
    const employeeData = {
        Nombre_Completo: "{{ $empleado->Nombre_Completo }}",
        NEmpleado: "{{ $empleado->N_Empleado }}",
        N_Empleado: "{{ $empleado->N_Empleado }}",
        Fecha_Ingreso: "{{ $primeraFecha->Fecha_Ingreso ?? '' }}",
        Correo_Electronico: "{{ $empleado->Correo_Electronico ?: '—' }}",
        Puesto: "{{ $laboral->puesto->Nombre_Puesto ?? '—' }}",
        Nombre_Puesto: "{{ $laboral->puesto->Nombre_Puesto ?? '—' }}",
        Fisica: "{{ $laboral->Fisica ?: '—' }}",
        Nomeclatura: "{{ $laboral->Nomeclatura ?: '—' }}",
        Sueldo: "{{ $laboral->Sueldo ?: '0' }}",
        Sueldo_Neto: "{{ $laboral->Sueldo_Neto ?: '0' }}",
        Efectivo: "{{ $laboral->Efectivo ?: '0' }}",
        Hora_Comida: "{{ $laboral->Hora_Comida ?: '0' }}",
        HoraComida: "{{ $laboral->Hora_Comida ?: '0' }}",
        Comision: "{{ $laboral->Comision ?: '0' }}",
        KPI: "{{ $laboral->KPI ?: '0' }}",
        Razon_Social_IMSS: "{{ $laboral->razonSocial->Razon_Social_IMSS ?? '—' }}",
        Representante_Legal: "{{ $laboral->razonSocial->Representante_Legal ?? '—' }}",
        Departamento: "{{ $laboral->puesto->Departamento ?? '—' }}",
        Fecha_Egreso: "{{ $fechasLaborales->first()->Fecha_Egreso ?? '—' }}",
        Quincenas: "{{ isset($fechasLaborales->first()->Fecha_Ingreso, $fechasLaborales->first()->Fecha_Egreso) ? floor(\Illuminate\Support\Carbon::parse($fechasLaborales->first()->Fecha_Ingreso)->diffInDays(\Illuminate\Support\Carbon::parse($fechasLaborales->first()->Fecha_Egreso)) / 15) : '0' }}",
        FechaHoy: "{{ now()->day }} de {{ ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'][now()->month - 1] }} del {{ now()->year }}",
        motivo_baja_Renuncia: "{{ ($fechasLaborales->first()->motivo_baja ?? '') === 'Renuncia' ? 'X' : '-' }}",
        motivo_baja_pension: "{{ ($fechasLaborales->first()->motivo_baja ?? '') === 'Solicitud de pension' ? 'X' : '-' }}",
        motivo_baja_No_Renovacion: "{{ ($fechasLaborales->first()->motivo_baja ?? '') === 'No Renovacion' ? 'X' : '-' }}",
        motivo_baja_Defuncion: "{{ ($fechasLaborales->first()->motivo_baja ?? '') === 'Defuncion' ? 'X' : '-' }}",
        motivo_baja_Ausentismo: "{{ ($fechasLaborales->first()->motivo_baja ?? '') === 'Ausentismo' ? 'X' : '-' }}",
        motivo_baja_Incapacidad: "{{ ($fechasLaborales->first()->motivo_baja ?? '') === 'Incapacidad Total y/o Invalidez' ? 'X' : '-' }}",
        motivo_baja_Desvinculacion: "{{ ($fechasLaborales->first()->motivo_baja ?? '') === 'Desvinculacion' ? 'X' : '-' }}",
        F_inicio: "{{ $contratos->last()->F_Inicio ?? '' }}",
        F_Vence: "{{ $contratos->last()->F_Vence ?? 'Indeterminado' }}",
        Salario_Diario: "{{ $laboral->Salario_Diario ?: '0' }}"
    };

    const documentTemplates = {
        carta_oferta: `
            <div class="carta-oferta-content">
                <div style="text-align: right; margin-bottom: 2rem;">
                    Guadalajara Jalisco, a <span class="preview-tag" data-tag="FechaHoy"></span>
                </div>
                <p>Estimado <strong><span class="preview-tag" data-tag="Nombre_Completo"></span></strong>,</p>
                <p>Por medio de la presente nos complace en ofrecerte formalmente el puesto de <strong><span class="preview-tag" data-tag="Nombre_Puesto"></span></strong> en la empresa <strong><span class="preview-tag" data-tag="Razon_Social_IMSS"></span></strong>.</p>
                <p>A continuación, encontrarás las prestaciones correspondientes al puesto en el que has sido contratado:</p>
                <ul style="list-style-type: none; padding-left: 0; display: flex; flex-direction: column; gap: 0.5rem; margin: 1.5rem 0;">
                    <li><strong>Ingreso Mensual Bruto:</strong> $ <span class="preview-tag" data-tag="Sueldo"></span></li>
                    <li><strong>Ingreso Mensual Neto:</strong> $ <span class="preview-tag" data-tag="Sueldo_Neto"></span> LIBRES</li>
                    <li><strong>Compensación:</strong> $ <span class="preview-tag" data-tag="Efectivo"></span></li>
                    <li><strong>Apoyo Horario:</strong> $ <span class="preview-tag" data-tag="Hora_Comida"></span></li>
                    <li><strong>Bono Garantía:</strong> $ <span class="preview-tag" data-tag="Comision"></span></li>
                    <li><strong>KPI:</strong> $ <span class="preview-tag" data-tag="KPI"></span></li>
                </ul>
                <ul style="margin: 1.5rem 0; padding-left: 1.5rem;">
                    <li>Prestaciones de Ley.</li>
                    <li>Uniformes.</li>
                    <li>Contratación Directa.</li>
                    <li>Reparto de P.T.U.</li>
                    <li>Caja de Ahorro.</li>
                </ul>
                <p>Confiamos en que tus conocimientos y experiencia serán uno de nuestros activos más valiosos. Esperamos que esta carta propuesta sea el comienzo de una sólida y fructífera relación laboral.</p>
                
                <div style="margin-top: 4rem; display: grid; grid-template-columns: 1fr 1fr; gap: 4rem;">
                    <div style="text-align: center;">
                        <div style="border-bottom: 1px solid #94a3b8; height: 50px;"></div>
                        <p style="margin-top: 0.5rem; font-size: 0.8rem; color: #64748b;">Gerente (Jefe directo según corresponda)</p>
                    </div>
                    <div style="text-align: center;">
                        <div style="border-bottom: 1px solid #94a3b8; height: 50px;"></div>
                        <p style="margin-top: 0.5rem; font-size: 0.8rem; color: #64748b;">Recursos Humanos</p>
                    </div>
                </div>
                <div style="margin-top: 3rem; text-align: center; max-width: 300px; margin-left: auto; margin-right: auto;">
                    <div style="border-bottom: 1px solid #94a3b8; height: 50px;"></div>
                    <p style="margin-top: 0.5rem; font-size: 0.8rem; color: #64748b;">Firma colaborador / Fecha</p>
                    <strong style="font-size: 0.85rem;"><span class="preview-tag" data-tag="Nombre_Completo"></span></strong>
                </div>
            </div>
        `,
        ficha_nomina: `
            <div class="ficha-nomina-content">
                <h2 style="text-align: center; margin-top: 0; color: #1e3a8a; border-bottom: 2px solid #1e3a8a; padding-bottom: 0.5rem;">FICHA DE EXPEDIENTE / NÓMINA</h2>
                <div style="display: grid; grid-template-columns: 120px 1fr; gap: 1.5rem; margin-top: 1.5rem;">
                    <div style="border: 2px solid #cbd5e1; border-radius: 8px; width: 120px; height: 120px; display: flex; align-items: center; justify-content: center; background-color: #f8fafc; font-size: 0.75rem; color: #94a3b8;">
                        [FOTO EMPLEADO]
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; font-size: 0.85rem;">
                        <div><strong>N° Empleado:</strong> <span class="preview-tag" data-tag="NEmpleado"></span></div>
                        <div><strong>Nombre:</strong> <span class="preview-tag" data-tag="Nombre_Completo"></span></div>
                        <div><strong>Fecha Ingreso:</strong> <span class="preview-tag" data-tag="Fecha_Ingreso"></span></div>
                        <div><strong>Email:</strong> <span class="preview-tag" data-tag="Correo_Electronico"></span></div>
                        <div style="grid-column: span 2; background-color: #f1f5f9; padding: 0.5rem; border-radius: 6px; border-left: 4px solid #3b82f6;">
                            <strong>Autoriza Ficha:</strong> <span class="preview-tag" data-tag="Autoriza"></span>
                        </div>
                    </div>
                </div>
                <h3 style="margin-top: 1.5rem; color: #1e3a8a; border-bottom: 1px solid #cbd5e1; padding-bottom: 0.25rem; font-size: 1rem;">Detalles Laborales</h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; font-size: 0.85rem; margin-top: 0.5rem;">
                    <div><strong>Puesto:</strong> <span class="preview-tag" data-tag="Puesto"></span></div>
                    <div><strong>Ubicación Física:</strong> <span class="preview-tag" data-tag="Fisica"></span></div>
                    <div><strong>Razón Social:</strong> <span class="preview-tag" data-tag="Nomeclatura"></span></div>
                    <div><strong>Sueldo:</strong> $ <span class="preview-tag" data-tag="Sueldo"></span></div>
                    <div><strong>Compensación:</strong> $ <span class="preview-tag" data-tag="HoraComida"></span></div>
                    <div><strong>KPI:</strong> $ <span class="preview-tag" data-tag="KPI"></span></div>
                    <div><strong>Comisión:</strong> $ <span class="preview-tag" data-tag="Comision"></span></div>
                    <div><strong>Efectivo:</strong> $ <span class="preview-tag" data-tag="Efectivo"></span></div>
                </div>
                
                <h3 style="margin-top: 1.5rem; color: #1e3a8a; border-bottom: 1px solid #cbd5e1; padding-bottom: 0.25rem; font-size: 1rem;">Documentación Recibida</h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; font-size: 0.75rem; margin-top: 0.5rem;">
                    <div>✓ Acta de Nacimiento</div>
                    <div>✓ INE / Identificación</div>
                    <div>✓ CURP y RFC</div>
                    <div>✓ Comprobante Domicilio</div>
                    <div>✓ Número de Seguro Social (NSS)</div>
                    <div>✓ Aviso INFONAVIT / FONACOT</div>
                    <div>✓ Carta Oferta Firmada</div>
                    <div>✓ Código de Conducta / Vestimenta</div>
                    <div>✓ Contratos Laborales</div>
                    <div>✓ Aviso de Privacidad / Confidencialidad</div>
                </div>
            </div>
        `,
        carta_confidencialidad: `
            <div class="carta-confidencialidad-content">
                <h2 style="text-align: center; color: #0f172a; margin-top: 0;">CARTA DE CONFIDENCIALIDAD</h2>
                <p style="text-align: justify; margin-top: 1.5rem;">Por medio de la presente reconozco y acepto que:</p>
                <p style="text-align: justify;">Como colaborador de <strong><span class="preview-tag" data-tag="Razon_Social_IMSS"></span></strong> recibiré y tendré acceso a información privilegiada de carácter confidencial, secretos industriales y cartera de clientes.</p>
                <p style="text-align: justify;">Entiendo y reconozco que <strong><span class="preview-tag" data-tag="Razon_Social_IMSS"></span></strong> tiene los derechos exclusivos sobre dicha información y que su conocimiento está limitado únicamente a las personas que por naturaleza de las actividades de su trabajo deban conocerla.</p>
                <p style="text-align: justify;">Que dicha información será en todo momento considerada como un secreto industrial sobre el cual <strong><span class="preview-tag" data-tag="Razon_Social_IMSS"></span></strong> tiene los derechos exclusivos y que por lo tanto es considerada confidencial y goza de la protección que la Ley de la Propiedad Industrial concede a los secretos industriales.</p>
                <p style="text-align: justify;">Que el incumplimiento a la obligación de confidencialidad contenida en el presente documento en la que de cualquier forma se incurra, ya sea por mí o por cualquier otra persona que por mi conducto tenga acceso a esta información, dará lugar a las acciones civiles, penales y laborales correspondientes de conformidad con las leyes vigentes.</p>
                
                <div style="margin-top: 5rem; text-align: center; max-width: 350px; margin-left: auto; margin-right: auto;">
                    <div style="border-bottom: 1px solid #94a3b8; height: 50px;"></div>
                    <p style="margin-top: 0.5rem; font-size: 0.85rem; font-weight: 600;">Firma del Colaborador</p>
                    <strong><span class="preview-tag" data-tag="Nombre_Completo"></span></strong>
                </div>
            </div>
        `,
        politica_puntualidad: `
            <div class="politica-puntualidad-content">
                <h2 style="text-align: center; color: #0f172a; margin-top: 0; font-size: 1.2rem;">REGLAMENTO DE PUNTUALIDAD Y ASISTENCIA</h2>
                <p style="text-align: justify; margin-top: 1.5rem;">Todo colaborador que preste sus servicios en <strong><span class="preview-tag" data-tag="Razon_Social_IMSS"></span></strong>, deberá estar sujeto a la siguiente política de puntualidad y asistencia con la finalidad de propiciar un adecuado ambiente de trabajo, así como fundamentar los principios y valores de esta empresa.</p>
                <p style="text-align: justify;">Para que sea contemplada la asistencia es indispensable registrar la huella digital 4 veces por día de lunes a viernes, para el sábado serán únicamente 2 registros.</p>
                <p style="text-align: justify;">Para choferes y vendedores de campo en caso de contar con alguna actividad laboral que justifique el que no se encuentren en su sucursal durante el horario de alimentos; se permitirá la falta de alguno de los registros siempre y cuando la actividad esté documentada en la bitácora o en el CRM según aplique.</p>
                <p style="text-align: justify;">Se contará con un margen de 15 minutos de tolerancia por quincena. Una vez acumulados los 15 minutos, a partir del minuto 16 se regresará al personal a su domicilio sin goce de sueldo.</p>
                <p style="text-align: justify;">Una falta injustificada y sin permiso de jefe causará una penalización correspondiente.</p>
                
                <div style="margin-top: 5rem; text-align: center; max-width: 350px; margin-left: auto; margin-right: auto;">
                    <div style="border-bottom: 1px solid #94a3b8; height: 50px;"></div>
                    <p style="margin-top: 0.5rem; font-size: 0.85rem; font-weight: 600;">Enterado y Conforme</p>
                    <strong><span class="preview-tag" data-tag="Nombre_Completo"></span></strong>
                </div>
            </div>
        `,
        folio_baja: `
            <div class="folio-baja-content" style="font-size: 0.85rem; line-height: 1.4;">
                <h2 style="text-align: center; color: #b91c1c; border-bottom: 2px solid #b91c1c; padding-bottom: 0.25rem; font-size: 1.15rem; margin-top: 0;">SOLICITUD DE FINIQUITO</h2>
                <p style="margin-bottom: 1rem;">Solicito se sirvan gestionar la elaboración del finiquito, con base a los siguientes datos:</p>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; background-color: #f8fafc; padding: 0.75rem; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 1rem;">
                    <div><strong>Nombre del colaborador:</strong> <span class="preview-tag" data-tag="Nombre_Completo"></span></div>
                    <div><strong>Empresa:</strong> <span class="preview-tag" data-tag="Razon_Social_IMSS"></span></div>
                    <div><strong>Departamento:</strong> <span class="preview-tag" data-tag="Departamento"></span></div>
                    <div><strong>Puesto:</strong> <span class="preview-tag" data-tag="Nombre_Puesto"></span></div>
                    <div><strong>Fecha de Ingreso:</strong> <span class="preview-tag" data-tag="Fecha_Ingreso"></span></div>
                    <div><strong>Fecha de Egreso:</strong> <span class="preview-tag" data-tag="Fecha_Egreso"></span></div>
                </div>

                <h3 style="color: #b91c1c; font-size: 0.9rem; margin-top: 1rem; border-bottom: 1px solid #e2e8f0; padding-bottom: 0.25rem;">PERIODO A CONSIDERAR PARA EL CÁLCULO</h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; margin-bottom: 1rem;">
                    <div><strong>Periodo:</strong> del <span class="preview-tag" data-tag="Fecha_Ingreso"></span> al <span class="preview-tag" data-tag="Fecha_Egreso"></span></div>
                    <div><strong>Última Quincena Cobrada:</strong> <span class="preview-tag" data-tag="Quincenas"></span> quincenas estimadas</div>
                </div>

                <h3 style="color: #b91c1c; font-size: 0.9rem; margin-top: 1rem; border-bottom: 1px solid #e2e8f0; padding-bottom: 0.25rem;">MOTIVO DE LA BAJA</h3>
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.5rem; margin-bottom: 1rem; background-color: #fef2f2; padding: 0.5rem; border-radius: 6px;">
                    <div><strong>Renuncia Voluntaria:</strong> [<span class="preview-tag" data-tag="motivo_baja_Renuncia"></span>]</div>
                    <div><strong>Pensión:</strong> [<span class="preview-tag" data-tag="motivo_baja_pension"></span>]</div>
                    <div><strong>No Renovación:</strong> [<span class="preview-tag" data-tag="motivo_baja_No_Renovacion"></span>]</div>
                    <div><strong>Defunción:</strong> [<span class="preview-tag" data-tag="motivo_baja_Defuncion"></span>]</div>
                    <div><strong>Ausentismo:</strong> [<span class="preview-tag" data-tag="motivo_baja_Ausentismo"></span>]</div>
                    <div><strong>Incapacidad:</strong> [<span class="preview-tag" data-tag="motivo_baja_Incapacidad"></span>]</div>
                    <div><strong>Desvinculación:</strong> [<span class="preview-tag" data-tag="motivo_baja_Desvinculacion"></span>]</div>
                </div>

                <h3 style="color: #b91c1c; font-size: 0.9rem; margin-top: 1rem; border-bottom: 1px solid #e2e8f0; padding-bottom: 0.25rem;">PAGOS PENDIENTES Y ADICIONALES</h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; margin-bottom: 1rem;">
                    <div><strong>Días Laborados en quincena:</strong> <span class="preview-tag" data-tag="Dias_laborados"></span></div>
                    <div><strong>Concepto Adicional:</strong> <span class="preview-tag" data-tag="Concepto_Adicional"></span></div>
                    <div><strong>Descontar Incidencias:</strong> <span class="preview-tag" data-tag="Descontar_Incidencias"></span></div>
                    <div><strong>Horas Incidencias:</strong> <span class="preview-tag" data-tag="Incidencia_Hrs"></span></div>
                    <div><strong>Importe a pagar:</strong> $ <span class="preview-tag" data-tag="Importe"></span></div>
                </div>
                
                <div style="margin-top: 0.5rem;">
                    <strong>Observaciones:</strong>
                    <div style="border: 1px solid #cbd5e1; padding: 0.5rem; border-radius: 4px; background-color: #f8fafc; min-height: 40px; margin-top: 0.25rem;">
                        <span class="preview-tag" data-tag="Observaciones"></span>
                    </div>
                </div>

                <div style="margin-top: 3rem; display: grid; grid-template-columns: 1fr 1fr; gap: 3rem; text-align: center;">
                    <div>
                        <div style="border-bottom: 1px solid #94a3b8; height: 30px;"></div>
                        <p style="margin-top: 0.25rem; font-size: 0.7rem; color: #64748b;">Solicitado por</p>
                    </div>
                    <div>
                        <div style="border-bottom: 1px solid #94a3b8; height: 30px;"></div>
                        <p style="margin-top: 0.25rem; font-size: 0.7rem; color: #64748b;">Autorizado Recursos Humanos</p>
                    </div>
                </div>
            </div>
        `,
        contrato_determinado: `
            <div class="contrato-content">
                <h2 style="text-align: center; color: #1e3a8a;">CONTRATO INDIVIDUAL DE TRABAJO POR TIEMPO DETERMINADO</h2>
                <p style="text-align: justify; margin-top: 2rem;">
                    Contrato individual de trabajo por tiempo determinado que celebran por una parte la empresa <strong><span class="preview-tag" data-tag="Razon_Social_IMSS"></span></strong>, representada por el Sr(a). <strong><span class="preview-tag" data-tag="Representante_Legal"></span></strong>, a quien en lo sucesivo se le denominará el "PATRÓN", y por la otra parte el Sr(a). <strong><span class="preview-tag" data-tag="Nombre_Completo"></span></strong>, a quien en lo sucesivo se le denominará el "TRABAJADOR".
                </p>
                <p style="text-align: justify;">
                    Ambas partes convienen en sujetar su relación laboral bajo las siguientes cláusulas:
                </p>
                <div style="margin-left: 1rem; margin-top: 1.5rem; display: flex; flex-direction: column; gap: 0.75rem;">
                    <p><strong>Primera:</strong> El TRABAJADOR se obliga a prestar sus servicios personales bajo la dirección y dependencia del PATRÓN en el puesto de <strong><span class="preview-tag" data-tag="Nombre_Puesto"></span></strong>.</p>
                    <p><strong>Segunda:</strong> El presente contrato tendrá una vigencia del <strong><span class="preview-tag" data-tag="F_inicio"></span></strong> al <strong><span class="preview-tag" data-tag="F_Vence"></span></strong>.</p>
                    <p><strong>Tercera:</strong> Como retribución por sus servicios prestados, el TRABAJADOR recibirá un salario diario bruto de $ <strong><span class="preview-tag" data-tag="Salario_Diario"></span></strong>.</p>
                </div>
            </div>
        `,
        contrato_indeterminado: `
            <div class="contrato-content">
                <h2 style="text-align: center; color: #1e3a8a;">CONTRATO INDIVIDUAL DE TRABAJO POR TIEMPO INDETERMINADO</h2>
                <p style="text-align: justify; margin-top: 2rem;">
                    Contrato individual de trabajo por tiempo indeterminado que celebran por una parte la empresa <strong><span class="preview-tag" data-tag="Razon_Social_IMSS"></span></strong>, representada por el Sr(a). <strong><span class="preview-tag" data-tag="Representante_Legal"></span></strong>, a quien en lo sucesivo se le denominará el "PATRÓN", y por la otra parte el Sr(a). <strong><span class="preview-tag" data-tag="Nombre_Completo"></span></strong>, a quien en lo sucesivo se le denominará el "TRABAJADOR".
                </p>
                <p style="text-align: justify;">
                    Ambas partes convienen en sujetar su relación laboral bajo las siguientes cláusulas:
                </p>
                <div style="margin-left: 1rem; margin-top: 1.5rem; display: flex; flex-direction: column; gap: 0.75rem;">
                    <p><strong>Primera:</strong> El TRABAJADOR se obliga a prestar sus servicios personales bajo la dirección y dependencia del PATRÓN en el puesto de <strong><span class="preview-tag" data-tag="Nombre_Puesto"></span></strong>.</p>
                    <p><strong>Segunda:</strong> La relación de trabajo se pacta por tiempo indeterminado a partir del <strong><span class="preview-tag" data-tag="Fecha_Ingreso"></span></strong>.</p>
                    <p><strong>Tercera:</strong> Como retribución por sus servicios prestados, el TRABAJADOR recibirá un salario diario bruto de $ <strong><span class="preview-tag" data-tag="Salario_Diario"></span></strong>.</p>
                </div>
            </div>
        `,
        convenio: `
            <div class="contrato-content">
                <h2 style="text-align: center; color: #1e3a8a;">CONVENIO DE CONFIDENCIALIDAD Y FIDELIDAD</h2>
                <p style="text-align: justify; margin-top: 2rem; text-indent: 2rem;">
                    Convenio de confidencialidad, no divulgación y fidelidad de información que celebran por una parte la empresa <strong><span class="preview-tag" data-tag="Razon_Social_IMSS"></span></strong>, representada por el Sr(a). <strong><span class="preview-tag" data-tag="Representante_Legal"></span></strong>, y por la otra el colaborador <strong><span class="preview-tag" data-tag="Nombre_Completo"></span></strong>.
                </p>
                <p style="text-align: justify; text-indent: 2rem;">
                    El colaborador se compromete y obliga a no revelar, transferir, divulgar o hacer uso de ningún tipo de información confidencial relacionada con la empresa, sus marcas, clientes, finanzas y operaciones comerciales a la cual tenga acceso en el desempeño de su puesto de <strong><span class="preview-tag" data-tag="Puesto"></span></strong>.
                </p>
            </div>
        `
    };

    function openDocumentPreview(type, title, actionUrl) {
        const form = document.getElementById('previewForm');
        form.action = actionUrl;
        document.getElementById('previewModalTitle').innerText = title;

        document.querySelectorAll('.preview-form-group').forEach(group => {
            group.style.display = 'none';
        });

        const specificFormGroup = document.getElementById(`form-group-${type}`);
        if (specificFormGroup) {
            specificFormGroup.style.display = 'block';
        } else {
            document.getElementById('form-group-empty').style.display = 'block';
        }

        const paperContainer = document.getElementById('paperContainer');
        paperContainer.innerHTML = documentTemplates[type] || '<p>No hay vista previa disponible.</p>';

        updatePreviewTags(type);

        const inputs = form.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            input.removeEventListener('input', handleInputChange);
            input.removeEventListener('change', handleInputChange);
            
            input.addEventListener('input', handleInputChange);
            input.addEventListener('change', handleInputChange);
        });

        document.getElementById('documentPreviewModal').style.display = 'flex';
    }

    function handleInputChange(event) {
        const input = event.target;
        const name = input.name;
        let value = input.value;

        if (name === 'motivo_baja') {
            const motivos = ['Renuncia', 'pension', 'No_Renovacion', 'Defuncion', 'Ausentismo', 'Incapacidad', 'Desvinculacion'];
            motivos.forEach(m => {
                const tagEl = document.querySelector(`.paper-sheet [data-tag="motivo_baja_${m}"]`);
                if (tagEl) {
                    let normalizedVal = (m === 'pension' && value === 'Solicitud de pension') || 
                                        (m === 'Incapacidad' && value === 'Incapacidad Total y/o Invalidez') || 
                                        (m === 'No_Renovacion' && value === 'No Renovacion') || 
                                        (value === m) ? 'X' : '-';
                    tagEl.innerText = normalizedVal;
                    tagEl.classList.add('updated');
                }
            });
            return;
        }

        let tagName = name;
        if (name === 'dias_laborados') tagName = 'Dias_laborados';
        if (name === 'Concepto_Adicional') tagName = 'Concepto_Adicional';
        if (name === 'Incidencias_Descontar') tagName = 'Descontar_Incidencias';
        if (name === 'Incidencias_Hrs') tagName = 'Incidencia_Hrs';
        if (name === 'Importe') tagName = 'Importe';
        if (name === 'observaciones') tagName = 'Observaciones';
        if (name === 'HoraComida') tagName = 'HoraComida';

        const tagElements = document.querySelectorAll(`.paper-sheet [data-tag="${tagName}"]`);
        tagElements.forEach(tagEl => {
            tagEl.innerText = value || '—';
            tagEl.classList.add('updated');
        });
    }

    function updatePreviewTags(type) {
        const tags = document.querySelectorAll('.paper-sheet [data-tag]');
        tags.forEach(tagEl => {
            const tagName = tagEl.getAttribute('data-tag');
            let inputVal = null;
            let inputName = tagName;
            
            if (tagName === 'Dias_laborados') inputName = 'dias_laborados';
            if (tagName === 'Descontar_Incidencias') inputName = 'Incidencias_Descontar';
            if (tagName === 'Incidencia_Hrs') inputName = 'Incidencias_Hrs';
            if (tagName === 'Observaciones') inputName = 'observaciones';
            
            const input = document.querySelector(`#form-group-${type} [name="${inputName}"]`);
            if (input) {
                inputVal = input.value;
            }

            if (inputVal !== null) {
                tagEl.innerText = inputVal || '—';
            } else if (employeeData[tagName] !== undefined) {
                tagEl.innerText = employeeData[tagName] || '—';
            } else {
                tagEl.innerText = '—';
            }
        });
    }

    function closeDocumentPreview() {
        document.getElementById('documentPreviewModal').style.display = 'none';
    }

    // Modal de Subida de Expediente
    function openExpedienteUploadModal(group, id, name) {
        const form = document.getElementById('expedienteUploadForm');
        document.getElementById('expediente_documento_id').value = id;
        document.getElementById('expediente_nombre_documento').value = name;
        
        if (group === 'personal') {
            form.action = `{{ route('empleados.documentos.subirPersonal', $empleado->N_Empleado) }}`;
        } else {
            form.action = `{{ route('empleados.documentos.subirLaboral', $empleado->N_Empleado) }}`;
        }
        
        document.getElementById('expedienteUploadModal').style.display = 'flex';
    }

    function closeExpedienteUploadModal() {
        document.getElementById('expedienteUploadModal').style.display = 'none';
        document.getElementById('expediente_documento').value = '';
    }

    // Cerrar modales si se hace clic fuera del contenido
    window.addEventListener('click', function(event) {
        const modalBaja = document.getElementById('modalBaja');
        const uploadModal = document.getElementById('uploadModal');
        const photoModal = document.getElementById('photoModal');
        const expedienteUploadModal = document.getElementById('expedienteUploadModal');
        const documentPreviewModal = document.getElementById('documentPreviewModal');
        if (event.target === modalBaja) {
            modalBaja.style.display = 'none';
        }
        if (event.target === uploadModal) {
            closeUploadModal();
        }
        if (event.target === photoModal) {
            closePhotoModal();
        }
        if (event.target === expedienteUploadModal) {
            closeExpedienteUploadModal();
        }
        if (event.target === documentPreviewModal) {
            closeDocumentPreview();
        }
    });
</script>
@endpush
