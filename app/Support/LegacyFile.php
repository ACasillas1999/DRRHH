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

        /**
     * Igual que url(), pero devuelve la ruta física en disco para leer el archivo desde PHP
     * (por ejemplo, la foto del empleado para la Ficha Nómina). Las URL http:// no tienen ruta local.
     */
    public static function path(?string $ruta): ?string
    {
        if ($ruta === null || trim($ruta) === '') {
            return null;
        }

        $ruta = str_replace('\\', '/', trim($ruta));
        $base = rtrim(config('legacy.path'), '/');

        // URL completa: no hay archivo local
        if (preg_match('#^https?://#i', $ruta)) {
            return null;
        }

        // Archivo subido desde DRRHH: vive en public/
        if (str_starts_with($ruta, '/uploads/')) {
            return public_path(ltrim($ruta, '/'));
        }

        // Ruta de Windows: ya es física
        if (preg_match('#^[a-z]:/#i', $ruta)) {
            return $ruta;
        }

        // "/RH/..." es la carpeta del legacy (config legacy.path ya termina en /RH)
        if (str_starts_with($ruta, '/RH/')) {
            return $base . substr($ruta, 3);
        }

        // Relativa a /RH/
        return $base . '/' . ltrim($ruta, '/');
    }

}
