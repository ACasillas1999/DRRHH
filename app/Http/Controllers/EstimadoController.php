<?php

namespace App\Http\Controllers;

use App\Models\EmpleadoEstimado;
use App\Models\Puesto;
use App\Models\Ubicacion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EstimadoController extends Controller
{
    public function index(): View
    {
        $estimadosTodos = EmpleadoEstimado::orderBy('Sucursal')->orderBy('Departamento')->orderBy('Puesto')->get();
        $estimadosPorSucursal = $estimadosTodos->groupBy('Sucursal');

        $sucursales = Ubicacion::orderBy('Ubicacion')->pluck('Ubicacion');
        $puestos = Puesto::orderBy('Nombre_Puesto')->get(['Nombre_Puesto', 'Departamento']);

        return view('estimados.index', compact('estimadosPorSucursal', 'sucursales', 'puestos'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'Sucursal' => ['required', 'string', 'max:150'],
            'Departamento' => ['required', 'string', 'max:150'],
            'Puesto' => ['required', 'string', 'max:150'],
            'Estimado_Empleados' => ['required', 'integer', 'min:0'],
        ]);

        EmpleadoEstimado::create($data);

        return redirect()->route('estimados.index')->with('status', 'Empleado estimado agregado correctamente.');
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $estimado = EmpleadoEstimado::findOrFail($id);

        $data = $request->validate([
            'Departamento' => ['required', 'string', 'max:150'],
            'Puesto' => ['required', 'string', 'max:150'],
            'Estimado_Empleados' => ['required', 'integer', 'min:0'],
        ]);

        $estimado->update($data);

        return redirect()->route('estimados.index')->with('status', 'Empleado estimado actualizado correctamente.');
    }

    public function destroy(string $id): RedirectResponse
    {
        EmpleadoEstimado::findOrFail($id)->delete();

        return redirect()->route('estimados.index')->with('status', 'Empleado estimado eliminado correctamente.');
    }
}
