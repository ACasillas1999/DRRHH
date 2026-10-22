<?php

namespace App\Http\Controllers;

use App\Models\HistorialCambio;
use App\Models\LaboralEmpleado;
use App\Models\Puesto;
use App\Models\RazonSocial;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmpleadoLaboralesController extends Controller
{
    public function edit(string $n_empleado)
    {
        $laboral = LaboralEmpleado::with('puesto')->where('N_Empleado', $n_empleado)->firstOrFail();
        $puestos = Puesto::orderBy('Nombre_Puesto')->get(['ID', 'Nombre_Puesto', 'Departamento']);
        $razonesSociales = RazonSocial::orderBy('Nomenclatura')->get(['Nomenclatura']);

        return view('empleados.editar-laborales', compact('laboral', 'puestos', 'razonesSociales'));
    }

    public function update(Request $request, string $n_empleado): RedirectResponse
    {
        $laboral = LaboralEmpleado::where('N_Empleado', $n_empleado)->firstOrFail();

        $data = $request->validate([
            'ID_Puesto' => ['required', 'exists:puestos,ID'],
            'Nomeclatura' => ['nullable', 'string', 'max:100'],
            'Fisica' => ['nullable', 'string', 'max:100'],
            'Salario_Diario' => ['nullable', 'numeric'],
            'Sueldo' => ['nullable', 'numeric'],
            'Salario_Diario_Neto' => ['nullable', 'numeric'],
            'Sueldo_Neto' => ['nullable', 'numeric'],
            'Hora_Comida' => ['nullable', 'string', 'max:50'],
            'Efectivo' => ['nullable', 'numeric'],
            'Comision' => ['nullable', 'numeric'],
            'KPI' => ['nullable', 'numeric'],
            'Horario' => ['nullable', 'string', 'max:100'],
            'Telefono_Empresarial' => ['nullable', 'string', 'max:30'],
            'Extension' => ['nullable', 'string', 'max:30'],
            'Correo_Empresarial' => ['nullable', 'email', 'max:150'],
        ]);

        $data['Nomeclatura'] = $data['Nomeclatura'] ?? null;
        foreach (['Salario_Diario', 'Sueldo', 'Salario_Diario_Neto', 'Sueldo_Neto', 'Efectivo', 'Comision', 'KPI'] as $campo) {
            $data[$campo] = $data[$campo] ?? 0;
        }
        foreach (['Fisica', 'Hora_Comida', 'Horario', 'Telefono_Empresarial', 'Extension', 'Correo_Empresarial'] as $campo) {
            $data[$campo] = $data[$campo] ?? '';
        }

        $laboral->update($data);

        HistorialCambio::create([
            'Usuario_ID' => auth()->id(),
            'N_Empleado' => $n_empleado,
            'Cambio' => "Actualización de datos laborales del empleado con ID: {$n_empleado}",
            'Fecha_Hora' => now(),
        ]);

        return redirect()->route('empleados.show', $n_empleado)->with('status', 'Datos laborales actualizados correctamente.');
    }
}
