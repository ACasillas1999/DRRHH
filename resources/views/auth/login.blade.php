<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Recursos Humanos</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-page: #f8fafc; --bg-card: #ffffff; --primary: #1d4ed8; --primary-light: #dbeafe;
            --text-main: #0f172a; --text-muted: #64748b; --border-color: #e2e8f0;
            --shadow-lg: 0 10px 25px -5px rgba(0,0,0,0.02), 0 4px 6px -4px rgba(0,0,0,0.02);
            --baja: #dc2626; --baja-bg: #fef2f2;
        }
        [data-theme="dark"] {
            --bg-page: #0b0f19; --bg-card: #151f32; --primary: #3b82f6; --primary-light: #1e293b;
            --text-main: #f8fafc; --text-muted: #94a3b8; --border-color: #223049;
            --shadow-lg: 0 10px 25px -5px rgba(0,0,0,0.4);
            --baja: #f87171; --baja-bg: #2d1f23;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg-page);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }
        .login-card {
            background-color: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            box-shadow: var(--shadow-lg);
            padding: 2.5rem;
            width: 100%;
            max-width: 380px;
        }
        .logo-box {
            width: 52px; height: 52px;
            background: linear-gradient(135deg, var(--primary) 0%, #4f46e5 100%);
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            color: white; font-weight: 700; font-size: 1.3rem;
            margin: 0 auto 1.25rem;
        }
        h1 { font-size: 1.4rem; font-weight: 700; text-align: center; letter-spacing: -0.02em; }
        .subtitle { text-align: center; color: var(--text-muted); font-size: 0.85rem; margin-top: 0.35rem; margin-bottom: 1.75rem; }
        .campo { margin-bottom: 1rem; }
        .campo label { display: block; font-size: 0.75rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.03em; margin-bottom: 0.4rem; }
        .campo input {
            width: 100%; padding: 0.65rem 0.85rem; border-radius: 10px;
            border: 1px solid var(--border-color); background-color: var(--bg-page);
            color: var(--text-main); font-family: inherit; font-size: 0.9rem;
        }
        .campo input:focus { outline: none; border-color: var(--primary); }
        .btn-login {
            width: 100%; padding: 0.75rem; border-radius: 10px; border: none;
            background-color: var(--primary); color: white; font-weight: 600;
            font-size: 0.9rem; cursor: pointer; margin-top: 0.5rem;
        }
        .btn-login:hover { opacity: 0.9; }
        .error-msg {
            background-color: var(--baja-bg); border: 1px solid var(--baja); color: var(--baja);
            border-radius: 10px; padding: 0.7rem 0.9rem; font-size: 0.8rem; margin-bottom: 1rem;
        }
    </style>
</head>
<body data-theme="light">
    <div class="login-card">
        <div class="logo-box">AS</div>
        <h1>Grupo Ascencio</h1>
        <p class="subtitle">Recursos Humanos</p>

        @if ($errors->any())
            <div class="error-msg">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="campo">
                <label for="username">Usuario</label>
                <input type="text" id="username" name="username" value="{{ old('username') }}" required autofocus>
            </div>
            <div class="campo">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn-login">Iniciar Sesión</button>
        </form>
    </div>

    <script>
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme) document.body.setAttribute('data-theme', savedTheme);
    </script>
</body>
</html>
