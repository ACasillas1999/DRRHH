@extends('layouts.app')

@section('title', 'Inicio')
@section('page_title', 'Menú Principal')

@push('styles')
<style>
    /* Estilos específicos del Dashboard */
    .hero-banner {
        background: linear-gradient(135deg, var(--primary) 0%, #4f46e5 100%);
        border-radius: 20px;
        padding: 2.5rem;
        color: white;
        margin-bottom: 2rem;
        position: relative;
        overflow: hidden;
        box-shadow: var(--shadow-md);
    }

    .hero-banner::before {
        content: '';
        position: absolute;
        width: 300px;
        height: 300px;
        background-color: rgba(255, 255, 255, 0.05);
        border-radius: 50%;
        top: -100px;
        right: -100px;
        z-index: 1;
    }

    .hero-content {
        position: relative;
        z-index: 2;
        max-width: 600px;
    }

    .hero-title {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
        letter-spacing: -0.02em;
    }

    .hero-desc {
        font-size: 1rem;
        opacity: 0.9;
        line-height: 1.5;
        margin-bottom: 1.5rem;
    }

    .hero-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        background-color: white;
        color: var(--primary);
        padding: 0.75rem 1.5rem;
        border-radius: 12px;
        font-weight: 600;
        text-decoration: none;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        transition: transform 0.2s ease;
    }

    .hero-btn:hover {
        transform: translateY(-2px);
    }

    /* Grid de Estadísticas */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2.5rem;
    }

    .stat-card {
        background-color: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 20px;
        padding: 1.5rem;
        display: flex;
        align-items: center;
        gap: 1.25rem;
        box-shadow: var(--shadow-md);
        position: relative;
    }

    .stat-icon {
        width: 54px;
        height: 54px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .stat-info {
        display: flex;
        flex-direction: column;
    }

    .stat-label {
        font-size: 0.85rem;
        color: var(--text-muted);
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .stat-val {
        font-size: 1.75rem;
        font-weight: 700;
        color: var(--text-main);
        margin-top: 0.15rem;
    }

    /* Colores de Iconos de Estadísticas */
    .icon-blue { background-color: var(--primary-light); color: var(--primary); }
    .icon-green { background-color: rgba(22, 163, 74, 0.1); color: var(--activo); }
    .icon-red { background-color: rgba(220, 38, 38, 0.1); color: var(--baja); }

    /* Accesos Rápidos */
    .section-title {
        font-size: 1.25rem;
        font-weight: 600;
        margin-bottom: 1.25rem;
        letter-spacing: -0.01em;
    }

    .actions-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1.5rem;
    }

    .action-card {
        background-color: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 20px;
        padding: 1.5rem;
        display: flex;
        flex-direction: column;
        gap: 1rem;
        text-decoration: none;
        color: inherit;
        box-shadow: var(--shadow-sm);
        transition: all 0.2s ease;
    }

    .action-card:hover {
        transform: translateY(-3px);
        border-color: var(--primary);
        box-shadow: var(--shadow-md);
    }

    .action-card-title {
        font-size: 1.1rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .action-card-title svg {
        color: var(--primary);
        width: 22px;
        height: 22px;
    }

    .action-card-desc {
        color: var(--text-muted);
        font-size: 0.85rem;
        line-height: 1.4;
    }
</style>
@endpush

@section('content')
    <!-- Banner de Bienvenida -->
    <div class="hero-banner">
        <div class="hero-content">
            <h1 class="hero-title">¡Bienvenido al Portal de RH!</h1>
            <p class="hero-desc">Desde este panel unificado puedes gestionar expedientes del personal, dar de alta y baja contratos, registrar incidencias y controlar los saldos de vacaciones de los colaboradores.</p>
            <a href="{{ route('empleados.index') }}" class="hero-btn">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
                Ver Lista de Empleados
            </a>
        </div>
    </div>

    <!-- Indicadores Estadísticos en Tiempo Real -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon icon-blue">
                <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
            </div>
            <div class="stat-info">
                <span class="stat-label">Total Empleados</span>
                <span class="stat-val">{{ $totalEmpleados }}</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon icon-green">
                <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
            </div>
            <div class="stat-info">
                <span class="stat-label">Activos</span>
                <span class="stat-val">{{ $activos }}</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon icon-red">
                <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="15" y1="9" x2="9" y2="15"></line>
                    <line x1="9" y1="9" x2="15" y2="15"></line>
                </svg>
            </div>
            <div class="stat-info">
                <span class="stat-label">Bajas</span>
                <span class="stat-val">{{ $bajas }}</span>
            </div>
        </div>
    </div>

    <!-- Accesos Rápidos Principales -->
    <h2 class="section-title">Accesos Rápidos</h2>
    <div class="actions-grid">
        <a class="action-card" href="{{ route('empleados.crear') }}">
            <div class="action-card-title">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="8.5" cy="7" r="4"></circle>
                    <line x1="20" y1="8" x2="20" y2="14"></line>
                    <line x1="17" y1="11" x2="23" y2="11"></line>
                </svg>
                Registrar Nuevo Empleado
            </div>
            <p class="action-card-desc">Carga la información personal, laboral y contractual de un nuevo colaborador al sistema.</p>
        </a>

        <a class="action-card" href="{{ route('empleados.index') }}">
            <div class="action-card-title">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="16" y1="13" x2="8" y2="13"></line>
                    <line x1="16" y1="17" x2="8" y2="17"></line>
                </svg>
                Expedientes Digitales
            </div>
            <p class="action-card-desc">Consulta detalles, actualiza información de contacto, genera contratos o sube documentación de IMSS.</p>
        </a>
    </div>
@endsection
