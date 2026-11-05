# DRRHH

Al empezar una sesión, lee `AI_CONTEXT.md` (notas de traspaso entre IAs: decisiones, gotchas, pendientes).

Permisos: el acceso a cada módulo sale de la pivote `permission_role` (roles ↔ `permissions`), se edita en
`/roles` y el catálogo está en `config/permissions.php` (permiso nuevo = clave ahí + `PermissionsSeeder`).
Rutas con `can:modules.<clave>`, vistas con `@can`, menú con `data-perm`. No uses `role:`, ni
`in_array(auth()->user()->Rol, [...])`, ni leas la columna JSON `roles.permissions` (solo respaldo, se borrará).
Detalle en `docs/PLAN_ROLES.md` y `docs/PLAN_PERMISOS_TABLAS.md`.
