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

use App\Models\LaboralEmpleado;

Route::get('/', function () {
    $totalEmpleados = Personal::count();
    $activos = LaboralEmpleado::where('Estatus', 'ACTIVO')->count();
    $bajas = LaboralEmpleado::where('Estatus', 'BAJA')->count();
    return view('menu', compact('totalEmpleados', 'activos', 'bajas'));
});

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
Route::post('/empleados/{n_empleado}/documentos', [EmpleadoDocumentoController::class, 'storeUpload'])->name('empleados.documentos.store');

Route::resource('razones-sociales', RazonSocialController::class);

Route::resource('puestos', PuestoController::class);
Route::get('/organigrama', [PuestoController::class, 'organigrama'])->name('organigrama.index');
Route::resource('responsables', ResponsableController::class);
Route::resource('ubicaciones', UbicacionController::class);

Route::get('/altas-imss', [AltaBajaImssController::class, 'index'])->name('altas-imss.index');
Route::post('/altas-imss/{n_empleado}/fecha-alta', [AltaBajaImssController::class, 'actualizarFechaAlta'])->name('altas-imss.fechaAlta');
Route::post('/altas-imss/{n_empleado}/fecha-baja', [AltaBajaImssController::class, 'actualizarFechaBaja'])->name('altas-imss.fechaBaja');
Route::post('/altas-imss/{n_empleado}/documento', [AltaBajaImssController::class, 'subirDocumento'])->name('altas-imss.documento');

Route::get('/reportes', [ReporteController::class, 'index'])->name('reportes.index');
Route::get('/reportes/ingresos-renuncias', [ReporteController::class, 'ingresosRenuncias'])->name('reportes.ingresosRenuncias');
Route::match(['get', 'post'], '/reportes/exportar', [ReporteController::class, 'exportar'])->name('reportes.exportar');
Route::post('/reportes/exportar/excel', [ReporteController::class, 'exportarExcel'])->name('reportes.exportarExcel');
Route::resource('estimados', EstimadoController::class)->only(['index', 'store', 'update', 'destroy']);

// Diario
Route::get('/diario', [DiarioController::class, 'index'])->name('diario.index');
Route::get('/diario/eventos-feed', [DiarioController::class, 'getEventosFeed'])->name('diario.eventos-feed');
Route::get('/diario/dia-info', [DiarioController::class, 'getDiaInfo'])->name('diario.dia-info');
Route::post('/diario/evaluacion/{id}/realizar', [DiarioController::class, 'realizarEvaluacion'])->name('diario.evaluacion.realizar');

// APIs de cascada select para Responsables
Route::get('/api/puestos/subdepartamentos', [PuestoController::class, 'apiSubdepartamentos'])->name('api.puestos.subdepartamentos');
Route::get('/api/puestos/empleados', [PuestoController::class, 'apiEmpleados'])->name('api.puestos.empleados');

Route::get('/test-db', function () {
    $empleados = Personal::has('laboral')->with('laboral')->take(5)->get();
    return view('test', compact('empleados'));
});

