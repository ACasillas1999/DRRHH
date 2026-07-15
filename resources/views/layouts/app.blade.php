<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Recursos Humanos') - Grupo Ascencio</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Variables globales - Paleta Azul/Blanco y Oscuro */
        :root {
            --bg-page: #f8fafc;
            --bg-card: #ffffff;
            --bg-card-hover: #f1f5f9;
            --primary: #1d4ed8;
            --primary-hover: #1e40af;
            --primary-light: #eff6ff;
            --primary-glow: rgba(29, 78, 216, 0.05);
            --border-color: #e2e8f0;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.05), 0 2px 4px -2px rgb(0 0 0 / 0.05);
            --shadow-lg: 0 10px 15px -3px rgba(0,0,0,0.02), 0 4px 6px -4px rgba(0,0,0,0.02);
            --sidebar-width: 260px;
            --sidebar-collapsed-width: 78px;
            --topbar-height: 70px;
            --transition-speed: 0.25s;
            --activo: #16a34a;
            --baja: #dc2626;
            --pendiente: #d97706;
        }

        [data-theme="dark"] {
            --bg-page: #0b0f19;
            --bg-card: #151f32;
            --bg-card-hover: #1b2940;
            --primary: #3b82f6;
            --primary-hover: #60a5fa;
            --primary-light: #1e293b;
            --primary-glow: rgba(59, 130, 246, 0.1);
            --border-color: #223049;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.3);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.3), 0 2px 4px -2px rgb(0 0 0 / 0.3);
            --shadow-lg: 0 10px 25px -5px rgba(0, 0, 0, 0.4);
            --activo: #4ade80;
            --baja: #f87171;
            --pendiente: #fbbf24;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg-page);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            overflow-x: hidden;
        }

        /* ----- SIDEBAR ----- */
        aside {
            width: var(--sidebar-width);
            background-color: var(--bg-card);
            border-right: 1px solid var(--border-color);
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 100;
            display: flex;
            flex-direction: column;
            transition: width var(--transition-speed) cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: var(--shadow-sm);
        }

        .sidebar-header {
            height: var(--topbar-height);
            display: flex;
            align-items: center;
            padding: 0 1.5rem;
            border-bottom: 1px solid var(--border-color);
            overflow: hidden;
            white-space: nowrap;
            gap: 0.75rem;
        }

        .logo-box {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, var(--primary) 0%, #4f46e5 100%);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 1rem;
            flex-shrink: 0;
        }

        .logo-text {
            display: flex;
            flex-direction: column;
        }

        .logo-title {
            font-size: 1.1rem;
            font-weight: 700;
            letter-spacing: -0.02em;
        }

        .logo-subtitle {
            font-size: 0.65rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .sidebar-menu {
            flex-grow: 1;
            padding: 1.25rem 0.75rem;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 0.3rem;
        }

        .menu-item {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            border-radius: 12px;
            text-decoration: none;
            color: var(--text-main);
            font-size: 0.9rem;
            font-weight: 500;
            gap: 0.85rem;
            cursor: pointer;
            transition: all var(--transition-speed) ease;
            white-space: nowrap;
        }

        .menu-item svg {
            width: 20px;
            height: 20px;
            flex-shrink: 0;
            color: var(--text-muted);
            transition: color var(--transition-speed) ease;
        }

        .menu-item:hover {
            background-color: var(--bg-card-hover);
            color: var(--primary);
        }

        .menu-item:hover svg {
            color: var(--primary);
        }

        .menu-item.active {
            background-color: var(--primary-light);
            color: var(--primary);
            font-weight: 600;
        }

        .menu-item.active svg {
            color: var(--primary);
        }

        .menu-item.hidden-role {
            display: none !important;
        }

        .hidden {
            display: none !important;
        }

        .menu-item-soon {
            opacity: 0.65;
            position: relative;
        }
        .menu-item-soon-tag {
            font-size: 0.6rem;
            background-color: var(--primary-light);
            color: var(--primary);
            padding: 0.1rem 0.35rem;
            border-radius: 4px;
            font-weight: 700;
            margin-left: auto;
        }

        .sidebar-footer {
            padding: 1rem 0.75rem;
            border-top: 1px solid var(--border-color);
        }

        /* ----- SUBMENU ITEMS ----- */
        .menu-subitems {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
            padding-left: 2.35rem;
            margin-top: 0.2rem;
            margin-bottom: 0.4rem;
            transition: all var(--transition-speed) ease;
        }

        aside.collapsed .menu-subitems {
            display: none !important;
        }

        .menu-subitem {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            padding: 0.45rem 0.75rem !important;
            font-size: 0.825rem !important;
            font-weight: 500;
            color: var(--text-muted) !important;
            border-radius: 8px !important;
        }

        .menu-subitem:hover {
            background-color: var(--bg-card-hover) !important;
            color: var(--primary) !important;
        }

        .menu-subitem.active {
            background-color: var(--primary-light) !important;
            color: var(--primary) !important;
            font-weight: 700 !important;
        }

        .subitem-bullet {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background-color: var(--text-muted);
            transition: background-color var(--transition-speed) ease, box-shadow var(--transition-speed) ease;
        }

        .menu-subitem:hover .subitem-bullet {
            background-color: var(--primary);
        }

        .menu-subitem.active .subitem-bullet {
            background-color: var(--primary);
            box-shadow: 0 0 6px var(--primary);
        }

        /* ----- COLLAPSIBLE SIDEBAR MENU ----- */
        .menu-chevron {
            margin-left: auto;
            transition: transform var(--transition-speed) ease;
            color: var(--text-muted);
        }

        .menu-parent-toggle.active .menu-chevron {
            transform: rotate(180deg);
        }

        aside.collapsed .menu-chevron {
            display: none !important;
        }

        .menu-parent-toggle.parent-active {
            color: var(--primary) !important;
            font-weight: 600;
        }

        .menu-parent-toggle.parent-active svg.menu-icon {
            color: var(--primary) !important;
        }

        /* ----- COLLAPSED SIDEBAR ----- */
        aside.collapsed {
            width: var(--sidebar-collapsed-width);
        }

        aside.collapsed .logo-text,
        aside.collapsed .menu-item span,
        aside.collapsed .menu-item-soon-tag {
            display: none;
        }

        aside.collapsed .sidebar-header {
            justify-content: center;
            padding: 0;
        }

        aside.collapsed .menu-item {
            justify-content: center;
            padding: 0.75rem 0;
        }

        /* ----- MAIN CONTENT SECTION ----- */
        .wrapper {
            margin-left: var(--sidebar-width);
            flex-grow: 1;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            transition: margin-left var(--transition-speed) cubic-bezier(0.4, 0, 0.2, 1);
            min-width: 0;
        }

        .wrapper.expanded {
            margin-left: var(--sidebar-collapsed-width);
        }

        /* ----- TOPBAR ----- */
        .topbar {
            height: var(--topbar-height);
            background-color: rgba(var(--bg-card), 0.8);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border-color);
            position: sticky;
            top: 0;
            z-index: 90;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 2rem;
            box-shadow: var(--shadow-sm);
        }

        .topbar-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .collapse-btn {
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: 8px;
            transition: all var(--transition-speed) ease;
        }

        .collapse-btn:hover {
            background-color: var(--bg-card-hover);
            color: var(--primary);
        }

        .page-title {
            font-size: 1.15rem;
            font-weight: 600;
            letter-spacing: -0.01em;
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 1.25rem;
        }

        /* Role Selector */
        .role-box {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background-color: var(--bg-page);
            border: 1px solid var(--border-color);
            padding: 0.35rem 0.75rem;
            border-radius: 8px;
            font-size: 0.85rem;
        }

        .role-box span {
            color: var(--text-muted);
            font-weight: 500;
        }

        .role-select {
            background: none;
            border: none;
            color: var(--primary);
            font-family: inherit;
            font-weight: 600;
            outline: none;
            cursor: pointer;
        }

        /* Control Buttons */
        .top-btn {
            background-color: var(--bg-card);
            border: 1px solid var(--border-color);
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: var(--text-muted);
            box-shadow: var(--shadow-sm);
            position: relative;
        }

        .top-btn:hover {
            border-color: var(--primary);
            color: var(--primary);
        }

        .top-btn-dot {
            position: absolute;
            top: 6px;
            right: 6px;
            width: 8px;
            height: 8px;
            background-color: var(--baja);
            border-radius: 50%;
            border: 2px solid var(--bg-card);
        }

        /* Profile Widget */
        .profile-widget {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding-left: 1.25rem;
            border-left: 1px solid var(--border-color);
        }

        .avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background-color: var(--primary-light);
            border: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            font-weight: 700;
            font-size: 0.9rem;
        }

        .user-details {
            display: flex;
            flex-direction: column;
            line-height: 1.2;
        }

        .user-name {
            font-size: 0.875rem;
            font-weight: 600;
        }

        .user-role {
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        /* ----- CONTENT ----- */
        main {
            flex-grow: 1;
            padding: 2rem;
            width: 100%;
            min-width: 0;
        }

        /* ----- MODAL NOTIFICACIONES ----- */
        .notif-modal {
            display: none;
            position: absolute;
            top: calc(var(--topbar-height) - 10px);
            right: 5rem;
            width: 320px;
            background-color: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            box-shadow: var(--shadow-lg);
            padding: 1rem;
            z-index: 150;
        }

        .notif-header {
            font-weight: 600;
            font-size: 0.95rem;
            margin-bottom: 0.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid var(--border-color);
            color: var(--text-main);
        }

        .notif-body {
            max-height: 200px;
            overflow-y: auto;
            font-size: 0.85rem;
            color: var(--text-muted);
            text-align: center;
            padding: 1rem 0;
        }

        /* ----- MOBILE RESPONSIVENESS ----- */
        @media (max-width: 768px) {
            aside {
                transform: translateX(-100%);
            }
            aside.mobile-open {
                transform: translateX(0);
                width: var(--sidebar-width);
            }
            .wrapper {
                margin-left: 0 !important;
            }
            .role-box {
                display: none;
            }
        }

        @media (max-width: 480px) {
            .topbar {
                padding: 0 1rem;
            }
            .profile-widget {
                display: none;
            }
        }

        /* ----- PAGINACION LARAVEL ----- */
        .pagination-wrap nav {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
            padding: 1rem 0;
        }

        .pagination-wrap svg {
            width: 1.25rem !important;
            height: 1.25rem !important;
            display: inline-block;
            vertical-align: middle;
        }

        .pagination-wrap nav > div:first-child {
            display: flex;
            gap: 0.5rem;
        }

        .pagination-wrap nav > div:first-child a,
        .pagination-wrap nav > div:first-child span {
            padding: 0.5rem 1rem;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            text-decoration: none;
            color: var(--text-main);
            font-size: 0.875rem;
            font-weight: 500;
            background-color: var(--bg-card);
        }

        .pagination-wrap nav > div:last-child {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            gap: 1rem;
        }

        @media (min-width: 640px) {
            .pagination-wrap nav > div:first-child {
                display: none !important;
            }
            .pagination-wrap nav > div:last-child {
                justify-content: space-between;
            }
        }

        .pagination-wrap .shadow-sm {
            box-shadow: none !important;
            display: inline-flex;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid var(--border-color);
        }

        .pagination-wrap a, 
        .pagination-wrap span[aria-disabled="true"] span, 
        .pagination-wrap span[aria-current="page"] span {
            padding: 0.5rem 1.1rem;
            background-color: var(--bg-card);
            border-right: 1px solid var(--border-color);
            color: var(--text-main);
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
        }

        .pagination-wrap a:hover {
            background-color: var(--bg-card-hover);
            color: var(--primary);
        }

        .pagination-wrap span[aria-current="page"] span {
            background-color: var(--primary-light);
            color: var(--primary);
            font-weight: 600;
        }

        .pagination-wrap span[aria-disabled="true"] span {
            color: var(--text-muted);
            cursor: not-allowed;
            background-color: var(--bg-card);
        }

        .pagination-wrap .shadow-sm > *:last-child,
        .pagination-wrap .shadow-sm > *:last-child span {
            border-right: none !important;
        }
    </style>
    @stack('styles')
</head>
<body data-theme="light">

    <!-- BARRA LATERAL (SIDEBAR) -->
    <aside id="appSidebar">
        <div class="sidebar-header">
            <div class="logo-box">AS</div>
            <div class="logo-text">
                <span class="logo-title">Gpo Ascencio</span>
                <span class="logo-subtitle">Recursos Humanos</span>
            </div>
        </div>

        <nav class="sidebar-menu">
            <!-- 1. Dashboard / Menú Principal -->
            <a href="{{ url('/') }}" class="menu-item {{ Request::is('/') ? 'active' : '' }}" data-roles="Admin,RH,Nominas,Jefe,Gerente,Encargado">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="7" height="9"></rect>
                    <rect x="14" y="3" width="7" height="5"></rect>
                    <rect x="14" y="12" width="7" height="9"></rect>
                    <rect x="3" y="16" width="7" height="5"></rect>
                </svg>
                <span>Menú Principal</span>
            </a>

            <!-- 2. Empleados -->
            <a href="{{ route('empleados.index') }}" class="menu-item {{ Request::is('empleados*') ? 'active' : '' }}" data-roles="Admin,RH,Nominas">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
                <span>Empleados</span>
            </a>

            <!-- 3. Altas/Bajas IMSS -->
            <a href="{{ route('altas-imss.index') }}" class="menu-item {{ Request::is('altas-imss*') ? 'active' : '' }}" data-roles="Admin,RH,Nominas">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="8.5" cy="7" r="4"></circle>
                    <line x1="20" y1="8" x2="20" y2="14"></line>
                    <line x1="17" y1="11" x2="23" y2="11"></line>
                </svg>
                <span>Altas/Bajas IMSS</span>
            </a>

            <!-- 4. Razones Sociales -->
            <a href="{{ route('razones-sociales.index') }}" class="menu-item {{ Request::is('razones-sociales*') ? 'active' : '' }}" data-roles="Admin,RH">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                    <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                </svg>
                <span>Razones Sociales</span>
            </a>

            <!-- 5. Puestos (Desplegable en Acordeón) -->
            <div class="menu-item-group" style="display: flex; flex-direction: column;">
                <a href="javascript:void(0)" class="menu-item menu-parent-toggle {{ Request::is('puestos*') || Request::is('organigrama*') || Request::is('responsables*') || Request::is('ubicaciones*') ? 'active parent-active' : '' }}" onclick="toggleSidebarMenu(this)" data-roles="Admin,RH">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="menu-icon">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                    </svg>
                    <span>Puestos</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="menu-chevron">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </a>
                <div class="menu-subitems {{ Request::is('puestos*') || Request::is('organigrama*') || Request::is('responsables*') || Request::is('ubicaciones*') ? '' : 'hidden' }}">
                    <a href="{{ route('puestos.index') }}" class="menu-item menu-subitem {{ Request::is('puestos') || (Request::is('puestos*') && !Request::is('puestos/*/edit') && !Request::is('puestos/crear')) ? 'active' : '' }}" data-roles="Admin,RH">
                        <span class="subitem-bullet"></span>
                        <span>Lista de Puestos</span>
                    </a>
                    <a href="{{ route('organigrama.index') }}" class="menu-item menu-subitem {{ Request::is('organigrama*') ? 'active' : '' }}" data-roles="Admin,RH">
                        <span class="subitem-bullet"></span>
                        <span>Organigrama</span>
                    </a>
                    <a href="{{ route('responsables.index') }}" class="menu-item menu-subitem {{ Request::is('responsables*') ? 'active' : '' }}" data-roles="Admin,RH">
                        <span class="subitem-bullet"></span>
                        <span>Responsables</span>
                    </a>
                    <a href="{{ route('ubicaciones.index') }}" class="menu-item menu-subitem {{ Request::is('ubicaciones*') ? 'active' : '' }}" data-roles="Admin,RH">
                        <span class="subitem-bullet"></span>
                        <span>Ubicaciones</span>
                    </a>
                </div>
            </div>

            <!-- 6. Reportes -->
            <a href="{{ route('reportes.index') }}" class="menu-item {{ Request::is('reportes*') || Request::is('estimados*') ? 'active' : '' }}" data-roles="Admin,RH">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="20" x2="18" y2="10"></line>
                    <line x1="12" y1="20" x2="12" y2="4"></line>
                    <line x1="6" y1="20" x2="6" y2="14"></line>
                </svg>
                <span>Reportes</span>
            </a>

            <!-- 7. Usuarios -->
            <a href="#usuarios" class="menu-item menu-item-soon" data-roles="Admin">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                </svg>
                <span>Usuarios</span>
                <span class="menu-item-soon-tag">Próx</span>
            </a>

            <!-- 8. Historial -->
            <a href="#historial" class="menu-item menu-item-soon" data-roles="Admin,RH">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
                <span>Historial</span>
                <span class="menu-item-soon-tag">Próx</span>
            </a>

            <!-- 9. Inventario -->
            <a href="#inventario" class="menu-item menu-item-soon" data-roles="Admin,RH">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                    <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                    <line x1="12" y1="22.08" x2="12" y2="12"></line>
                </svg>
                <span>Inventario</span>
                <span class="menu-item-soon-tag">Próx</span>
            </a>

            <!-- 10. Vacaciones -->
            <a href="#vacaciones" class="menu-item menu-item-soon" data-roles="Admin,RH,Jefe,Gerente,Encargado,Nominas">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                </svg>
                <span>Vacaciones</span>
                <span class="menu-item-soon-tag">Próx</span>
            </a>

            <!-- 11. Incapacidades -->
            <a href="#incapacidades" class="menu-item menu-item-soon" data-roles="Admin,RH">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 12h-4l-3 9L9 3l-3 9H2"></path>
                </svg>
                <span>Incapacidades</span>
                <span class="menu-item-soon-tag">Próx</span>
            </a>

            <!-- 12. Diario -->
            <a href="{{ route('diario.index') }}" class="menu-item {{ Request::is('diario*') ? 'active' : '' }}" data-roles="Admin,RH">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="16" y1="2" x2="16" y2="6"></line>
                    <line x1="8" y1="2" x2="8" y2="6"></line>
                    <line x1="3" y1="10" x2="21" y2="10"></line>
                </svg>
                <span>Diario</span>
            </a>

            <!-- 13. Incidencias -->
            <a href="#incidencias" class="menu-item menu-item-soon" data-roles="Admin,RH,Jefe,Gerente,Encargado,Nominas">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                    <line x1="12" y1="9" x2="12" y2="13"></line>
                    <line x1="12" y1="17" x2="12.01" y2="17"></line>
                </svg>
                <span>Incidencias</span>
                <span class="menu-item-soon-tag">Próx</span>
            </a>

            <!-- 14. Reader -->
            <a href="#reader" class="menu-item menu-item-soon" data-roles="Admin,RH">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 7V4a2 2 0 0 1 2-2h3"></path>
                    <path d="M20 7V4a2 2 0 0 0-2-2h-3"></path>
                    <path d="M4 17v3a2 2 0 0 0 2 2h3"></path>
                    <path d="M20 17v3a2 2 0 0 1-2 2h-3"></path>
                    <line x1="12" y1="8" x2="12" y2="16"></line>
                    <line x1="8" y1="12" x2="16" y2="12"></line>
                </svg>
                <span>Reader</span>
                <span class="menu-item-soon-tag">Próx</span>
            </a>

            <!-- 15. Reclutamiento -->
            <a href="#reclutamiento" class="menu-item menu-item-soon" data-roles="Admin,RH">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    <path d="M11 8v6"></path>
                    <path d="M8 11h6"></path>
                </svg>
                <span>Reclutamiento</span>
                <span class="menu-item-soon-tag">Próx</span>
            </a>
        </nav>

        <div class="sidebar-footer">
            <a href="#logout" class="menu-item" style="color: var(--baja); border: 1px dashed rgba(220,38,38,0.15)">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--baja)">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                    <polyline points="16 17 21 12 16 7"></polyline>
                    <line x1="21" y1="12" x2="9" y2="12"></line>
                </svg>
                <span>Cerrar Sesión</span>
            </a>
        </div>
    </aside>

    <!-- WRAPPER DE CONTENIDO -->
    <div class="wrapper" id="appWrapper">
        
        <!-- BARRA SUPERIOR (TOPBAR) -->
        <div class="topbar">
            <div class="topbar-left">
                <!-- Botón colapsar -->
                <button class="collapse-btn" id="sidebarCollapseBtn" title="Contraer/Expandir Menú">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="3" y1="12" x2="21" y2="12"></line>
                        <line x1="3" y1="6" x2="21" y2="6"></line>
                        <line x1="3" y1="18" x2="21" y2="18"></line>
                    </svg>
                </button>
                <div class="page-title">@yield('page_title', 'Administración')</div>
            </div>

            <div class="topbar-right">
                <!-- Simulador de Rol (Desarrollo) -->
                <div class="role-box">
                    <span>Simular Rol:</span>
                    <select id="appRoleSelector" class="role-select">
                        <option value="Admin">Administrador</option>
                        <option value="RH">Recursos Humanos</option>
                        <option value="Nominas">Nóminas</option>
                        <option value="Jefe">Jefe</option>
                        <option value="Gerente">Gerente</option>
                        <option value="Encargado">Encargado</option>
                    </select>
                </div>

                <!-- Notificaciones flotantes -->
                <button class="top-btn" id="notifBtn" title="Notificaciones / Cumpleaños">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                        <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                    </svg>
                    <span class="top-btn-dot"></span>
                </button>

                <!-- Modal Flotante de Notificaciones (Simuladas) -->
                <div class="notif-modal" id="notifModal">
                    <div class="notif-header">Eventos de Hoy</div>
                    <div class="notif-body" id="notifBody">
                        Cargando eventos...
                    </div>
                </div>

                <!-- Botón de Tema (Modo Oscuro) -->
                <button class="top-btn" id="appThemeToggle" title="Cambiar Tema">
                    <svg id="appSunIcon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: block;">
                        <circle cx="12" cy="12" r="5"></circle>
                        <line x1="12" y1="1" x2="12" y2="3"></line>
                        <line x1="12" y1="21" x2="12" y2="23"></line>
                        <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
                        <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
                        <line x1="1" y1="12" x2="3" y2="12"></line>
                        <line x1="21" y1="12" x2="23" y2="12"></line>
                        <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
                        <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
                    </svg>
                    <svg id="appMoonIcon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: none;">
                        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
                    </svg>
                </button>

                <!-- Perfil Widget -->
                <div class="profile-widget">
                    <div class="avatar" id="avatarLetter">A</div>
                    <div class="user-details">
                        <span class="user-name" id="userNameLabel">Alex User</span>
                        <span class="user-role" id="userRoleLabel">Administrador</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- CONTENIDO PRINCIPAL -->
        <main>
            @yield('content')
        </main>
    </div>

    <!-- Scripts globales de interactividad de la plantilla -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const sidebar = document.getElementById('appSidebar');
            const wrapper = document.getElementById('appWrapper');
            const collapseBtn = document.getElementById('sidebarCollapseBtn');
            const roleSelector = document.getElementById('appRoleSelector');
            const themeToggle = document.getElementById('appThemeToggle');
            const sunIcon = document.getElementById('appSunIcon');
            const moonIcon = document.getElementById('appMoonIcon');
            const menuItems = document.querySelectorAll('.menu-item');
            const userRoleLabel = document.getElementById('userRoleLabel');
            
            const notifBtn = document.getElementById('notifBtn');
            const notifModal = document.getElementById('notifModal');
            const notifBody = document.getElementById('notifBody');

            // 1. Control de Colapso del Sidebar
            function toggleSidebar() {
                const isCollapsed = sidebar.classList.toggle('collapsed');
                wrapper.classList.toggle('expanded', isCollapsed);
                localStorage.setItem('sidebar-collapsed', isCollapsed ? 'true' : 'false');
            }

            collapseBtn.addEventListener('click', () => {
                // En móviles actúa como menú hamburguesa
                if (window.innerWidth <= 768) {
                    sidebar.classList.toggle('mobile-open');
                } else {
                    toggleSidebar();
                }
            });

            // Recuperar estado colapsado
            if (localStorage.getItem('sidebar-collapsed') === 'true' && window.innerWidth > 768) {
                sidebar.classList.add('collapsed');
                wrapper.classList.add('expanded');
            }

            // 2. Control de Roles (Filtrado del menú)
            function filterMenuByRole(role) {
                menuItems.forEach(item => {
                    const rolesPermitidos = item.getAttribute('data-roles')?.split(',') || [];
                    item.classList.toggle('hidden-role', !rolesPermitidos.includes(role));
                });
                
                // Actualizar labels visuales
                userRoleLabel.textContent = roleSelector.options[roleSelector.selectedIndex].text;
                
                // Disparar evento personalizado por si las vistas embebidas quieren reaccionar al cambio de rol
                const event = new CustomEvent('roleChanged', { detail: { role: role } });
                document.dispatchEvent(event);
            }

            roleSelector.addEventListener('change', (e) => {
                const newRole = e.target.value;
                localStorage.setItem('simulated-role', newRole);
                filterMenuByRole(newRole);
            });

            // Cargar rol guardado o por defecto
            const savedRole = localStorage.getItem('simulated-role') || 'Admin';
            roleSelector.value = savedRole;
            filterMenuByRole(savedRole);

            // 3. Control de Modo Oscuro
            function setTheme(theme) {
                document.documentElement.setAttribute('data-theme', theme);
                localStorage.setItem('theme', theme);
                sunIcon.style.display = theme === 'dark' ? 'none' : 'block';
                moonIcon.style.display = theme === 'dark' ? 'block' : 'none';
                
                // Disparar evento personalizado
                const event = new CustomEvent('themeChanged', { detail: { theme: theme } });
                document.dispatchEvent(event);
            }

            themeToggle.addEventListener('click', () => {
                const currentTheme = document.documentElement.getAttribute('data-theme');
                setTheme(currentTheme === 'dark' ? 'light' : 'dark');
            });

            // Cargar tema guardado o por defecto de sistema
            const savedTheme = localStorage.getItem('theme');
            const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            setTheme(savedTheme || (systemPrefersDark ? 'dark' : 'light'));

            // 4. Modal de Notificaciones (Simulado)
            notifBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                const isVisible = notifModal.style.display === 'block';
                notifModal.style.display = isVisible ? 'none' : 'block';
                
                if (!isVisible) {
                    // Cargar notificaciones / eventos
                    fetchNotifMock();
                }
            });

            document.addEventListener('click', (e) => {
                if (!notifModal.contains(e.target) && e.target !== notifBtn) {
                    notifModal.style.display = 'none';
                }
            });

            function fetchNotifMock() {
                notifBody.innerHTML = '<div style="padding: 1rem 0;">Buscando eventos...</div>';
                
                // Hacemos una consulta rápida a un endpoint local o simulamos en JS de forma elegante
                const hoy = new Date();
                const dia = hoy.getDate();
                const mes = hoy.getMonth() + 1;
                
                // Podemos hacer fetch de cumpleaños o bajas reales
                notifBody.innerHTML = `
                    <div style="display: flex; flex-direction: column; gap: 0.75rem; text-align: left;">
                        <div style="padding: 0.5rem; border-radius: 8px; background-color: var(--primary-light); color: var(--primary); font-size: 0.8rem; font-weight: 500;">
                            🎂 ¡Cumpleaños de hoy!
                            <div style="font-weight: normal; margin-top: 0.2rem; color: var(--text-main);">Verifica la lista de diario para felicitarlos.</div>
                        </div>
                        <div style="padding: 0.5rem; border-radius: 8px; background-color: var(--bg-page); border: 1px solid var(--border-color); font-size: 0.8rem;">
                            📅 Evento Diario
                            <div style="margin-top: 0.2rem; color: var(--text-muted);">Sin eventos extraordinarios reportados.</div>
                        </div>
                    </div>
                `;
            }
        });

        // Control de Sub-Menús del Sidebar
        window.toggleSidebarMenu = function(element) {
            const sidebar = document.getElementById('appSidebar');
            const wrapper = document.getElementById('appWrapper');
            
            if (sidebar.classList.contains('collapsed')) {
                sidebar.classList.remove('collapsed');
                wrapper.classList.remove('expanded');
                localStorage.setItem('sidebar-collapsed', 'false');
            }
            
            element.classList.toggle('active');
            const subitems = element.nextElementSibling;
            if (subitems) {
                subitems.classList.toggle('hidden');
            }
        }
    </script>
    @stack('scripts')
</body>
</html>
