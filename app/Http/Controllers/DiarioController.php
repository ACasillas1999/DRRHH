<?php

namespace App\Http\Controllers;

use App\Models\Personal;
use App\Models\LaboralEmpleado;
use App\Models\Vacacion;
use App\Models\Contrato;
use App\Models\FechaLaboral;
use App\Models\Incapacidad;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DiarioController extends Controller
{
    // Listado de días festivos fijos de México
    private function getHolidays($year)
    {
        return [
            "$year-01-01" => "Año Nuevo",
            "$year-02-05" => "Día de la Constitución",
            "$year-05-01" => "Día del Trabajo",
            "$year-09-15" => "Fiesta de la Independencia",
            "$year-09-16" => "Día de la Independencia",
            "$year-10-12" => "Día de la Raza",
            "$year-11-20" => "Día de la Revolución",
            "$year-12-25" => "Navidad",
        ];
    }

    /**
     * Vista principal del Diario (Pestañas de Calendario y Rango de Eventos)
     */
    public function index(Request $request)
    {
        // 1. Parámetros para el calendario
        $year = $request->input('year', date('Y'));
        $month = $request->input('month', date('m'));

        // Asegurar formato de dos dígitos para mes
        $month = str_pad($month, 2, '0', STR_PAD_LEFT);

        $firstDayOfMonth = Carbon::create($year, $month, 1);
        $daysInMonth = $firstDayOfMonth->daysInMonth;
        
        // El día de la semana del primer día del mes (1 = Lunes, 7 = Domingo)
        $firstDayOfWeek = $firstDayOfMonth->dayOfWeekIso;

        $startOfMonth = $firstDayOfMonth->toDateString();
        $endOfMonth = Carbon::create($year, $month, 1)->endOfMonth()->toDateString();

        // 2. Obtener eventos agrupados por día para pintar badges en el calendario
        // 2a. Cumpleaños en el mes
        $birthdays = Personal::join('laborales_empleado', 'personales.N_Empleado', '=', 'laborales_empleado.N_Empleado')
            ->where('laborales_empleado.Estatus', 'ACTIVO')
            ->whereMonth('personales.Fecha_Nacimiento', $month)
            ->select('personales.N_Empleado', 'personales.Nombre_Completo', 'personales.Fecha_Nacimiento')
            ->get()
            ->groupBy(function ($item) {
                return Carbon::parse($item->Fecha_Nacimiento)->day;
            });

        // 2b. Aniversarios en el mes
        $anniversaries = FechaLaboral::join('laborales_empleado', 'fechas_laborales.N_Empleado', '=', 'laborales_empleado.N_Empleado')
            ->where('laborales_empleado.Estatus', 'ACTIVO')
            ->whereMonth('fechas_laborales.Fecha_Ingreso', $month)
            ->select('fechas_laborales.N_Empleado', 'fechas_laborales.Fecha_Ingreso')
            ->get()
            ->groupBy(function ($item) {
                return Carbon::parse($item->Fecha_Ingreso)->day;
            });

        // 2c. Vacaciones en el mes (aprobadas y de activos)
        $vacations = Vacacion::join('laborales_empleado', 'vacaciones.N_Empleado', '=', 'laborales_empleado.N_Empleado')
            ->where('vacaciones.Estatus', 'APROBADO')
            ->where('laborales_empleado.Estatus', 'ACTIVO')
            ->where(function ($q) use ($startOfMonth, $endOfMonth) {
                $q->whereBetween('vacaciones.Fecha_Inicial', [$startOfMonth, $endOfMonth])
                  ->orWhereBetween('vacaciones.Fecha_Final', [$startOfMonth, $endOfMonth])
                  ->orWhere(function ($sq) use ($startOfMonth, $endOfMonth) {
                      $sq->where('vacaciones.Fecha_Inicial', '<=', $startOfMonth)
                         ->where('vacaciones.Fecha_Final', '>=', $endOfMonth);
                  });
            })
            ->select('vacaciones.Fecha_Inicial', 'vacaciones.Fecha_Final')
            ->get();

        $vacationsByDay = [];
        foreach ($vacations as $vac) {
            $start = Carbon::parse($vac->Fecha_Inicial);
            $end = Carbon::parse($vac->Fecha_Final);
            $current = $start->copy();
            while ($current->lte($end)) {
                if ($current->year == $year && $current->month == (int)$month) {
                    $vacationsByDay[$current->day] = ($vacationsByDay[$current->day] ?? 0) + 1;
                }
                $current->addDay();
            }
        }

        // 2d. Contratos que vencen en el mes (Determinado)
        $expirations = Contrato::join('laborales_empleado', 'contratos.N_Empleado', '=', 'laborales_empleado.N_Empleado')
            ->where('contratos.Tipo_de_Contrato', 'Determinado')
            ->where('laborales_empleado.Estatus', 'ACTIVO')
            ->whereBetween('contratos.F_Vence', [$startOfMonth, $endOfMonth])
            ->select('contratos.F_Vence')
            ->get()
            ->groupBy(function ($item) {
                return Carbon::parse($item->F_Vence)->day;
            });

        // 2e. Evaluaciones que vencen en el mes (Evaluacion)
        $evaluations = Contrato::join('laborales_empleado', 'contratos.N_Empleado', '=', 'laborales_empleado.N_Empleado')
            ->where('contratos.Tipo_de_Contrato', 'Evaluacion')
            ->where('laborales_empleado.Estatus', 'ACTIVO')
            ->whereBetween('contratos.F_Vence', [$startOfMonth, $endOfMonth])
            ->select('contratos.F_Vence')
            ->get()
            ->groupBy(function ($item) {
                return Carbon::parse($item->F_Vence)->day;
            });

        // 2f. Incapacidades en el mes
        $incapacities = Incapacidad::join('laborales_empleado', 'incapacidades.N_Empleado', '=', 'laborales_empleado.N_Empleado')
            ->where('laborales_empleado.Estatus', 'ACTIVO')
            ->where(function ($q) use ($startOfMonth, $endOfMonth) {
                $q->whereBetween('incapacidades.Fecha_Inicio', [$startOfMonth, $endOfMonth])
                  ->orWhereBetween('incapacidades.Fecha_Fin', [$startOfMonth, $endOfMonth])
                  ->orWhere(function ($sq) use ($startOfMonth, $endOfMonth) {
                      $sq->where('incapacidades.Fecha_Inicio', '<=', $startOfMonth)
                         ->where('incapacidades.Fecha_Fin', '>=', $endOfMonth);
                  });
            })
            ->select('incapacidades.Fecha_Inicio', 'incapacidades.Fecha_Fin')
            ->get();

        $incapacitiesByDay = [];
        foreach ($incapacities as $inc) {
            $start = Carbon::parse($inc->Fecha_Inicio);
            $end = Carbon::parse($inc->Fecha_Fin);
            $current = $start->copy();
            while ($current->lte($end)) {
                if ($current->year == $year && $current->month == (int)$month) {
                    $incapacitiesByDay[$current->day] = ($incapacitiesByDay[$current->day] ?? 0) + 1;
                }
                $current->addDay();
            }
        }

        // 2g. Festivos del mes
        $holidaysList = $this->getHolidays($year);
        $holidaysInMonth = [];
        foreach ($holidaysList as $dateStr => $name) {
            $cDate = Carbon::parse($dateStr);
            if ($cDate->month == (int)$month) {
                $holidaysInMonth[$cDate->day] = $name;
            }
        }

        // Construir arreglo estructurado de días para la vista
        $calendarDays = [];
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $counts = [
                'birthdays' => isset($birthdays[$d]) ? count($birthdays[$d]) : 0,
                'anniversaries' => isset($anniversaries[$d]) ? count($anniversaries[$d]) : 0,
                'vacations' => $vacationsByDay[$d] ?? 0,
                'expirations' => isset($expirations[$d]) ? count($expirations[$d]) : 0,
                'evaluations' => isset($evaluations[$d]) ? count($evaluations[$d]) : 0,
                'incapacities' => $incapacitiesByDay[$d] ?? 0,
                'holiday' => $holidaysInMonth[$d] ?? null,
            ];
            $counts['total'] = $counts['birthdays'] + $counts['anniversaries'] + $counts['vacations'] + 
                               $counts['expirations'] + $counts['evaluations'] + $counts['incapacities'] + 
                               ($counts['holiday'] ? 1 : 0);
            
            $calendarDays[$d] = $counts;
        }

        // 3. Parámetros para la vista de rango de eventos (Listado)
        // Por defecto, lunes a domingo de la semana actual
        $start_date = $request->input('start_date', date('Y-m-d', strtotime('monday this week')));
        $end_date = $request->input('end_date', date('Y-m-d', strtotime('sunday this week')));

        $listEvents = $this->getEventsForRange($start_date, $end_date);

        return view('diario.index', compact(
            'year', 'month', 'daysInMonth', 'firstDayOfWeek', 'calendarDays', 
            'start_date', 'end_date', 'listEvents'
        ));
    }

    /**
     * Retorna los detalles de los eventos para un día seleccionado vía AJAX
     */
    public function getDiaInfo(Request $request)
    {
        $day = $request->input('day');
        $month = $request->input('month');
        $year = $request->input('year');

        if (!$day || !$month || !$year) {
            return response()->json(['error' => 'Parámetros incompletos'], 400);
        }

        $selectedDate = Carbon::create($year, $month, $day);
        $selectedDateStr = $selectedDate->toDateString();

        // 1. Verificar Festivo
        $holidays = $this->getHolidays($year);
        // Formato para buscar en la clave del array heredado o estándar
        $key1 = "$year-" . (int)$month . "-" . (int)$day;
        $key2 = $selectedDate->format('Y-m-d');
        $holidayName = $holidays[$key1] ?? ($holidays[$key2] ?? null);

        // 2. Cumpleaños de hoy (activos)
        $cumpleañeros = Personal::join('laborales_empleado', 'personales.N_Empleado', '=', 'laborales_empleado.N_Empleado')
            ->where('laborales_empleado.Estatus', 'ACTIVO')
            ->whereMonth('personales.Fecha_Nacimiento', $month)
            ->whereDay('personales.Fecha_Nacimiento', $day)
            ->select('personales.N_Empleado', 'personales.Nombre_Completo', 'personales.Fecha_Nacimiento', 'personales.Edad')
            ->get();

        $cumpleañerosIds = $cumpleañeros->pluck('N_Empleado')->toArray();

        // 3. Vacaciones aprobadas cruzando el día seleccionado
        $vacations = Vacacion::join('laborales_empleado', 'vacaciones.N_Empleado', '=', 'laborales_empleado.N_Empleado')
            ->join('personales', 'vacaciones.N_Empleado', '=', 'personales.N_Empleado')
            ->where('vacaciones.Estatus', 'APROBADO')
            ->where('laborales_empleado.Estatus', 'ACTIVO')
            ->whereRaw('? BETWEEN vacaciones.Fecha_Inicial AND vacaciones.Fecha_Final', [$selectedDateStr])
            ->select('vacaciones.*', 'personales.Nombre_Completo')
            ->get()
            ->map(function ($vac) use ($cumpleañerosIds) {
                $vac->is_birthday = in_array($vac->N_Empleado, $cumpleañerosIds);
                return $vac;
            });

        // 4. Vencimiento de contratos determinados (F_Vence es hoy o en los próximos 5 días)
        $fiveDaysAfter = $selectedDate->copy()->addDays(5)->toDateString();
        $contracts = Contrato::join('personales', 'contratos.N_Empleado', '=', 'personales.N_Empleado')
            ->join('laborales_empleado', 'contratos.N_Empleado', '=', 'laborales_empleado.N_Empleado')
            ->join('puestos', 'laborales_empleado.ID_Puesto', '=', 'puestos.ID')
            ->leftJoin('fechas_laborales', 'contratos.N_Empleado', '=', 'fechas_laborales.N_Empleado')
            ->where('contratos.Tipo_de_Contrato', 'Determinado')
            ->where('laborales_empleado.Estatus', 'ACTIVO')
            ->whereBetween('contratos.F_Vence', [$selectedDateStr, $fiveDaysAfter])
            ->select([
                'contratos.*', 'personales.Nombre_Completo', 
                'fechas_laborales.Fecha_Ingreso', 'laborales_empleado.Fisica', 'puestos.Nombre_Puesto'
            ])
            ->get()
            ->map(function ($con) use ($selectedDate) {
                $expiry = Carbon::parse($con->F_Vence);
                $con->days_left = $selectedDate->diffInDays($expiry, false);
                
                // Fallback a documento del contrato Indeterminado si existiera (conforme lógica legacy)
                $con->ruta_doc_fallback = Contrato::where('N_Empleado', $con->N_Empleado)
                    ->where('Tipo_de_Contrato', 'Indeterminado')
                    ->value('Ruta_Doc');
                
                return $con;
            });

        // 5. Evaluaciones vencidas o por vencer (Tipo_de_Contrato = 'Evaluacion')
        $evaluations = Contrato::join('personales', 'contratos.N_Empleado', '=', 'personales.N_Empleado')
            ->join('laborales_empleado', 'contratos.N_Empleado', '=', 'laborales_empleado.N_Empleado')
            ->join('puestos', 'laborales_empleado.ID_Puesto', '=', 'puestos.ID')
            ->leftJoin('fechas_laborales', 'contratos.N_Empleado', '=', 'fechas_laborales.N_Empleado')
            ->where('contratos.Tipo_de_Contrato', 'Evaluacion')
            ->where('laborales_empleado.Estatus', 'ACTIVO')
            ->whereBetween('contratos.F_Vence', [$selectedDateStr, $fiveDaysAfter])
            ->select([
                'contratos.*', 'personales.Nombre_Completo', 
                'fechas_laborales.Fecha_Ingreso', 'laborales_empleado.Fisica', 'puestos.Nombre_Puesto'
            ])
            ->orderBy('personales.N_Empleado')
            ->orderBy('contratos.F_Vence')
            ->get()
            ->map(function ($ev) use ($selectedDate) {
                $expiry = Carbon::parse($ev->F_Vence);
                $ev->days_left = $selectedDate->diffInDays($expiry, false);
                
                // Calcular número de evaluación (histórica del empleado)
                $allEvals = Contrato::where('N_Empleado', $ev->N_Empleado)
                    ->where('Tipo_de_Contrato', 'Evaluacion')
                    ->orderBy('F_Vence', 'asc')
                    ->pluck('ID')
                    ->toArray();
                
                $idx = array_search($ev->ID, $allEvals);
                $ev->evaluation_number = ($idx !== false) ? $idx + 1 : 1;
                
                return $ev;
            });

        // 6. Incapacidades
        $incapacities = Incapacidad::join('laborales_empleado', 'incapacidades.N_Empleado', '=', 'laborales_empleado.N_Empleado')
            ->join('personales', 'incapacidades.N_Empleado', '=', 'personales.N_Empleado')
            ->where('laborales_empleado.Estatus', 'ACTIVO')
            ->whereRaw('? BETWEEN incapacidades.Fecha_Inicio AND incapacidades.Fecha_Fin', [$selectedDateStr])
            ->select('incapacidades.*', 'personales.Nombre_Completo')
            ->get();

        // 7. Aniversarios Laborales
        $anniversaries = FechaLaboral::join('laborales_empleado', 'fechas_laborales.N_Empleado', '=', 'laborales_empleado.N_Empleado')
            ->join('personales', 'fechas_laborales.N_Empleado', '=', 'personales.N_Empleado')
            ->where('laborales_empleado.Estatus', 'ACTIVO')
            ->whereMonth('fechas_laborales.Fecha_Ingreso', $month)
            ->whereDay('fechas_laborales.Fecha_Ingreso', $day)
            ->select('fechas_laborales.N_Empleado', 'personales.Nombre_Completo', 'fechas_laborales.Fecha_Ingreso')
            ->get()
            ->map(function ($ann) use ($year) {
                $hireDate = Carbon::parse($ann->Fecha_Ingreso);
                $ann->years_completed = $year - $hireDate->year;
                return $ann;
            });

        // Nombre del mes en español
        $meses = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 5 => 'Mayo', 6 => 'Junio',
            7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];
        $selectedDateFormatted = "$day de " . $meses[(int)$month] . " de $year";

        return response()->json([
            'date_formatted' => $selectedDateFormatted,
            'holiday' => $holidayName,
            'birthdays' => $cumpleañeros,
            'vacations' => $vacations,
            'contracts' => $contracts,
            'evaluations' => $evaluations,
            'incapacities' => $incapacities,
            'anniversaries' => $anniversaries
        ]);
    }

    /**
     * AJAX/POST para marcar una evaluación como realizada (Ruta_Doc = 'Realizada')
     */
    public function realizarEvaluacion(Request $request, $id)
    {
        $contrato = Contrato::where('ID', $id)
            ->where('Tipo_de_Contrato', 'Evaluacion')
            ->first();

        if (!$contrato) {
            return response()->json(['success' => false, 'error' => 'Evaluación no encontrada.'], 404);
        }

        $contrato->update([
            'Ruta_Doc' => 'Realizada'
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Feed JSON de eventos para FullCalendar
     */
    public function getEventosFeed(Request $request)
    {
        $start = $request->input('start');
        $end = $request->input('end');

        if (!$start || !$end) {
            $start = Carbon::now()->startOfMonth()->toDateString();
            $end = Carbon::now()->endOfMonth()->toDateString();
        }

        // Obtener eventos usando nuestra función existente
        $rawEvents = $this->getEventsForRange($start, $end);

        $fcEvents = [];

        // 1. Mapear eventos del rango a formato FullCalendar
        foreach ($rawEvents as $event) {
            $title = '';
            $color = '';

            switch ($event['tipo']) {
                case 'Cumpleaños':
                    $title = "🎂 Cumple: " . $event['nombre'];
                    $color = '#22c55e'; // Emerald
                    break;
                case 'Aniversario Laboral':
                    $title = "🏆 Aniv: " . $event['nombre'];
                    $color = '#3b82f6'; // Royal Blue
                    break;
                case 'Vacaciones':
                    $title = "🏖️ Vac: " . $event['nombre'];
                    $color = '#eab308'; // Amber
                    break;
                case 'Vencimiento de contrato':
                    $title = "📄 Vence: " . $event['nombre'];
                    $color = '#ef4444'; // Red
                    break;
                case 'Evaluación':
                    $title = "🎓 Eval: " . $event['nombre'];
                    $color = '#14b8a6'; // Teal
                    break;
                case 'Incapacidad':
                    $title = "🏥 Incap: " . $event['nombre'];
                    $color = '#a855f7'; // Purple
                    break;
            }

            // Separar fechas en caso de rangos
            $startDateVal = $start;
            $endDateVal = $end;

            if (strpos($event['fecha_evento'], ' al ') !== false) {
                $dates = explode(' al ', $event['fecha_evento']);
                $startDateVal = $dates[0];
                // Sumar 1 día al final en FullCalendar (porque la fecha de término es exclusiva en eventos todo el día)
                $endDateVal = Carbon::parse($dates[1])->addDay()->toDateString();
            } else {
                $startDateVal = $event['fecha_evento'];
                $endDateVal = $event['fecha_evento'];
            }

            $fcEvents[] = [
                'id' => uniqid('evt_'),
                'title' => $title,
                'start' => $startDateVal,
                'end' => $endDateVal,
                'color' => $color,
                'allDay' => true,
                'extendedProps' => [
                    'tipo' => $event['tipo'],
                    'n_empleado' => $event['n_empleado']
                ]
            ];
        }

        // 2. Mapear días festivos oficiales en el rango
        $startYear = Carbon::parse($start)->year;
        $endYear = Carbon::parse($end)->year;
        
        for ($y = $startYear; $y <= $endYear; $y++) {
            $holidaysList = $this->getHolidays($y);
            foreach ($holidaysList as $dateStr => $name) {
                if ($dateStr >= $start && $dateStr <= $end) {
                    $fcEvents[] = [
                        'id' => uniqid('hol_'),
                        'title' => "🎉 " . $name,
                        'start' => $dateStr,
                        'end' => $dateStr,
                        'color' => '#f97316', // Orange
                        'allDay' => true,
                        'extendedProps' => [
                            'tipo' => 'Festivo'
                        ]
                    ];
                }
            }
        }

        return response()->json($fcEvents);
    }

    /**
     * Método auxiliar para buscar todos los eventos dentro de un rango de fechas
     */
    private function getEventsForRange($startDate, $endDate)
    {
        $events = [];

        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        // Crear una lista de días (mes-día) en el rango para mapear cumpleaños y aniversarios
        $daysInRange = [];
        $current = $start->copy();
        while ($current->lte($end)) {
            $daysInRange[] = [
                'month' => $current->month,
                'day' => $current->day,
                'date_str' => $current->toDateString(),
                'year' => $current->year
            ];
            $current->addDay();
        }

        // Obtener todos los empleados activos para mapear cumpleaños y aniversarios en PHP
        $activeEmployees = Personal::join('laborales_empleado', 'personales.N_Empleado', '=', 'laborales_empleado.N_Empleado')
            ->join('puestos', 'laborales_empleado.ID_Puesto', '=', 'puestos.ID')
            ->leftJoin('fechas_laborales', 'personales.N_Empleado', '=', 'fechas_laborales.N_Empleado')
            ->where('laborales_empleado.Estatus', 'ACTIVO')
            ->select([
                'personales.N_Empleado', 'personales.Nombre_Completo', 'personales.Fecha_Nacimiento',
                'laborales_empleado.Fisica', 'puestos.Nombre_Puesto', 'fechas_laborales.Fecha_Ingreso'
            ])
            ->get();

        foreach ($activeEmployees as $emp) {
            // Checar cumpleaños
            if ($emp->Fecha_Nacimiento) {
                $bday = Carbon::parse($emp->Fecha_Nacimiento);
                foreach ($daysInRange as $dayInfo) {
                    if ($bday->month == $dayInfo['month'] && $bday->day == $dayInfo['day']) {
                        $events[] = [
                            'tipo' => 'Cumpleaños',
                            'nombre' => $emp->Nombre_Completo,
                            'n_empleado' => $emp->N_Empleado,
                            'fecha_evento' => $dayInfo['date_str'],
                            'fecha_ingreso' => $emp->Fecha_Ingreso,
                            'fisica' => $emp->Fisica,
                            'puesto' => $emp->Nombre_Puesto,
                            'detalles' => "Cumple " . ($dayInfo['year'] - $bday->year) . " años (" . $bday->format('d/m/Y') . ")"
                        ];
                    }
                }
            }

            // Checar aniversario laboral
            if ($emp->Fecha_Ingreso) {
                $ann = Carbon::parse($emp->Fecha_Ingreso);
                foreach ($daysInRange as $dayInfo) {
                    if ($ann->month == $dayInfo['month'] && $ann->day == $dayInfo['day']) {
                        $events[] = [
                            'tipo' => 'Aniversario Laboral',
                            'nombre' => $emp->Nombre_Completo,
                            'n_empleado' => $emp->N_Empleado,
                            'fecha_evento' => $dayInfo['date_str'],
                            'fecha_ingreso' => $emp->Fecha_Ingreso,
                            'fisica' => $emp->Fisica,
                            'puesto' => $emp->Nombre_Puesto,
                            'detalles' => "Cumple " . ($dayInfo['year'] - $ann->year) . " años de servicio"
                        ];
                    }
                }
            }
        }

        // Obtener vacaciones aprobadas en el rango
        $vacations = Vacacion::join('laborales_empleado', 'vacaciones.N_Empleado', '=', 'laborales_empleado.N_Empleado')
            ->join('personales', 'vacaciones.N_Empleado', '=', 'personales.N_Empleado')
            ->join('puestos', 'laborales_empleado.ID_Puesto', '=', 'puestos.ID')
            ->leftJoin('fechas_laborales', 'vacaciones.N_Empleado', '=', 'fechas_laborales.N_Empleado')
            ->where('vacaciones.Estatus', 'APROBADO')
            ->where('laborales_empleado.Estatus', 'ACTIVO')
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('vacaciones.Fecha_Inicial', [$startDate, $endDate])
                  ->orWhereBetween('vacaciones.Fecha_Final', [$startDate, $endDate])
                  ->orWhere(function ($sq) use ($startDate, $endDate) {
                      $sq->where('vacaciones.Fecha_Inicial', '<=', $startDate)
                         ->where('vacaciones.Fecha_Final', '>=', $endDate);
                  });
            })
            ->select([
                'vacaciones.*', 'personales.Nombre_Completo', 'laborales_empleado.Fisica', 
                'puestos.Nombre_Puesto', 'fechas_laborales.Fecha_Ingreso'
            ])
            ->get();

        foreach ($vacations as $vac) {
            $events[] = [
                'tipo' => 'Vacaciones',
                'nombre' => $vac->Nombre_Completo,
                'n_empleado' => $vac->N_Empleado,
                'fecha_evento' => $vac->Fecha_Inicial . ' al ' . $vac->Fecha_Final,
                'fecha_ingreso' => $vac->Fecha_Ingreso,
                'fisica' => $vac->Fisica,
                'puesto' => $vac->Nombre_Puesto,
                'detalles' => "Concepto: " . $vac->Concepto . " | Días tomados: " . $vac->Tomadas
            ];
        }

        // Obtener contratos que vencen en el rango (Determinado)
        $contratos = Contrato::join('personales', 'contratos.N_Empleado', '=', 'personales.N_Empleado')
            ->join('laborales_empleado', 'contratos.N_Empleado', '=', 'laborales_empleado.N_Empleado')
            ->join('puestos', 'laborales_empleado.ID_Puesto', '=', 'puestos.ID')
            ->leftJoin('fechas_laborales', 'contratos.N_Empleado', '=', 'fechas_laborales.N_Empleado')
            ->where('contratos.Tipo_de_Contrato', 'Determinado')
            ->where('laborales_empleado.Estatus', 'ACTIVO')
            ->whereBetween('contratos.F_Vence', [$startDate, $endDate])
            ->select([
                'contratos.*', 'personales.Nombre_Completo', 'laborales_empleado.Fisica', 
                'puestos.Nombre_Puesto', 'fechas_laborales.Fecha_Ingreso'
            ])
            ->get();

        foreach ($contratos as $con) {
            $events[] = [
                'tipo' => 'Vencimiento de contrato',
                'nombre' => $con->Nombre_Completo,
                'n_empleado' => $con->N_Empleado,
                'fecha_evento' => $con->F_Vence,
                'fecha_ingreso' => $con->Fecha_Ingreso,
                'fisica' => $con->Fisica,
                'puesto' => $con->Nombre_Puesto,
                'detalles' => "Vencimiento de contrato determinado"
            ];
        }

        // Obtener evaluaciones en el rango (Evaluacion)
        $evaluations = Contrato::join('personales', 'contratos.N_Empleado', '=', 'personales.N_Empleado')
            ->join('laborales_empleado', 'contratos.N_Empleado', '=', 'laborales_empleado.N_Empleado')
            ->join('puestos', 'laborales_empleado.ID_Puesto', '=', 'puestos.ID')
            ->leftJoin('fechas_laborales', 'contratos.N_Empleado', '=', 'fechas_laborales.N_Empleado')
            ->where('contratos.Tipo_de_Contrato', 'Evaluacion')
            ->where('laborales_empleado.Estatus', 'ACTIVO')
            ->whereBetween('contratos.F_Vence', [$startDate, $endDate])
            ->select([
                'contratos.*', 'personales.Nombre_Completo', 'laborales_empleado.Fisica', 
                'puestos.Nombre_Puesto', 'fechas_laborales.Fecha_Ingreso'
            ])
            ->get();

        foreach ($evaluations as $ev) {
            // Calcular número de evaluación (histórica del empleado)
            $allEvals = Contrato::where('N_Empleado', $ev->N_Empleado)
                ->where('Tipo_de_Contrato', 'Evaluacion')
                ->orderBy('F_Vence', 'asc')
                ->pluck('ID')
                ->toArray();
            
            $idx = array_search($ev->ID, $allEvals);
            $evalNum = ($idx !== false) ? $idx + 1 : 1;

            $status = ($ev->Ruta_Doc === 'Realizada') ? 'Realizada' : 'Pendiente';

            $events[] = [
                'tipo' => 'Evaluación',
                'nombre' => $ev->Nombre_Completo,
                'n_empleado' => $ev->N_Empleado,
                'fecha_evento' => $ev->F_Vence,
                'fecha_ingreso' => $ev->Fecha_Ingreso,
                'fisica' => $ev->Fisica,
                'puesto' => $ev->Nombre_Puesto,
                'detalles' => "Evaluación #{$evalNum} | Estado: {$status}"
            ];
        }

        // Obtener incapacidades en el rango
        $incapacities = Incapacidad::join('laborales_empleado', 'incapacidades.N_Empleado', '=', 'laborales_empleado.N_Empleado')
            ->join('personales', 'incapacidades.N_Empleado', '=', 'personales.N_Empleado')
            ->join('puestos', 'laborales_empleado.ID_Puesto', '=', 'puestos.ID')
            ->leftJoin('fechas_laborales', 'incapacidades.N_Empleado', '=', 'fechas_laborales.N_Empleado')
            ->where('laborales_empleado.Estatus', 'ACTIVO')
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('incapacidades.Fecha_Inicio', [$startDate, $endDate])
                  ->orWhereBetween('incapacidades.Fecha_Fin', [$startDate, $endDate])
                  ->orWhere(function ($sq) use ($startDate, $endDate) {
                      $sq->where('incapacidades.Fecha_Inicio', '<=', $startDate)
                         ->where('incapacidades.Fecha_Fin', '>=', $endDate);
                  });
            })
            ->select([
                'incapacidades.*', 'personales.Nombre_Completo', 'laborales_empleado.Fisica', 
                'puestos.Nombre_Puesto', 'fechas_laborales.Fecha_Ingreso'
            ])
            ->get();

        foreach ($incapacities as $inc) {
            $events[] = [
                'tipo' => 'Incapacidad',
                'nombre' => $inc->Nombre_Completo,
                'n_empleado' => $inc->N_Empleado,
                'fecha_evento' => $inc->Fecha_Inicio . ' al ' . $inc->Fecha_Fin,
                'fecha_ingreso' => $inc->Fecha_Ingreso,
                'fisica' => $inc->Fisica,
                'puesto' => $inc->Nombre_Puesto,
                'detalles' => "Descripción: " . $inc->Descripcion . " | Días: " . $inc->Total_Dias
            ];
        }

        // Ordenar los eventos resultantes por la fecha en la que ocurren (o la fecha del rango)
        usort($events, function ($a, $b) {
            $dateA = explode(' al ', $a['fecha_evento'])[0];
            $dateB = explode(' al ', $b['fecha_evento'])[0];
            return strcmp($dateA, $dateB);
        });

        return $events;
    }
}
