<?php

namespace App\Http\Controllers;

use App\Models\HistorialCambio;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HistorialController extends Controller
{
    public function index(Request $request): View
    {
        $fecha = $request->input('fecha', '');
        $usuario = $request->input('usuario', '');
        $empleado = $request->input('empleado', '');
        $cambio = $request->input('cambio', '');

        $historial = HistorialCambio::with(['personal', 'usuario'])
            ->when($fecha, fn ($q) => $q->whereDate('Fecha_Hora', $fecha))
            ->when($usuario, fn ($q) => $q->whereHas('usuario', fn ($q2) => $q2->where('Nombre', 'like', "%{$usuario}%")))
            ->when($empleado, fn ($q) => $q->whereHas('personal', fn ($q2) => $q2->where('Nombre_Completo', 'like', "%{$empleado}%")))
            ->when($cambio, fn ($q) => $q->where('Cambio', 'like', "%{$cambio}%"))
            ->orderByDesc('Fecha_Hora')
            ->paginate(100)
            ->withQueryString();

        return view('historial.index', compact('historial', 'fecha', 'usuario', 'empleado', 'cambio'));
    }
}
