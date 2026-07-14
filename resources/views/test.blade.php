<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conexión de Base de Datos - DRRHH</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-color: #0d0e12;
            --card-bg: rgba(22, 24, 33, 0.7);
            --border-color: rgba(255, 255, 255, 0.08);
            --text-primary: #f3f4f6;
            --text-secondary: #9ca3af;
            --primary: #6366f1;
            --primary-glow: rgba(99, 102, 241, 0.15);
            --success: #10b981;
            --success-bg: rgba(16, 185, 129, 0.1);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            background-image: 
                radial-gradient(circle at 10% 20%, rgba(99, 102, 241, 0.05) 0%, transparent 40%),
                radial-gradient(circle at 90% 80%, rgba(16, 185, 129, 0.05) 0%, transparent 40%);
        }

        .container {
            width: 100%;
            max-width: 900px;
            background: var(--card-bg);
            backdrop-filter: blur(12px);
            border: 1px solid var(--border-color);
            border-radius: 24px;
            padding: 2.5rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3), inset 0 1px 0 rgba(255, 255, 255, 0.05);
        }

        .header {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: var(--success-bg);
            color: var(--success);
            padding: 0.5rem 1rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 500;
            border: 1px solid rgba(16, 185, 129, 0.2);
            margin-bottom: 1rem;
        }

        .badge-dot {
            width: 8px;
            height: 8px;
            background-color: var(--success);
            border-radius: 50%;
            animation: pulse 2s infinite;
        }

        h1 {
            font-size: 2.25rem;
            font-weight: 700;
            background: linear-gradient(to right, #ffffff, #9ca3af);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 0.5rem;
        }

        .subtitle {
            color: var(--text-secondary);
            font-size: 1rem;
        }

        .employee-list {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }

        .employee-card {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s ease;
        }

        .employee-card:hover {
            transform: translateY(-2px);
            border-color: rgba(99, 102, 241, 0.3);
            box-shadow: 0 10px 20px rgba(99, 102, 241, 0.05);
            background: rgba(255, 255, 255, 0.03);
        }

        .employee-info h2 {
            font-size: 1.125rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .employee-meta {
            display: flex;
            gap: 1rem;
            font-size: 0.875rem;
            color: var(--text-secondary);
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .employee-salary {
            text-align: right;
        }

        .salary-label {
            font-size: 0.75rem;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.25rem;
        }

        .salary-value {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--primary);
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
            color: var(--text-secondary);
        }

        @keyframes pulse {
            0% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
            }
            70% {
                transform: scale(1);
                box-shadow: 0 0 0 8px rgba(16, 185, 129, 0);
            }
            100% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(16, 185, 129, 0);
            }
        }

        @media (max-width: 640px) {
            .employee-card {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
            .employee-salary {
                text-align: left;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="badge">
                <span class="badge-dot"></span>
                Conectado a MySQL (gpoascen_rh)
            </div>
            <h1>Control de Empleados</h1>
            <p class="subtitle">Muestra en tiempo real de los datos leídos con Laravel Eloquent (Modo Seguro)</p>
        </div>

        <div class="employee-list">
            @forelse($empleados as $empleado)
                <div class="employee-card">
                    <div class="employee-info">
                        <h2>{{ $empleado->Nombre_Completo }}</h2>
                        <div class="employee-meta">
                            <div class="meta-item">
                                <strong>Nº:</strong> {{ $empleado->N_Empleado }}
                            </div>
                            @if($empleado->laboral)
                                <div class="meta-item">
                                    <strong>Estatus:</strong> {{ $empleado->laboral->Estatus ?? 'No definido' }}
                                </div>
                                <div class="meta-item">
                                    <strong>Horario:</strong> {{ $empleado->laboral->Horario ?? 'N/D' }}
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="employee-salary">
                        @if($empleado->laboral)
                            <div class="salary-label">Sueldo Neto</div>
                            <div class="salary-value">{{ $empleado->laboral->Sueldo_Neto ?: 'N/D' }}</div>
                        @else
                            <div class="salary-label">Laboral</div>
                            <div class="salary-value" style="color: var(--text-secondary); font-size: 1rem;">Sin registro</div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="empty-state">
                    No se encontraron registros de empleados en la base de datos.
                </div>
            @endforelse
        </div>
    </div>
</body>
</html>
