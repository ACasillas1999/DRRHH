<?php

namespace App\Http\Controllers;

use App\Models\Contrato;
use App\Models\DocumentoEmpleado;
use App\Models\DocumentoLaboral;
use App\Models\FechaLaboral;
use App\Models\HistorialCambio;
use App\Models\LaboralEmpleado;
use App\Models\Personal;
use App\Models\Puesto;
use App\Models\RazonSocial;
use App\Models\RutaDocumento;
use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmpleadoCrearController extends Controller
{
    private const DOCUMENTOS_EMPLEADO = [
        'ACTA DE NACIMIENTO', 'ACTA DE MATRIMONIO', 'CURP', 'CONSTANCIA DE SITUACIÓN FISCAL', 'INE', 'IMSS',
        'COMPROBANTE DE ESTUDIOS', 'COMPROBANTE DE DOMICILIO', 'CERTIFICADO MEDICO', 'CARTA DE NO ANTECEDENTES PENALES',
        'CARTA DE RECOMENDACIÓN LABORAL', 'CARTA DE RECOMENDACIÓN PERSONAL', 'AVISO DE RETENCIÓN', 'PSICOMETRIA',
        'SOCIOECONOMICO', 'CV(CURRICULUM)', 'LICENCIA CHOFER', 'CUENTA BANCARIA', 'EXPEDIENTE COMPLETO',
    ];

    private const DOCUMENTOS_LABORALES = [
        'POLÍTICA_DE_USO_DE_CELULAR_EN_EL_ALMACEN', 'Aviso_de_Privacidad', 'CARTA_RESPONSIVA_DE_NO_ADEUDO_FONACOT',
        'CARTA_RESPONSIVA_DE_NO_ADEUDO_INFONAVIT', 'CARTA_RESPONSIVA_DE_SI_ADEUDO_FONACOT', 'CARTA_RESPONSIVA_DE_SI_ADEUDO_INFONAVIT',
        'CÓDIGO_DE_CONDUCTA', 'CÓDIGO_DE_VESTIMENTA', 'POLÍTICA_DE_USO_DE_CELULAR_EN_AREAS_ADMINISTRATIVAS',
        'POLÍTICA_DE_USO_DE_CELULAR_EN_EL_ÁREA_CAJAS', 'POLÍTICA_DE_USO_DE_CELULAR_EN_EL_ÁREA_DE_TALLER',
        'Reglamento_de_Puntualidad_CORPORATIVO_AIESA', 'DATOS_DEL_TRABAJADOR', 'RESULTADOS_DOPING',
        'CARTA_CONFIDENCIALIDAD_TEMP', 'RESPONSIVA_EQUIPOS',
    ];

    public function create()
    {
        $puestos = Puesto::orderBy('Nombre_Puesto')->get(['ID', 'Nombre_Puesto', 'Departamento']);
        $reclutadores = Usuario::whereIn('Rol', ['RH', 'Admin'])->orderBy('Nombre')->get(['ID', 'Nombre']);
        $razonesSociales = RazonSocial::orderBy('Nomenclatura')->get(['Nomenclatura']);

        return view('empleados.crear', compact('puestos', 'reclutadores', 'razonesSociales'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'N_Empleado' => ['required', 'integer', 'unique:personales,N_Empleado'],
            'Nombre_Completo' => ['required', 'string', 'max:255'],
            'IMSS' => ['nullable', 'string', 'max:50'],
            'CURP' => ['nullable', 'string', 'max:50'],
            'RFC' => ['nullable', 'string', 'max:50'],
            'Sexo' => ['nullable', 'string', 'max:20'],
            'Fecha_Nacimiento' => ['nullable', 'date'],
            'Edad' => ['nullable', 'integer'],
            'Ciudad' => ['nullable', 'string', 'max:100'],
            'Estado' => ['nullable', 'string', 'max:100'],
            'Domicilio' => ['nullable', 'string', 'max:255'],
            'Estado_Civil' => ['nullable', 'string', 'max:50'],
            'Telefono' => ['nullable', 'string', 'max:30'],
            'Celular' => ['nullable', 'string', 'max:30'],
            'Correo_Electronico' => ['nullable', 'email', 'max:150'],
            'Contacto_Emergencia' => ['nullable', 'string', 'max:150'],
            'Tel_Emergencia' => ['nullable', 'string', 'max:30'],
            'Parentesco_B' => ['nullable', 'string', 'max:100'],
            'Nombre_Completo_B' => ['nullable', 'string', 'max:255'],
            'Domicilio_B' => ['nullable', 'string', 'max:255'],
            'Telefono_B' => ['nullable', 'string', 'max:30'],
            'F_NAC_B' => ['nullable', 'date'],

            'ID_Puesto' => ['required', 'exists:puestos,ID'],
            'Nomeclatura' => ['nullable', 'string', 'max:100'],
            'Fisica' => ['nullable', 'string', 'max:100'],
            'Hora_Comida' => ['nullable', 'string', 'max:50'],
            'Efectivo' => ['nullable', 'string', 'max:50'],
            'Salario_Diario' => ['nullable', 'numeric'],
            'Sueldo' => ['nullable', 'numeric'],
            'Salario_Diario_Neto' => ['nullable', 'numeric'],
            'Sueldo_Neto' => ['nullable', 'numeric'],
            'Horario' => ['nullable', 'string', 'max:100'],
            'KPI' => ['nullable', 'string', 'max:50'],
            'Comision' => ['nullable', 'string', 'max:50'],
            'Reclutador' => ['nullable', 'string', 'max:150'],
            'Asesor_Ingreso' => ['nullable', 'string', 'max:150'],
            'Entrevista' => ['nullable', 'string', 'max:150'],

            'fecha_ingreso' => ['required', 'date'],
            'Tipo_de_Contrato' => ['required', 'in:Determinado,Indeterminado'],
        ]);

        DB::transaction(function () use ($data) {
            $nEmpleado = $data['N_Empleado'];

            Personal::create([
                'N_Empleado' => $nEmpleado,
                'Nombre_Completo' => $data['Nombre_Completo'],
                'IMSS' => $data['IMSS'] ?? null,
                'CURP' => $data['CURP'] ?? null,
                'RFC' => $data['RFC'] ?? null,
                'Sexo' => $data['Sexo'] ?? null,
                'Fecha_Nacimiento' => $data['Fecha_Nacimiento'] ?? null,
                'Edad' => $data['Edad'] ?? null,
                'Ciudad' => $data['Ciudad'] ?? null,
                'Estado' => $data['Estado'] ?? null,
                'Domicilio' => $data['Domicilio'] ?? null,
                'Estado_Civil' => $data['Estado_Civil'] ?? null,
                'Telefono' => $data['Telefono'] ?? null,
                'Celular' => $data['Celular'] ?? null,
                'Correo_Electronico' => $data['Correo_Electronico'] ?? null,
                'Contacto_Emergencia' => $data['Contacto_Emergencia'] ?? null,
                'Tel_Emergencia' => $data['Tel_Emergencia'] ?? null,
                'Parentesco_B' => $data['Parentesco_B'] ?? null,
                'Nombre_Completo_B' => $data['Nombre_Completo_B'] ?? null,
                'Domicilio_B' => $data['Domicilio_B'] ?? null,
                'Telefono_B' => $data['Telefono_B'] ?? null,
                'F_NAC_B' => $data['F_NAC_B'] ?? null,
            ]);

            LaboralEmpleado::create([
                'N_Empleado' => $nEmpleado,
                'ID_Puesto' => $data['ID_Puesto'],
                'Nomeclatura' => $data['Nomeclatura'] ?? null,
                'Fisica' => $data['Fisica'] ?? '',
                'Hora_Comida' => $data['Hora_Comida'] ?? '',
                'Efectivo' => $data['Efectivo'] ?? 0,
                'Salario_Diario' => $data['Salario_Diario'] ?? 0,
                'Sueldo' => $data['Sueldo'] ?? 0,
                'Salario_Diario_Neto' => $data['Salario_Diario_Neto'] ?? 0,
                'Sueldo_Neto' => $data['Sueldo_Neto'] ?? 0,
                'Horario' => $data['Horario'] ?? '',
                'Estatus' => 'ACTIVO',
                'KPI' => $data['KPI'] ?? 0,
                'Comision' => $data['Comision'] ?? 0,
                'Reclutador' => $data['Reclutador'] ?? '',
                'Asesor_Ingreso' => $data['Asesor_Ingreso'] ?? '',
                'Entrevista' => $data['Entrevista'] ?? '',
            ]);

            FechaLaboral::create([
                'N_Empleado' => $nEmpleado,
                'Fecha_Ingreso' => $data['fecha_ingreso'],
                'Fecha_Egreso' => null,
                'Fecha_Alta_IMSS' => null,
                'Fecha_Baja_IMSS' => null,
                'motivo_baja' => '',
                'comentario' => '',
                'recontratable' => '',
            ]);

            RutaDocumento::create([
                'N_Empleado' => $nEmpleado,
                'Ruta_Foto_Empleado' => '/Img/avatar.png',
                'Ruta_Renuncia' => null,
            ]);

            foreach (self::DOCUMENTOS_EMPLEADO as $tipo) {
                DocumentoEmpleado::create([
                    'N_Empleado' => $nEmpleado,
                    'Tipo_Documento' => $tipo,
                    'Ruta_Documento' => '',
                    'Fecha_Subida' => null,
                ]);
            }

            foreach (self::DOCUMENTOS_LABORALES as $tipo) {
                DocumentoLaboral::create([
                    'N_Empleado' => $nEmpleado,
                    'Tipo_Documento' => $tipo,
                    'Ruta_Documento' => '',
                    'Fecha_Subida' => now(),
                ]);
            }

            foreach ($this->construirContratos($data['Tipo_de_Contrato'], $data['fecha_ingreso']) as $contrato) {
                Contrato::create(array_merge(['N_Empleado' => $nEmpleado], $contrato));
            }

            HistorialCambio::create([
                'Usuario_ID' => null,
                'N_Empleado' => $nEmpleado,
                'Cambio' => "Registro de Nuevo Empleado con ID: {$nEmpleado}",
                'Fecha_Hora' => now(),
            ]);
        });

        return redirect()->route('empleados.show', $data['N_Empleado'])->with('status', 'Empleado registrado correctamente.');
    }

    private function construirContratos(string $tipo, string $fechaIngreso): array
    {
        if ($tipo === 'Indeterminado') {
            return [
                ['Tipo_de_Contrato' => 'Indeterminado', 'F_Inicio' => $fechaIngreso, 'F_Vence' => null],
                ['Tipo_de_Contrato' => 'Convenio', 'F_Inicio' => $fechaIngreso, 'F_Vence' => null],
            ];
        }

        $contratos = [];
        $inicio = Carbon::parse($fechaIngreso);

        for ($i = 0; $i < 3; $i++) {
            $vence = $inicio->copy()->addDays(28);
            $contratos[] = [
                'Tipo_de_Contrato' => 'Determinado',
                'F_Inicio' => $inicio->toDateString(),
                'F_Vence' => $vence->toDateString(),
            ];
            $inicio = $vence->copy()->addDay();
        }

        $contratos[] = ['Tipo_de_Contrato' => 'Indeterminado', 'F_Inicio' => $inicio->toDateString(), 'F_Vence' => null];
        $contratos[] = ['Tipo_de_Contrato' => 'Convenio', 'F_Inicio' => $inicio->toDateString(), 'F_Vence' => null];

        return $contratos;
    }
}
