<?php

namespace App\Http\Controllers;

use App\Models\Checador;
use App\Models\Incapacidad;
use App\Models\Incidencia;
use App\Models\Personal;
use App\Models\Vacacion;
use App\Support\AsistenciaHelper;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ChecadorWebhookController extends Controller
{
    private const LOG_FILE = 'checador_push.log';
    private const LINEAS_ACTIVIDAD = 100;

    /**
     * Recibe eventos "HTTP Listening" del checador y actualiza incidencias en tiempo real.
     *
     * Al activar el push por primera vez el dispositivo reenvía TODO su historial guardado
     * (no solo eventos nuevos) antes de asentarse en tiempo real. En vez de descartar ese
     * backlog, se aprovecha para rellenar huecos de días donde nadie corrió conciliación,
     * con el mismo cuidado que el proceso manual (respeta vacaciones/incapacidades y nunca
     * pisa una Asistencia/Retardo ya confirmado, solo corrige "Falta" o rellena vacíos).
     */
    public function receive(Request $request)
    {
        $checador = Checador::where('ip', $request->ip())->first();
        $nombreDispositivo = $checador->nombre ?? "IP {$request->ip()}";

        // Marca "última señal" del dispositivo para el panel de salud, sin importar si el evento se procesa o se ignora.
        $checador?->update(['push_ultimo_evento_at' => now()]);

        $payloadRaw = $request->input('event_log');
        if (!$payloadRaw) {
            return response('OK', 200);
        }

        $payload = json_decode($payloadRaw, true);
        $evento = $payload['AccessControllerEvent'] ?? null;
        $dateTime = $payload['dateTime'] ?? null;

        if (!$evento || !$dateTime || empty($evento['employeeNoString'])) {
            return response('OK', 200);
        }

        $fechaEvento = Carbon::parse($dateTime);
        $nEmp = (int) $evento['employeeNoString'];
        $hora = $fechaEvento->format('H:i:s');
        $fecha = $fechaEvento->toDateString();

        $empleado = Personal::join('laborales_empleado', 'personales.N_Empleado', '=', 'laborales_empleado.N_Empleado')
            ->where('personales.N_Empleado', $nEmp)
            ->where('laborales_empleado.Estatus', 'ACTIVO')
            ->select('personales.N_Empleado', 'laborales_empleado.Horario')
            ->first();

        if (!$empleado) {
            $this->log("[{$nombreDispositivo}] Ignorado (empleado {$nEmp} no encontrado o inactivo)");

            return response('OK', 200);
        }

        $incidencia = Incidencia::where('N_Empleado', $nEmp)->where('Fecha', $fecha)->first();

        if (!$incidencia) {
            $enVacaciones = Vacacion::where('N_Empleado', $nEmp)
                ->where('Estatus', 'APROBADO')
                ->whereRaw('? BETWEEN Fecha_Inicial AND Fecha_Final', [$fecha])
                ->exists();
            $enIncapacidad = Incapacidad::where('N_Empleado', $nEmp)
                ->whereRaw('? BETWEEN Fecha_Inicio AND Fecha_Fin', [$fecha])
                ->exists();

            if ($enVacaciones || $enIncapacidad) {
                $this->log("[{$nombreDispositivo}] Ignorado (vacaciones/incapacidad): {$evento['name']} el {$fecha}");

                return response('OK', 200);
            }

            $tipo = AsistenciaHelper::esRetardo($hora, $empleado->Horario) ? 'Retardo' : 'Asistencia';

            Incidencia::create([
                'N_Empleado' => $nEmp,
                'Fecha' => $fecha,
                'Tipo_Incidencia' => $tipo,
                'Primera_Checada' => $hora,
                'Ultima_Checada' => null,
                'Incidencia_Activa' => 1,
                'Justificacion_Eliminacion' => null,
            ]);

            $this->log("[{$nombreDispositivo}] Nueva incidencia ({$fecha}): {$evento['name']} (#{$nEmp}) {$tipo} a las {$hora}");

            return response('OK', 200);
        }

        // Los eventos no siempre llegan en orden cronológico (el dispositivo puede reenviar
        // el backlog en un orden distinto al real). Usar min/max de todas las horas vistas
        // ese día converge al resultado correcto sin importar el orden de llegada.
        $horas = array_filter([$incidencia->Primera_Checada, $incidencia->Ultima_Checada, $hora]);
        sort($horas);
        $nuevaPrimera = $horas[0];
        $nuevaUltima = count($horas) > 1 ? end($horas) : null;

        $updateData = [
            'Primera_Checada' => $nuevaPrimera,
            'Ultima_Checada' => $nuevaUltima,
        ];

        // Recalcular Retardo/Asistencia si la hora de entrada cambió, o si estaba mal marcado Falta.
        if ($incidencia->Tipo_Incidencia === 'Falta' || $nuevaPrimera !== $incidencia->Primera_Checada) {
            $updateData['Tipo_Incidencia'] = AsistenciaHelper::esRetardo($nuevaPrimera, $empleado->Horario) ? 'Retardo' : 'Asistencia';
        }

        $incidencia->update($updateData);

        $this->log("[{$nombreDispositivo}] Actualizada ({$fecha}): {$evento['name']} (#{$nEmp}) checada a las {$hora}");

        return response('OK', 200);
    }

    /**
     * Devuelve las últimas líneas de actividad del push para mostrarlas en el panel de Checadores.
     */
    public function actividadReciente()
    {
        $ruta = storage_path('logs/' . self::LOG_FILE);

        if (!file_exists($ruta)) {
            return response()->json(['lineas' => []]);
        }

        $lineas = file($ruta, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
        $ultimas = array_slice($lineas, -self::LINEAS_ACTIVIDAD);

        return response()->json(['lineas' => array_reverse($ultimas)]);
    }

    private function log(string $mensaje): void
    {
        $linea = sprintf("[%s] %s\n", now()->format('Y-m-d H:i:s'), $mensaje);
        file_put_contents(storage_path('logs/' . self::LOG_FILE), $linea, FILE_APPEND);
    }
}
