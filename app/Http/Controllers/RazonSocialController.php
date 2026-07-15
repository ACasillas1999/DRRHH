<?php

namespace App\Http\Controllers;

use App\Models\RazonSocial;
use App\Models\LaboralEmpleado;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RazonSocialController extends Controller
{
    public function index(Request $request): View
    {
        $busqueda = $request->input('busqueda');
        
        $query = RazonSocial::orderBy('Nomenclatura');

        if (!empty($busqueda)) {
            $query->where(function($q) use ($busqueda) {
                $q->where('Nomenclatura', 'like', '%' . $busqueda . '%')
                  ->orWhere('Razon_Social_IMSS', 'like', '%' . $busqueda . '%')
                  ->orWhere('Representante_Legal', 'like', '%' . $busqueda . '%')
                  ->orWhere('RFC_Empresa', 'like', '%' . $busqueda . '%');
            });
        }

        $razonesSociales = $query->paginate(15);

        return view('razones-sociales.index', compact('razonesSociales', 'busqueda'));
    }

    public function create(): View
    {
        return view('razones-sociales.crear');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'Nomenclatura' => ['required', 'string', 'max:255', 'unique:razones_sociales,Nomenclatura'],
            'Razon_Social_IMSS' => ['required', 'string', 'max:255'],
            'Representante_Legal' => ['required', 'string', 'max:255'],
            'RFC_Empresa' => ['required', 'string', 'max:13'],
            'Registro_Patronal' => ['required', 'string', 'max:50'],
            'Declaraciones_A' => ['required', 'string', 'max:500'],
            'Declaraciones_B' => ['required', 'string', 'max:500'],
            'Declaraciones_F' => ['required', 'string', 'max:255'],
        ]);

        RazonSocial::create($data);

        return redirect()->route('razones-sociales.index')
            ->with('status', 'Razón Social registrada correctamente.');
    }

    public function edit(string $id): View
    {
        $razonSocial = RazonSocial::findOrFail($id);
        return view('razones-sociales.editar', compact('razonSocial'));
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $razonSocial = RazonSocial::findOrFail($id);

        $data = $request->validate([
            'Nomenclatura' => ['required', 'string', 'max:255', 'unique:razones_sociales,Nomenclatura,' . $id],
            'Razon_Social_IMSS' => ['required', 'string', 'max:255'],
            'Representante_Legal' => ['required', 'string', 'max:255'],
            'RFC_Empresa' => ['required', 'string', 'max:13'],
            'Registro_Patronal' => ['required', 'string', 'max:50'],
            'Declaraciones_A' => ['required', 'string', 'max:500'],
            'Declaraciones_B' => ['required', 'string', 'max:500'],
            'Declaraciones_F' => ['required', 'string', 'max:255'],
        ]);

        $razonSocial->update($data);

        return redirect()->route('razones-sociales.index')
            ->with('status', 'Razón Social actualizada correctamente.');
    }

    public function destroy(string $id): RedirectResponse
    {
        $razonSocial = RazonSocial::findOrFail($id);

        // Validar si hay empleados asociados
        $empleadosAsociados = LaboralEmpleado::where('Nomeclatura', $razonSocial->Nomenclatura)->count();
        if ($empleadosAsociados > 0) {
            return redirect()->route('razones-sociales.index')
                ->with('error', "No es posible eliminar esta Razón Social porque está asignada a {$empleadosAsociados} empleado(s).");
        }

        $razonSocial->delete();

        return redirect()->route('razones-sociales.index')
            ->with('status', 'Razón Social eliminada correctamente.');
    }
}
