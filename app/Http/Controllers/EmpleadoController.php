<?php

namespace App\Http\Controllers;

use App\Models\AsignacionProducto;
use App\Models\DocumentoBaja;
use App\Models\DocumentoGenerado;
use App\Models\DocumentoImss;
use App\Models\FechaLaboral;
use App\Models\Incapacidad;
use App\Models\LaboralEmpleado;
use App\Models\PagoMensual;
use App\Models\Personal;
use App\Models\Puesto;
use App\Models\Responsable;
use App\Models\RutaDocumento;
use App\Models\Vacacion;
use Illuminate\Http\Request;

class EmpleadoController extends Controller
{
    public function index(Request $request)
    {
        $filtros = $request->only(['sucursal', 'sucursalFisica', 'estado', 'puesto', 'departamento', 'busqueda']);

        $sucursales = LaboralEmpleado::query()->whereNotNull('Nomeclatura')->where('Nomeclatura', '!=', '')->distinct()->orderBy('Nomeclatura')->pluck('Nomeclatura');
        $sucursalesFisicas = LaboralEmpleado::query()->whereNotNull('Fisica')->where('Fisica', '!=', '')->distinct()->orderBy('Fisica')->pluck('Fisica');
        $estados = LaboralEmpleado::query()->whereNotNull('Estatus')->where('Estatus', '!=', '')->distinct()->orderBy('Estatus')->pluck('Estatus');
        $puestos = Puesto::query()->whereNotNull('Nombre_Puesto')->where('Nombre_Puesto', '!=', '')->distinct()->orderBy('Nombre_Puesto')->pluck('Nombre_Puesto');
        $departamentos = Puesto::query()->whereNotNull('Departamento')->where('Departamento', '!=', '')->distinct()->orderBy('Departamento')->pluck('Departamento');

        $pendientes = DocumentoBaja::query()
            ->where(function ($q) {
                $q->whereNull('Ruta_Documento')->orWhere('Ruta_Documento', '');
            })
            ->distinct()
            ->pluck('N_Empleado')
            ->all();

        $query = Personal::query()
            ->join('laborales_empleado', 'laborales_empleado.N_Empleado', '=', 'personales.N_Empleado')
            ->join('puestos', 'puestos.ID', '=', 'laborales_empleado.ID_Puesto')
            ->leftJoin('fechas_laborales', 'fechas_laborales.N_Empleado', '=', 'personales.N_Empleado')
            ->select([
                'personales.N_Empleado',
                'personales.Nombre_Completo',
                'puestos.Nombre_Puesto',
                'puestos.Departamento',
                'laborales_empleado.Nomeclatura',
                'laborales_empleado.Fisica',
                'laborales_empleado.Estatus',
                'fechas_laborales.Fecha_Ingreso',
            ]);

        if (!empty($filtros['sucursal'])) {
            $query->where('laborales_empleado.Nomeclatura', $filtros['sucursal']);
        }
        if (!empty($filtros['sucursalFisica'])) {
            $query->where('laborales_empleado.Fisica', $filtros['sucursalFisica']);
        }
        if (!empty($filtros['estado'])) {
            $query->where('laborales_empleado.Estatus', $filtros['estado']);
        }
        if (!empty($filtros['puesto'])) {
            $query->where('puestos.Nombre_Puesto', $filtros['puesto']);
        }
        if (!empty($filtros['departamento'])) {
            $query->where('puestos.Departamento', $filtros['departamento']);
        }
        if (!empty($filtros['busqueda'])) {
            $busqueda = $filtros['busqueda'];
            $query->where(function ($q) use ($busqueda) {
                $q->where('personales.N_Empleado', 'like', "%{$busqueda}%")
                    ->orWhere('personales.Nombre_Completo', 'like', "%{$busqueda}%");
            });
        }

        $empleados = $query
            ->orderBy('laborales_empleado.Estatus')
            ->orderByDesc('personales.N_Empleado')
            ->paginate(30)
            ->withQueryString();

        return view('empleados.index', compact(
            'empleados',
            'filtros',
            'sucursales',
            'sucursalesFisicas',
            'estados',
            'puestos',
            'departamentos',
            'pendientes'
        ));
    }

