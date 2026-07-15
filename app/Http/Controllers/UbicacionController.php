<?php

namespace App\Http\Controllers;

use App\Models\Ubicacion;
use App\Models\LaboralEmpleado;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UbicacionController extends Controller
{
    public function index(): View
    {
        $ubicaciones = Ubicacion::orderBy('Ubicacion')->get();
        return view('ubicaciones.index', compact('ubicaciones'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'Ubicacion' => ['required', 'string', 'max:255'],
            'Direccion' => ['required', 'string', 'max:255'],
        ]);

        Ubicacion::create($data);

        return redirect()->route('ubicaciones.index')
            ->with('status', 'Ubicación registrada correctamente.');
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $ubicacion = Ubicacion::findOrFail($id);

        $data = $request->validate([
            'Ubicacion' => ['required', 'string', 'max:255'],
            'Direccion' => ['required', 'string', 'max:255'],
        ]);

        $ubicacion->update($data);

        return redirect()->route('ubicaciones.index')
            ->with('status', 'Ubicación actualizada correctamente.');
    }

    public function destroy(string $id): RedirectResponse
    {
        $ubicacion = Ubicacion::findOrFail($id);

        $empleadosCount = LaboralEmpleado::where('Fisica', $ubicacion->Ubicacion)->count();
        if ($empleadosCount > 0) {
            return redirect()->route('ubicaciones.index')
                ->with('error', "No es posible eliminar esta ubicación porque está asignada a {$empleadosCount} empleado(s).");
        }

        $ubicacion->delete();

        return redirect()->route('ubicaciones.index')
            ->with('status', 'Ubicación eliminada correctamente.');
    }
}
