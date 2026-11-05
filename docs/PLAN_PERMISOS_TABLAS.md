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

- **Un rol por usuario**, como hoy (`usuarios.Rol`). Serán muchos usuarios y muchos roles
  personalizados, pero cada usuario tiene uno solo.

**Pendiente de confirmar con el equipo:**
- ¿El catálogo de permisos sigue en `config/permissions.php` (se sincroniza a la tabla con el seeder)
  o se administra desde pantalla? Este plan asume que **sigue en config**.

**Estado actual:** Pasos 1-11 hechos y probados en la rama `feat/permisos-tablas` (2026-11-05), listo para
merge a `main`. Faltan: correrlo en producción (ver "Producción" abajo) y el paso 12. Actualiza esta línea y las casillas cuando avance.

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

- [x] 1. Rama nueva `feat/permisos-tablas` desde `main` limpio.
- [x] 2. Migración que crea `permissions` y `permission_role` (con guard `Schema::hasTable`).
      Correr solo con `php artisan migrate --path=database/migrations/<archivo>.php` (BD compartida).
      En .117 se creó por phpMyAdmin con el SQL equivalente + INSERT en `migrations` (batch 4).
- [x] 3. Modelo `Permission` + seeder `PermissionsSeeder`: llena `permissions` desde `config/permissions.php` con
      `updateOrCreate` por `clave` (se puede volver a correr sin duplicar). En .117: 15 filas, por SQL.
- [x] 4. Copiar el JSON actual a `permission_role`: por cada rol, cada clave en `true` → una fila.
      SQL en "Copiar JSON a la pivote" (abajo). En .117: 35 filas, cuadra rol por rol con el JSON.
      En .117 no hizo falta repetirla antes del merge: la carpeta del servidor tuvo el código de la rama
      todo el tiempo y la doble escritura mantuvo JSON = pivote (verificado 2026-11-05). En producción sí.
- [x] 5. Relación `Role::permisos()` (`belongsToMany`).
      **No** llamarla `permissions`: choca con el atributo JSON mientras conviva.
- [x] 6. Reescribir `Role::hasPermission()` y `Role::permisosActivos()` para leer la relación.
      Se usa la propiedad `$this->permisos` (se consulta una vez y queda en memoria del modelo), porque
      `@can` se llama muchas veces por página. En `/roles` se carga con `Role::with('permisos')` (evita N+1).
- [x] 7. `RolController::permisos()` (interruptores): `attach()` / `detach()` en vez de reescribir
      el JSON. Seguir aceptando solo claves que existen en el catálogo y mantener el anti-bloqueo
      (Admin no puede perder `modules.roles`). Hecho con `syncWithoutDetaching`/`detach` en una
      transacción, con **doble escritura** al JSON hasta el paso 12; 422 si una clave no está en `permissions`.
- [x] 8. `RolController` duplicar: copiar permisos con `sync()`. Crear: rol sin filas en la pivote.
- [x] 9. `resources/views/roles/index.blade.php` (línea ~135): estado de cada interruptor desde
      la relación.
- [x] 10. Actualizar `RolesSeeder` para asignar permisos con la relación.
- [x] 11. Pruebas (mismas que la fase 2 de roles):
      - Admin: 200 en todas las páginas.
      - Nominas/Jefe: 403 donde no tienen permiso.
      - Prender/apagar un interruptor en `/roles` → el menú y la ruta cambian (200 ↔ 403).
      - Duplicar un rol copia sus permisos; borrar un rol borra sus filas en la pivote.
      - Contar consultas por página (no debe crecer con cada `@can`). No se midió: se cubre por diseño
        (propiedad de relación + `with('permisos')`).
      Resultado 2026-11-05: todo OK (Admin 200; Nominas 200/403; toggle 403→200→403; anti-bloqueo 422;
      crear = 0 filas; duplicar = mismas 4; borrar = 0 huérfanas; JSON = pivote en los 6 roles).
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

## Producción

En cada ambiente nuevo (antes o junto con el deploy del código):

1. `php artisan migrate --path=database/migrations/2026_11_04_000000_create_permissions_tables.php`
   (o el SQL equivalente + INSERT en `migrations`, como en .117).
2. `php artisan db:seed --class=PermissionsSeeder` (15 permisos).
3. Si la tabla `roles` ya tiene roles: la copia de "Copiar JSON a la pivote" (ensayo → copia → verificación).
   Si está vacía: `php artisan db:seed --class=RolesSeeder` (crea roles y su pivote).
4. Probar con Admin que entra a `/roles` antes de dar acceso a los demás.

## Copiar JSON a la pivote (SQL)

MariaDB 10.4 (XAMPP): `JSON_VALUE` devuelve `1` para `true`, por eso se compara `JSON_EXTRACT(...) = 'true'`.

```sql
-- Ensayo: debe dar Admin = 15
SELECT r.id, r.name, COUNT(p.id) AS permisos_activos
FROM roles r
LEFT JOIN permissions p
  ON JSON_EXTRACT(r.permissions,
                  CONCAT('$.', p.seccion, '.', SUBSTRING_INDEX(p.clave, '.', -1))) = 'true'
GROUP BY r.id, r.name ORDER BY r.id;

-- Copia (para re-sincronizar, antes: DELETE FROM permission_role;)
INSERT IGNORE INTO permission_role (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
JOIN permissions p
  ON JSON_EXTRACT(r.permissions,
                  CONCAT('$.', p.seccion, '.', SUBSTRING_INDEX(p.clave, '.', -1))) = 'true';

-- Verificación: en_json = en_pivote en todas las filas
SELECT r.id, r.name,
       (SELECT COUNT(*) FROM permissions p
         WHERE JSON_EXTRACT(r.permissions,
                 CONCAT('$.', p.seccion, '.', SUBSTRING_INDEX(p.clave, '.', -1))) = 'true') AS en_json,
       (SELECT COUNT(*) FROM permission_role pr WHERE pr.role_id = r.id) AS en_pivote
FROM roles r ORDER BY r.id;
```
