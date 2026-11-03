# Plan: roles con permisos editables

Port del sistema de roles modulares de Unidata a DRRHH.

**Proyecto de origen:** `\\192.168.60.117\xampp\htdocs\Unidata` (mismo stack: Laravel 12, PHP 8.2, Tailwind 4).
Archivos de referencia ahí:

| Archivo en Unidata | Qué es |
|---|---|
| `database/migrations/2026_04_14_180000_create_roles_table.php` | Tabla `roles` con permisos en JSON |
| `database/seeders/RolesSeeder.php` | Roles iniciales |
| `app/Models/Role.php` | `hasPermission()` y `defaultPermissions()` (catálogo) |
| `app/Models/User.php` (líneas 32-49) | `roleModel()` y `hasPermission()` del usuario |
| `app/Http/Controllers/RolesController.php` | Crear, editar, duplicar y borrar roles |
| `resources/views/roles/index.blade.php` | Pantalla de interruptores |
| `resources/views/layouts/app.blade.php` (líneas 234-502) | Menú filtrado con `hasPermission()` |
| `routes/web.php` (líneas 131-136) | Rutas `/roles` |

Diferencias a propósito con Unidata: Unidata solo revisa permisos en el menú (sus rutas no tienen
middleware de permisos; cualquier usuario logueado entra por URL). Aquí se revisan también en las
rutas con `can:`. El catálogo vive en `config/permissions.php` en vez de en el modelo. La vista se
reescribió con el estilo de DRRHH; la de Unidata depende de SweetAlert y de su propio layout.

**Objetivo:** que el acceso de cada rol a cada módulo se configure desde la pantalla `/roles`,
en lugar de estar escrito a mano en `routes/web.php` (`role:Admin,RH`), en el menú
(`data-roles="..."`) y en `UsuarioController::ROLES`.

**Estado actual:** Fase 2 en curso: rutas ya con `can:` y probadas (2026-11-03). Siguiente: menú, `@can` en vistas y `UsuarioController`.
Actualiza esta línea y las casillas cuando avance.

## Decisiones (no revertir sin hablarlo con el usuario)

- **No se toca la tabla `usuarios`.** La BD `gpoascen_rh` es compartida con el sistema legacy `RH/`,
  que lee `usuarios.Rol` como texto. El rol se liga por `usuarios.Rol = roles.name`
  (`Usuario::roleModel()`), no por un `role_id`.
- Los 6 roles existentes (Admin, RH, Nominas, Jefe, Gerente, Encargado) son `is_system`:
  no se pueden renombrar ni borrar, porque el legacy depende de esos nombres exactos.
- Un rol creado desde `/roles` no existe para el legacy: un usuario con ese rol puede perder acceso allá.
- Admin no puede perder `modules.roles` (lo bloquea `RolController::permisos`), para que nadie se quede fuera de `/roles`.
- `RolController::permisos` solo acepta claves que existen en `config/permissions.php`.
- El seeder reproduce el acceso REAL de las rutas, no el del menú (ver "Bug conocido").

## Fase 1: base del sistema (sin cambiar el acceso de nadie)

- [x] `config/permissions.php`: catálogo de módulos y acciones (única fuente de verdad).
- [x] Migración `database/migrations/2026_11_02_000000_create_roles_table.php` (con guard `Schema::hasTable`).
- [x] `app/Models/Role.php`: `hasPermission()`, `permisosActivos()`, `defaultPermissions()`.
- [x] `app/Models/Usuario.php`: `roleModel()` y `hasPermission()`.
- [x] `app/Providers/AppServiceProvider.php`: `Gate::before` para `modules.*` y `actions.*` → habilita `can:` y `@can`.
- [x] `database/seeders/RolesSeeder.php`: 6 roles con `firstOrCreate` (no pisa ediciones si se re-corre).
- [x] `app/Http/Controllers/RolController.php` + `resources/views/roles/index.blade.php` + rutas `/roles` (dentro del grupo `role:Admin`).
- [x] Layout: `<meta name="csrf-token">` y enlace "Roles y Permisos" en el menú (`data-roles="Admin"`).
- [x] Verificado: `php -l` en los 8 archivos, `route:list --path=roles` (6 rutas), `view:cache` OK.
- [x] Tabla creada en el .117 desde phpMyAdmin con SQL equivalente (migración + seeder + fila en `migrations`).
  Collation `utf8mb4_general_ci` (la de `usuarios`; con `unicode_ci` el `withCount` truena por "Illegal mix of collations"),
  ya reflejada en la migración con `$table->collation(...)`. Alternativa por artisan, desde el servidor: (desde la PC del dev `localhost` NO es el .117), en la carpeta de DRRHH:
  ```
  php artisan migrate --path=database/migrations/2026_11_02_000000_create_roles_table.php
  php artisan db:seed --class=RolesSeeder
  ```
  Solo esa migración, NO `php artisan migrate` a secas: puede que la tabla `migrations` no tenga
  registradas las de `users`/`cache`/`checadores` y las intente crear en la BD compartida.
