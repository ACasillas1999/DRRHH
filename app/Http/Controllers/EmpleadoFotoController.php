<?php

namespace App\Http\Controllers;

use App\Models\RutaDocumento;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmpleadoFotoController extends Controller
{
    /**
     * Port de la subida de foto de Detalles_Empleado.php: guarda en la carpeta del legacy
     * (RH/Fotos_Empleados) y con su mismo formato de ruta, para que ambas apps vean la foto.
     */
    public function store(Request $request, string $n_empleado): RedirectResponse
    {
        // Mismos formatos que el legacy ($allowedTypes)
        $request->validate([
            'foto_perfil' => ['required', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:5120'],
        ], [
            'foto_perfil.mimes' => 'Solo se permiten formatos de imagen JPG, JPEG, PNG, GIF, y WEBP.',
        ]);

        // Carpeta física del legacy: C:/xampp/htdocs/RH/Fotos_Empleados
        $carpeta = rtrim(config('legacy.path'), '/') . '/Fotos_Empleados';
        if (!is_dir($carpeta)) {
            mkdir($carpeta, 0755, true);
        }

        // Nombre original como el legacy. A diferencia del legacy, si ya existe se agrega _1, _2...
        // para no sobrescribir la foto de OTRO empleado que tenga el mismo nombre de archivo.
        $archivo = $request->file('foto_perfil');
        $nombreOriginal = basename($archivo->getClientOriginalName());
        $nombreArchivo = $nombreOriginal;
        $i = 1;
        while (file_exists($carpeta . '/' . $nombreArchivo)) {
            $nombreArchivo = pathinfo($nombreOriginal, PATHINFO_FILENAME) . "_{$i}." . pathinfo($nombreOriginal, PATHINFO_EXTENSION);
            $i++;
        }
        $archivo->move($carpeta, $nombreArchivo);

        // Misma ruta que guarda el legacy ("/RH/Fotos_Empleados/...")
        RutaDocumento::updateOrCreate(
            ['N_Empleado' => $n_empleado],
            ['Ruta_Foto_Empleado' => '/RH/Fotos_Empleados/' . $nombreArchivo]
        );

        return redirect()->route('empleados.show', $n_empleado)->with('status', 'Foto actualizada correctamente.');
    }

}
