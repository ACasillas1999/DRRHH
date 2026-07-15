<?php

namespace App\Http\Controllers;

use App\Models\DocumentoImss;
use App\Models\FechaLaboral;
use App\Models\HistorialCambio;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AltaBajaImssController extends Controller
{
    public function index(Request $request): View
    {
        $busqueda = $request->input('busqueda');

        $query = DB::table('fechas_laborales')
            ->join('personales', 'fechas_laborales.N_Empleado', '=', 'personales.N_Empleado')
            ->join('laborales_empleado', 'fechas_laborales.N_Empleado', '=', 'laborales_empleado.N_Empleado')
            ->join('puestos', 'laborales_empleado.ID_Puesto', '=', 'puestos.ID')
            ->join('documentos_imss as di', 'di.n_empleado', '=', 'laborales_empleado.N_Empleado')
            ->select([
                'personales.N_Empleado', 'personales.Nombre_Completo', 'personales.IMSS',
                'laborales_empleado.Estatus', 'laborales_empleado.Nomeclatura', 'laborales_empleado.Fisica',
                'puestos.Nombre_Puesto',
                'fechas_laborales.Fecha_Ingreso', 'fechas_laborales.Fecha_Alta_IMSS', 'fechas_laborales.Fecha_Baja_IMSS',
                'di.tipo', 'di.ruta_documento',
            ])
            ->where(function ($q) {
                $q->where(function ($qq) {
                    $qq->whereNull('fechas_laborales.Fecha_Alta_IMSS')->where('laborales_empleado.Estatus', 'ACTIVO')->where('di.tipo', 'ACTIVO');
                })->orWhere(function ($qq) {
                    $qq->whereNull('fechas_laborales.Fecha_Baja_IMSS')->where('laborales_empleado.Estatus', 'BAJA')->where('di.tipo', 'BAJA');
                })->orWhere(function ($qq) {
                    $qq->whereNotNull('fechas_laborales.Fecha_Alta_IMSS')->whereNull('di.ruta_documento')->where('laborales_empleado.Estatus', 'ACTIVO')->where('di.tipo', 'ACTIVO');
                })->orWhere(function ($qq) {
                    $qq->whereNotNull('fechas_laborales.Fecha_Baja_IMSS')->whereNull('di.ruta_documento')->where('laborales_empleado.Estatus', 'BAJA')->where('di.tipo', 'BAJA');
                });
            });

        if (!empty($busqueda)) {
            $query->where(function ($q) use ($busqueda) {
                $q->where('personales.N_Empleado', 'like', "%{$busqueda}%")
                    ->orWhere('personales.Nombre_Completo', 'like', "%{$busqueda}%");
            });
        }

        $registros = $query->orderByDesc('personales.N_Empleado')->paginate(30)->withQueryString();

        return view('altas-imss.index', compact('registros', 'busqueda'));
    }

    public function actualizarFechaAlta(Request $request, string $n_empleado): RedirectResponse
    {
        $data = $request->validate(['fecha_alta_imss' => ['required', 'date']]);

        FechaLaboral::where('N_Empleado', $n_empleado)->update(['Fecha_Alta_IMSS' => $data['fecha_alta_imss']]);

        HistorialCambio::create([
            'Usuario_ID' => null,
            'N_Empleado' => $n_empleado,
            'Cambio' => "Actualización de la Fecha de ALTA IMSS del empleado con ID: {$n_empleado}",
            'Fecha_Hora' => now(),
        ]);

        return back()->with('status', 'Fecha de alta IMSS actualizada correctamente.');
    }

    public function actualizarFechaBaja(Request $request, string $n_empleado): RedirectResponse
    {
        $data = $request->validate(['fecha_baja_imss' => ['required', 'date']]);

        FechaLaboral::where('N_Empleado', $n_empleado)->update(['Fecha_Baja_IMSS' => $data['fecha_baja_imss']]);

        HistorialCambio::create([
            'Usuario_ID' => null,
            'N_Empleado' => $n_empleado,
            'Cambio' => "Actualización de la Fecha de BAJA IMSS del empleado con ID: {$n_empleado}",
            'Fecha_Hora' => now(),
        ]);

        return back()->with('status', 'Fecha de baja IMSS actualizada correctamente.');
    }

    public function subirDocumento(Request $request, string $n_empleado): RedirectResponse
    {
        $data = $request->validate([
            'tipo' => ['required', 'string', 'in:activo,baja'],
            'documento' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
        ]);

        $archivo = $request->file('documento');
        $nombreArchivo = $n_empleado . '_' . $data['tipo'] . '_' . time() . '.' . $archivo->getClientOriginalExtension();
        $archivo->move(public_path('uploads/docs_altas_bajas'), $nombreArchivo);
        $rutaPublica = '/uploads/docs_altas_bajas/' . $nombreArchivo;

        $existente = DocumentoImss::where('n_empleado', $n_empleado)->where('tipo', $data['tipo'])->first();
        if ($existente) {
            $existente->update(['ruta_documento' => $rutaPublica, 'documento_subido' => 1]);
        } else {
            DocumentoImss::create([
                'n_empleado' => $n_empleado,
                'tipo' => $data['tipo'],
                'ruta_documento' => $rutaPublica,
                'documento_subido' => 1,
            ]);
        }

        return back()->with('status', 'Documento subido correctamente.');
    }
}
