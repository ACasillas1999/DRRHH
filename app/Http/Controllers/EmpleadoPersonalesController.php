<?php

namespace App\Http\Controllers;

use App\Models\HistorialCambio;
use App\Models\Personal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmpleadoPersonalesController extends Controller
{
    public function edit(string $n_empleado)
    {
        $empleado = Personal::where('N_Empleado', $n_empleado)->firstOrFail();

        return view('empleados.editar-personales', compact('empleado'));
    }

    public function update(Request $request, string $n_empleado): RedirectResponse
    {
        $empleado = Personal::where('N_Empleado', $n_empleado)->firstOrFail();

        $data = $request->validate([
            'Nombre_Completo' => ['required', 'string', 'max:255'],
            'IMSS' => ['nullable', 'string', 'max:50'],
            'CURP' => ['nullable', 'string', 'max:50'],
            'RFC' => ['nullable', 'string', 'max:50'],
            'Sexo' => ['nullable', 'string', 'max:20'],
            'Fecha_Nacimiento' => ['nullable', 'date'],
            'Edad' => ['nullable', 'integer'],
            'Ciudad' => ['nullable', 'string', 'max:100'],
            'Estado' => ['nullable', 'string', 'max:100'],
            'Domicilio' => ['nullable', 'string', 'max:255'],
            'Estado_Civil' => ['nullable', 'string', 'max:50'],
            'Telefono' => ['nullable', 'string', 'max:30'],
            'Celular' => ['nullable', 'string', 'max:30'],
            'Correo_Electronico' => ['nullable', 'email', 'max:150'],
            'Contacto_Emergencia' => ['nullable', 'string', 'max:150'],
            'Tel_Emergencia' => ['nullable', 'string', 'max:30'],
            'Parentesco_B' => ['nullable', 'string', 'max:100'],
            'Nombre_Completo_B' => ['nullable', 'string', 'max:255'],
            'Domicilio_B' => ['nullable', 'string', 'max:255'],
            'Telefono_B' => ['nullable', 'string', 'max:30'],
            'F_NAC_B' => ['nullable', 'date'],
        ]);

        $empleado->update($data);

        HistorialCambio::create([
            'Usuario_ID' => null,
            'N_Empleado' => $n_empleado,
            'Cambio' => "Actualización de datos Personales del empleado con ID: {$n_empleado}",
            'Fecha_Hora' => now(),
        ]);

        return redirect()->route('empleados.show', $n_empleado)->with('status', 'Datos personales actualizados correctamente.');
    }
}
