<?php

namespace App\Http\Controllers;

use App\Models\Candidato;
use App\Models\EmpleadoEstimado;
use App\Models\HistorialEstado;
use App\Models\LaboralEmpleado;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ReclutamientoController extends Controller
{
    private const ESTADOS_PROCESO = [
        'En revisión', 'Primera Entrevista', 'Segunda Entrevista', 'Aplicando Psicometria',
        'Socieconomico', 'Entrevista', 'Contratado', 'Rechazado',
    ];

    private const COLORES_ESTADO = [
        'En revisión' => '#fadb14',
        'Primera Entrevista' => '#9D7FEA',
        'Segunda Entrevista' => '#FFA940',
        'Aplicando Psicometria' => '#13C2C2',
        'Socieconomico' => '#F759AB',
        'Entrevista' => '#1890ff',
        'Contratado' => '#52c41a',
        'Rechazado' => '#ff4d4f',
    ];

    private const DIAS_SEMANA = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];

    public function index(Request $request): View
    {
        // 1. Comparativa: estimados vs empleados activos actuales
        $estimados = EmpleadoEstimado::orderBy('Sucursal')->orderBy('Puesto')->get();

        $actuales = LaboralEmpleado::join('puestos', 'laborales_empleado.ID_Puesto', '=', 'puestos.ID')
            ->where('laborales_empleado.Estatus', 'ACTIVO')
            ->groupBy('laborales_empleado.Fisica', 'puestos.Nombre_Puesto')
            ->select('laborales_empleado.Fisica as sucursal', 'puestos.Nombre_Puesto as puesto', DB::raw('COUNT(*) as total'))
            ->get()
            ->keyBy(fn ($row) => $row->sucursal . '|' . $row->puesto);

        $comparativaTodos = $estimados->map(function ($estimado) use ($actuales) {
            $clave = $estimado->Sucursal . '|' . $estimado->Puesto;
            $actual = (int) ($actuales->get($clave)?->total ?? 0);

            return (object) [
                'sucursal' => $estimado->Sucursal,
                'puesto' => $estimado->Puesto,
                'estimado' => $estimado->Estimado_Empleados,
                'actual' => $actual,
                'diferencia' => $actual - $estimado->Estimado_Empleados,
            ];
        })->values();

        $comparativaPage = max(1, (int) $request->input('comparativa_page', 1));
        $comparativaPerPage = 30;
        $comparativa = new \Illuminate\Pagination\LengthAwarePaginator(
            $comparativaTodos->forPage($comparativaPage, $comparativaPerPage)->values(),
            $comparativaTodos->count(),
            $comparativaPerPage,
            $comparativaPage,
            ['path' => $request->url(), 'pageName' => 'comparativa_page', 'query' => $request->query()]
        );

        // 2. Candidatos en proceso
        $candidatos = Candidato::whereIn('estado', self::ESTADOS_PROCESO)
            ->orderByDesc('fecha_postulacion')
            ->with('ultimoHistorial.usuario')
            ->paginate(30, ['*'], 'candidatos_page')
            ->withQueryString();

        // 3. Contador por estado
        $contadorEstados = Candidato::whereIn('estado', self::ESTADOS_PROCESO)
            ->select('estado', DB::raw('COUNT(*) as total'))
            ->groupBy('estado')
            ->pluck('total', 'estado');

        $totalCandidatos = $contadorEstados->sum();

        // 4. Nuevas postulaciones hoy / semana / mes
        $hoy = now()->toDateString();
        $inicioSemana = now()->startOfWeek()->toDateString();
        $inicioMes = now()->startOfMonth()->toDateString();

        $nuevosHoy = Candidato::whereDate('fecha_postulacion', $hoy)->count();
        $nuevosSemana = Candidato::whereDate('fecha_postulacion', '>=', $inicioSemana)->count();
        $nuevosMes = Candidato::whereDate('fecha_postulacion', '>=', $inicioMes)->count();

        // 5. Gráfica semanal: total + por estado
        $valoresSemana = $this->serieSemanaPorEstado(null);

        $estadoDatasets = collect(self::ESTADOS_PROCESO)->map(fn ($estado) => [
            'label' => $estado,
            'data' => $this->serieSemanaPorEstado($estado),
            'borderColor' => self::COLORES_ESTADO[$estado],
            'backgroundColor' => self::COLORES_ESTADO[$estado] . '33',
        ])->values();

        $reclutadores = Usuario::whereIn('Rol', ['RH', 'Admin'])->orderBy('Nombre')->pluck('Nombre');

        return view('reclutamiento.index', compact(
            'comparativa', 'candidatos', 'contadorEstados', 'totalCandidatos',
            'nuevosHoy', 'nuevosSemana', 'nuevosMes', 'valoresSemana', 'estadoDatasets', 'reclutadores'
        ) + [
            'estados' => self::ESTADOS_PROCESO,
            'diasSemana' => self::DIAS_SEMANA,
            'estadoColores' => self::COLORES_ESTADO,
        ]);
    }

    private function serieSemanaPorEstado(?string $estado): array
    {
        $query = Candidato::query()->whereRaw('YEARWEEK(fecha_postulacion, 1) = YEARWEEK(CURDATE(), 1)');
        if ($estado !== null) {
            $query->where('estado', $estado);
        }

        $rows = $query->select(DB::raw('DAYOFWEEK(fecha_postulacion) as dia'), DB::raw('COUNT(*) as total'))
            ->groupBy('dia')
            ->get();

        $serie = array_fill(0, 7, 0);
        foreach ($rows as $row) {
            $indice = ((int) $row->dia) - 2;
            if ($indice < 0) {
                $indice = 6;
            }
            $serie[$indice] = (int) $row->total;
        }

        return $serie;
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'correo' => ['nullable', 'email', 'max:100'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'puesto' => ['required', 'string', 'max:255'],
            'sucursal' => ['required', 'string', 'max:255'],
            'reclutador' => ['required', 'string', 'max:255'],
            'medio_reclutamiento' => ['required', 'string', 'max:255'],
            'motivo_rechazo' => ['nullable', 'string', 'max:255'],
            'cv' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
        ]);

        $cvRuta = null;
        if ($request->hasFile('cv')) {
            $cvRuta = $this->guardarCv($request->file('cv'));
        }

        Candidato::create([
            'vacante_id' => 0,
            'nombre' => $data['nombre'],
            'correo' => $data['correo'] ?? null,
            'telefono' => $data['telefono'] ?? null,
            'cv' => $cvRuta,
            'estado' => 'En revisión',
            'fecha_postulacion' => now()->toDateString(),
            'Reclutador' => $data['reclutador'],
            'Fisica' => $data['sucursal'],
            'Puesto' => $data['puesto'],
            'Medio_Reclutamiento' => $data['medio_reclutamiento'],
            'Motivo_rechazo' => $data['motivo_rechazo'] ?? '',
        ]);

        return response()->json(['success' => true, 'mensaje' => 'Candidato registrado exitosamente.']);
    }

    public function update(Request $request, string $id)
    {
        $candidato = Candidato::findOrFail($id);

        $data = $request->validate([
            'estado' => ['required', 'string', 'max:30'],
            'notas' => ['nullable', 'string'],
            'motivo_rechazo' => ['nullable', 'string', 'max:255'],
            'cv' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
        ]);

        $cambios = [];
        $cambioEstado = $data['estado'] !== $candidato->estado;
        $cambioNotas = ($data['notas'] ?? '') !== $candidato->notas;
        $cambioMotivo = ($data['motivo_rechazo'] ?? '') !== $candidato->Motivo_rechazo;

        $updateData = [
            'estado' => $data['estado'],
            'notas' => $data['notas'] ?? '',
            'Motivo_rechazo' => $data['motivo_rechazo'] ?? '',
        ];

        if ($request->hasFile('cv')) {
            $updateData['cv'] = $this->guardarCv($request->file('cv'));
            $cambios[] = 'CV subido correctamente';
        }

        $candidato->update($updateData);

        if ($cambioEstado) {
            HistorialEstado::create([
                'candidato_id' => $candidato->id,
                'estado' => $data['estado'],
                'usuario_id' => auth()->id(),
                'fecha' => now(),
            ]);
            $cambios[] = 'Estado actualizado';
        }
        if ($cambioNotas) {
            $cambios[] = 'Notas actualizadas';
        }
        if ($cambioMotivo) {
            $cambios[] = 'Motivo de rechazo actualizado';
        }

        $mensaje = $cambios ? implode(', ', $cambios) : 'No se realizaron cambios';

        return response()->json(['success' => true, 'mensaje' => $mensaje]);
    }

    public function historial(string $id): View
    {
        $historial = HistorialEstado::where('candidato_id', $id)
            ->with('usuario')
            ->orderByDesc('fecha')
            ->get();

        return view('reclutamiento._historial', compact('historial'));
    }

    private function guardarCv($archivo): string
    {
        $carpeta = public_path('uploads/candidatos');
        if (!is_dir($carpeta)) {
            mkdir($carpeta, 0755, true);
        }

        $nombreOriginal = pathinfo($archivo->getClientOriginalName(), PATHINFO_FILENAME);
        $nombreArchivo = time() . '_' . Str::slug($nombreOriginal) . '.' . $archivo->getClientOriginalExtension();
        $archivo->move($carpeta, $nombreArchivo);

        return '/uploads/candidatos/' . $nombreArchivo;
    }
}
