<?php

namespace App\Support;

use Carbon\Carbon;

class AsistenciaHelper
{
    /**
     * Extrae la hora de entrada a partir de la columna Horario (ej. "09:00 - 18:00").
     */
    public static function horaEntradaEstablecida(?string $horario): string
    {
        if (!$horario) {
            return '09:00';
        }

        if (preg_match('/^(\d{2}:\d{2})/', trim($horario), $matches)) {
            return $matches[1];
        }

        return '09:00';
    }

    /**
     * Determina si una checada (formato H:i o H:i:s) cuenta como Retardo,
     * con 10 minutos de tolerancia sobre la hora de entrada establecida.
     */
    public static function esRetardo(string $horaChecada, ?string $horario): bool
    {
        $horaEntrada = self::horaEntradaEstablecida($horario);
        $entradaCarbon = Carbon::createFromFormat('H:i', $horaEntrada)->addMinutes(10);
        $checadaCarbon = Carbon::createFromFormat(strlen($horaChecada) === 5 ? 'H:i' : 'H:i:s', $horaChecada);

        return $checadaCarbon->gt($entradaCarbon);
    }
}
