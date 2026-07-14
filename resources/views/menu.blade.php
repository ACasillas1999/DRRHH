<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menú Principal - Recursos Humanos</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Paleta de Colores y Variables de Diseño */
        :root {
            /* Modo Claro (Azul y Blanco) */
            --bg-page: #f8fafc;
            --bg-card: #ffffff;
            --bg-card-hover: #f1f5f9;
            --primary: #1d4ed8; /* Azul cobalto vibrante */
            --primary-light: #dbeafe;
            --primary-glow: rgba(29, 78, 216, 0.08);
            --text-main: #0f172a; /* Pizarra oscuro */
            --text-muted: #64748b;
            --border-color: #e2e8f0;
            --shadow-card: 0 4px 6px -1px rgb(0 0 0 / 0.05), 0 2px 4px -2px rgb(0 0 0 / 0.05);
            --shadow-hover: 0 10px 25px -5px rgba(29, 78, 216, 0.1), 0 8px 10px -6px rgba(29, 78, 216, 0.05);
            --transition-speed: 0.3s;
            --logout-color: #ef4444;
            --logout-bg: #fef2f2;
        }

        [data-theme="dark"] {
            /* Modo Oscuro (Azul Oscuro Profundo) */
            --bg-page: #0b0f19;
            --bg-card: #151f32;
            --bg-card-hover: #1b2940;
            --primary: #3b82f6; /* Azul brillante */
            --primary-light: #1e293b;
            --primary-glow: rgba(59, 130, 246, 0.15);
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --border-color: #223049;
            --shadow-card: 0 4px 6px -1px rgb(0 0 0 / 0.3), 0 2px 4px -2px rgb(0 0 0 / 0.3);
            --shadow-hover: 0 12px 30px -5px rgba(59, 130, 246, 0.2), 0 8px 10px -6px rgba(59, 130, 246, 0.1);
            --logout-color: #f87171;
            --logout-bg: #2d1f23;
        }

        /* Estilos Globales */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            transition: background-color var(--transition-speed) ease, 
                        border-color var(--transition-speed) ease, 
                        color var(--transition-speed) ease, 
                        box-shadow var(--transition-speed) ease;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg-page);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            padding: 2rem;
            position: relative;
            overflow-x: hidden;
        }

        /* Decoración de Fondo (Glows) */
        body::before, body::after {
            content: '';
            position: absolute;
            width: 350px;
            height: 350px;
            border-radius: 50%;
            filter: blur(120px);
            z-index: -1;
            opacity: 0.5;
        }
        body::before {
            background-color: var(--primary-glow);
            top: -50px;
            left: -50px;
        }
        body::after {
            background-color: var(--primary-glow);
            bottom: -50px;
            right: -50px;
        }

        /* Cabecera */
        header {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto 2.5rem auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .brand-logo {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, var(--primary) 0%, #4f46e5 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 1.25rem;
            box-shadow: 0 4px 12px rgba(29, 78, 216, 0.2);
        }

        .brand-title {
            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: -0.02em;
        }

        .brand-subtitle {
            font-size: 0.75rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .controls {
            display: flex;
            align-items: center;
            gap: 1.25rem;
        }

        /* Selector de Rol para Demo */
        .role-selector-container {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background-color: var(--bg-card);
            border: 1px solid var(--border-color);
            padding: 0.35rem 0.75rem;
            border-radius: 10px;
            font-size: 0.875rem;
            box-shadow: var(--shadow-card);
        }

        .role-selector-container span {
            color: var(--text-muted);
            font-weight: 500;
        }

        .role-select {
            background: none;
            border: none;
            color: var(--primary);
            font-family: inherit;
            font-weight: 600;
            font-size: 0.875rem;
            outline: none;
            cursor: pointer;
        }

        /* Toggle de Modo Oscuro */
        .theme-toggle-btn {
            background-color: var(--bg-card);
            border: 1px solid var(--border-color);
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: var(--shadow-card);
            color: var(--text-main);
        }

        .theme-toggle-btn:hover {
            border-color: var(--primary);
            color: var(--primary);
        }

        /* Bienvenida */
        .welcome-section {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto 2rem auto;
            text-align: left;
        }

        .welcome-title {
            font-size: 1.85rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .welcome-text {
            color: var(--text-muted);
            font-size: 0.95rem;
        }

        /* Grid del Menú */
        .menu-grid {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 1.5rem;
            flex-grow: 1;
        }

        /* Tarjeta de Menú */
        .menu-card {
            background-color: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 1.75rem;
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
            text-decoration: none;
            color: inherit;
            cursor: pointer;
            box-shadow: var(--shadow-card);
            transition: transform 0.2s cubic-bezier(0.16, 1, 0.3, 1),
                        border-color 0.2s ease,
                        box-shadow 0.2s ease;
            position: relative;
            overflow: hidden;
        }

        .menu-card:hover {
            transform: translateY(-4px);
            border-color: var(--primary);
            box-shadow: var(--shadow-hover);
        }

        /* Ocultar tarjetas según el rol (con animación suave) */
        .menu-card.hidden-role {
            display: none;
        }

        .menu-card-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            background-color: var(--primary-light);
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.3s ease;
        }

        .menu-card:hover .menu-card-icon {
            transform: scale(1.1) rotate(3deg);
        }

        .menu-card-info {
            display: flex;
            flex-direction: column;
            gap: 0.35rem;
        }

        .menu-card-title {
            font-size: 1.15rem;
            font-weight: 600;
            letter-spacing: -0.01em;
        }

        .menu-card-desc {
            color: var(--text-muted);
            font-size: 0.85rem;
            line-height: 1.4;
        }

        /* Flecha de enlace */
        .menu-card-arrow {
            position: absolute;
            bottom: 1.5rem;
            right: 1.5rem;
            color: var(--text-muted);
            opacity: 0;
            transform: translateX(-5px);
            transition: opacity 0.25s ease, transform 0.25s ease;
        }

        .menu-card:hover .menu-card-arrow {
            opacity: 1;
            transform: translateX(0);
            color: var(--primary);
        }

        /* Tarjeta de Logout */
        .menu-card-logout {
            border-color: rgba(239, 68, 68, 0.15);
        }

        .menu-card-logout .menu-card-icon {
            background-color: var(--logout-bg);
            color: var(--logout-color);
        }

        .menu-card-logout:hover {
            border-color: var(--logout-color);
            box-shadow: 0 10px 25px -5px rgba(239, 68, 68, 0.15);
        }

        .menu-card-logout:hover .menu-card-arrow {
            color: var(--logout-color);
        }

        /* Footer */
        footer {
            margin-top: 3rem;
            text-align: center;
            font-size: 0.85rem;
            color: var(--text-muted);
            padding: 1rem 0;
            border-top: 1px solid var(--border-color);
        }

        /* Responsive Layout */
        @media (max-width: 640px) {
            body {
                padding: 1rem;
            }
            header {
                flex-direction: column;
                align-items: flex-start;
            }
            .controls {
                width: 100%;
                justify-content: space-between;
            }
        }
    </style>
</head>
<body data-theme="light">

    <!-- Cabecera Principal -->
    <header>
        <div class="brand">
            <div class="brand-logo">RH</div>
            <div>
                <h1 class="brand-title">Grupo Ascencio</h1>
                <div class="brand-subtitle">Recursos Humanos</div>
            </div>
        </div>
        
        <div class="controls">
            <!-- Selector de Roles interactivo para pruebas -->
            <div class="role-selector-container">
                <span>Rol Actual:</span>
                <select id="roleSelector" class="role-select">
                    <option value="Admin">Administrador</option>
                    <option value="RH">Recursos Humanos</option>
                    <option value="Nominas">Nóminas</option>
                    <option value="Jefe">Jefe</option>
                    <option value="Gerente">Gerente</option>
                    <option value="Encargado">Encargado</option>
                </select>
            </div>

            <!-- Botón alternador de tema claro/oscuro -->
            <button id="themeToggle" class="theme-toggle-btn" title="Alternar Modo Oscuro">
                <svg id="themeIconSun" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: block;">
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
                <svg id="themeIconMoon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: none;">
                    <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
                </svg>
            </</button>
        </div>
    </header>

    <!-- Sección de Bienvenida -->
    <div class="welcome-section">
        <h2 class="welcome-title">Bienvenido de nuevo</h2>
        <p class="welcome-text">Selecciona una de las siguientes opciones para gestionar el sistema de personal.</p>
    </div>

    <!-- Grid de Tarjetas del Menú -->
    <div class="menu-grid" id="menuGrid">
        
        <!-- 1. Altas/Bajas IMSS -->
        <a class="menu-card" data-roles="Admin,RH,Nominas" href="#altas-imss">
            <div class="menu-card-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="8.5" cy="7" r="4"></circle>
                    <line x1="20" y1="8" x2="20" y2="14"></line>
                    <line x1="17" y1="11" x2="23" y2="11"></line>
                </svg>
            </div>
            <div class="menu-card-info">
                <h3 class="menu-card-title">Altas/Bajas IMSS</h3>
                <p class="menu-card-desc">Registra e informa altas y bajas del seguro social del personal.</p>
            </div>
            <div class="menu-card-arrow">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
            </div>
        </a>

        <!-- 2. Razones Sociales -->
        <a class="menu-card" data-roles="Admin,RH" href="#razones-sociales">
            <div class="menu-card-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                    <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                </svg>
            </div>
            <div class="menu-card-info">
                <h3 class="menu-card-title">Razones Sociales</h3>
                <p class="menu-card-desc">Administra las razones sociales y registros patronales de las empresas.</p>
            </div>
            <div class="menu-card-arrow">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
            </div>
        </a>

        <!-- 3. Puestos -->
        <a class="menu-card" data-roles="Admin,RH" href="#puestos">
            <div class="menu-card-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
            </div>
            <div class="menu-card-info">
                <h3 class="menu-card-title">Puestos</h3>
                <p class="menu-card-desc">Configura departamentos, subdepartamentos y catálogo de puestos.</p>
            </div>
            <div class="menu-card-arrow">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
            </div>
        </a>

        <!-- 4. Empleados -->
        <a class="menu-card" data-roles="Admin,RH,Nominas" href="/test-db">
            <div class="menu-card-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="16" y1="13" x2="8" y2="13"></line>
                    <line x1="16" y1="17" x2="8" y2="17"></line>
                    <polyline points="10 9 9 9 8 9"></polyline>
                </svg>
            </div>
            <div class="menu-card-info">
                <h3 class="menu-card-title">Empleados</h3>
                <p class="menu-card-desc">Accede al expediente digital y administración laboral del personal.</p>
            </div>
            <div class="menu-card-arrow">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
            </div>
        </a>

        <!-- 5. Reportes -->
        <a class="menu-card" data-roles="Admin,RH" href="#reportes">
            <div class="menu-card-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="20" x2="18" y2="10"></line>
                    <line x1="12" y1="20" x2="12" y2="4"></line>
                    <line x1="6" y1="20" x2="6" y2="14"></line>
                </svg>
            </div>
            <div class="menu-card-info">
                <h3 class="menu-card-title">Reportes</h3>
                <p class="menu-card-desc">Genera reportes generales, estadísticas y exportación de datos en Excel.</p>
            </div>
            <div class="menu-card-arrow">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
            </div>
        </a>

        <!-- 6. Usuarios -->
        <a class="menu-card" data-roles="Admin" href="#usuarios">
            <div class="menu-card-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                </svg>
            </div>
            <div class="menu-card-info">
                <h3 class="menu-card-title">Usuarios</h3>
                <p class="menu-card-desc">Configura accesos, contraseñas y permisos del sistema.</p>
            </div>
            <div class="menu-card-arrow">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
            </div>
        </a>

        <!-- 7. Historial -->
        <a class="menu-card" data-roles="Admin,RH" href="#historial">
            <div class="menu-card-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
            </div>
            <div class="menu-card-info">
                <h3 class="menu-card-title">Historial</h3>
                <p class="menu-card-desc">Visualiza la bitácora de cambios y estados históricos de la plantilla.</p>
            </div>
            <div class="menu-card-arrow">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
            </div>
        </a>

        <!-- 8. Inventario -->
        <a class="menu-card" data-roles="Admin,RH" href="#inventario">
            <div class="menu-card-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                    <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                    <line x1="12" y1="22.08" x2="12" y2="12"></line>
                </svg>
            </div>
            <div class="menu-card-info">
                <h3 class="menu-card-title">Inventario</h3>
                <p class="menu-card-desc">Controla los artículos y asignaciones a los empleados.</p>
            </div>
            <div class="menu-card-arrow">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
            </div>
        </a>

        <!-- 9. Vacaciones -->
        <a class="menu-card" data-roles="Admin,RH,Jefe,Gerente,Encargado,Nominas" href="#vacaciones">
            <div class="menu-card-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                </svg>
            </div>
            <div class="menu-card-info">
                <h3 class="menu-card-title">Vacaciones</h3>
                <p class="menu-card-desc">Gestiona saldos, solicitudes y autorización de días de vacaciones.</p>
            </div>
            <div class="menu-card-arrow">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
            </div>
        </a>

        <!-- 10. Incapacidades -->
        <a class="menu-card" data-roles="Admin,RH" href="#incapacidades">
            <div class="menu-card-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 12h-4l-3 9L9 3l-3 9H2"></path>
                </svg>
            </div>
            <div class="menu-card-info">
                <h3 class="menu-card-title">Incapacidades</h3>
                <p class="menu-card-desc">Administración y control de justificantes médicos e incapacidades.</p>
            </div>
            <div class="menu-card-arrow">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
            </div>
        </a>

        <!-- 11. Diario -->
        <a class="menu-card" data-roles="Admin,RH" href="#diario">
            <div class="menu-card-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="16" y1="2" x2="16" y2="6"></line>
                    <line x1="8" y1="2" x2="8" y2="6"></line>
                    <line x1="3" y1="10" x2="21" y2="10"></line>
                </svg>
            </div>
            <div class="menu-card-info">
                <h3 class="menu-card-title">Diario</h3>
                <p class="menu-card-desc">Calendario y agenda de eventos para el seguimiento diario del equipo.</p>
            </div>
            <div class="menu-card-arrow">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
            </div>
        </a>

        <!-- 12. Incidencias -->
        <a class="menu-card" data-roles="Admin,RH,Jefe,Gerente,Encargado,Nominas" href="#incidencias">
            <div class="menu-card-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                    <line x1="12" y1="9" x2="12" y2="13"></line>
                    <line x1="12" y1="17" x2="12.01" y2="17"></line>
                </svg>
            </div>
            <div class="menu-card-info">
                <h3 class="menu-card-title">Incidencias</h3>
                <p class="menu-card-desc">Reporte y seguimiento de faltas, retardos y asistencias.</p>
            </div>
            <div class="menu-card-arrow">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
            </div>
        </a>

        <!-- 13. Reader -->
        <a class="menu-card" data-roles="Admin,RH" href="#reader">
            <div class="menu-card-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 7V4a2 2 0 0 1 2-2h3"></path>
                    <path d="M20 7V4a2 2 0 0 0-2-2h-3"></path>
                    <path d="M4 17v3a2 2 0 0 0 2 2h3"></path>
                    <path d="M20 17v3a2 2 0 0 1-2 2h-3"></path>
                    <line x1="12" y1="8" x2="12" y2="16"></line>
                    <line x1="8" y1="12" x2="16" y2="12"></line>
                </svg>
            </div>
            <div class="menu-card-info">
                <h3 class="menu-card-title">Reader</h3>
                <p class="menu-card-desc">Lector digital y herramientas de escaneo y consulta rápida.</p>
            </div>
            <div class="menu-card-arrow">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
            </div>
        </a>

        <!-- 14. Reclutamiento -->
        <a class="menu-card" data-roles="Admin,RH" href="#reclutamiento">
            <div class="menu-card-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    <path d="M11 8v6"></path>
                    <path d="M8 11h6"></path>
                </svg>
            </div>
            <div class="menu-card-info">
                <h3 class="menu-card-title">Reclutamiento</h3>
                <p class="menu-card-desc">Bolsa de trabajo, vacantes y proceso de reclutamiento de candidatos.</p>
            </div>
            <div class="menu-card-arrow">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
            </div>
        </a>

        <!-- Botón de Cerrar Sesión -->
        <a class="menu-card menu-card-logout" data-roles="Admin,RH,Nominas,Jefe,Gerente,Encargado" href="#logout">
            <div class="menu-card-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                    <polyline points="16 17 21 12 16 7"></polyline>
                    <line x1="21" y1="12" x2="9" y2="12"></line>
                </svg>
            </div>
            <div class="menu-card-info">
                <h3 class="menu-card-title" style="color: var(--logout-color)">Cerrar Sesión</h3>
                <p class="menu-card-desc">Salir de forma segura de tu cuenta en el sistema de personal.</p>
            </div>
            <div class="menu-card-arrow">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
            </div>
        </a>

    </div>

    <!-- Pie de página -->
    <footer>
        <p>&copy; 2026 Grupo Ascencio. Todos los derechos reservados.</p>
    </footer>

    <!-- Script de Interactividad -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const roleSelector = document.getElementById('roleSelector');
            const themeToggle = document.getElementById('themeToggle');
            const sunIcon = document.getElementById('themeIconSun');
            const moonIcon = document.getElementById('themeIconMoon');
            const menuCards = document.querySelectorAll('.menu-card');

            // 1. Filtrado dinámico de tarjetas por Rol
            function filterMenuByRole(role) {
                menuCards.forEach(card => {
                    const rolesPermitidos = card.getAttribute('data-roles').split(',');
                    if (rolesPermitidos.includes(role)) {
                        card.classList.remove('hidden-role');
                    } else {
                        card.classList.add('hidden-role');
                    }
                });
            }

            roleSelector.addEventListener('change', (e) => {
                filterMenuByRole(e.target.value);
            });

            // Cargar rol inicial
            filterMenuByRole(roleSelector.value);

            // 2. Control de Tema Oscuro / Claro
            function setTheme(theme) {
                document.body.setAttribute('data-theme', theme);
                localStorage.setItem('theme', theme);

                if (theme === 'dark') {
                    sunIcon.style.display = 'none';
                    moonIcon.style.display = 'block';
                } else {
                    sunIcon.style.display = 'block';
                    moonIcon.style.display = 'none';
                }
            }

            themeToggle.addEventListener('click', () => {
                const currentTheme = document.body.getAttribute('data-theme');
                const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
                setTheme(newTheme);
            });

            // Cargar preferencia guardada o por esquema de sistema
            const savedTheme = localStorage.getItem('theme');
            const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            
            if (savedTheme) {
                setTheme(savedTheme);
            } else if (systemPrefersDark) {
                setTheme('dark');
            } else {
                setTheme('light');
            }
        });
    </script>
</body>
</html>
