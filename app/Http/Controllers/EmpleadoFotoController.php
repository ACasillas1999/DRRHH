<?php

namespace App\Http\Controllers;

use App\Models\RutaDocumento;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmpleadoFotoController extends Controller
{
    public function store(Request $request, string $n_empleado): RedirectResponse
    {
        $request->validate([
            'foto_perfil' => ['required', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:5120'],
        ]);

        $archivo = $request->file('foto_perfil');
        $nombreArchivo = $n_empleado . '_' . time() . '.' . $archivo->getClientOriginalExtension();
        $archivo->move(public_path('uploads/fotos_empleados'), $nombreArchivo);
        $rutaPublica = '/uploads/fotos_empleados/' . $nombreArchivo;

        RutaDocumento::updateOrCreate(
            ['N_Empleado' => $n_empleado],
            ['Ruta_Foto_Empleado' => $rutaPublica]
        );

        return redirect()->route('empleados.show', $n_empleado)->with('status', 'Foto actualizada correctamente.');
    }
}
