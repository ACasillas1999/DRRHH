<?php

use Illuminate\Support\Facades\Route;

use App\Models\Personal;
use App\Http\Controllers\EmpleadoController;
use App\Http\Controllers\EmpleadoBajaController;
use App\Http\Controllers\EmpleadoFotoController;
use App\Http\Controllers\EmpleadoCrearController;
use App\Http\Controllers\EmpleadoPersonalesController;
use App\Http\Controllers\EmpleadoLaboralesController;
use App\Http\Controllers\EmpleadoDocumentoController;
use App\Http\Controllers\RazonSocialController;
use App\Http\Controllers\PuestoController;
use App\Http\Controllers\ResponsableController;
use App\Http\Controllers\UbicacionController;
use App\Http\Controllers\AltaBajaImssController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\EstimadoController;
use App\Http\Controllers\DiarioController;
use App\Http\Controllers\IncidenciaController;
use App\Http\Controllers\ChecadorController;
use App\Http\Controllers\HistorialController;
use App\Http\Controllers\ReclutamientoController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ChecadorWebhookController;

use App\Models\LaboralEmpleado;
use App\Models\FechaLaboral;

Route::get('/login', [LoginController::class, 'showForm'])->name('login')->middleware('guest');
Route::post('/login', [LoginController::class, 'login'])->middleware(['guest', 'throttle:5,1']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Recibe eventos push de los checadores en modo HTTP Listening (sin auth, el dispositivo no tiene sesión)
Route::post('/webhooks/checador', [ChecadorWebhookController::class, 'receive']);

Route::middleware('auth')->group(function () {
    Route::get('/', function () {
        // Cada alta (y reingreso) crea una fila en fechas_laborales; se cuentan todas las del año, sigan activos o no
        $anio = now('America/Mexico_City')->year;
        $contratacionesAnio = FechaLaboral::whereYear('Fecha_Ingreso', $anio)->count();
        $activos = LaboralEmpleado::where('Estatus', 'ACTIVO')->count();
        $bajas = LaboralEmpleado::where('Estatus', 'BAJA')->count();

        // Sexo vive en personales y Estatus en laborales_empleado → whereHas cruza ambas
        $hombres = Personal::where('Sexo', 'Masculino')
            ->whereHas('laboral', fn ($q) => $q->where('Estatus', 'ACTIVO'))
            ->count();
        $mujeres = Personal::where('Sexo', 'Femenino')
            ->whereHas('laboral', fn ($q) => $q->where('Estatus', 'ACTIVO'))
            ->count();

        return view('menu', compact('anio', 'contratacionesAnio', 'activos', 'bajas', 'hombres', 'mujeres'));
    });

    Route::get('/test-db', function () {
        $empleados = Personal::has('laboral')->with('laboral')->take(5)->get();
        return view('test', compact('empleados'));
    });

    Route::get('/notificaciones', [EmpleadoController::class, 'notificaciones'])->name('notificaciones');

    Route::middleware('role:Admin,RH,Nominas')->group(function () {
        Route::get('/empleados', [EmpleadoController::class, 'index'])->name('empleados.index');
        Route::get('/empleados/crear', [EmpleadoCrearController::class, 'create'])->name('empleados.crear');
        Route::post('/empleados', [EmpleadoCrearController::class, 'store'])->name('empleados.store');
        Route::get('/empleados/{n_empleado}', [EmpleadoController::class, 'show'])->name('empleados.show');
        Route::post('/empleados/{n_empleado}/baja', [EmpleadoBajaController::class, 'store'])->name('empleados.baja');
        Route::post('/empleados/{n_empleado}/foto', [EmpleadoFotoController::class, 'store'])->name('empleados.foto');
        Route::get('/empleados/{n_empleado}/personales/editar', [EmpleadoPersonalesController::class, 'edit'])->name('empleados.personales.edit');
        Route::put('/empleados/{n_empleado}/personales', [EmpleadoPersonalesController::class, 'update'])->name('empleados.personales.update');
        Route::get('/empleados/{n_empleado}/laborales/editar', [EmpleadoLaboralesController::class, 'edit'])->name('empleados.laborales.edit');
        Route::put('/empleados/{n_empleado}/laborales', [EmpleadoLaboralesController::class, 'update'])->name('empleados.laborales.update');

        Route::get('/empleados/{n_empleado}/documentos/ficha-nomina', [EmpleadoDocumentoController::class, 'fichaNomina'])->name('empleados.documentos.fichaNomina');
        Route::get('/empleados/{n_empleado}/documentos/folio-baja', [EmpleadoDocumentoController::class, 'folioBaja'])->name('empleados.documentos.folioBaja');
        Route::get('/empleados/{n_empleado}/documentos/carta-oferta', [EmpleadoDocumentoController::class, 'cartaOferta'])->name('empleados.documentos.cartaOferta');
        Route::get('/empleados/{n_empleado}/documentos/carta-confidencialidad', [EmpleadoDocumentoController::class, 'cartaConfidencialidad'])->name('empleados.documentos.cartaConfidencialidad');
        Route::get('/empleados/{n_empleado}/documentos/politica-puntualidad', [EmpleadoDocumentoController::class, 'politicaPuntualidad'])->name('empleados.documentos.politicaPuntualidad');
        Route::get('/empleados/{n_empleado}/contratos/determinado', [EmpleadoDocumentoController::class, 'contratoDeterminado'])->name('empleados.contratos.determinado');
        Route::get('/empleados/{n_empleado}/contratos/indeterminado', [EmpleadoDocumentoController::class, 'contratoIndeterminado'])->name('empleados.contratos.indeterminado');
        Route::get('/empleados/{n_empleado}/contratos/convenio', [EmpleadoDocumentoController::class, 'convenio'])->name('empleados.contratos.convenio');
        // Pantalla previa a descargar un contrato (port de Generador_Docs/Determinado.php, GenerarDoc.php y Convenio.php)
        Route::get('/empleados/{n_empleado}/contratos/{tipo}/generar', [EmpleadoDocumentoController::class, 'generarContrato'])->whereIn('tipo', ['determinado', 'indeterminado', 'convenio'])->name('empleados.contratos.generar');
        Route::post('/empleados/{n_empleado}/documentos', [EmpleadoDocumentoController::class, 'storeUpload'])->name('empleados.documentos.store');
        // Subir contrato firmado a un contrato específico (por ID). Port de procesar_contrato.php
        Route::post('/empleados/{n_empleado}/contratos/subir', [EmpleadoDocumentoController::class, 'subirContrato'])->name('empleados.contratos.subir');
        // Subir la entrega firmada de una asignación (uniformes/equipo) por su ID. Port de procesar_asignaciones.php
        Route::post('/empleados/{n_empleado}/asignaciones/subir', [EmpleadoDocumentoController::class, 'subirAsignacion'])->name('empleados.asignaciones.subir');
        Route::post('/empleados/{n_empleado}/documentos-personales', [EmpleadoDocumentoController::class, 'subirPersonal'])->name('empleados.documentos.subirPersonal');
        Route::post('/empleados/{n_empleado}/documentos-laborales', [EmpleadoDocumentoController::class, 'subirLaboral'])->name('empleados.documentos.subirLaboral');
        // Borrar el archivo de un documento del expediente (port de Mas_Documentos/ y Doc_Laborales/borrar_documento.php)
        Route::delete('/empleados/{n_empleado}/documentos-personales/{id}', [EmpleadoDocumentoController::class, 'borrarPersonal'])->name('empleados.documentos.borrarPersonal');
        Route::delete('/empleados/{n_empleado}/documentos-laborales/{id}', [EmpleadoDocumentoController::class, 'borrarLaboral'])->name('empleados.documentos.borrarLaboral');
        // Subir documento de baja (port de Doc_BAJA/Subir_Documento.php)
        Route::post('/empleados/{n_empleado}/documentos-baja', [EmpleadoDocumentoController::class, 'subirBaja'])->name('empleados.documentos.subirBaja');
        Route::get('/altas-imss', [AltaBajaImssController::class, 'index'])->name('altas-imss.index');
        Route::post('/altas-imss/{n_empleado}/fecha-alta', [AltaBajaImssController::class, 'actualizarFechaAlta'])->name('altas-imss.fechaAlta');
        Route::post('/altas-imss/{n_empleado}/fecha-baja', [AltaBajaImssController::class, 'actualizarFechaBaja'])->name('altas-imss.fechaBaja');
        Route::post('/altas-imss/{n_empleado}/documento', [AltaBajaImssController::class, 'subirDocumento'])->name('altas-imss.documento');
    });

    Route::middleware('role:Admin,RH')->group(function () {
        // Inventario: en el legacy es RH/Inventario.php (solo Admin y RH). Aún no se migra, se muestra la página de mantenimiento
        Route::view('/inventario', 'mantenimiento', ['seccion' => 'Inventario'])->name('inventario');

        // Subir el documento de una incapacidad por su ID. Port de procesar_incapacidad.php
        // Va en este grupo porque en el legacy solo Admin y RH ven el botón de subir (Nominas no)
        Route::post('/empleados/{n_empleado}/incapacidades/subir', [EmpleadoDocumentoController::class, 'subirIncapacidad'])->name('empleados.incapacidades.subir');

        Route::resource('razones-sociales', RazonSocialController::class);

        Route::resource('puestos', PuestoController::class)->except(['create', 'edit', 'show']);
        Route::get('/organigrama', [PuestoController::class, 'organigrama'])->name('organigrama.index');
        Route::resource('responsables', ResponsableController::class);
        Route::resource('ubicaciones', UbicacionController::class);
        Route::get('/api/puestos/subdepartamentos', [PuestoController::class, 'apiSubdepartamentos'])->name('api.puestos.subdepartamentos');
        Route::get('/api/puestos/empleados', [PuestoController::class, 'apiEmpleados'])->name('api.puestos.empleados');

        Route::get('/reportes', [ReporteController::class, 'index'])->name('reportes.index');
        Route::get('/reportes/ingresos-renuncias', [ReporteController::class, 'ingresosRenuncias'])->name('reportes.ingresosRenuncias');
        Route::match(['get', 'post'], '/reportes/exportar', [ReporteController::class, 'exportar'])->name('reportes.exportar');
        Route::post('/reportes/exportar/excel', [ReporteController::class, 'exportarExcel'])->name('reportes.exportarExcel');
        Route::resource('estimados', EstimadoController::class)->only(['index', 'store', 'update', 'destroy']);

        Route::get('/diario', [DiarioController::class, 'index'])->name('diario.index');
        Route::get('/diario/eventos-feed', [DiarioController::class, 'getEventosFeed'])->name('diario.eventos-feed');
        Route::get('/diario/dia-info', [DiarioController::class, 'getDiaInfo'])->name('diario.dia-info');
        Route::post('/diario/evaluacion/{id}/realizar', [DiarioController::class, 'realizarEvaluacion'])->name('diario.evaluacion.realizar');

        // Incidencias
        Route::get('/incidencias', [IncidenciaController::class, 'index'])->name('incidencias.index');
        Route::get('/incidencias/reportes', [IncidenciaController::class, 'reportesVista'])->name('incidencias.reportes');
        Route::get('/incidencias/biometrico', [IncidenciaController::class, 'consultarBiometrico'])->name('incidencias.biometrico');
        Route::post('/incidencias/guardar', [IncidenciaController::class, 'guardarIncidencia'])->name('incidencias.guardar');
        Route::post('/incidencias/auto-guardar-lote', [IncidenciaController::class, 'autoGuardarLote'])->name('incidencias.autoGuardarLote');
        Route::post('/incidencias/{id}/desactivar', [IncidenciaController::class, 'desactivarIncidencia'])->name('incidencias.desactivar');
        Route::get('/incidencias/reporte-sin-checar', [IncidenciaController::class, 'reporteSinChecar'])->name('incidencias.reporteSinChecar');
        Route::get('/incidencias/reporte-puntualidad', [IncidenciaController::class, 'reportePuntualidad'])->name('incidencias.reportePuntualidad');

        // Checadores CRUD & Lector
        Route::resource('checadores', ChecadorController::class)->except(['create', 'show', 'edit']);
        Route::get('/checadores-lector', [ChecadorController::class, 'lector'])->name('checadores.lector');
        Route::get('/checadores-lector/datos', [ChecadorController::class, 'leerEventosRaw'])->name('checadores.lector.datos');
        Route::post('/checadores/{id}/push/activar', [ChecadorController::class, 'activarPush'])->name('checadores.push.activar');
        Route::post('/checadores/{id}/push/desactivar', [ChecadorController::class, 'desactivarPush'])->name('checadores.push.desactivar');
        Route::post('/checadores-push/activar-todos', [ChecadorController::class, 'activarPushTodos'])->name('checadores.push.activarTodos');
        Route::post('/checadores-push/desactivar-todos', [ChecadorController::class, 'desactivarPushTodos'])->name('checadores.push.desactivarTodos');
        Route::get('/checadores-push/actividad', [ChecadorWebhookController::class, 'actividadReciente'])->name('checadores.push.actividad');

        // Reclutamiento
        Route::get('/reclutamiento', [ReclutamientoController::class, 'index'])->name('reclutamiento.index');
        Route::post('/reclutamiento', [ReclutamientoController::class, 'store'])->name('reclutamiento.store');
        Route::post('/reclutamiento/{id}', [ReclutamientoController::class, 'update'])->name('reclutamiento.update');
        Route::get('/reclutamiento/{id}/historial', [ReclutamientoController::class, 'historial'])->name('reclutamiento.historial');

        // Historial de Cambios
        Route::get('/historial', [HistorialController::class, 'index'])->name('historial.index');
    });

    Route::middleware('role:Admin')->group(function () {
        Route::resource('usuarios', UsuarioController::class)->only(['index', 'store', 'update']);
        Route::get('/api/usuarios/responsables', [UsuarioController::class, 'apiResponsables'])->name('api.usuarios.responsables');
    });
});


