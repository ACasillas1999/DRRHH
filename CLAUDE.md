# DRRHH

Al empezar una sesión, lee `AI_CONTEXT.md` (notas de traspaso entre IAs: decisiones, gotchas, pendientes).

Permisos: el acceso a cada módulo sale de la tabla `roles` (se edita en `/roles`), con el catálogo en
`config/permissions.php`. Rutas con `can:modules.<clave>`, vistas con `@can`, menú con `data-perm`.
No uses `role:` ni `in_array(auth()->user()->Rol, [...])`. Detalle y pendientes en `docs/PLAN_ROLES.md`.
