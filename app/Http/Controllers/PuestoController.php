<?php

namespace App\Http\Controllers;

use App\Models\Puesto;
use App\Models\HistorialCambio;
use App\Models\LaboralEmpleado;
use App\Models\Ubicacion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Validation\ValidationException;

class PuestoController extends Controller
{
    public function index(Request $request): View
    {
        $busqueda = $request->input('busqueda');

        $puestosQuery = Puesto::orderBy('Departamento')->orderBy('SubDepartamento')->orderBy('Nombre_Puesto');
        if (!empty($busqueda)) {
            $puestosQuery->where(function($q) use ($busqueda) {
                $q->where('Nombre_Puesto', 'like', '%' . $busqueda . '%')
                  ->orWhere('Departamento', 'like', '%' . $busqueda . '%')
                  ->orWhere('SubDepartamento', 'like', '%' . $busqueda . '%');
            });
        }
        $puestosTodos = $puestosQuery->get();
        $puestosGrouped = [];
        foreach ($puestosTodos as $puesto) {
            $dep = $puesto->Departamento ?: 'Sin departamento';
            $sub = $puesto->SubDepartamento ?: 'Sin subdepartamento';
            if (!isset($puestosGrouped[$dep])) $puestosGrouped[$dep] = [];
            if (!isset($puestosGrouped[$dep][$sub])) $puestosGrouped[$dep][$sub] = [];
            $puestosGrouped[$dep][$sub][] = $puesto;
        }

        // Listas para los <datalist> del modal (autocompletar con valores que ya existen)
        $nombresPuestos = Puesto::distinct()->whereNotNull('Nombre_Puesto')->where('Nombre_Puesto', '!=', '')->orderBy('Nombre_Puesto')->pluck('Nombre_Puesto');
        $departamentos = Puesto::distinct()->whereNotNull('Departamento')->where('Departamento', '!=', '')->orderBy('Departamento')->pluck('Departamento');
        $subdepartamentos = Puesto::distinct()->whereNotNull('SubDepartamento')->where('SubDepartamento', '!=', '')->orderBy('SubDepartamento')->pluck('SubDepartamento');

        return view('puestos.index', compact('puestosGrouped', 'busqueda', 'nombresPuestos', 'departamentos', 'subdepartamentos'));
    }

    public function organigrama(): View
    {
        $puestosTodos = Puesto::orderBy('Departamento')->orderBy('SubDepartamento')->orderBy('Nombre_Puesto')->get();
        $hierarchy = [];
        foreach ($puestosTodos as $puesto) {
            $dep = $puesto->Departamento ?: 'Sin departamento';
            $sub = $puesto->SubDepartamento ?: 'Sin subdepartamento';
            if (!isset($hierarchy[$dep])) $hierarchy[$dep] = [];
            if (!isset($hierarchy[$dep][$sub])) $hierarchy[$dep][$sub] = [];
            $hierarchy[$dep][$sub][] = $puesto;
        }
        return view('organigrama.index', compact('hierarchy'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'Nombre_Puesto' => ['required', 'string', 'max:250'],
            'Departamento' => ['required', 'string', 'max:255'],
            'SubDepartamento' => ['nullable', 'string', 'max:255'],
            'Descripcion' => ['nullable', 'string'],
        ]);

        $data['SubDepartamento'] = $data['SubDepartamento'] ?? '';
        $data['Descripcion'] = $data['Descripcion'] ?? '';
        $data = $this->normalizarYValidarDuplicado($data);

        Puesto::create($data);

        HistorialCambio::create([
            'Usuario_ID' => auth()->id(),
            'N_Empleado' => 0,
            'Cambio' => "Registro de Nuevo Puesto con Nombre: {$data['Nombre_Puesto']}",
            // Hora de México como el legacy (su date() usa date.timezone=America/Mexico_City); la app sigue en UTC
            'Fecha_Hora' => now('America/Mexico_City'),
        ]);


        return redirect()->route('puestos.index')
            ->with('status', 'Puesto de trabajo creado correctamente.');
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $puesto = Puesto::findOrFail($id);

        $data = $request->validate([
            'Nombre_Puesto' => ['required', 'string', 'max:250'],
            'Departamento' => ['required', 'string', 'max:255'],
            'SubDepartamento' => ['nullable', 'string', 'max:255'],
            'Descripcion' => ['nullable', 'string'],
        ]);

        $data['SubDepartamento'] = $data['SubDepartamento'] ?? '';
        $data['Descripcion'] = $data['Descripcion'] ?? '';
        $data = $this->normalizarYValidarDuplicado($data, $puesto->ID);

        $puesto->update($data);

        HistorialCambio::create([
            'Usuario_ID' => auth()->id(),
            'N_Empleado' => 0,
            'Cambio' => "Actualizacion de Puesto con Nombre: {$data['Nombre_Puesto']}",
            'Fecha_Hora' => now('America/Mexico_City'),
        ]);

        return redirect()->route('puestos.index')
            ->with('status', 'Puesto de trabajo actualizado correctamente.');
    }

