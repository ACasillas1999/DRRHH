# Plan: permisos en tablas en lugar de JSON

Pasar los permisos de la columna JSON `roles.permissions` a tablas propias (`permissions` +
`permission_role`). Sigue al módulo de roles terminado en `docs/PLAN_ROLES.md`.

**Por qué:** el equipo espera muchos usuarios con muchos roles personalizados. Con tablas, la BD
garantiza que solo existan permisos válidos (llaves foráneas), se puede consultar en SQL quién
tiene acceso a qué y queda la puerta abierta a auditoría o permisos por usuario.

**Decisiones tomadas (2026-11-04):**
- Tablas propias, **no** `spatie/laravel-permission`. Spatie liga usuario↔rol por ID en su propia
  tabla (`model_has_roles`); el legacy RH asigna el rol escribiendo texto en `usuarios.Rol`, así que
  habría dos fuentes del rol de cada usuario que se desincronizan. Se conserva
  `usuarios.Rol = roles.name`.
- No cambia nada fuera del modelo, el controlador de roles y su vista: rutas `can:`, `@can`,
  `data-perm` del menú y el `Gate::before` siguen igual, porque todos pasan por `hasPermission()`.

**Pendiente de confirmar con el equipo:**
- ¿Un usuario puede tener **varios** roles a la vez, o uno solo como hoy? Si son varios, hace falta
  además una pivote `role_usuario` y deja de bastar `usuarios.Rol` (afecta al legacy). Este plan
  asume **un rol por usuario**.
- ¿El catálogo de permisos sigue en `config/permissions.php` (se sincroniza a la tabla con el seeder)
  o se administra desde pantalla? Este plan asume que **sigue en config**.

**Estado actual:** Sin empezar. Actualiza esta línea y las casillas cuando avance.

## Diseño

```
roles            (ya existe; la columna permissions se queda de respaldo hasta el paso 12)
permissions      id | clave (modules.empleados) unique | seccion | label | descripcion | timestamps
permission_role  role_id → roles.id | permission_id → permissions.id | PK (role_id, permission_id)
```

- Una fila en `permission_role` = el rol **sí** tiene ese permiso. Sin fila = no lo tiene.
- Llaves foráneas con `cascadeOnDelete()`: borrar un rol o un permiso limpia la pivote.
- Collation `utf8mb4_general_ci` en ambas tablas (igual que `roles` y `usuarios`).

## Pasos

- [ ] 1. Rama nueva `feat/permisos-tablas` desde `main` limpio.
- [ ] 2. Migración que crea `permissions` y `permission_role` (con guard `Schema::hasTable`).
      Correr solo con `php artisan migrate --path=database/migrations/<archivo>.php` (BD compartida).
- [ ] 3. Seeder `PermissionsSeeder`: llena `permissions` desde `config/permissions.php` con
      `updateOrCreate` por `clave` (se puede volver a correr sin duplicar).
- [ ] 4. Copiar el JSON actual a `permission_role`: por cada rol, cada clave en `true` → una fila.
      Antes: `SELECT id, name, permissions FROM roles;` y guardar el resultado como respaldo.
- [ ] 5. Modelo `app/Models/Permission.php` y relación `Role::permisos()` (`belongsToMany`).
      **No** llamarla `permissions`: choca con el atributo JSON mientras conviva.
- [ ] 6. Reescribir `Role::hasPermission()` y `Role::permisosActivos()` para leer la relación.
      Cargar los permisos una sola vez por petición (`loadMissing('permisos')`), porque `@can`
      se llama muchas veces por página.
- [ ] 7. `RolController::permisos()` (interruptores): `attach()` / `detach()` en vez de reescribir
      el JSON. Seguir aceptando solo claves que existen en el catálogo y mantener el anti-bloqueo
      (Admin no puede perder `modules.roles`).
- [ ] 8. `RolController` duplicar: copiar permisos con `sync()`. Crear: rol sin filas en la pivote.
- [ ] 9. `resources/views/roles/index.blade.php` (línea ~135): estado de cada interruptor desde
      la relación.
- [ ] 10. Actualizar `RolesSeeder` para asignar permisos con la relación.
- [ ] 11. Pruebas (mismas que la fase 2 de roles):
      - Admin: 200 en todas las páginas.
      - Nominas/Jefe: 403 donde no tienen permiso.
      - Prender/apagar un interruptor en `/roles` → el menú y la ruta cambian (200 ↔ 403).
      - Duplicar un rol copia sus permisos; borrar un rol borra sus filas en la pivote.
      - Contar consultas por página (no debe crecer con cada `@can`).
- [ ] 12. Ya estable (semanas después): migración que elimina la columna `roles.permissions` y
      quitar el cast `'permissions' => 'array'` y `defaultPermissions()` si ya no se usan.

## Archivos que se tocan

| Archivo | Cambio |
|---|---|
| `database/migrations/<nuevo>_create_permissions_tables.php` | nuevo |
| `database/seeders/PermissionsSeeder.php` | nuevo |
| `app/Models/Permission.php` | nuevo |
| `app/Models/Role.php` | relación `permisos()`, `hasPermission()`, `permisosActivos()` |
| `app/Http/Controllers/RolController.php` | interruptores, duplicar, crear |
| `resources/views/roles/index.blade.php` | leer interruptores de la relación |
| `database/seeders/RolesSeeder.php` | asignar con la relación |

No se tocan: `routes/web.php`, `AppServiceProvider` (Gate), `app/Models/Usuario.php`,
`layouts/app.blade.php`, vistas con `@can`.