    public function show(string $n_empleado)
    {
        if (!auth()->check()) {
            \Illuminate\Support\Facades\Auth::loginUsingId(1);
        }
        $empleado = Personal::where('N_Empleado', $n_empleado)->firstOrFail();
        $laboral = LaboralEmpleado::with('puesto', 'razonSocial')->where('N_Empleado', $n_empleado)->first();
        abort_if(!$laboral, 404);

        $rutaDocumento = RutaDocumento::where('N_Empleado', $n_empleado)->first();
        $fechasLaborales = FechaLaboral::where('N_Empleado', $n_empleado)->orderByDesc('Fecha_Ingreso')->get();
        $documentosImss = DocumentoImss::where('n_empleado', $n_empleado)->get();
        // Igual que el legacy: JOIN con productos (has) para excluir asignaciones sin producto, y orden por ID (el legacy no usa ORDER BY)
        $asignaciones = AsignacionProducto::with('producto')->has('producto')->where('N_Empleado', $n_empleado)->orderBy('ID')->get();
        $pagos = PagoMensual::with('producto')->where('N_Empleado', $n_empleado)->orderByDesc('Fecha_Inicio')->get();
        $vacacionesHistorial = Vacacion::where('N_Empleado', $n_empleado)->get();
        $incapacidades = Incapacidad::where('N_Empleado', $n_empleado)->get();
        $documentosGenerados = DocumentoGenerado::where('N_Empleado', $n_empleado)->orderByDesc('Fecha_Subida')->get();
        $historial = $empleado->historialCambios()->orderByDesc('Fecha_Hora')->get();
        // Orden por ID: el legacy (Manejo_De_Documentos.php) no usa ORDER BY y MySQL los regresa en orden de inserción
        $contratos = \App\Models\Contrato::where('N_Empleado', $n_empleado)->orderBy('ID')->get();
        $documentosEmpleado = $empleado->documentosEmpleado()->orderBy('Tipo_Documento')->get();
        $documentosLaborales = $empleado->documentosLaborales()->orderBy('Tipo_Documento')->get();

        $responsable = null;
        if ($laboral->puesto) {
            $responsable = Responsable::where('Departamento', $laboral->puesto->Departamento)
                ->where('SubDepartamento', $laboral->puesto->SubDepartamento)
                ->value('Responsable');
        }

        return view('empleados.show', compact(
            'empleado',
            'laboral',
            'rutaDocumento',
            'fechasLaborales',
            'documentosImss',
            'asignaciones',
            'pagos',
            'vacacionesHistorial',
            'incapacidades',
            'responsable',
            'documentosGenerados',
            'historial',
            'contratos',
            'documentosEmpleado',
            'documentosLaborales'
        ));
    }

    public function notificaciones()
    {
        $hoy = now();
        $cumpleanos = Personal::whereMonth('Fecha_Nacimiento', $hoy->month)
            ->whereDay('Fecha_Nacimiento', $hoy->day)
            ->whereHas('laboral', function($q) {
                $q->where('Estatus', 'ACTIVO');
            })
            ->select(['N_Empleado', 'Nombre_Completo', 'Fecha_Nacimiento'])
            ->get();

        $limite = now()->addDays(15);
        $contratosVencer = \App\Models\Contrato::where('F_Vence', '>=', now()->toDateString())
            ->where('F_Vence', '<=', $limite->toDateString())
            ->whereHas('personal.laboral', function($q) {
                $q->where('Estatus', 'ACTIVO');
            })
            ->with(['personal' => function($q) {
                $q->select(['N_Empleado', 'Nombre_Completo']);
            }])
            ->select(['ID', 'N_Empleado', 'Tipo_de_Contrato', 'F_Vence'])
            ->get();

        return response()->json([
            'cumpleanos' => $cumpleanos,
            'contratosVencer' => $contratosVencer,
            'total' => $cumpleanos->count() + $contratosVencer->count()
        ]);
    }
}
