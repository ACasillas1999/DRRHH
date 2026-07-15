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
        $hoy = now();

        return "{$hoy->day} de " . self::MESES[$hoy->month] . " del {$hoy->year}";
    }

    /**
     * Rellena una plantilla .docx con los valores dados y devuelve la ruta del archivo temporal generado.
     * El llamador es responsable de enviar el archivo (response()->download()) y limpiarlo después.
     */
    public function generar(string $plantilla, array $valores, string $nombreSalida, array $imagenes = []): string
    {
        $rutaPlantilla = storage_path("app/machotes/{$plantilla}");
        $procesador = new TemplateProcessor($rutaPlantilla);

        foreach ($valores as $tag => $valor) {
            $procesador->setValue($tag, htmlspecialchars((string) ($valor ?? ''), ENT_QUOTES, 'UTF-8'));
        }

        foreach ($imagenes as $tag => $rutaImagen) {
            if ($rutaImagen && file_exists($rutaImagen)) {
                $procesador->setImageValue($tag, ['path' => $rutaImagen, 'width' => 120, 'height' => 120, 'ratio' => true]);
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
