<?php

namespace App\Http\Controllers;

use App\Models\Responsable;
use App\Models\Puesto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ResponsableController extends Controller
{
    public function index(): View
    {
        $responsables = \DB::table('Responsables as r')
            ->join('personales as p', 'r.Responsable', '=', 'p.N_Empleado')
            ->select('r.*', 'p.N_Empleado', 'p.Nombre_Completo')
            ->orderBy('r.Departamento')
            ->get();

        $departamentosList = Puesto::distinct()
            ->whereNotNull('Departamento')
            ->where('Departamento', '!=', '')
            ->orderBy('Departamento')
            ->pluck('Departamento');

        return view('responsables.index', compact('responsables', 'departamentosList'));
    }

    public function store(Request $request): RedirectResponse
    {
        $val = $request->validate([
            'departamento'  => ['required', 'string', 'max:255'],
            'subdepartamento' => ['required', 'string', 'max:255'],
            'responsable'   => ['required', 'string', 'max:255'],
        ]);

        Responsable::create([
            'Departamento'    => $val['departamento'],
            'SubDepartamento' => $val['subdepartamento'],
            'Responsable'     => $val['responsable'],
        ]);

        return redirect()->route('responsables.index')
            ->with('status', 'Responsable asignado correctamente.');
    }

    public function destroy(string $id): RedirectResponse
    {
        $responsable = Responsable::findOrFail($id);
        $responsable->delete();

        return redirect()->route('responsables.index')
            ->with('status', 'Asignación de responsable eliminada correctamente.');
    }
}
