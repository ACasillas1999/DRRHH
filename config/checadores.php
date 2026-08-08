<?php

return [
    // Servidor que los checadores deben alcanzar para mandar eventos en tiempo real (push).
    // Debe ser accesible desde la red LAN de cada sucursal, no un dominio publico.
    'webhook_ip' => env('CHECADOR_WEBHOOK_IP', '192.168.60.117'),
    'webhook_port' => env('CHECADOR_WEBHOOK_PORT', 8042),
    'webhook_path' => '/webhooks/checador',
];
