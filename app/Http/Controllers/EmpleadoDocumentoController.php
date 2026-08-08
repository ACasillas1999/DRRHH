<?php

namespace App\Http\Controllers;

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

    public function storeUpload(Request $request, string $n_empleado): RedirectResponse
    {
        $data = $request->validate([
            'nombre_documento' => ['required', 'string', 'max:150'],
            'documento' => ['required', 'file', 'max:10240'],
        ]);

        $yaExiste = DocumentoGenerado::where('N_Empleado', $n_empleado)
            ->where('Nombre_Documento', $data['nombre_documento'])
            ->exists();

        if ($yaExiste) {
            return back()->withErrors(['documento' => 'Ya existe un documento con ese nombre para este empleado.']);
        }

        $archivo = $request->file('documento');
        $nombreArchivo = $n_empleado . '_' . \Illuminate\Support\Str::slug($data['nombre_documento']) . '_' . time() . '.' . $archivo->getClientOriginalExtension();
        $archivo->move(public_path('uploads/documentos_generados'), $nombreArchivo);

        DocumentoGenerado::create([
            'N_Empleado' => $n_empleado,
            'Nombre_Documento' => $data['nombre_documento'],
            'Ruta_Documento' => '/uploads/documentos_generados/' . $nombreArchivo,
            'Fecha_Subida' => now(),
        ]);

        return redirect()->route('empleados.show', $n_empleado)->with('status', 'Documento subido correctamente.');
    }

    public function subirPersonal(Request $request, string $n_empleado): RedirectResponse
    {
        $request->validate([
            'documento_id' => ['required', 'integer'],
            'documento' => ['required', 'file', 'max:10240'],
        ]);

        $doc = DocumentoEmpleado::where('N_Empleado', $n_empleado)->where('ID', $request->input('documento_id'))->firstOrFail();

        $archivo = $request->file('documento');
        $nombreArchivo = $n_empleado . '_pers_' . \Illuminate\Support\Str::slug($doc->Tipo_Documento) . '_' . time() . '.' . $archivo->getClientOriginalExtension();
        $archivo->move(public_path('uploads/documentos_empleado'), $nombreArchivo);

        $doc->update([
            'Ruta_Documento' => '/uploads/documentos_empleado/' . $nombreArchivo,
            'Fecha_Subida' => now(),
        ]);

        return redirect()->route('empleados.show', $n_empleado)->with('status', 'Documento personal ' . $doc->Tipo_Documento . ' subido correctamente.');
    }

    public function subirLaboral(Request $request, string $n_empleado): RedirectResponse
    {
        $request->validate([
            'documento_id' => ['required', 'integer'],
            'documento' => ['required', 'file', 'max:10240'],
        ]);

        $doc = DocumentoLaboral::where('N_Empleado', $n_empleado)->where('ID', $request->input('documento_id'))->firstOrFail();

        $archivo = $request->file('documento');
        $nombreArchivo = $n_empleado . '_lab_' . \Illuminate\Support\Str::slug($doc->Tipo_Documento) . '_' . time() . '.' . $archivo->getClientOriginalExtension();
        $archivo->move(public_path('uploads/documentos_laborales'), $nombreArchivo);

        $doc->update([
            'Ruta_Documento' => '/uploads/documentos_laborales/' . $nombreArchivo,
            'Fecha_Subida' => now(),
        ]);

        return redirect()->route('empleados.show', $n_empleado)->with('status', 'Documento laboral ' . $doc->Tipo_Documento . ' subido correctamente.');
    }
}
