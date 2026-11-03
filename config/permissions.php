<?php

/*
|--------------------------------------------------------------------------
| Catálogo de permisos por rol
|--------------------------------------------------------------------------
|
| Única fuente de verdad de los permisos que existen. La pantalla /roles
| dibuja un interruptor por cada entrada, y Role::defaultPermissions() las
| genera en false para los roles nuevos.
|
| Para agregar un módulo: añade la clave aquí, protege su ruta con
| ->middleware('can:modules.<clave>') y ponle data-perm="modules.<clave>"
| a su enlace del menú en layouts/app.blade.php.
|
*/

return [
    'modules' => [
        'empleados'        => ['label' => 'Empleados',          'desc' => 'Expedientes, alta, baja, documentos y contratos'],
        'altas_imss'       => ['label' => 'Altas/Bajas IMSS',   'desc' => 'Fechas y documentos de alta y baja ante el IMSS'],
        'razones_sociales' => ['label' => 'Razones Sociales',   'desc' => 'Catálogo de razones sociales'],
        'puestos'          => ['label' => 'Puestos',            'desc' => 'Puestos, organigrama, responsables y ubicaciones'],
        'reportes'         => ['label' => 'Reportes',           'desc' => 'Reportes, exportaciones y estimados'],
        'diario'           => ['label' => 'Diario',             'desc' => 'Calendario de eventos y evaluaciones'],
        'incidencias'      => ['label' => 'Incidencias',        'desc' => 'Conciliación del checador, matriz y reportes de asistencia'],
        'checadores'       => ['label' => 'Checadores',         'desc' => 'Alta de checadores, lector y modo push'],
        'reclutamiento'    => ['label' => 'Reclutamiento',      'desc' => 'Candidatos y su historial'],
        'historial'        => ['label' => 'Historial',          'desc' => 'Bitácora de cambios'],
        'inventario'       => ['label' => 'Inventario',         'desc' => 'Inventario (en mantenimiento)'],
        'usuarios'         => ['label' => 'Usuarios',           'desc' => 'Cuentas de acceso al sistema'],
        'roles'            => ['label' => 'Roles y Permisos',   'desc' => 'Crear roles y configurar sus permisos'],
    ],
    'actions' => [
        'incapacidades_subir' => ['label' => 'Subir incapacidades', 'desc' => 'Subir el documento de una incapacidad desde el expediente'],
        'incidencias_editar'  => ['label' => 'Editar incidencias',  'desc' => 'Justificar o editar una asistencia y desactivar incidencias'],
    ],
];
