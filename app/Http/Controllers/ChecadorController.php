<?php

namespace App\Http\Controllers;

use App\Models\Checador;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class ChecadorController extends Controller
{
    /**
     * CRUD: Listado de checadores
     */
    public function index()
    {
        $checadores = Checador::orderBy('nombre')->get();
        return view('checadores.index', compact('checadores'));
    }

    /**
     * CRUD: Guardar nuevo checador
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100|unique:checadores,nombre',
            'ip' => 'required|string|max:50',
            'puerto' => 'required|integer',
            'usuario' => 'required|string|max:50',
            'password' => 'required|string|max:100',
        ]);

        $checador = Checador::create([
            'nombre' => $request->input('nombre'),
            'ip' => $request->input('ip'),
            'puerto' => $request->input('puerto'),
            'usuario' => $request->input('usuario'),
            'password' => $request->input('password'),
            'activo' => $request->has('activo') ? 1 : 0
        ]);

        return redirect()->route('checadores.index')->with('success', 'Checador creado correctamente.');
    }

    /**
     * CRUD: Actualizar checador
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'required|string|max:100|unique:checadores,nombre,' . $id,
            'ip' => 'required|string|max:50',
            'puerto' => 'required|integer',
            'usuario' => 'required|string|max:50',
            'password' => 'required|string|max:100',
        ]);

        $checador = Checador::findOrFail($id);
        $checador->update([
            'nombre' => $request->input('nombre'),
            'ip' => $request->input('ip'),
            'puerto' => $request->input('puerto'),
            'usuario' => $request->input('usuario'),
            'password' => $request->input('password'),
            'activo' => $request->has('activo') ? 1 : 0
        ]);

        return redirect()->route('checadores.index')->with('success', 'Checador actualizado correctamente.');
    }

    /**
     * CRUD: Eliminar checador
     */
    public function destroy($id)
    {
        $checador = Checador::findOrFail($id);
        $checador->delete();

        return redirect()->route('checadores.index')->with('success', 'Checador eliminado correctamente.');
    }

    /**
     * LECTOR: Vista principal del lector de eventos en vivo
     */
    public function lector()
    {
        $checadores = Checador::where('activo', 1)->orderBy('nombre')->get();
        return view('checadores.lector', compact('checadores'));
    }

    /**
     * LECTOR: Petición AJAX para leer eventos RAW del biométrico
     */
    public function leerEventosRaw(Request $request)
    {
        $checadorId = $request->input('checador_id');
        $fecha = $request->input('fecha', Carbon::today()->toDateString());
        
        $major = (int)$request->input('major', 0);
        $minor = (int)$request->input('minor', 0);
        $maxResults = (int)$request->input('max_results', 50);
        $position = (int)$request->input('position', 0);

        $checador = Checador::findOrFail($checadorId);

        try {
            $url = "http://{$checador->ip}:{$checador->puerto}/ISAPI/AccessControl/AcsEvent?format=json";
            
            $payload = [
                "AcsEventCond" => [
                    "searchID" => "lector_" . time(),
                    "searchResultPosition" => $position,
                    "maxResults" => $maxResults,
                    "major" => $major,
                    "minor" => $minor,
                    "startTime" => $fecha . "T00:00:00-06:00",
                    "endTime" => $fecha . "T23:59:59-06:00"
                ]
            ];

            $response = Http::withDigestAuth($checador->usuario, $checador->password)
                ->timeout(4)
                ->post($url, $payload);

            if ($response->successful()) {
                return response()->json($response->json());
            } else {
                return response()->json([
                    'error' => "El checador respondió con código HTTP: " . $response->status() . ". Valida las credenciales de conexión en la configuración."
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'error' => "No se pudo conectar al checador en la IP {$checador->ip}. Verifique que el dispositivo esté encendido y conectado a la red local."
            ], 500);
        }
    }
}
