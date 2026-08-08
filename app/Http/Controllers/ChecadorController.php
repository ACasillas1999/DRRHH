<?php

namespace App\Http\Controllers;

use App\Models\Checador;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class ChecadorController extends Controller
{
    // Códigos de evento "acceso concedido" (0x1), "tarjeta exitosa" (0x5), "rostro reconocido" (0x4b)
    private const CODIGOS_CHECADA_EXITOSA = '0x1,0x5,0x4b';

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

    /**
     * PUSH: Activa el envío de eventos en tiempo real (HTTP Listening) de un checador
     */
    public function activarPush($id)
    {
        $checador = Checador::findOrFail($id);
        $resultado = $this->configurarPushEnDispositivo($checador, true);

        if ($resultado['ok']) {
            $checador->update(['push_habilitado' => true]);

            return redirect()->route('checadores.index')->with('success', "Push en tiempo real activado en {$checador->nombre}.");
        }

        return redirect()->route('checadores.index')->with('error', "No se pudo activar el push en {$checador->nombre}: {$resultado['error']}");
    }

    /**
     * PUSH: Desactiva el envío de eventos en tiempo real de un checador
     */
    public function desactivarPush($id)
    {
        $checador = Checador::findOrFail($id);
        $resultado = $this->configurarPushEnDispositivo($checador, false);

        if ($resultado['ok']) {
            $checador->update(['push_habilitado' => false]);

            return redirect()->route('checadores.index')->with('success', "Push en tiempo real desactivado en {$checador->nombre}.");
        }

        return redirect()->route('checadores.index')->with('error', "No se pudo desactivar el push en {$checador->nombre}: {$resultado['error']}");
    }

    /**
     * PUSH: Activa el push en todos los checadores activos
     */
    public function activarPushTodos()
    {
        return $this->aplicarPushATodos(true);
    }

    /**
     * PUSH: Desactiva el push en todos los checadores
     */
    public function desactivarPushTodos()
    {
        return $this->aplicarPushATodos(false);
    }

    private function aplicarPushATodos(bool $activar)
    {
        $checadores = Checador::where('activo', 1)->get();
        $exitosos = [];
        $fallidos = [];

        foreach ($checadores as $checador) {
            $resultado = $this->configurarPushEnDispositivo($checador, $activar);
            if ($resultado['ok']) {
                $checador->update(['push_habilitado' => $activar]);
                $exitosos[] = $checador->nombre;
            } else {
                $fallidos[] = "{$checador->nombre} ({$resultado['error']})";
            }
        }

        $accion = $activar ? 'activado' : 'desactivado';
        $mensaje = "Push {$accion} en " . count($exitosos) . " de " . count($checadores) . " checadores.";
        if (!empty($fallidos)) {
            $mensaje .= ' Fallaron: ' . implode(', ', $fallidos) . '.';

            return redirect()->route('checadores.index')->with('error', $mensaje);
        }

        return redirect()->route('checadores.index')->with('success', $mensaje);
    }

    /**
     * Envía la configuración de HTTP Listening (httpHosts/1) al dispositivo.
     * Requiere XML (el firmware rechaza JSON en el PUT aunque el GET sí lo acepte).
     */
    private function configurarPushEnDispositivo(Checador $checador, bool $activar): array
    {
        $xml = $activar ? $this->xmlActivarPush() : $this->xmlDesactivarPush();
        $url = "http://{$checador->ip}:{$checador->puerto}/ISAPI/Event/notification/httpHosts/1";

        try {
            $response = Http::withDigestAuth($checador->usuario, $checador->password)
                ->withBody($xml, 'application/xml')
                ->timeout(6)
                ->put($url);

            if ($response->successful() && str_contains($response->body(), '<statusCode>1</statusCode>')) {
                return ['ok' => true];
            }

            return ['ok' => false, 'error' => 'el dispositivo rechazó la configuración'];
        } catch (\Exception $e) {
            return ['ok' => false, 'error' => 'no se pudo conectar'];
        }
    }

    private function xmlActivarPush(): string
    {
        $ip = config('checadores.webhook_ip');
        $puerto = config('checadores.webhook_port');
        $path = config('checadores.webhook_path');
        $codigos = self::CODIGOS_CHECADA_EXITOSA;

        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<HttpHostNotification version="2.0" xmlns="http://www.isapi.org/ver20/XMLSchema">
<id>1</id>
<url>{$path}</url>
<protocolType>HTTP</protocolType>
<parameterFormatType>XML</parameterFormatType>
<addressingFormatType>ipaddress</addressingFormatType>
<ipAddress>{$ip}</ipAddress>
<portNo>{$puerto}</portNo>
<httpAuthenticationMethod>none</httpAuthenticationMethod>
<SubscribeEvent>
<heartbeat>30</heartbeat>
<eventMode>list</eventMode>
<EventList>
<Event>
<type>AccessControllerEvent</type>
<minorAlarm></minorAlarm>
<minorException></minorException>
<minorOperation></minorOperation>
<minorEvent>{$codigos}</minorEvent>
<pictureURLType>binary</pictureURLType>
</Event>
</EventList>
</SubscribeEvent>
</HttpHostNotification>
XML;
    }

    private function xmlDesactivarPush(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<HttpHostNotification version="2.0" xmlns="http://www.isapi.org/ver20/XMLSchema">
<id>1</id>
<url></url>
<protocolType>HTTP</protocolType>
<parameterFormatType></parameterFormatType>
<addressingFormatType>ipaddress</addressingFormatType>
<ipAddress>0.0.0.0</ipAddress>
<portNo>0</portNo>
<httpAuthenticationMethod>none</httpAuthenticationMethod>
</HttpHostNotification>
XML;
    }
}