    // Un puesto no se puede repetir: mismo nombre en el mismo departamento y subdepartamento.
    // Mayúsculas/minúsculas cuentan igual por la collation _ci de MySQL; los espacios dobles se juntan antes de comparar y guardar
    private function normalizarYValidarDuplicado(array $data, ?int $ignorarId = null): array
    {
        foreach (['Nombre_Puesto', 'Departamento', 'SubDepartamento'] as $campo) {
            $data[$campo] = preg_replace('/\s+/', ' ', trim($data[$campo]));
        }

        $existe = Puesto::where('Nombre_Puesto', $data['Nombre_Puesto'])
            ->where('Departamento', $data['Departamento'])
            ->where('SubDepartamento', $data['SubDepartamento'])
            ->when($ignorarId, fn ($q) => $q->where('ID', '!=', $ignorarId))
            ->exists();

        if ($existe) {
            throw ValidationException::withMessages([
                'Nombre_Puesto' => 'Ya existe el puesto "' . $data['Nombre_Puesto'] . '" en ' . $data['Departamento'] . ' / ' . ($data['SubDepartamento'] ?: 'sin subdepartamento') . '.',
            ]);
        }

        return $data;
    }

    public function destroy(string $id): RedirectResponse
    {
        $puesto = Puesto::findOrFail($id);

        $empleadosAsociados = LaboralEmpleado::where('ID_Puesto', $id)->count();
        if ($empleadosAsociados > 0) {
            return redirect()->route('puestos.index')
                ->with('error', "No es posible eliminar este puesto porque está asignado a {$empleadosAsociados} empleado(s).");
        }

        $puesto->delete();

        return redirect()->route('puestos.index')
            ->with('status', 'Puesto de trabajo eliminado correctamente.');
    }

    // --- Endpoints de API para Carga Dinámica de Responsables ---

    public function apiSubdepartamentos(Request $request): JsonResponse
    {
        $departamento = $request->input('departamento');
        if (empty($departamento)) {
            return response()->json([]);
        }

        $subdepartamentos = Puesto::where('Departamento', $departamento)
            ->whereNotNull('SubDepartamento')
            ->where('SubDepartamento', '!=', '')
            ->distinct()
            ->orderBy('SubDepartamento')
            ->pluck('SubDepartamento');

        return response()->json($subdepartamentos);
    }

    public function apiEmpleados(Request $request): JsonResponse
    {
        $departamento = $request->input('departamento');
        $subdepartamento = $request->input('subdepartamento');

        if (empty($departamento) || empty($subdepartamento)) {
            return response()->json([]);
        }

        if ($subdepartamento === 'JEFE DE DEPARTAMENTO') {
            $empleados = \App\Models\Personal::join('laborales_empleado as le', 'personales.N_Empleado', '=', 'le.N_Empleado')
                ->join('puestos as pu', 'le.ID_Puesto', '=', 'pu.ID')
                ->where('pu.Departamento', $departamento)
                ->select('personales.N_Empleado', 'personales.Nombre_Completo')
                ->orderBy('personales.Nombre_Completo')
                ->get();
        } else {
            $empleados = \App\Models\Personal::join('laborales_empleado as le', 'personales.N_Empleado', '=', 'le.N_Empleado')
                ->join('puestos as pu', 'le.ID_Puesto', '=', 'pu.ID')
                ->where('pu.Departamento', $departamento)
                ->where('pu.SubDepartamento', $subdepartamento)
                ->where('le.Estatus', 'ACTIVO')
                ->select('personales.N_Empleado', 'personales.Nombre_Completo')
                ->orderBy('personales.Nombre_Completo')
                ->get();
        }

        return response()->json($empleados);
    }
}
