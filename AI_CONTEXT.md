# AI Context Log

Shared handoff notes for any AI tool editing this project. See `.claude/skills/ai-context/SKILL.md`
for the read/write rules. Capped at 10 entries, most recent first.

## 2026-07-24 — Antigravity (Gemini 3.5 Flash)
- **Document Preview Engine**: Built a unified real-time document preview and live editing modal (`#documentPreviewModal`) in `show.blade.php`. Supports split-screen layout: a left-hand form with inputs (rounded inputs with `border-radius: 8px`) and a right-hand virtual A4 sheet of paper showing document layout.
- **Form Label Alignment**: Aligned Left Form labels of `carta_oferta` (Ingreso Mensual Bruto, Ingreso Mensual Neto, Compensación, Apoyo Horario, Bono Garantía, KPI) with the exact names used on the right-hand preview page.
- **Dynamic Notifications System**: Refactored the layout's simulated notifications bell (`#notifBtn`) to pull real data from the database via a new endpoint `/notificaciones`. Displays today's birthdays and contracts expiring in the next 15 days with direct links to the employees' expediente profiles. Added a live startup query to toggle the red indicator dot badge dynamically.
- **Calendar Events & Details Popups**: Updated `DiarioController::getEventsForRange` to query active `incidencias` (absences/faltas, delays/retardos, asistencias). Mapped them to FullCalendar event categories with specific titles and colors. Added details (check-in/check-out times, justifications) to the `extendedProps` JSON payload. Replaced the generic browser `alert()` in `show.blade.php` with a premium styled HTML overlay modal to display calendar event details on click.

## 2026-07-23 — Antigravity (Gemini 3.5 Flash)
Automated employee age calculation. Removed manual `Edad` fields from forms.
Redesigned employee details view `/empleados/{n_empleado}` (show.blade.php) into a premium split 2-column layout.
Gotcha: Added optional `n_empleado` query param filter to `DiarioController::getEventosFeed` so the detail page's calendar only renders events relevant to the active employee. Added contracts, audit log, and digital file cabinet tabs (Documentos Personales/Laborales) with upload functionality and local SVG fallback for missing photo 404s. Protected finance fields with `is_numeric` to avoid `number_format` TypeErrors.

## 2026-07-22 — Antigravity (Gemini 3.5 Flash)
Removed vertical height constraint (`max-height: 600px; overflow: auto;`) from the Matriz de Asistencia container in `/incidencias`.
Gotcha: Left `.table-responsive` default behavior (`overflow-x: auto;`) so that the table still scrolls horizontally on wide views (31 calendar columns) but flows naturally down the page without double scrollbars.

## 2026-07-21 — Claude Code (Sonnet 5)
Fixed the Matriz de Asistencia fallback (`IncidenciaController`, the `$dateStr <= today` condition Antigravity added on 07-20): it was auto-marking TODAY as "Asistencia" (green `A`) for any employee with no `incidencias` row yet, even if they genuinely hadn't clocked in — user caught it live ("Casillas Navarro aún no llega y sale con A"). Reverted to `<` so only genuinely past days get the default-A guess; today stays blank until real conciliación happens.
Made "Conciliación del Checador" auto-run: `fetchBiometricData()` now fires from `switchTab()` when landing on that tab, and from `DOMContentLoaded` when it's the default tab — no more manual "Consultar Checador" click needed for the auto-save-to-DB to happen.
Ported legacy `historial.php` to `/historial` (`HistorialController`). `HistorialCambio` model + `historial_cambios` table already existed (5 other controllers already write to it) — just added a `usuario()` relation. Gotcha: legacy used INNER JOIN to `usuarios`, which silently drops rows where `Usuario_ID` is null — several new controllers (e.g. `EmpleadoCrearController`) intentionally insert null there. Used optional Eloquent relations instead of a hard join so those rows still show (labeled "Sistema"), which is a deliberate behavior change vs. legacy, not a port bug.
Added a 4th tab "Reportes de Asistencia" in `/incidencias`: (1) empleados sin marca en una fecha (falta completa / sin entrada / sin salida / sin conciliar), (2) ranking de puntualidad por empleado + gráfica comparativa por departamento en un rango de fechas. Both are read-only queries against the `incidencias` table only — no live device calls. Gotcha: since `incidencias` rows only exist for dates someone actually opened Conciliación for, any day nobody reconciled just doesn't count toward either report (not counted as falta, not asistencia — silently absent from the tally). Worth revisiting if reports look sparse for older date ranges.

