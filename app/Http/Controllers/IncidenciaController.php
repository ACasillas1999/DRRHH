<?php

namespace App\Http\Controllers;

use App\Models\Personal;
use App\Models\LaboralEmpleado;
use App\Models\Vacacion;
use App\Models\Incapacidad;
use App\Models\Incidencia;
use App\Models\Checador;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class IncidenciaController extends Controller
{
    /**
     * Vista principal de incidencias (Tablero, Matriz de Asistencias e Historial)
     */
    public function index(Request $request)
    {
        // 1. Filtrado para el historial de incidencias guardadas (Pestaña 3)
        $start_date = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $end_date = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());
        
        $savedIncidents = Incidencia::with('personal.laboral.puesto')
            ->where('Incidencia_Activa', 1)
            ->whereBetween('Fecha', [$start_date, $end_date])
            ->orderBy('Fecha', 'desc')
            ->orderBy('N_Empleado', 'asc')
            ->get();

        // Cargar los checadores activos directamente desde la base de datos
        $devices = Checador::where('activo', 1)->orderBy('nombre')->pluck('ip', 'nombre')->toArray();

        // 2. Parámetros para la Matriz de Asistencia (Pestaña 2)
        $matrix_start_date = $request->input('matrix_start_date', Carbon::now()->startOfMonth()->toDateString());
        $matrix_end_date = $request->input('matrix_end_date', Carbon::now()->endOfMonth()->toDateString());
        
        $sel_sucursal = $request->input('matrix_sucursal');
        $sel_puesto = $request->input('matrix_puesto');
        $sel_departamento = $request->input('matrix_departamento');

        // Cargar listas únicas para los dropdowns
        $sucursalesList = DB::table('laborales_empleado')
            ->whereNotNull('Fisica')
            ->where('Fisica', '!=', '')
            ->distinct()
            ->orderBy('Fisica')
            ->pluck('Fisica');

        $puestosList = DB::table('puestos')
            ->whereNotNull('Nombre_Puesto')
            ->where('Nombre_Puesto', '!=', '')
            ->distinct()
            ->orderBy('Nombre_Puesto')
            ->pluck('Nombre_Puesto');

        $departamentosList = DB::table('puestos')
            ->whereNotNull('Departamento')
            ->where('Departamento', '!=', '')
            ->distinct()
            ->orderBy('Departamento')
            ->pluck('Departamento');

        // Query para filtrar empleados de la matriz
        $matrixEmployeesQuery = Personal::join('laborales_empleado', 'personales.N_Empleado', '=', 'laborales_empleado.N_Empleado')
            ->leftJoin('puestos', 'laborales_empleado.ID_Puesto', '=', 'puestos.ID')
            ->where('laborales_empleado.Estatus', 'ACTIVO');

        if ($request->filled('matrix_sucursal')) {
            $matrixEmployeesQuery->where('laborales_empleado.Fisica', $sel_sucursal);
        }
        if ($request->filled('matrix_puesto')) {
            $matrixEmployeesQuery->where('puestos.Nombre_Puesto', $sel_puesto);
        }
        if ($request->filled('matrix_departamento')) {
            $matrixEmployeesQuery->where('puestos.Departamento', $sel_departamento);
        }

        $matrixEmployees = $matrixEmployeesQuery->select([
                'personales.N_Empleado', 'personales.Nombre_Completo',
                'laborales_empleado.Horario', 'laborales_empleado.Fisica', 
                'laborales_empleado.Nomeclatura', 'laborales_empleado.KPI',
                'puestos.Nombre_Puesto', 'puestos.Departamento'
            ])
            ->orderBy('personales.N_Empleado', 'asc')
            ->get();

        // Obtener incidencias, vacaciones e incapacidades en el rango para la matriz
        $incidentsMap = Incidencia::where('Incidencia_Activa', 1)
            ->whereBetween('Fecha', [$matrix_start_date, $matrix_end_date])
            ->get()
            ->groupBy(['N_Empleado', 'Fecha']);

        $vacations = Vacacion::where('Estatus', 'APROBADO')
            ->whereRaw('NOT (Fecha_Final < ? OR Fecha_Inicial > ?)', [$matrix_start_date, $matrix_end_date])
            ->get();

        $vacationsMap = [];
        foreach ($vacations as $vac) {
            $vacationsMap[(int)$vac->N_Empleado][] = [
                'start' => $vac->Fecha_Inicial,
                'end' => $vac->Fecha_Final
            ];
        }

        $incapacities = Incapacidad::whereRaw('NOT (Fecha_Fin < ? OR Fecha_Inicio > ?)', [$matrix_start_date, $matrix_end_date])
            ->get();

        $incapacitiesMap = [];
        foreach ($incapacities as $incap) {
            $incapacitiesMap[(int)$incap->N_Empleado][] = [
                'start' => $incap->Fecha_Inicio,
                'end' => $incap->Fecha_Fin
            ];
        }

        // Generar lista de fechas del rango
        $mStart = Carbon::parse($matrix_start_date);
        $mEnd = Carbon::parse($matrix_end_date);
        $matrixDates = [];
        for ($d = $mStart->copy(); $d->lte($mEnd); $d->addDay()) {
            $matrixDates[] = $d->toDateString();
        }

        // Armar el grid estructurado
        $matrixGrid = [];
        foreach ($matrixEmployees as $emp) {
            $empId = (int)$emp->N_Empleado;
            $row = [
                'n_empleado' => $emp->N_Empleado,
                'nombre' => $emp->Nombre_Completo,
                'razon_social' => $emp->Nomeclatura ?: '—',
                'sucursal' => $emp->Fisica ?: '—',
                'horario' => $emp->Horario ?: '—',
                'kpi' => $emp->KPI ?: 'NO',
                'days' => []
            ];

            foreach ($matrixDates as $dateStr) {
                $code = '';
                $pChecada = '—';
                $uChecada = '—';
                $justificacion = '';
                $idIncidencia = null;

                $carbonDate = Carbon::parse($dateStr);
                $isWeekend = $carbonDate->isWeekend();

                // A. Buscar incidencia guardada
                if (isset($incidentsMap[$empId][$dateStr])) {
                    $incModel = $incidentsMap[$empId][$dateStr][0];
                    $type = (string)$incModel->Tipo_Incidencia;
                    if (!empty($incModel->Primera_Checada)) $pChecada = $incModel->Primera_Checada;
                    if (!empty($incModel->Ultima_Checada)) $uChecada = $incModel->Ultima_Checada;
                    $justificacion = $incModel->justificacion ?: '';
                    $idIncidencia = $incModel->id;

                    if ($type === 'Retardo' || $type === '1') {
                        $code = 'R';
                    } elseif ($type === 'Falta' || $type === '2') {
                        $code = 'F';
                    } elseif ($type === 'Asistencia' || $type === 'A tiempo' || $type === '4') {
                        $code = 'A';
                    } elseif ($type === 'Justificado' || $type === '3') {
                        $code = 'J';
                    } else {
                        $code = 'J'; // Otro tipo justificado
                    }
                }
                // B. Buscar vacaciones
                elseif (isset($vacationsMap[$empId])) {
                    foreach ($vacationsMap[$empId] as $range) {
                        if ($dateStr >= $range['start'] && $dateStr <= $range['end']) {
                            $code = 'V';
                            break;
                        }
                    }
                }

                // C. Buscar incapacidades
                if ($code === '' && isset($incapacitiesMap[$empId])) {
                    foreach ($incapacitiesMap[$empId] as $range) {
                        if ($dateStr >= $range['start'] && $dateStr <= $range['end']) {
                            $code = 'I';
                            break;
                        }
                    }
                }

                // D. Fallback para fines de semana y días hábiles pasados
                if ($code === '') {
                    if ($isWeekend) {
                        $code = 'D'; // Día de descanso
                    } elseif ($dateStr < Carbon::today()->toDateString()) {
                        $code = 'A'; // Día hábil pasado sin incidencia registrada = Asistencia por defecto
                    }
                    // El día de hoy queda en blanco (pendiente) hasta que se concilie contra el checador real
                }

                $row['days'][$dateStr] = [
                    'code' => $code,
                    'p_checada' => $pChecada,
                    'u_checada' => $uChecada,
                    'justificacion' => $justificacion,
                    'id_incidencia' => $idIncidencia
                ];
            }

            $matrixGrid[] = $row;
        }

        return view('incidencias.index', compact(
            'savedIncidents', 'devices', 'start_date', 'end_date',
            'matrix_start_date', 'matrix_end_date', 'sel_sucursal', 'sel_puesto', 'sel_departamento',
            'sucursalesList', 'puestosList', 'departamentosList', 'matrixDates', 'matrixGrid'
        ));
    }

    /**
     * Consulta las checadas de un biométrico local vía ISAPI y concilia con la BD
     */
    public function consultarBiometrico(Request $request)
    {
        $fecha = $request->input('fecha', Carbon::today()->toDateString());
        $ip = $request->input('ip');

        // Buscar el checador por IP y validar que esté registrado y activo
        $checador = Checador::where('ip', $ip)->first();
        if (!$checador) {
            return response()->json(['error' => 'Dispositivo no registrado o IP no válida.'], 400);
        }
        if (!$checador->activo) {
            return response()->json(['error' => 'El dispositivo seleccionado está desactivado.'], 400);
        }

        // 1. Consultar el biométrico local (paginado, un checador puede tener más de 250 eventos/día)
        $events = [];
        try {
            $url = "http://{$checador->ip}:{$checador->puerto}/ISAPI/AccessControl/AcsEvent?format=json";
            $pageSize = 250;
            $pos = 0;
            $total = null;
            $searchId = "sync_" . time();

            do {
                $payload = [
                    "AcsEventCond" => [
                        "searchID" => $searchId,
                        "searchResultPosition" => $pos,
                        "maxResults" => $pageSize,
                        "major" => 0,
                        "minor" => 0,
                        "startTime" => $fecha . "T00:00:00-06:00",
                        "endTime" => $fecha . "T23:59:59-06:00"
                    ]
                ];

                // Petición Digest Auth con credenciales de la BD y timeout corto
                $response = Http::withDigestAuth($checador->usuario, $checador->password)
                    ->timeout(4) // 4 segundos de timeout
                    ->post($url, $payload);

                if (!$response->successful()) {
                    return response()->json([
                        'error' => "El checador respondió con un código de error HTTP: " . $response->status() . ". Verifique el estado de autenticación del dispositivo."
                    ], 500);
                }

                $page = $response->json()['AcsEvent'] ?? [];
                $total = $page['totalMatches'] ?? 0;
                $infoList = $page['InfoList'] ?? [];
                $events = array_merge($events, $infoList);

                // El dispositivo puede ignorar maxResults y devolver menos registros
                // por página; avanzar según lo realmente recibido evita saltarse eventos.
                $received = count($infoList);
                if ($received === 0) break;
                $pos += $received;
            } while ($pos < $total);
        } catch (\Exception $e) {
            return response()->json([
                'error' => "No se pudo conectar al checador en la IP {$ip}. Verifique que el dispositivo esté encendido y conectado a la red local."
            ], 500);
        }

        // 2. Agrupar las checadas del biométrico por el número de empleado
        $checadasPorEmpleado = [];
        foreach ($events as $event) {
            $nEmp = $event['employeeNoString'] ?? null;
            if (!$nEmp) continue;

            $timeStr = $event['time'] ?? null; // ISO 8601 string
            if (!$timeStr) continue;

            $timeParsed = Carbon::parse($timeStr);
            $nEmpInt = (int)$nEmp;

            if (!isset($checadasPorEmpleado[$nEmpInt])) {
                $checadasPorEmpleado[$nEmpInt] = [];
            }
            
            $checadasPorEmpleado[$nEmpInt][] = [
                'hora' => $timeParsed->format('H:i:s'),
                'tipo_verificacion' => $event['currentVerifyMode'] ?? 'Desconocido',
                'timestamp' => $timeParsed->timestamp
            ];
        }

        // Ordenar las checadas por hora para cada empleado
        foreach ($checadasPorEmpleado as $nEmp => &$arr) {
            usort($arr, function ($a, $b) {
                return $a['timestamp'] - $b['timestamp'];
            });
        }

        // 3. Obtener a todos los empleados activos de la base de datos (con filtro por sucursal si se especifica)
        $employeesQuery = Personal::join('laborales_empleado', 'personales.N_Empleado', '=', 'laborales_empleado.N_Empleado')
            ->leftJoin('puestos', 'laborales_empleado.ID_Puesto', '=', 'puestos.ID')
            ->where('laborales_empleado.Estatus', 'ACTIVO');

        if ($request->filled('sucursal')) {
            $employeesQuery->where('laborales_empleado.Fisica', $request->input('sucursal'));
        }

        $employees = $employeesQuery->select([
                'personales.N_Empleado', 'personales.Nombre_Completo',
                'laborales_empleado.Horario', 'laborales_empleado.Fisica', 'puestos.Nombre_Puesto'
            ])
            ->orderBy('personales.N_Empleado', 'asc')
            ->get();

        // 4. Conciliar la asistencia de cada empleado para el día consultado
        $conciliatedList = [];
        $carbonFecha = Carbon::parse($fecha);
        $esFinDeSemana = $carbonFecha->isWeekend();

        foreach ($employees as $emp) {
            $nEmp = (int)$emp->N_Empleado;
            
            // A. Verificar si cuenta con Incidencia activa ya registrada para esa fecha
            $savedIncident = Incidencia::where('N_Empleado', $nEmp)
                ->where('Fecha', $fecha)
                ->where('Incidencia_Activa', 1)
                ->first();

            // B. Verificar Vacaciones
            $onVacation = Vacacion::where('N_Empleado', $nEmp)
                ->where('Estatus', 'APROBADO')
                ->whereRaw('? BETWEEN Fecha_Inicial AND Fecha_Final', [$fecha])
                ->exists();

            // C. Verificar Incapacidad
            $onIncapacity = Incapacidad::where('N_Empleado', $nEmp)
                ->whereRaw('? BETWEEN Fecha_Inicio AND Fecha_Fin', [$fecha])
                ->exists();

            // D. Obtener los registros de checadas del biométrico
            $logs = $checadasPorEmpleado[$nEmp] ?? [];
            $firstCheckIn = !empty($logs) ? $logs[0]['hora'] : ($savedIncident ? $savedIncident->Primera_Checada : null);
            $lastCheckOut = (!empty($logs) && count($logs) > 1) ? end($logs)['hora'] : ($savedIncident ? $savedIncident->Ultima_Checada : null);

            // E. Obtener el horario establecido
            $horario = $emp->Horario;
            $horaEntradaEstablecida = \App\Support\AsistenciaHelper::horaEntradaEstablecida($horario);

            // F. Lógica de asignación de estados y sugerencia de incidencias
            $estado = 'Falta';
            $sugiereIncidencia = null;
            $comentarios = '';

            if ($onVacation) {
                $estado = 'Vacaciones';
                $comentarios = 'El empleado cuenta con vacaciones aprobadas para esta fecha.';
            } elseif ($onIncapacity) {
                $estado = 'Incapacidad';
                $comentarios = 'El empleado cuenta con incapacidad médica vigente.';
            } elseif ($savedIncident && !in_array($savedIncident->Tipo_Incidencia, ['Asistencia', 'Retardo', 'Falta'])) {
                $estado = $savedIncident->Tipo_Incidencia;
                $comentarios = 'Incidencia registrada en la base de datos.';
            } elseif ($firstCheckIn) {
                // Si checó, determinar si es Retardo comparando la primera checada con su Horario
                $firstCheckCarbon = Carbon::createFromFormat('H:i:s', $firstCheckIn);
                $entradaCarbon = Carbon::createFromFormat('H:i', $horaEntradaEstablecida);
                
                // Margen de tolerancia de 10 minutos
                $entradaConTolerancia = $entradaCarbon->copy()->addMinutes(10);

                if ($firstCheckCarbon->gt($entradaConTolerancia)) {
                    $estado = 'Retardo';
                    $sugiereIncidencia = 'Retardo';
                    $comentarios = "Entró a las {$firstCheckIn} (Horario límite de entrada: " . $entradaConTolerancia->format('H:i') . ").";
                } else {
                    $estado = 'A tiempo';
                    $comentarios = "Entró a las {$firstCheckIn} (Horario entrada: {$horaEntradaEstablecida}).";
                }
            } else {
                // No checó
                if ($esFinDeSemana) {
                    $estado = 'Día de descanso';
                    $comentarios = 'Fin de semana ordinario.';
                } else {
                    $estado = 'Falta';
                    $sugiereIncidencia = 'Falta';
                    $comentarios = 'No se registran marcas del empleado en el checador.';
                }
            }

            $conciliatedList[] = [
                'n_empleado' => $emp->N_Empleado,
                'nombre' => $emp->Nombre_Completo,
                'puesto' => $emp->Nombre_Puesto ?: '—',
                'sucursal' => $emp->Fisica ?: '—',
                'horario' => $horario ?: '—',
                'primera_checada' => $firstCheckIn ?: '—',
                'ultima_checada' => $lastCheckOut ?: ($firstCheckIn ? 'Sin salida' : '—'),
                'estado' => $estado,
                'sugiere_incidencia' => $sugiereIncidencia,
                'comentarios' => $comentarios,
                'id_incidencia_guardada' => $savedIncident ? $savedIncident->id : null
            ];
        }

        return response()->json($conciliatedList);
    }

    /**
     * Sincroniza y guarda en lote las incidencias conciliadas del checador
     * para alimentar la base de datos y la Matriz de Asistencia
     */
    public function autoGuardarLote(Request $request)
    {
        $request->validate([
            'fecha' => 'required|date',
            'registros' => 'required|array'
        ]);

        $fecha = $request->input('fecha');
        $registros = $request->input('registros');
        $guardados = 0;

        foreach ($registros as $item) {
            $nEmp = $item['n_empleado'] ?? null;
            $sugiere = $item['sugiere_incidencia'] ?? null;
            $estado = $item['estado'] ?? null;
            $pChecada = (!empty($item['primera_checada']) && $item['primera_checada'] !== '—') ? $item['primera_checada'] : null;
            $uChecada = (!empty($item['ultima_checada']) && $item['ultima_checada'] !== '—' && $item['ultima_checada'] !== 'Sin salida') ? $item['ultima_checada'] : null;

            if (!$nEmp) continue;

            $existing = Incidencia::where('N_Empleado', $nEmp)
                ->where('Fecha', $fecha)
                ->first();

            // Determinar si debemos registrar Tipo_Incidencia
            $targetType = null;
            if (in_array($sugiere, ['Retardo', 'Falta'])) {
                $targetType = $sugiere;
            } elseif ($estado === 'A tiempo') {
                $targetType = 'Asistencia';
            }

            if ($targetType) {
                if (!$existing) {
                    Incidencia::create([
                        'N_Empleado' => $nEmp,
                        'Fecha' => $fecha,
                        'Tipo_Incidencia' => $targetType,
                        'Primera_Checada' => $pChecada,
                        'Ultima_Checada' => $uChecada,
                        'Incidencia_Activa' => 1,
                        'Justificacion_Eliminacion' => null
                    ]);
                    $guardados++;
                } else {
                    $existing->update([
                        'Tipo_Incidencia' => $targetType,
                        'Primera_Checada' => $pChecada ?: $existing->Primera_Checada,
                        'Ultima_Checada' => $uChecada ?: $existing->Ultima_Checada,
                        'Incidencia_Activa' => 1
                    ]);
                    $guardados++;
                }
            } elseif (in_array($estado, ['Vacaciones', 'Incapacidad', 'Día de descanso'])) {
                if ($existing && $existing->Incidencia_Activa == 1 && empty($existing->Justificacion_Eliminacion)) {
                    $existing->update([
                        'Incidencia_Activa' => 0
                    ]);
                }
            }
        }

        return response()->json([
            'success' => true,
            'guardados' => $guardados
        ]);
    }

    /**
     * Guarda una nueva incidencia para un empleado
     */
    public function guardarIncidencia(Request $request)
    {
        $request->validate([
            'n_empleado' => 'required|integer',
            'fecha' => 'required|date',
            'tipo_incidencia' => 'required|string|max:50',
            'justificacion' => 'nullable|string',
        ]);

        // Registrar o actualizar incidencia activa
        $incidencia = Incidencia::updateOrCreate(
            [
                'N_Empleado' => $request->input('n_empleado'),
                'Fecha' => $request->input('fecha'),
            ],
            [
                'Tipo_Incidencia' => $request->input('tipo_incidencia'),
                'Incidencia_Activa' => 1,
                'Justificacion_Eliminacion' => null, // Limpiar si se vuelve a crear
                'justificacion' => $request->input('justificacion')
            ]
        );

        return response()->json(['success' => true, 'incidencia' => $incidencia]);
    }

    /**
     * Desactiva (soft-delete) una incidencia activa justificando su eliminación
     */
    public function desactivarIncidencia(Request $request, $id)
    {
        $request->validate([
            'justificacion' => 'required|string|min:4'
        ]);

        $incidencia = Incidencia::find($id);

        if (!$incidencia) {
            return response()->json(['success' => false, 'error' => 'Incidencia no encontrada.'], 404);
        }

        $incidencia->update([
            'Incidencia_Activa' => 0,
            'Justificacion_Eliminacion' => $request->input('justificacion')
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Reporte: empleados sin marca (falta completa, sin entrada o sin salida) en una fecha específica
     */
    public function reporteSinChecar(Request $request)
    {
        $fecha = $request->input('fecha', Carbon::today()->toDateString());
        $departamento = $request->input('departamento');
        $sucursal = $request->input('sucursal');
        $puesto = $request->input('puesto');

        $empleados = Personal::join('laborales_empleado', 'personales.N_Empleado', '=', 'laborales_empleado.N_Empleado')
            ->join('puestos', 'laborales_empleado.ID_Puesto', '=', 'puestos.ID')
            ->where('laborales_empleado.Estatus', 'ACTIVO')
            ->when($departamento, fn ($q) => $q->where('puestos.Departamento', $departamento))
            ->when($sucursal, fn ($q) => $q->where('laborales_empleado.Fisica', $sucursal))
            ->when($puesto, fn ($q) => $q->where('puestos.Nombre_Puesto', $puesto))
            ->select([
                'personales.N_Empleado', 'personales.Nombre_Completo',
                'laborales_empleado.Fisica', 'puestos.Nombre_Puesto', 'puestos.Departamento',
            ])
            ->orderBy('personales.N_Empleado')
            ->get();

        $incidenciasDelDia = Incidencia::where('Fecha', $fecha)->get()->keyBy('N_Empleado');

        $enVacaciones = Vacacion::where('Estatus', 'APROBADO')
            ->whereRaw('? BETWEEN Fecha_Inicial AND Fecha_Final', [$fecha])
            ->pluck('N_Empleado')->flip();

        $enIncapacidad = Incapacidad::whereRaw('? BETWEEN Fecha_Inicio AND Fecha_Fin', [$fecha])
            ->pluck('N_Empleado')->flip();

        $esFinDeSemana = Carbon::parse($fecha)->isWeekend();

        $resultado = ['falta_completa' => [], 'sin_entrada' => [], 'sin_salida' => [], 'sin_conciliar' => []];

        foreach ($empleados as $emp) {
            $nEmp = $emp->N_Empleado;

            if ($esFinDeSemana || isset($enVacaciones[$nEmp]) || isset($enIncapacidad[$nEmp])) {
                continue;
            }

            $fila = [
                'n_empleado' => $nEmp,
                'nombre' => $emp->Nombre_Completo,
                'sucursal' => $emp->Fisica,
                'puesto' => $emp->Nombre_Puesto,
                'departamento' => $emp->Departamento,
            ];

            $incidencia = $incidenciasDelDia->get($nEmp);

            if (!$incidencia) {
                $resultado['sin_conciliar'][] = $fila;
            } elseif ($incidencia->Tipo_Incidencia === 'Falta') {
                $resultado['falta_completa'][] = $fila;
            } elseif (empty($incidencia->Primera_Checada)) {
                $resultado['sin_entrada'][] = $fila;
            } elseif (empty($incidencia->Ultima_Checada)) {
                $resultado['sin_salida'][] = $fila;
            }
        }

        return response()->json($resultado);
    }

    /**
     * Reporte: puntualidad por empleado y comparativa por departamento en un rango de fechas
     */
    public function reportePuntualidad(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->toDateString());
        $departamento = $request->input('departamento');

        $empleados = Personal::join('laborales_empleado', 'personales.N_Empleado', '=', 'laborales_empleado.N_Empleado')
            ->join('puestos', 'laborales_empleado.ID_Puesto', '=', 'puestos.ID')
            ->where('laborales_empleado.Estatus', 'ACTIVO')
            ->when($departamento, fn ($q) => $q->where('puestos.Departamento', $departamento))
            ->select([
                'personales.N_Empleado', 'personales.Nombre_Completo',
                'puestos.Departamento', 'laborales_empleado.Horario',
            ])
            ->orderBy('personales.N_Empleado')
            ->get();

        $incidenciasPorEmpleado = Incidencia::whereIn('N_Empleado', $empleados->pluck('N_Empleado'))
            ->whereBetween('Fecha', [$startDate, $endDate])
            ->where('Incidencia_Activa', 1)
            ->get()
            ->groupBy('N_Empleado');

        $ranking = [];
        $acumuladoPorDepto = [];

        foreach ($empleados as $emp) {
            $registros = $incidenciasPorEmpleado->get($emp->N_Empleado, collect());

            $aTiempo = $registros->where('Tipo_Incidencia', 'Asistencia')->count();
            $retardos = $registros->where('Tipo_Incidencia', 'Retardo')->count();
            $faltas = $registros->where('Tipo_Incidencia', 'Falta')->count();

            $horaEntradaEstablecida = \App\Support\AsistenciaHelper::horaEntradaEstablecida($emp->Horario);
            $entradaTs = strtotime($horaEntradaEstablecida);

            $sumaChecadaSecs = 0;
            $diasConEntrada = 0;
            foreach ($registros as $r) {
                if (!empty($r->Primera_Checada)) {
                    $sumaChecadaSecs += strtotime($r->Primera_Checada);
                    $diasConEntrada++;
                }
            }

            $horaPromedioEntrada = $diasConEntrada > 0 ? date('H:i', intdiv($sumaChecadaSecs, $diasConEntrada)) : null;
            $minutosPromedioTarde = $diasConEntrada > 0
                ? round((($sumaChecadaSecs / $diasConEntrada) - $entradaTs) / 60, 1)
                : null;
            $porcentajePuntualidad = ($aTiempo + $retardos) > 0
                ? round($aTiempo / ($aTiempo + $retardos) * 100, 1)
                : null;

            $ranking[] = [
                'n_empleado' => $emp->N_Empleado,
                'nombre' => $emp->Nombre_Completo,
                'departamento' => $emp->Departamento,
                'hora_promedio_entrada' => $horaPromedioEntrada,
                'minutos_promedio_tarde' => $minutosPromedioTarde,
                'a_tiempo' => $aTiempo,
                'retardos' => $retardos,
                'faltas' => $faltas,
                'porcentaje_puntualidad' => $porcentajePuntualidad,
            ];

            $dept = $emp->Departamento ?: 'Sin Departamento';
            if (!isset($acumuladoPorDepto[$dept])) {
                $acumuladoPorDepto[$dept] = ['a_tiempo' => 0, 'retardos' => 0, 'faltas' => 0];
            }
            $acumuladoPorDepto[$dept]['a_tiempo'] += $aTiempo;
            $acumuladoPorDepto[$dept]['retardos'] += $retardos;
            $acumuladoPorDepto[$dept]['faltas'] += $faltas;
        }

        $porDepartamento = [];
        foreach ($acumuladoPorDepto as $dept => $d) {
            $totalConsiderado = $d['a_tiempo'] + $d['retardos'];
            $porDepartamento[] = [
                'departamento' => $dept,
                'a_tiempo' => $d['a_tiempo'],
                'retardos' => $d['retardos'],
                'faltas' => $d['faltas'],
                'porcentaje_puntualidad' => $totalConsiderado > 0 ? round($d['a_tiempo'] / $totalConsiderado * 100, 1) : null,
            ];
        }

        usort($porDepartamento, fn ($a, $b) => ($b['porcentaje_puntualidad'] ?? -1) <=> ($a['porcentaje_puntualidad'] ?? -1));

        return response()->json([
            'ranking' => $ranking,
            'por_departamento' => $porDepartamento,
        ]);
    }

    /**
     * Vista de los Reportes de Asistencia (Módulo Independiente)
     */
    public function reportesVista(Request $request)
    {
        $sucursalesList = DB::table('laborales_empleado')
            ->whereNotNull('Fisica')
            ->where('Fisica', '!=', '')
            ->distinct()
            ->orderBy('Fisica')
            ->pluck('Fisica');

        $puestosList = DB::table('puestos')
            ->whereNotNull('Nombre_Puesto')
            ->where('Nombre_Puesto', '!=', '')
            ->distinct()
            ->orderBy('Nombre_Puesto')
            ->pluck('Nombre_Puesto');

        $departamentosList = DB::table('puestos')
            ->whereNotNull('Departamento')
            ->where('Departamento', '!=', '')
            ->distinct()
            ->orderBy('Departamento')
            ->pluck('Departamento');

        return view('incidencias.reportes', compact('sucursalesList', 'puestosList', 'departamentosList'));
    }
}
