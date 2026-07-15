<?php

namespace App\Http\Controllers;

use App\Models\EmpleadoEstimado;
use App\Models\LaboralEmpleado;
use App\Models\Personal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReporteController extends Controller
{
    private const COLUMNAS_EXPORTABLES = [
        'Nomeclatura', 'Fisica', 'Fecha_Ingreso', 'Fecha_Egreso', 'N_Empleado', 'Nombre_Completo',
        'Nombre_Puesto', 'Departamento', 'Salario_Diario', 'IMSS', 'RFC', 'Sexo', 'Edad',
        'CURP', 'Fecha_Nacimiento', 'Domicilio', 'Estado_Civil', 'Telefono', 'Celular',
        'Correo_Electronico', 'Tel_Emergencia', 'Contacto_Emergencia', 'Parentesco_B', 'Domicilio_B', 'Horario', 'KPI',
        'Comision', 'Estatus', 'Telefono_Empresarial', 'Extension', 'Correo_Empresarial', 'Reclutador', 'motivo_baja', 'comentario',
    ];

    public function index(): View
    {
        $activos = DB::table('personales as p')
            ->join('laborales_empleado as le', 'p.N_Empleado', '=', 'le.N_Empleado')
            ->join('puestos as pu', 'le.ID_Puesto', '=', 'pu.ID')
            ->where('le.Estatus', 'ACTIVO')
            ->select('pu.Nombre_Puesto', 'le.Nomeclatura', 'pu.Departamento', 'le.Fisica')
            ->get();

        $porPuesto = $activos->countBy('Nombre_Puesto');
        $porNomenclatura = $activos->countBy('Nomeclatura');
        $porDepartamento = $activos->countBy('Departamento');
        $porEstatus = LaboralEmpleado::select('Estatus')->get()->countBy('Estatus');

        $actualesPorSucursalPuesto = DB::table('laborales_empleado as le')
            ->join('puestos as pu', 'le.ID_Puesto', '=', 'pu.ID')
            ->where('le.Estatus', 'ACTIVO')
            ->select('le.Fisica', 'pu.Nombre_Puesto')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('le.Fisica', 'pu.Nombre_Puesto')
            ->get();

        $estimados = EmpleadoEstimado::all();

        $comparativaPorSucursal = [];
        foreach ($estimados as $est) {
            $actual = $actualesPorSucursalPuesto->first(fn ($a) => $a->Fisica === $est->Sucursal && $a->Nombre_Puesto === $est->Puesto);
            $actualCount = $actual->total ?? 0;
            $comparativaPorSucursal[$est->Sucursal][] = [
                'puesto' => $est->Puesto,
                'actuales' => $actualCount,
                'estimados' => $est->Estimado_Empleados,
                'diferencia' => $actualCount - $est->Estimado_Empleados,
            ];
        }

        $comparativaGlobalPorPuesto = [];
        $estimadosPorPuesto = $estimados->groupBy('Puesto');
        foreach ($estimadosPorPuesto as $puesto => $grupo) {
            $totalEstimado = $grupo->sum('Estimado_Empleados');
            $totalActual = $actualesPorSucursalPuesto->where('Nombre_Puesto', $puesto)->sum('total');
            $comparativaGlobalPorPuesto[] = [
                'puesto' => $puesto,
                'actuales' => $totalActual,
                'estimados' => $totalEstimado,
                'diferencia' => $totalActual - $totalEstimado,
            ];
        }

        return view('reportes.index', compact(
            'porPuesto',
            'porNomenclatura',
            'porDepartamento',
            'porEstatus',
            'comparativaPorSucursal',
            'comparativaGlobalPorPuesto'
        ));
    }

    public function ingresosRenuncias(Request $request): View
    {
        $fechaInicio = $request->input('fecha_inicio', '2020-01-01');
        $fechaFin = $request->input('fecha_fin', now()->format('Y-m-d'));

        $sucursales = LaboralEmpleado::whereNotNull('Fisica')->where('Fisica', '!=', '')->distinct()->pluck('Fisica');

        $contratacionesPorFecha = [];
        $renunciasPorFecha = [];

        foreach ($sucursales as $fisica) {
            $contrataciones = DB::table('fechas_laborales as fl')
                ->join('laborales_empleado as le', 'le.N_Empleado', '=', 'fl.N_Empleado')
                ->where('le.Estatus', 'ACTIVO')
                ->whereBetween('fl.Fecha_Ingreso', [$fechaInicio, $fechaFin])
                ->where('le.Fisica', $fisica)
                ->select('fl.Fecha_Ingreso')
                ->selectRaw('COUNT(*) as total')
                ->groupBy('fl.Fecha_Ingreso')
                ->orderBy('fl.Fecha_Ingreso')
                ->get();

            $renuncias = DB::table('fechas_laborales as fl')
                ->join('laborales_empleado as le', 'le.N_Empleado', '=', 'fl.N_Empleado')
                ->where('le.Estatus', 'BAJA')
                ->whereBetween('fl.Fecha_Egreso', [$fechaInicio, $fechaFin])
                ->where('le.Fisica', $fisica)
                ->select('fl.Fecha_Egreso')
                ->selectRaw('COUNT(*) as total')
                ->groupBy('fl.Fecha_Egreso')
                ->orderBy('fl.Fecha_Egreso')
                ->get();

            $contratacionesPorFecha[$fisica] = $contrataciones->pluck('total', 'Fecha_Ingreso')->toArray();
            $renunciasPorFecha[$fisica] = $renuncias->pluck('total', 'Fecha_Egreso')->toArray();
        }

        $contratacionesGlobales = [];
        $renunciasGlobales = [];
        foreach ($sucursales as $fisica) {
            foreach ($contratacionesPorFecha[$fisica] as $fecha => $total) {
                $contratacionesGlobales[$fecha] = ($contratacionesGlobales[$fecha] ?? 0) + $total;
            }
            foreach ($renunciasPorFecha[$fisica] as $fecha => $total) {
                $renunciasGlobales[$fecha] = ($renunciasGlobales[$fecha] ?? 0) + $total;
            }
        }
        ksort($contratacionesGlobales);
        ksort($renunciasGlobales);

        return view('reportes.ingresos-renuncias', compact(
            'fechaInicio',
            'fechaFin',
            'sucursales',
            'contratacionesPorFecha',
            'renunciasPorFecha',
            'contratacionesGlobales',
            'renunciasGlobales'
        ));
    }

    public function exportar(Request $request): View
    {
        $columnasSeleccionadas = $request->input('columns', []);
        $previewData = [];

        if ($request->isMethod('post') && !empty($columnasSeleccionadas)) {
            $previewData = DB::table('personales as p')
                ->join('laborales_empleado as le', 'p.N_Empleado', '=', 'le.N_Empleado')
                ->join('fechas_laborales as fl', 'fl.N_Empleado', '=', 'le.N_Empleado')
                ->join('puestos as pu', 'pu.ID', '=', 'le.ID_Puesto')
                ->limit(50)
                ->get()
                ->map(fn ($row) => (array) $row);
        }

        return view('reportes.exportar', [
            'columnasDisponibles' => self::COLUMNAS_EXPORTABLES,
            'columnasSeleccionadas' => $columnasSeleccionadas,
            'previewData' => $previewData,
        ]);
    }

    public function exportarExcel(Request $request): StreamedResponse
    {
        $columnasSeleccionadas = array_values(array_intersect(
            $request->input('columns', []),
            self::COLUMNAS_EXPORTABLES
        ));

        abort_if(empty($columnasSeleccionadas), 422, 'No se seleccionaron columnas válidas para exportar.');

        $filas = DB::table('personales as p')
            ->join('laborales_empleado as le', 'p.N_Empleado', '=', 'le.N_Empleado')
            ->join('fechas_laborales as fl', 'fl.N_Empleado', '=', 'le.N_Empleado')
            ->join('puestos as pu', 'pu.ID', '=', 'le.ID_Puesto')
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        foreach (array_values($columnasSeleccionadas) as $i => $columna) {
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($i + 1) . '1', $columna);
        }

        foreach ($filas as $filaIndex => $fila) {
            foreach (array_values($columnasSeleccionadas) as $i => $columna) {
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($i + 1) . ($filaIndex + 2), $fila->{$columna} ?? '');
            }
        }

        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, 'RH_Personalizado.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
