<?php

namespace App\Support;

class LegacyFile
{
    /**
     * Convierte una ruta guardada en BD por el RH legacy en una URL que funcione desde DRRHH.
     * El legacy guardó rutas en varios formatos: "/RH/...", "RH/..." (relativa a /RH/)
     * y hasta rutas de Windows "C:\xampp\htdocs\RH\...".
     */
    public static function url(?string $ruta): ?string
    {
        if ($ruta === null || trim($ruta) === '') {
            return null;
        }

        $ruta = str_replace('\\', '/', trim($ruta));
        $base = rtrim(config('legacy.url'), '/');

        // Ya es URL completa, o es un archivo subido desde DRRHH
        if (preg_match('#^https?://#i', $ruta) || str_starts_with($ruta, '/uploads/')) {
            return $ruta;
        }

        // Ruta de Windows: C:/xampp/htdocs/RH/...
        if (preg_match('#^[a-z]:/.*?/htdocs/RH/(.*)$#i', $ruta, $m)) {
            return $base . '/' . $m[1];
        }

        // Absoluta desde la raíz del servidor legacy: /RH/...
        if (str_starts_with($ruta, '/RH/')) {
            return $base . substr($ruta, 3);
        }

        // Relativa: el navegador la resolvía desde /RH/Detalles_Empleado.php
        return $base . '/' . ltrim($ruta, '/');
    }
}
