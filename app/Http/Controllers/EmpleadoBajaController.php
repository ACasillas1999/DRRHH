<?php

namespace App\Http\Controllers;

use App\Models\DocumentoBaja;
use App\Models\FechaLaboral;
use App\Models\HistorialCambio;
use App\Models\LaboralEmpleado;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmpleadoBajaController extends Controller
{
    public function store(Request $request, string $n_empleado): RedirectResponse
    {
        $data = $request->validate([
            'fecha_baja' => ['required', 'date'],
            'motivo_baja' => ['required', 'string', 'max:255'],
            'recontratable' => ['nullable', 'string', 'max:10'],
             // Obligatorio como en el legacy (el textarea tiene required y maxlength="500")
            'comentarios' => ['required', 'string', 'max:500'],

        ]);

        $laboral = LaboralEmpleado::where('N_Empleado', $n_empleado)->firstOrFail();

        DB::transaction(function () use ($n_empleado, $data, $laboral) {
            $laboral->update(['Estatus' => 'BAJA']);

            FechaLaboral::where('N_Empleado', $n_empleado)->update([
                'Fecha_Egreso' => $data['fecha_baja'],
                'motivo_baja' => $data['motivo_baja'],
                'recontratable' => $data['recontratable'] ?? null,
                'comentario' => $data['comentarios'] ?? null,
            ]);

            HistorialCambio::create([
                'Usuario_ID' => auth()->id(),
                'N_Empleado' => $n_empleado,
                'Cambio' => "Se dio de BAJA a Empleado con ID: {$n_empleado}",
                // Hora de México como el legacy (su date() usa date.timezone=America/Mexico_City); la app sigue en UTC
                'Fecha_Hora' => now('America/Mexico_City'),

            ]);

            foreach (['RENUNCIA', 'ENCUESTA_DE_SALIDA', 'RATIFICACIÓN', 'FINIQUITO', 'FOLIO_BAJA'] as $nombreDocumento) {
                DocumentoBaja::create([
                    'N_Empleado' => $n_empleado,
                    'Nombre_Documento' => $nombreDocumento,
                    'Ruta_Documento' => null,
                    'Fecha_Subida' => null,
                ]);
            }
        });

        return redirect()->route('empleados.show', $n_empleado)->with('status', 'Empleado dado de baja correctamente.');
    }
}
