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
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
        letter-spacing: -0.02em;
    }

    .hero-desc {
        font-size: 1.25rem;
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
        font-size: 1.0rem;
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
    .icon-pink { background-color: rgba(219, 39, 119, 0.1); color: #db2777; }

    /* Tarjeta doble: Hombres | Mujeres */
    .stat-card-double {
        grid-column: span 2;
        justify-content: space-around;
    }

    .stat-half {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .stat-divider {
        width: 1px;
        align-self: stretch;
        background-color: var(--border-color);
    }

    @media (max-width: 640px) {
        .stat-card-double { grid-column: auto; flex-wrap: wrap; gap: 1rem; }
        .stat-divider { display: none; }
    }

    /* Accesos Rápidos */
    .section-title {
        font-size: 1.50rem;
        font-weight: 600;
        margin-bottom: 1.25rem;
        letter-spacing: -0.01em;
    }

    /* main en columna para que .actions-grid pueda crecer hasta el fondo de la pantalla.
       Solo afecta esta página porque este <style> solo se carga en el dashboard */
    main {
        display: flex;
        flex-direction: column;
    }

    .actions-grid {
        flex-grow: 1;
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
    }

    .action-card-title {
        font-size: 2.4rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .action-card-title svg {
        color: var(--primary);
        width: 30px;
        height: 30px;
    }

    .action-card-desc {
        color: var(--text-muted);
        font-size: 1.05rem;
        line-height: 1.4;
    }

    /* Links dentro de cada apartado */
    .action-links {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .action-links a {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.6rem 0.75rem;
        border-radius: 10px;
        color: var(--text-main);
        text-decoration: none;
        font-size: 1.5rem;
        font-weight: 500;
        transition: background-color 0.15s ease, color 0.15s ease;
    }

    .action-links a:hover {
        background-color: var(--primary-light);
        color: var(--primary);
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

        <div class="stat-card stat-card-double">
            <div class="stat-half">
                <div class="stat-icon icon-blue">
                    <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16 3h5v5"></path>
                        <path d="m21 3-6.75 6.75"></path>
                        <circle cx="10" cy="14" r="6"></circle>
                    </svg>
                </div>
                <div class="stat-info">
                    <span class="stat-label">Hombres</span>
                    <span class="stat-val">{{ $hombres }}</span>
                </div>
            </div>

            <div class="stat-divider"></div>

            <div class="stat-half">
                <div class="stat-icon icon-pink">
                    <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 15v7"></path>
                        <path d="M9 19h6"></path>
                        <circle cx="12" cy="9" r="6"></circle>
                    </svg>
                </div>
                <div class="stat-info">
                    <span class="stat-label">Mujeres</span>
                    <span class="stat-val">{{ $mujeres }}</span>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon icon-blue">
                <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="8.5" cy="7" r="4"></circle>
                    <line x1="20" y1="8" x2="20" y2="14"></line>
                    <line x1="17" y1="11" x2="23" y2="11"></line>
                </svg>
            </div>
            <div class="stat-info">
                <span class="stat-label">Contrataciones {{ $anio }}</span>
                <span class="stat-val">{{ $contratacionesAnio }}</span>
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
    @php
        // Mismos roles que los middleware 'role:' de routes/web.php, para no mostrar links que darían 403
        $rol = auth()->user()->Rol;
        $esRH = in_array($rol, ['Admin', 'RH']);
        $veEmpleados = in_array($rol, ['Admin', 'RH', 'Nominas']);
    @endphp
    <div class="actions-grid">
        @if($esRH)
        <div class="action-card">
            <div class="action-card-title">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="10" cy="7" r="4"></circle>
                    <path d="M10.3 15H7a4 4 0 0 0-4 4v2"></path>
                    <circle cx="17" cy="17" r="3"></circle>
                    <path d="m21 21-1.9-1.9"></path>
                </svg>
                Atracción de Talento
            </div>
            <p class="action-card-desc">Da seguimiento a los candidatos y a sus procesos de reclutamiento.</p>
            <div class="action-links">
                <a href="{{ route('reclutamiento.index') }}">Reclutamiento <span>&rsaquo;</span></a>
            </div>
        </div>
        @endif

        @if($veEmpleados)
        <div class="action-card">
            <div class="action-card-title">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                    <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                </svg>
                Administración de Personal
            </div>
            <p class="action-card-desc">Expedientes, altas de colaboradores, asistencia y eventos del día.</p>
            <div class="action-links">
                <a href="{{ route('empleados.index') }}">Empleados <span>&rsaquo;</span></a>
                <a href="{{ route('empleados.crear') }}">Registrar empleado <span>&rsaquo;</span></a>
                @if($esRH)
                <a href="{{ route('incidencias.index') }}">Incidencias <span>&rsaquo;</span></a>
                <a href="{{ route('diario.index') }}">Diario <span>&rsaquo;</span></a>
                @endif
            </div>
        </div>
        @endif

        @if($esRH)
        <div class="action-card">
            <div class="action-card-title">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="22 7 13.5 15.5 8.5 10.5 2 17"></polyline>
                    <polyline points="16 7 22 7 22 13"></polyline>
                </svg>
                Desarrollo Organizacional
            </div>
            <p class="action-card-desc">Estructura de puestos y reportes de la plantilla.</p>
            <div class="action-links">
                <a href="{{ route('organigrama.index') }}">Organigrama <span>&rsaquo;</span></a>
                <a href="{{ route('reportes.index') }}">Reportes <span>&rsaquo;</span></a>
            </div>
        </div>
        @endif
    </div>
@endsection
