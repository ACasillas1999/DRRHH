<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Usuario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Roles y permisos (port del módulo de Unidata, adaptado a DRRHH).
 *
 * usuarios.Rol guarda el NOMBRE del rol y lo lee también el legacy RH/,
 * por eso los roles de sistema no se pueden renombrar ni borrar.
 */
class RolController extends Controller
{
    /** Roles a los que no se les puede quitar el acceso a /roles (evita que el Admin se bloquee solo). */
    private const ROLES_PROTEGIDOS = ['Admin'];

    public function index(): View
    {
        $roles = Role::withCount('usuarios')->orderByDesc('is_system')->orderBy('name')->get();

        return view('roles.index', [
            'roles'    => $roles,
            'catalogo' => config('permissions'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:50', 'regex:/^[^,]+$/', 'unique:roles,name'],
            'description' => ['nullable', 'string', 'max:255'],
            'color'       => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ], ['name.regex' => 'El nombre no puede llevar comas.']);

        Role::create([
            'name'        => $data['name'],
            'slug'        => $this->slugUnico($data['name']),
            'description' => $data['description'] ?? null,
            'color'       => $data['color'] ?? '#1d4ed8',
            'is_system'   => false,
            'permissions' => Role::defaultPermissions(),
        ]);

        return redirect()->route('roles.index')->with('status', "Rol \"{$data['name']}\" creado. Actívale sus permisos.");
    }

    /** Nombre, descripción y color. */
    public function update(Request $request, Role $role): RedirectResponse
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:50', 'regex:/^[^,]+$/', 'unique:roles,name,' . $role->id],
            'description' => ['nullable', 'string', 'max:255'],
            'color'       => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ], ['name.regex' => 'El nombre no puede llevar comas.']);

        if ($data['name'] !== $role->name) {
            if ($role->is_system) {
                return back()->withErrors(['name' => "El rol \"{$role->name}\" es de sistema: el legacy depende de su nombre y no se puede renombrar."]);
            }
            Usuario::where('Rol', $role->name)->update(['Rol' => $data['name']]);
            $role->slug = $this->slugUnico($data['name'], $role->id);
        }

        $role->fill([
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
            'color'       => $data['color'] ?? $role->color,
        ])->save();

        return redirect()->route('roles.index', ['rol' => $role->id])->with('status', "Rol \"{$role->name}\" actualizado.");
    }

    /** Interruptores de permisos (JSON, se llama por fetch). */
    public function permisos(Request $request, Role $role): JsonResponse
    {
        $data = $request->validate(['permissions' => ['required', 'array']]);

        $catalogo = config('permissions');
        $actual   = array_replace_recursive(Role::defaultPermissions(), $role->permissions ?? []);

        // Solo se aceptan claves que existen en config/permissions.php
        foreach ($data['permissions'] as $section => $items) {
            if (!isset($catalogo[$section]) || !is_array($items)) {
                continue;
            }
            foreach ($items as $key => $value) {
                if (array_key_exists($key, $catalogo[$section])) {
                    $actual[$section][$key] = (bool) $value;
                }
            }
        }

        if (in_array($role->name, self::ROLES_PROTEGIDOS, true) && empty($actual['modules']['roles'])) {
            return response()->json([
                'ok'      => false,
                'message' => "No se le puede quitar \"Roles y Permisos\" a {$role->name}: nadie podría volver a entrar a esta pantalla.",
            ], 422);
        }

        $role->update(['permissions' => $actual]);

        return response()->json(['ok' => true, 'permissions' => $role->permissions]);
    }

    public function duplicate(Role $role): RedirectResponse
    {
        $nombre = $role->name . ' (copia)';
        for ($n = 2; Role::where('name', $nombre)->exists(); $n++) {
            $nombre = $role->name . " (copia {$n})";
        }

        $copia = Role::create([
            'name'        => $nombre,
            'slug'        => $this->slugUnico($nombre),
            'description' => $role->description,
            'color'       => $role->color,
            'is_system'   => false,
            'permissions' => $role->permissions,
        ]);

        return redirect()->route('roles.index', ['rol' => $copia->id])->with('status', "Rol \"{$nombre}\" creado como copia de \"{$role->name}\".");
    }

    public function destroy(Role $role): RedirectResponse
    {
        if ($role->is_system) {
            return back()->withErrors(['rol' => "El rol \"{$role->name}\" es de sistema y no se puede eliminar."]);
        }

        $usuarios = $role->usuarios()->count();
        if ($usuarios > 0) {
            return back()->withErrors(['rol' => "No se puede eliminar \"{$role->name}\": {$usuarios} usuario(s) lo tienen asignado. Reasígnalos primero."]);
        }

        $nombre = $role->name;
        $role->delete();

        return redirect()->route('roles.index')->with('status', "Rol \"{$nombre}\" eliminado.");
    }

    private function slugUnico(string $nombre, ?int $ignorarId = null): string
    {
        $base = Str::slug($nombre) ?: 'rol';
        $slug = $base;
        for ($n = 1; Role::where('slug', $slug)->when($ignorarId, fn ($q) => $q->where('id', '!=', $ignorarId))->exists(); $n++) {
            $slug = "{$base}-{$n}";
        }

        return $slug;
    }
}