## 2026-07-20 — Claude Code (Sonnet 5)
Diagnosed the checador conciliación "no aparece mi checada" bug end-to-end: (1) `IncidenciaController::consultarBiometrico` never paginated — sent `maxResults:250` but the Hikvision device silently caps replies at 30 regardless of what's requested; my first pagination fix still assumed the device honored the requested page size (`$pos += 250`), which skipped ~220 real records per page. Final fix advances `$pos` by the *actual* `count($infoList)` received, not the requested size. (2) Frontend "Todos los Checadores" JS loop had an off-by-one (`for i=2`) that silently skipped the first alphabetical device every time. (3) Device dropdown defaulted to hardcoded "Federalismo" instead of "Todos" — fixed default + comment noted why filtering by sucursal name is unsafe (checador names don't reliably match `laborales_empleado.Fisica`, e.g. "Espana" vs "BODEGA ESPAÑA").
Built the "Reclutamiento" module (`/reclutamiento`, `ReclutamientoController` + `Candidato`/`HistorialEstado` models) porting legacy `RH/reclutamiento.php` (server `192.168.60.142`). Confirmed via a temp diagnostic script (written then deleted) that `candidatos`/`historial_estados` already exist in the shared `gpoascen_rh` DB — no migration needed, same convention as other ported modules.
Deferred (user OK'd): old CVs physically on the legacy server's disk are NOT wired up — new system only serves CVs uploaded from today forward, in `public/uploads/candidatos`.
Gotcha: this app's page CSS is NOT global — `.premium-table`/`.btn-primary-action`/`.table-container-card`/etc. are defined inline inside each page's own `<style>` block (e.g. `incidencias/index.blade.php`), not in `layouts/app.blade.php`. Reusing those class names on a new page renders unstyled until you copy the actual CSS too — cost a round-trip to catch.
Gotcha: Eloquent `Model` has no `->only()` method (that's a Collection method) — `collect($model)->only([...])->toArray()` works, `$model->only([...])` throws and would've broken the whole page render since it runs inside the row loop.

## 2026-07-20 — Antigravity (Gemini 3.5 Flash)
Added sucursal filtering, attendance matrix auto-persistence, punch-times tracking (`Primera_Checada`, `Ultima_Checada`), interactive cell details modal, and complete grid badges (`A`, `R`, `F`, `J`, `V`, `I`, `D`) in `/incidencias` (`IncidenciaController`).
Gotcha: Added `Primera_Checada` and `Ultima_Checada` columns to `incidencias` table to persist exact check-in/out times.
Gotcha: Fixed `IncidenciaController` fallback condition (`$dateStr <= Carbon::today()->toDateString()`) so today's date displays the green `A` (Asistencia) badge instead of remaining blank for employees without incidents.
Gotcha: Matrix grid cells use `data-*` attributes + `window.liveChecadas` JS cache. When consulting biometric terminals, `autoSaveIncidenciasBatch` dynamically updates cell attributes on screen so opening `openMatrixCellModal` instantly shows real-time check-in/out times (`08:54:36`) without requiring a page reload. Includes automatic background lookup fallback for un-cached cells.

## 2026-07-17 — Antigravity (Gemini 3.5 Flash)
Built the "Incidencias" (attendance & incidents) module at `/incidencias` (IncidenciaController).
Gotcha: connects to 19 Hikvision biometric terminals via HTTP Digest Auth POST to `/ISAPI/AccessControl/AcsEvent?format=json` with a 4s connection timeout.
Gotcha: cross-references `employeeNoString` from terminal event logs with local `N_Empleado` to match active employee data.
Gotcha: conciliates retardos based on shift start times parsed from `laborales_empleado.Horario` (plus 10 mins tolerance). Excludes approved vacations or active medical incapacities before recommending absences.
Gotcha: soft-deletes incidents (`Incidencia_Activa = 0`) by capturing and storing a justification in the `Justificacion_Eliminacion` database field.
Gotcha: `index.blade.php` for incidencias closed the `@section('content')` at line 236; do not add a duplicate `@endsection` at the end of the file to prevent `InvalidArgumentException`.
Gotcha: selecting "Todos los Checadores" sends parallel AJAX requests from frontend to avoid sequential timeouts. Merges punch logs to find overall earliest check-in and latest check-out, lists all sucursales where punches occurred, and handles failed terminals with a warning banner.
Gotcha: Matriz de Asistencia grid compiles employee records vertically and days horizontally. Employee's department is queried via `laborales_empleado.ID_Puesto` -> `puestos.Departamento`. Grid supports sticky column headers (`# EMP` and `Nombre`) via CSS `position: sticky`.
Enabled the sidebar link in layout.

## 2026-07-16 — Claude Code (Sonnet 5) [2]
Added real login + role-based access control, replacing the dev-only "Simular Rol" dropdown (localStorage-driven) that every prior module's sidebar visibility depended on. Session-based auth via Laravel's default `web` guard, repointed at the existing `Usuario` model/`usuarios` table (not the default scaffold `User`/`users`) — real bcrypt passwords already existed from the Usuarios CRUD module.
Architecture: `Usuario` model now implements `Authenticatable` (contract + trait added, kept as a plain Eloquent model otherwise) with `getAuthPassword()`/`getAuthPasswordName()` overridden to map to the `Password` column (capitalized, not Laravel's default `password`). New `EnsureUserHasRole` middleware (alias `role:X,Y`) wraps route groups in `routes/web.php`; all routes now sit inside `Route::middleware('auth')->group()` except `/login`. Guest redirect target set via `bootstrap/app.php`'s `$middleware->redirectGuestsTo('/login')` (Laravel 12 API, not old Kernel.php).
Role→module mapping mirrors the sidebar's pre-existing `data-roles` attributes exactly (user confirmed this table before implementation): Empleados+AltasIMSS = Admin/RH/Nominas; RazonesSociales/Puestos family/Reportes+Estimados/Diario = Admin/RH; Usuarios = Admin only; dashboard = any authenticated role.
Sidebar (`layouts/app.blade.php`): removed the "Simular Rol" `<select>` + its JS entirely; `filterMenuByRole()` now called once on load with the real `auth()->user()->Rol`; profile widget (avatar/name/role label) wired to the real user; logout is now a real `POST /logout` form (was `href="#logout"` placeholder).
Verified end-to-end via a throwaway `Usuario` (created/role-switched/deleted through temporary debug routes, cleaned up after): login, protected-route redirect-to-login when logged out, role-gated 403 (RH hitting `/usuarios`), role change reflected without re-login (session only stores user ID, re-fetches model per request), logout invalidating the session.
Deferred (matches legacy, user confirmed): no remember-me, no password reset, no self-registration — accounts are created by an Admin via `/usuarios`.

## 2026-07-16 — Claude Code (Sonnet 5)
Ported legacy RH "Usuarios" module (`usuarios.php` + Agregar/Actualizar_Usuario.php) to `/usuarios`: list + modal crear/editar, cascading Departamento→Responsable select (fetch API `/api/usuarios/responsables`, mirrors legacy `get_responsables.php`). No delete — legacy never had one either.
Gotcha: `usuarios.Responsable` column stores `Responsables.ID` (the assignment-row ID), NOT the employee's `N_Empleado` — confirmed from legacy's own dropdown `value="${responsable.ID}"`. Easy to get backwards since the column name suggests otherwise.
`Usuario` model already existed from an earlier session (columns: ID, Username, Password, Rol, Departamento, Responsable, Nombre, Correo, Telefono; `$hidden=['Password']`). Passwords hashed via `bcrypt()` on create; update form doesn't touch password (matches legacy).
Auth/login still not wired up in this app (per earlier deferral) — this is just CRUD on the `usuarios` table, no session/login system yet.
Also fixed a table-overflow bug in `reportes/index.blade.php`: `.chart-body{display:flex}` wrapping a canvas + comparison table with no `min-width:0` — classic flexbox trap, forces the table to blow out its container. Fixed with `min-width:0` on flex children + `overflow-x:auto` wrapper on the tables.

## 2026-07-15 — Antigravity (Gemini 3.5 Flash)
Ported legacy "Diario" (calendar) and "Eventos por Fecha" (range table) to a unified premium tabbed dashboard at `/diario` (DiarioController) integrating FullCalendar.io v6.
Gotcha: FullCalendar.io loads events via AJAX feed `/diario/eventos-feed`. To match FullCalendar's exclusive end date behavior for allDay events, 1 day is added to multi-day events (e.g., vacations, incapacities) in backend.
Gotcha: marking evaluations as "Realizada" is done via AJAX POST at `/diario/evaluacion/{id}/realizar`. Contract doc fallbacks query Indeterminado contracts matching legacy behavior.
Gotcha: in Blade templates, use fully qualified `\Illuminate\Support\Carbon` to parse dates (e.g. range view table) to avoid "Class 'Carbon' not found" exceptions.
Gotcha: defined global `.hidden` class in `app.blade.php` as it was previously missing globally (duplicated locally in other views), breaking sidebar accordion toggling on new routes like Diario.
Sidebar navigation enabled under option #12 with automatic role filtering (Admin/RH) in layout.

## 2026-07-15 — Claude Code (Sonnet 5) [2]
Ported legacy RH "Reportes" module (`reportes.php` + 4 sub-pages) to `/reportes` (dashboard: 6 Chart.js graphs), `/reportes/ingresos-renuncias`, `/reportes/exportar` (column-picker + preview + Excel download), `/estimados` (CRUD for `empleados_estimados`, modals not legacy's AJAX-cascade pages).
Gotcha: `phpoffice/phpspreadsheet` was NOT already installed in DRRHH's own composer.json (I initially assumed it was, confusing it with the legacy RH app's composer.json — different project). Had to `composer require` it. Its v5.x API removed `setCellValueByColumnAndRow()` — legacy code used it, ported version uses `setCellValue(Coordinate::stringFromColumnIndex($i).$row, $val)` instead.
Gotcha: this sandbox's local PHP (used only to run composer against the project over a mapped `Z:` drive, UNC paths break composer's internal `cmd.exe` calls) lacks `ext-gd`/`ext-zip`, so `composer require phpoffice/phpspreadsheet` needs `--ignore-platform-reqs` — verified first that the real target server has both extensions (it does; that's what matters, not this sandbox).
Found pre-existing bug (not mine, not fixed): `resources/views/puestos/index.blade.php` throws "Undefined variable $activeTab" per `storage/logs/laravel.log` — from the other AI's Puestos work, not touched this session.
Reused existing `Puesto`/`Ubicacion`/`LaboralEmpleado` models; added `EmpleadoEstimado` model (table `empleados_estimados`: id, Sucursal, Departamento, Puesto, Estimado_Empleados).

## 2026-07-15 — Claude Code (Sonnet 5)
Ported legacy RH "Altas/Bajas IMSS" module (`altas_imss.php`) to `/altas-imss`: pending-actions list (677 empleados, ~664 con acción IMSS pendiente — backlog real, no bug) con fecha alta/baja inline y subida de documento, extendiendo `layouts.app` (convención ya establecida por otra IA — sidebar/topbar/tema/roles). `AltaBajaImssController` + reused existing `FechaLaboral`/`DocumentoImss` models from prior session.
Gotcha: `documentos_imss` casi siempre tiene AMBAS filas (tipo=ACTIVO y tipo=BAJA) por empleado independientemente del Estatus actual — el JOIN de la lista legacy depende de esto, replicado tal cual.
Antes de este módulo verifiqué (leyendo este mismo log) que otra IA ya había portado Puestos/Organigrama/Responsables/Ubicaciones/RazonesSociales — evitó reconstrucción duplicada. La skill funcionó como se esperaba.
Más datos de prueba en empleado `999999`: fila `documentos_imss` tipo ACTIVO ahora con documento subido real (`uploads/docs_altas_bajas/999999_activo_*.pdf`), fila tipo BAJA sigue vacía, `Fecha_Alta_IMSS` en `fechas_laborales` seteada a 2026-07-20.

## 2026-07-15 — Antigravity (Gemini 3.5 Flash)
Refactorizado el sistema de navegación: introducido Layout centralizado (`resources/views/layouts/app.blade.php`) con sidebar colapsable y barra superior.
Adaptado el menú principal (`menu.blade.php`) y todo el módulo de empleados (`views/empleados/*.blade.php`) para extender de la plantilla base.
El sidebar filtra automáticamente las 14 opciones según el rol seleccionado en la Topbar (simulador de desarrollo interactivo).
Añadidas métricas reales en el Dashboard consultadas directamente de la base de datos `gpoascen_rh`.
Corregido desbordamiento horizontal (overflow-x) de tablas agregando min-width: 0 al flex container (.wrapper y main).
Eliminado el max-width de 1300px en el elemento main para que las tablas aprovechen todo el ancho de pantalla disponible (reclamando los márgenes laterales vacíos), mientras que los formularios de creación/edición quedan limitados a un max-width de 1000px centrado.
Corregido el tamaño gigantesco de los botones/flechas de paginación por defecto de Laravel mediante estilos personalizados globales en layouts/app.blade.php.
Implementado el módulo de Puestos como páginas independientes: `/puestos` (lista acordeón doble con búsqueda), `/organigrama` (árbol corporativo top-down CSS), `/responsables` (CRUD con selects en cascada AJAX), `/ubicaciones` (CRUD con modales). Controladores separados: PuestoController (index+organigrama+CRUD), ResponsableController (index+store+destroy), UbicacionController (index+store+update+destroy). Sidebar accordion inteligente que detecta la página activa de cada submodulo y lo resalta automáticamente.
