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
            // Texto libre como en el legacy (bind "s"): hay valores como "9000 LIBRES", "N/A", "$500.00 A MES VENCIDO"
            'Sueldo' => ['nullable', 'string', 'max:255'],
            'Salario_Diario_Neto' => ['nullable', 'numeric'],
            'Sueldo_Neto' => ['nullable', 'string', 'max:255'],
            'Hora_Comida' => ['nullable', 'string', 'max:50'],
            'Efectivo' => ['nullable', 'numeric'],
            'Comision' => ['nullable', 'string', 'max:255'],
            'KPI' => ['nullable', 'string', 'max:255'],
            'Horario' => ['nullable', 'string', 'max:100'],
            'Telefono_Empresarial' => ['nullable', 'string', 'max:30'],
            'Extension' => ['nullable', 'string', 'max:30'],
            'Correo_Empresarial' => ['nullable', 'email', 'max:150'],
        ]);

        $data['Nomeclatura'] = $data['Nomeclatura'] ?? null;
        // Vacío → 0 solo en los campos numéricos del legacy (Salario_Diario, Salario_Diario_Neto, Efectivo)
        foreach (['Salario_Diario', 'Salario_Diario_Neto', 'Efectivo'] as $campo) {
            $data[$campo] = $data[$campo] ?? 0;
        }
        // Vacío → '' en los campos de texto, igual que el legacy (ya NO se convierte KPI/Comisión a 0)
        foreach (['Fisica', 'Hora_Comida', 'Horario', 'Telefono_Empresarial', 'Extension', 'Correo_Empresarial', 'Sueldo', 'Sueldo_Neto', 'Comision', 'KPI'] as $campo) {
            $data[$campo] = $data[$campo] ?? '';
        }

        $laboral->update($data);

        HistorialCambio::create([
            'Usuario_ID' => auth()->id(),
            'N_Empleado' => $n_empleado,
            'Cambio' => "Actualización de datos laborales del empleado con ID: {$n_empleado}",
            // Hora de México como el legacy (su date() usa date.timezone=America/Mexico_City); la app sigue en UTC
            'Fecha_Hora' => now('America/Mexico_City'),
        ]);

        return redirect()->route('empleados.show', $n_empleado)->with('status', 'Datos laborales actualizados correctamente.');
    }
}
