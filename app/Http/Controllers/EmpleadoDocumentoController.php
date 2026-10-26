<?php

namespace App\Http\Controllers;

use App\Models\DocumentoBaja;
use App\Models\HistorialCambio;
use App\Models\Incapacidad;
use Illuminate\Support\Facades\DB;
use App\Models\AsignacionProducto;
use App\Models\Contrato;
use App\Models\DocumentoEmpleado;
use App\Models\DocumentoLaboral;
use App\Models\DocumentoGenerado;
use App\Models\FechaLaboral;
use App\Models\LaboralEmpleado;
use App\Models\Personal;
use App\Models\RutaDocumento;
use App\Services\WordDocumentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Luecano\NumeroALetras\NumeroALetras;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class EmpleadoDocumentoController extends Controller
{
    public function __construct(private WordDocumentService $documentos)
    {
    }

    private function datosBase(string $n_empleado): array
    {
        $empleado = Personal::where('N_Empleado', $n_empleado)->firstOrFail();
        $laboral = LaboralEmpleado::with('puesto', 'razonSocial')->where('N_Empleado', $n_empleado)->firstOrFail();
        $rutaDocumento = RutaDocumento::where('N_Empleado', $n_empleado)->first();
        $fechaLaboral = FechaLaboral::where('N_Empleado', $n_empleado)->orderBy('Fecha_Ingreso')->first();

        return compact('empleado', 'laboral', 'rutaDocumento', 'fechaLaboral');
    }

    private function tagsComunes(array $datos): array
    {
        ['empleado' => $empleado, 'laboral' => $laboral, 'fechaLaboral' => $fechaLaboral] = $datos;
        $razonSocial = $laboral->razonSocial;
        $puesto = $laboral->puesto;

        return [
            'N_Empleado' => $empleado->N_Empleado,
            'NEmpleado' => $empleado->N_Empleado,
            'Nombre_Completo' => $empleado->Nombre_Completo,
            'Correo_Electronico' => $empleado->Correo_Electronico,
            'RFC' => $empleado->RFC,
            'CURP' => $empleado->CURP,
            'IMSS' => $empleado->IMSS,
            'Sexo' => $empleado->Sexo,
            'Domicilio' => $empleado->Domicilio,
            'Ciudad' => $empleado->Ciudad,
            'Estado' => $empleado->Estado,
            'Fecha_Nacimiento' => $empleado->Fecha_Nacimiento,
            'Estado_Civil' => $empleado->Estado_Civil,
            'Parentesco_B' => $empleado->Parentesco_B,
            'Nombre_Completo_B' => $empleado->Nombre_Completo_B,
            'Domicilio_B' => $empleado->Domicilio_B,
            'Telefono_B' => $empleado->Telefono_B,
            'F_NAC_B' => $empleado->F_NAC_B,

            'Nomeclatura' => $laboral->Nomeclatura,
            'Puesto' => $puesto->Nombre_Puesto ?? '',
            'Nombre_Puesto' => $puesto->Nombre_Puesto ?? '',
            'Departamento' => $puesto->Departamento ?? '',
            'Descripcion' => $puesto->Descripcion ?? '',
            'Sueldo' => $laboral->Sueldo,
            'Sueldo_Neto' => $laboral->Sueldo_Neto,
            'Salario_Diario' => $laboral->Salario_Diario,
            'Hora_Comida' => $laboral->Hora_Comida,
            'HoraComida' => $laboral->Hora_Comida,
            'Efectivo' => $laboral->Efectivo,
            'Comision' => $laboral->Comision,
            'KPI' => $laboral->KPI,
            'Horario' => $laboral->Horario,
            'Estatus' => $laboral->Estatus,

            'Representante_Legal' => $razonSocial->Representante_Legal ?? '',
            'Razon_Social_IMSS' => $razonSocial->Razon_Social_IMSS ?? '',
            'Registro_Patronal' => $razonSocial->Registro_Patronal ?? '',
            'RFC_Empresa' => $razonSocial->RFC_Empresa ?? '',
            'Declaraciones_F' => $razonSocial->Declaraciones_F ?? '',

            'Fecha_Ingreso' => $fechaLaboral->Fecha_Ingreso ?? '',
            'Fecha_Egreso' => $fechaLaboral->Fecha_Egreso ?? '',

            'FechaHoy' => $this->documentos->fechaHoyEnEspanol(),
        ];
    }

    private function descargar(string $rutaArchivo): BinaryFileResponse
    {
        return response()->download($rutaArchivo)->deleteFileAfterSend(true);
    }

    private function salarioConLetra(?float $salarioDiario): string
    {
        return (new NumeroALetras())->toWords((float) ($salarioDiario ?? 0));
    }

    public function fichaNomina(Request $request, string $n_empleado)
    {
        $request->validate(['Autoriza' => ['nullable', 'string', 'max:150']]);
        $datos = $this->datosBase($n_empleado);
        $tags = $this->tagsComunes($datos) + ['Autoriza' => $request->input('Autoriza', '')];

        $imagenes = [];
        $rutaFoto = $datos['rutaDocumento']->Ruta_Foto_Empleado ?? null;
        if ($rutaFoto) {
            $rutaLocal = public_path(ltrim($rutaFoto, '/'));
            if (file_exists($rutaLocal)) {
                $imagenes['Ruta_foto'] = $rutaLocal;
            }
        }

        $archivo = $this->documentos->generar('ficha_nominas.docx', $tags, "Ficha_Nominas_{$n_empleado}.docx", $imagenes);

        return $this->descargar($archivo);
    }

    public function folioBaja(Request $request, string $n_empleado)
    {
        $data = $request->validate([
            'dias_laborados' => ['nullable', 'string', 'max:50'],
            'Concepto_Adicional' => ['nullable', 'string', 'max:255'],
            'Incidencias_Descontar' => ['nullable', 'string', 'max:255'],
            'Incidencias_Hrs' => ['nullable', 'string', 'max:50'],
            'Importe' => ['nullable', 'string', 'max:50'],
            'observaciones' => ['nullable', 'string', 'max:500'],
            'motivo_baja' => ['nullable', 'string', 'max:100'],
        ]);

        $datos = $this->datosBase($n_empleado);
        $motivo = $data['motivo_baja'] ?? '';

        $motivos = [
            'motivo_baja_Renuncia' => $motivo === 'Renuncia',
            'motivo_baja_pension' => $motivo === 'Solicitud de pension',
            'motivo_baja_No_Renovacion' => $motivo === 'No Renovacion',
            'motivo_baja_Defuncion' => $motivo === 'Defuncion',
            'motivo_baja_Ausentismo' => $motivo === 'Ausentismo',
            'motivo_baja_Desvinculacion' => $motivo === 'Desvinculacion',
            'motivo_baja_Incapacidad' => $motivo === 'Incapacidad Total y/o Invalidez',
        ];

        $tags = $this->tagsComunes($datos) + [
            'Dias_laborados' => $data['dias_laborados'] ?? '',
            'Concepto_Adicional' => $data['Concepto_Adicional'] ?? '',
            'Descontar_Incidencias' => $data['Incidencias_Descontar'] ?? '',
            'Incidencia_Hrs' => $data['Incidencias_Hrs'] ?? '',
            'Importe' => $data['Importe'] ?? '',
            'Observaciones' => $data['observaciones'] ?? '',
            'Quincenas' => '',
        ];

        foreach ($motivos as $tag => $activo) {
            $tags[$tag] = $activo ? 'X' : '-';
        }

        $archivo = $this->documentos->generar('folio_baja.docx', $tags, "Folio_Baja_{$n_empleado}.docx");

        return $this->descargar($archivo);
    }

    public function cartaOferta(Request $request, string $n_empleado)
    {
        $datos = $this->datosBase($n_empleado);
        $tags = $this->tagsComunes($datos);

        // Permitir sobrescribir los valores financieros desde el request
        $inputs = $request->only(['Sueldo', 'Sueldo_Neto', 'Efectivo', 'Hora_Comida', 'Comision', 'KPI']);
        foreach ($inputs as $key => $val) {
            if ($val !== null && $val !== '') {
                $tags[$key] = $val;
            }
        }

        $archivo = $this->documentos->generar('carta_oferta.docx', $tags, "Carta_Oferta_{$n_empleado}.docx");

        return $this->descargar($archivo);
    }

    public function cartaConfidencialidad(string $n_empleado)
    {
        $datos = $this->datosBase($n_empleado);
        $archivo = $this->documentos->generar('carta_confidencialidad.docx', $this->tagsComunes($datos), "Carta_Confidencialidad_{$n_empleado}.docx");

        return $this->descargar($archivo);
    }

    public function politicaPuntualidad(string $n_empleado)
    {
        $datos = $this->datosBase($n_empleado);
        $archivo = $this->documentos->generar('politica_puntualidad.docx', $this->tagsComunes($datos), "Politica_Puntualidad_{$n_empleado}.docx");

        return $this->descargar($archivo);
    }

    public function contratoDeterminado(string $n_empleado)
    {
        $datos = $this->datosBase($n_empleado);
        $contrato = Contrato::where('N_Empleado', $n_empleado)->where('Tipo_de_Contrato', 'Determinado')->orderByDesc('F_Inicio')->first();

        $tags = $this->tagsComunes($datos) + [
            'Salario_Con_Letra' => $this->salarioConLetra($datos['laboral']->Salario_Diario),
            'Ruta_Renuncia' => $datos['rutaDocumento']->Ruta_Renuncia ?? '',
            'Ruta_Foto_Empleado' => $datos['rutaDocumento']->Ruta_Foto_Empleado ?? '',
            'F_inicio' => $contrato->F_Inicio ?? '',
            'F_Vence' => $contrato->F_Vence ?? '',
        ];

        $archivo = $this->documentos->generar('contrato_determinado.docx', $tags, "Contrato_Determinado_{$n_empleado}.docx");

        return $this->descargar($archivo);
    }

    public function contratoIndeterminado(string $n_empleado)
    {
        $datos = $this->datosBase($n_empleado);
        $contrato = Contrato::where('N_Empleado', $n_empleado)->where('Tipo_de_Contrato', 'Indeterminado')->orderByDesc('F_Inicio')->first();

        $tags = $this->tagsComunes($datos) + [
            'Salario_Con_Letra' => $this->salarioConLetra($datos['laboral']->Salario_Diario),
            'Ruta_Renuncia' => $datos['rutaDocumento']->Ruta_Renuncia ?? '',
            'Ruta_Foto_Empleado' => $datos['rutaDocumento']->Ruta_Foto_Empleado ?? '',
            'F_inicio' => $contrato->F_Inicio ?? '',
            'F_Vence' => $contrato->F_Vence ?? '',
        ];

        $archivo = $this->documentos->generar('contrato_indeterminado.docx', $tags, "Contrato_Indeterminado_{$n_empleado}.docx");

        return $this->descargar($archivo);
    }

    public function convenio(string $n_empleado)
    {
        $datos = $this->datosBase($n_empleado);

        $tags = $this->tagsComunes($datos) + [
            'Salario_Con_Letra' => $this->salarioConLetra($datos['laboral']->Salario_Diario),
        ];

        $archivo = $this->documentos->generar('convenio.docx', $tags, "Convenio_{$n_empleado}.docx");

        return $this->descargar($archivo);
    }

    /**
     * Port de la pantalla de Generador_Docs del legacy: muestra los datos que usa el contrato
     * (las mismas 4 consultas, con print_r) y el botón "Descargar Contrato".
     */
    public function generarContrato(string $n_empleado, string $tipo)
    {
        // Por tipo: título (<h1> del legacy), contratos que consulta en "Rutas de documentos" y ruta que descarga el .docx
        $config = [
            'determinado' => ['Determinado', ['Determinado', 'Indeterminado'], 'empleados.contratos.determinado'],
            'indeterminado' => ['Indeterminado', ['Indeterminado'], 'empleados.contratos.indeterminado'],
            'convenio' => ['Convenio', ['Indeterminado'], 'empleados.contratos.convenio'],
        ];
        [$titulo, $tiposContrato, $rutaDescarga] = $config[$tipo];

        // Mismas consultas que el legacy (SELECT * ...), como arreglos para mostrarlas igual que print_r
        $personales = (array) DB::table('personales')->where('N_Empleado', $n_empleado)->first();
        $laborales = (array) DB::table('laborales_empleado')
            ->join('puestos', 'laborales_empleado.ID_Puesto', '=', 'puestos.ID')
            ->join('razones_sociales', 'laborales_empleado.Nomeclatura', '=', 'razones_sociales.Nomenclatura')
            ->where('laborales_empleado.N_Empleado', $n_empleado)
            ->first();
        $fechas = DB::table('fechas_laborales')->where('N_Empleado', $n_empleado)->get()->map(fn ($fila) => (array) $fila)->all();
        $rutas = (array) DB::table('rutas_documentos')
            ->join('contratos', 'rutas_documentos.N_Empleado', '=', 'contratos.N_Empleado')
            ->where('contratos.N_Empleado', $n_empleado)
            ->whereIn('contratos.Tipo_de_Contrato', $tiposContrato)
            ->first();

        // Igual que el legacy: solo hay botón de descarga si las 4 consultas regresaron datos
        $hayDatos = $personales && $laborales && $fechas && $rutas;

        return view('empleados.generar-contrato', compact('n_empleado', 'titulo', 'personales', 'laborales', 'fechas', 'rutas', 'hayDatos', 'rutaDescarga'));
    }


    /**
     * Port de procesar_contrato.php: guarda el contrato firmado en la misma carpeta
     * y con el mismo formato de ruta que el legacy, para que ambas apps lo vean.
     */
    public function subirContrato(Request $request, string $n_empleado): RedirectResponse
    {
        // Mismas extensiones permitidas que el legacy ($allowedExtensions) y su mismo mensaje de error
        $data = $request->validate([
            'contrato_id' => ['required', 'integer'],
            'archivo' => ['required', 'file', 'extensions:pdf,doc,docx,txt'],
        ], [
            'archivo.extensions' => 'Solo se permiten archivos PDF, DOC, DOCX y TXT.',
        ]);

        // Igual que el legacy: el contrato debe pertenecer al empleado (WHERE ID = ? AND N_Empleado = ?)
        $contrato = Contrato::where('ID', $data['contrato_id'])->where('N_Empleado', $n_empleado)->first();

        // Mismo mensaje que muestra el legacy cuando el UPDATE no afecta filas
        if (!$contrato) {
            return back()->withErrors(['archivo' => 'No se pudo actualizar el contrato.']);
        }

        // Carpeta física del legacy: C:/xampp/htdocs/RH/RH/Contratos_Firmados (se crea si no existe, como en el legacy)
        $carpeta = rtrim(config('legacy.path'), '/') . '/RH/Contratos_Firmados';
        if (!is_dir($carpeta)) {
            mkdir($carpeta, 0755, true);
        }

        // Mismo formato de nombre que el legacy: uniqid("empleado_{N}_") + extensión en minúsculas
        $archivo = $request->file('archivo');
        $nombreArchivo = uniqid('empleado_' . $n_empleado . '_') . '.' . strtolower($archivo->getClientOriginalExtension());
        $archivo->move($carpeta, $nombreArchivo);

        // Se guarda la ruta relativa tal cual la guarda el legacy ("RH/Contratos_Firmados/...")
        $contrato->update(['Ruta_Doc' => 'RH/Contratos_Firmados/' . $nombreArchivo]);

        // Mismo mensaje de éxito que el alert() del legacy
        return redirect()->route('empleados.show', $n_empleado)->with('status', 'El archivo se cargó correctamente.');
    }

    /**
     * Port de procesar_asignaciones.php: guarda la entrega firmada de una asignación
     * en la misma carpeta y con el mismo formato de ruta que el legacy.
     */
    public function subirAsignacion(Request $request, string $n_empleado): RedirectResponse
    {
        // Mismas extensiones permitidas que el legacy ($allowedExtensions)
        $data = $request->validate([
            'asignacion_id' => ['required', 'integer'],
            'archivo' => ['required', 'file', 'extensions:pdf,doc,docx,txt'],
        ], [
            'archivo.extensions' => 'Solo se permiten archivos PDF, DOC, DOCX y TXT.',
        ]);

        // Igual que el legacy: la asignación debe pertenecer al empleado (WHERE ID = ? AND N_Empleado = ?)
        $asignacion = AsignacionProducto::where('ID', $data['asignacion_id'])->where('N_Empleado', $n_empleado)->first();

        // El legacy muestra este mismo texto (copiado de contratos) cuando el UPDATE no afecta filas
        if (!$asignacion) {
            return back()->withErrors(['archivo' => 'No se pudo actualizar el contrato.']);
        }

        // Carpeta física del legacy: C:/xampp/htdocs/RH/RH/Asignaciones (se crea si no existe, como en el legacy)
        $carpeta = rtrim(config('legacy.path'), '/') . '/RH/Asignaciones';
        if (!is_dir($carpeta)) {
            mkdir($carpeta, 0755, true);
        }

        // Mismo formato de nombre que el legacy: uniqid("empleado_{N}_Asignacion") + extensión en minúsculas
        $archivo = $request->file('archivo');
        $nombreArchivo = uniqid('empleado_' . $n_empleado . '_Asignacion') . '.' . strtolower($archivo->getClientOriginalExtension());
        $archivo->move($carpeta, $nombreArchivo);

        // Se guarda la ruta relativa tal cual la guarda el legacy ("RH/Asignaciones/...")
        $asignacion->update(['Ruta_Documento' => 'RH/Asignaciones/' . $nombreArchivo]);

        // Mismo mensaje de éxito que el alert() del legacy
        return redirect()->route('empleados.show', $n_empleado)->with('status', 'El archivo se cargó correctamente.');
    }

    
    /**
     * Port de procesar_incapacidad.php: guarda el documento de una incapacidad
     * en la misma carpeta y con el mismo formato de ruta que el legacy.
     */
    public function subirIncapacidad(Request $request, string $n_empleado): RedirectResponse
    {
        // Mismas extensiones permitidas que el legacy ($allowedExtensions)
        $data = $request->validate([
            'incapacidad_id' => ['required', 'integer'],
            'archivo' => ['required', 'file', 'extensions:pdf,doc,docx,txt'],
        ], [
            'archivo.extensions' => 'Solo se permiten archivos PDF, DOC, DOCX y TXT.',
        ]);

        // Igual que el legacy: la incapacidad debe pertenecer al empleado (WHERE ID = ? AND N_Empleado = ?)
        $incapacidad = Incapacidad::where('ID', $data['incapacidad_id'])->where('N_Empleado', $n_empleado)->first();

        // El legacy muestra este mismo texto (copiado de contratos) cuando el UPDATE no afecta filas
        if (!$incapacidad) {
            return back()->withErrors(['archivo' => 'No se pudo actualizar el contrato.']);
        }

        // Carpeta física del legacy: C:/xampp/htdocs/RH/RH/Incapacidades (se crea si no existe, como en el legacy)
        $carpeta = rtrim(config('legacy.path'), '/') . '/RH/Incapacidades';
        if (!is_dir($carpeta)) {
            mkdir($carpeta, 0755, true);
        }

        // Mismo formato de nombre que el legacy: uniqid("empleado_{N}_incapacidad") + extensión en minúsculas
        $archivo = $request->file('archivo');
        $nombreArchivo = uniqid('empleado_' . $n_empleado . '_incapacidad') . '.' . strtolower($archivo->getClientOriginalExtension());
        $archivo->move($carpeta, $nombreArchivo);

        // Se guarda la ruta relativa tal cual la guarda el legacy ("RH/Incapacidades/...")
        $incapacidad->update(['Ruta_Documento' => 'RH/Incapacidades/' . $nombreArchivo]);

        // Mismo mensaje de éxito que el alert() del legacy
        return redirect()->route('empleados.show', $n_empleado)->with('status', 'El archivo se cargó correctamente.');
    }

    /**
     * Port de upload_documento_ficha_nomina.php / carta_oferta / carta_Confidencialidad / Pol_Punt_Asist / baja
     * (los 5 son idénticos): guarda en la misma carpeta, con el mismo nombre y formato de ruta que el legacy.
     */
    public function storeUpload(Request $request, string $n_empleado): RedirectResponse
    {
        // El legacy no limita extensión ni tamaño (solo el límite de PHP)
        $data = $request->validate([
            'nombre_documento' => ['required', 'string', 'max:150'],
            // Seguridad (diferencia con el legacy): aquí el legacy acepta cualquier tipo y se mantiene igual,
            // pero se bloquean los archivos que Apache ejecutaría (.php y similares) si alguien abre su URL.
            'documento' => ['required', 'file', function ($attribute, $value, $fail) {
                if (preg_match('/\.(php\d*|phtml|phar|pht|phps|cgi|pl|asp|aspx|jsp|htaccess)$/i', $value->getClientOriginalName())) {
                    $fail('Tipo de archivo no permitido.');
                }
            }],
        ]);

        // Igual que el legacy: un solo documento por nombre y empleado (SELECT COUNT(*) ... WHERE N_Empleado = ? AND Nombre_Documento = ?)
        $yaExiste = DocumentoGenerado::where('N_Empleado', $n_empleado)
            ->where('Nombre_Documento', $data['nombre_documento'])
            ->exists();

        // Mismo mensaje que el alert() del legacy
        if ($yaExiste) {
            return back()->withErrors(['documento' => '¡Error! Ya existe un documento con el mismo nombre para este empleado.']);
        }

        // Carpeta física del legacy: C:/xampp/htdocs/RH/uploads/documentos (se crea si no existe, como en el legacy)
        $carpeta = rtrim(config('legacy.path'), '/') . '/uploads/documentos';
        if (!is_dir($carpeta)) {
            mkdir($carpeta, 0777, true);
        }

        // Mismo nombre que el legacy: el nombre original; si ya existe, nombre_1.ext, nombre_2.ext...
        $archivo = $request->file('documento');
        $nombreOriginal = basename($archivo->getClientOriginalName());
        $nombreArchivo = $nombreOriginal;
        $i = 1;
        while (file_exists($carpeta . '/' . $nombreArchivo)) {
            $nombreArchivo = pathinfo($nombreOriginal, PATHINFO_FILENAME) . "_{$i}." . pathinfo($nombreOriginal, PATHINFO_EXTENSION);
            $i++;
        }
        $archivo->move($carpeta, $nombreArchivo);

        DocumentoGenerado::create([
            'N_Empleado' => $n_empleado,
            'Nombre_Documento' => $data['nombre_documento'],
            // Ruta relativa tal cual la guarda el legacy ("uploads/documentos/...")
            'Ruta_Documento' => 'uploads/documentos/' . $nombreArchivo,
            // Hora de México como el legacy (date_default_timezone_set('America/Mexico_City')); la app sigue en UTC
            'Fecha_Subida' => now('America/Mexico_City')->format('Y-m-d H:i:s'),
        ]);

        // Mismo mensaje de éxito que el alert() del legacy
        return redirect()->route('empleados.show', $n_empleado)->with('status', '¡Documento Subido con Éxito!');
    }

        /**
     * Port de Mas_Documentos/Subir_Documento.php: documento personal del expediente.
     */
    public function subirPersonal(Request $request, string $n_empleado): RedirectResponse
    {
        $doc = DocumentoEmpleado::where('N_Empleado', $n_empleado)->where('ID', $request->input('documento_id'))->firstOrFail();

        return $this->subirExpediente($request, $n_empleado, $doc, 'Mas_Documentos', 'documentos_empleado', 'personal');
    }

    /**
     * Port de Doc_Laborales/Subir_Documento.php: documento laboral del expediente.
     */
    public function subirLaboral(Request $request, string $n_empleado): RedirectResponse
    {
        $doc = DocumentoLaboral::where('N_Empleado', $n_empleado)->where('ID', $request->input('documento_id'))->firstOrFail();

        return $this->subirExpediente($request, $n_empleado, $doc, 'Doc_Laborales', 'documentos_laborales', 'laboral');
    }

    /**
     * Lógica común de los Subir_Documento.php del legacy (Mas_Documentos y Doc_Laborales son iguales):
     * nombre original, rechaza si ya existe un archivo con ese nombre, y guarda la ruta completa
     * de Windows como hace el legacy con __DIR__ ("C:\xampp\htdocs\RH\Mas_Documentos/uploads/...").
     */
    private function subirExpediente(Request $request, string $n_empleado, $doc, string $subApp, string $carpetaUploads, string $tipo): RedirectResponse
    {
        $request->validate([
            'documento_id' => ['required', 'integer'],
            // Seguridad (diferencia con el legacy): el legacy solo limita la extensión en el navegador (accept=".pdf,.doc,.docx,.jpg,.png").
            // Como el archivo se guarda con su nombre original dentro de htdocs, un .php se ejecutaría; aquí se valida en el servidor.
            'documento' => ['required', 'file', 'extensions:pdf,doc,docx,jpg,png'],
        ]);

        // Carpeta física del legacy: C:/xampp/htdocs/RH/{subApp}/uploads/{carpetaUploads}
        $carpeta = rtrim(config('legacy.path'), '/') . "/{$subApp}/uploads/{$carpetaUploads}";
        if (!is_dir($carpeta)) {
            mkdir($carpeta, 0777, true);
        }

        // Igual que el legacy: nombre original y, si ya existe un archivo con ese nombre, se rechaza (mismo mensaje)
        $nombreArchivo = basename($request->file('documento')->getClientOriginalName());
        if (file_exists($carpeta . '/' . $nombreArchivo)) {
            return back()->withErrors(['documento' => 'Error: Ya existe un documento con el mismo nombre.']);
        }
        $request->file('documento')->move($carpeta, $nombreArchivo);

        $doc->update([
            // Mismo formato que __DIR__ . '/uploads/...' del legacy (ruta de Windows con barras invertidas hasta la sub-app)
            'Ruta_Documento' => str_replace('/', '\\', rtrim(config('legacy.path'), '/')) . "\\{$subApp}/uploads/{$carpetaUploads}/{$nombreArchivo}",
            // Hora de México como el legacy (su date() usa date.timezone=America/Mexico_City); la app sigue en UTC
            'Fecha_Subida' => now('America/Mexico_City')->format('Y-m-d H:i:s'),
        ]);

        // Mismo mensaje de éxito que el legacy
        return redirect()->route('empleados.show', $n_empleado)->with('status', 'Documento actualizado correctamente.');
    }

    /**
     * Port de Doc_BAJA/Subir_Documento.php: sube un documento de baja (RENUNCIA, FINIQUITO, etc.).
     * A diferencia de Personales/Laborales, el legacy NO rechaza duplicados: si ya existe un archivo
     * con ese nombre lo reemplaza. Tampoco escribe en el historial.
     */
    public function subirBaja(Request $request, string $n_empleado): RedirectResponse
    {
        $request->validate([
            'documento_id' => ['required', 'integer'],
            // Misma protección que subirExpediente: solo las extensiones que permite el legacy
            'documento' => ['required', 'file', 'extensions:pdf,doc,docx,jpg,png'],
        ]);

        $doc = DocumentoBaja::where('N_Empleado', $n_empleado)->where('ID', $request->input('documento_id'))->firstOrFail();

        // Carpeta física del legacy ("Doc_Baja" así, con minúsculas, es como la guarda el legacy en BD)
        $carpeta = rtrim(config('legacy.path'), '/') . '/Doc_Baja/uploads/documento_baja';
        if (!is_dir($carpeta)) {
            mkdir($carpeta, 0777, true);
        }

        // Igual que el legacy: nombre original; move() reemplaza el archivo si ya existe
        $nombreArchivo = basename($request->file('documento')->getClientOriginalName());
        $request->file('documento')->move($carpeta, $nombreArchivo);

        // Igual que el legacy: UPDATE por N_Empleado + Nombre_Documento (si el empleado tuvo varias bajas, se actualizan todos sus renglones con ese nombre)
        DocumentoBaja::where('N_Empleado', $n_empleado)
            ->where('Nombre_Documento', $doc->Nombre_Documento)
            ->update([
                // Ruta completa de Windows como el __DIR__ del legacy: C:\xampp\htdocs\RH\Doc_Baja/uploads/documento_baja/{nombre}
                'Ruta_Documento' => str_replace('/', '\\', rtrim(config('legacy.path'), '/')) . "\\Doc_Baja/uploads/documento_baja/{$nombreArchivo}",
                // Hora de México como el legacy; la app sigue en UTC
                'Fecha_Subida' => now('America/Mexico_City'),
            ]);

        return redirect()->route('empleados.show', $n_empleado)->with('status', 'Documento subido exitosamente');
    }

    
    /**
     * Port de Mas_Documentos/borrar_documento.php: quita el archivo de un documento personal.
     */
    public function borrarPersonal(string $n_empleado, string $id): RedirectResponse
    {
        $doc = DocumentoEmpleado::where('N_Empleado', $n_empleado)->where('ID', $id)->firstOrFail();

        return $this->borrarExpediente($n_empleado, $doc, 'personal');
    }

    /**
     * Port de Doc_Laborales/borrar_documento.php: quita el archivo de un documento laboral.
     */
    public function borrarLaboral(string $n_empleado, string $id): RedirectResponse
    {
        $doc = DocumentoLaboral::where('N_Empleado', $n_empleado)->where('ID', $id)->firstOrFail();

        return $this->borrarExpediente($n_empleado, $doc, 'laboral');
    }

    /**
     * Lógica común de los borrar_documento.php del legacy: Ruta_Documento = NULL, borra el archivo
     * y registra en el historial, todo en una transacción (si algo falla se deshace todo).
     * A diferencia del legacy, la ruta del archivo se toma de la BD y no del navegador.
     */
    private function borrarExpediente(string $n_empleado, $doc, string $tipo): RedirectResponse
    {
        $ruta = $doc->Ruta_Documento ? str_replace('\\', '/', $doc->Ruta_Documento) : null;
        // Rutas viejas de DRRHH ("/uploads/...") viven en public/; las del legacy ya son rutas completas (C:/xampp/...)
        $rutaFisica = ($ruta && str_starts_with($ruta, '/uploads/')) ? public_path(ltrim($ruta, '/')) : $ruta;

        DB::transaction(function () use ($doc, $n_empleado, $tipo, $rutaFisica) {
            // El legacy hace "= NULL", pero la columna es NOT NULL: MySQL del servidor (no estricto) lo guarda como ''.
            // Laravel corre en modo estricto y rechazaría el NULL, así que guardamos '' directamente (mismo resultado en BD).
            $doc->update(['Ruta_Documento' => '']);


            // Igual que el legacy: si el archivo existe y no se puede borrar, se deshace todo
            if ($rutaFisica && file_exists($rutaFisica) && !unlink($rutaFisica)) {
                throw new \RuntimeException('Error al eliminar el archivo físico.');
            }

            // Mismo texto que el legacy (con su doble espacio después del ID)
            HistorialCambio::create([
                'Usuario_ID' => auth()->id(),
                'N_Empleado' => $n_empleado,
                'Cambio' => "Se eliminó un documento {$tipo} - {$doc->ID}  - del Empleado: {$n_empleado}",
                // Hora de México como el legacy; la app sigue en UTC
                'Fecha_Hora' => now('America/Mexico_City'),
            ]);
        });

        return redirect()->route('empleados.show', $n_empleado)->with('status', 'Documento borrado correctamente.');
    }

}
