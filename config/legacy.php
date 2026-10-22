<?php

return [
    // URL pública del RH legacy (para mostrar fotos y documentos guardados por él)
    'url' => env('LEGACY_URL', 'http://192.168.60.117/RH'),

    // Carpeta del RH legacy en este servidor (para guardar archivos donde él los espera)
    'path' => env('LEGACY_PATH', 'C:/xampp/htdocs/RH'),
];