- [x] Probado como Admin: 6 roles con conteo de usuarios (Admin 3, RH 10, Gerente 2, Nominas/Jefe/Encargado 1), interruptores guardan,
  claves fuera del catálogo se ignoran, Admin no pierde `roles` (422), roles de sistema no se renombran ni borran,
  crear/duplicar/borrar rol de prueba OK (borrados).
  Ojo fase 2: hay 1 usuario con `Rol = "BAJA"` (no es un rol) → sin permisos = sin acceso, que es lo correcto.

## Fase 2: activar los permisos (NO empezar hasta que la tabla exista)

Si la tabla `roles` no existe, estos cambios dejan a todos los usuarios con 403/500.

- [x] `routes/web.php`: 17 grupos `can:` (mismas 75 rutas, mismo orden). `/empleados/{n}/incapacidades/subir` → `can:actions.incapacidades_subir`.
      Nueva acción `actions.incidencias_editar` (guardar + desactivar incidencia); ver y auto-guardar-lote quedan en `modules.incidencias`.
      Decisión del usuario (2026-11-03): por ahora Nominas/Jefe/Gerente/Encargado SÍ entran a Incidencias (solo ver); se activó desde /roles.
      Probado: Admin 200 en 23 páginas; Jefe y Nominas 403 donde no tienen permiso, POST guardar/desactivar/subir incapacidad → 403.
- [x] Vistas: `in_array(...->Rol, [...])` → `@can` / `->can()` (`Usuario` ahora usa el trait `Authorizable`). empleados/show: subir incapacidad
      (`actions.incapacidades_subir`), botón Inventario (`modules.inventario`). incidencias: Justificar/Editar + su modal, botón Eliminar
      y botones Guardar/Justificar de Conciliación (antes los veía todo el que entraba) → `actions.incidencias_editar`. Probado 2026-11-03.
- [ ] `layouts/app.blade.php`: cambiar `data-roles="..."` por `data-perm="modules.<clave>"` y que
      `filterMenuByRole()` filtre con `auth()->user()->roleModel?->permisosActivos()`.
      Los enlaces "Próx" (Vacaciones, Incapacidades) siguen sin módulo: decidir con el usuario.
- [ ] `UsuarioController`: cambiar la constante `ROLES` por `Role::orderBy('name')->pluck('name')`
      (en `index` y en las dos reglas `in:` de validación).
- [ ] `$rolLabels` del layout: usar `roleModel->description` en lugar del arreglo fijo.
- [ ] Probar con cada rol: lo que ve en el menú coincide con lo que le deja abrir la URL; URL protegida a mano → 403.
- [ ] Opcional, cuando todo funcione: borrar el middleware `EnsureUserHasRole` y su alias `role` en `bootstrap/app.php`.

## Matriz inicial (la que crea el seeder)

| Rol | Módulos |
|---|---|
| Admin | Todos + `incapacidades_subir` |
| RH | Todos menos `usuarios` y `roles` + `incapacidades_subir` |
| Nominas | `empleados`, `altas_imss` |
| Jefe, Gerente, Encargado | Ninguno (solo dashboard, que no lleva permiso) |

## Pendientes detectados en fase 2

- Jefe/Gerente/Encargado ven a TODOS los empleados en Incidencias; el legacy (`incidencias.php:224`, `vacaciones.php:107`) les filtra a su gente. Falta portar ese filtro.
- `/diario/eventos-feed` está en `modules.diario`, pero el calendario del expediente (`empleados/show`) lo llama: Nominas ve el expediente y el calendario recibe 403. Ya pasaba antes con `role:Admin,RH`.

## Bug conocido (se corrige en la fase 2)

El menú muestra Incidencias y Vacaciones a Jefe/Gerente/Encargado/Nominas, pero la ruta de
Incidencias solo deja entrar a Admin y RH: esos roles ven el enlace y reciben 403.
El seeder les deja `incidencias` en false (igual que la ruta). Si el usuario quiere que sí
entren, se activa desde `/roles` una vez hecha la fase 2.
