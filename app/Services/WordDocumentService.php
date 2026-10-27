<?php

namespace App\Services;

use PhpOffice\PhpWord\TemplateProcessor;

class WordDocumentService
{
    private const MESES = [
        1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril', 5 => 'mayo', 6 => 'junio',
        7 => 'julio', 8 => 'agosto', 9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre',
    ];

    public function fechaHoyEnEspanol(): string
    {
        // Hora de México como el legacy (su "new DateTime()" usa date.timezone=America/Mexico_City); la app sigue en UTC.
        // Sin esto, después de las 6 pm de México los documentos salían con la fecha de mañana.
        $hoy = now('America/Mexico_City');

        // format('d') = día con dos dígitos ("05"), igual que el legacy; ->day daba "5"
        return $hoy->format('d') . ' de ' . self::MESES[$hoy->month] . " del {$hoy->year}";
    }


        /**
     * Machotes del legacy (RH/...). Se leen de ahí para que, si RH actualiza un machote en el legacy,
     * DRRHH genere lo mismo. La copia de storage/app/machotes solo se usa si el archivo del legacy no existe.
     */
    private const MACHOTES_LEGACY = [
        'ficha_nominas.docx' => 'Machotes/Ingreso/FICHA_EXPEDIENTE_RH.docx',
        'carta_oferta.docx' => 'Machotes/Ingreso/Carta_Oferta.docx',
        'carta_confidencialidad.docx' => 'Machotes/Ingreso/CARTA_DE_CONFIDENCIALIDAD.docx',
        'politica_puntualidad.docx' => 'Machotes/Ingreso/Reglamento_de_Puntualidad.docx',
        'folio_baja.docx' => 'Machotes/SOLICITUD DE FINIQUITO.docx',
        'contrato_determinado.docx' => 'Machotes/CONTRATO_DETERMINADO_GRUPO_ASCENCIO_2024.docx',
        'contrato_indeterminado.docx' => 'Machotes/CONTRATO_INDETERMINADO_GRUPO_ASCENCIO_2024.docx',
        'convenio.docx' => 'Machotes/Ingreso/CONVENIO_DE_CONFIDENCIALIDAD_Y_FIDELIDAD.docx',
    ];


    /**
     * Rellena una plantilla .docx con los valores dados y devuelve la ruta del archivo temporal generado.
     * El llamador es responsable de enviar el archivo (response()->download()) y limpiarlo después.
     */
    public function generar(string $plantilla, array $valores, string $nombreSalida, array $imagenes = []): string
    {
        // Primero el machote del legacy; si no existe, la copia local
        $rutaLegacy = isset(self::MACHOTES_LEGACY[$plantilla])
            ? rtrim(config('legacy.path'), '/') . '/' . self::MACHOTES_LEGACY[$plantilla]
            : null;
        $rutaPlantilla = ($rutaLegacy && file_exists($rutaLegacy)) ? $rutaLegacy : storage_path("app/machotes/{$plantilla}");
        $procesador = new TemplateProcessor($rutaPlantilla);

        foreach ($valores as $tag => $valor) {
            $procesador->setValue($tag, htmlspecialchars((string) ($valor ?? ''), ENT_QUOTES, 'UTF-8'));
        }

        foreach ($imagenes as $tag => $rutaImagen) {
            if ($rutaImagen && file_exists($rutaImagen)) {
                $procesador->setImageValue($tag, ['path' => $rutaImagen, 'width' => 100, 'height' => 100, 'ratio' => true]);
            }
        }

        $dirTemporal = storage_path('app/temp');
        if (!is_dir($dirTemporal)) {
            mkdir($dirTemporal, 0777, true);
        }

        $rutaSalida = $dirTemporal . '/' . uniqid('doc_') . '_' . $nombreSalida;
        $procesador->saveAs($rutaSalida);

        return $rutaSalida;
    }
}
