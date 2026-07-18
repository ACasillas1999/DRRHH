<?php

namespace App\Http\Controllers;

use App\Models\Puesto;
use App\Models\Usuario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class UsuarioController extends Controller
{
    private const ROLES = ['Admin', 'RH', 'Nominas', 'Jefe', 'Gerente', 'Encargado'];

    public function index(): View
    {
        $usuarios = Usuario::orderBy('Nombre')->get();

        $departamentos = Puesto::distinct()
            ->whereNotNull('Departamento')
            ->where('Departamento', '!=', '')
            ->orderBy('Departamento')
            ->pluck('Departamento');

        return view('usuarios.index', [
            'usuarios' => $usuarios,
            'departamentos' => $departamentos,
            'roles' => self::ROLES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'username' => ['required', 'string', 'max:100', 'unique:usuarios,Username'],
            'nombre' => ['required', 'string', 'max:150'],
            'correo' => ['required', 'email', 'max:150'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'rol' => ['required', 'string', 'in:' . implode(',', self::ROLES)],
            'departamento' => ['nullable', 'string', 'max:150'],
            'responsable' => ['nullable', 'string', 'max:50'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        Usuario::create([
            'Username' => $data['username'],
            'Password' => bcrypt($data['password']),
            'Rol' => $data['rol'],
            'Nombre' => $data['nombre'],
            'Correo' => $data['correo'],
            'Telefono' => $data['telefono'] ?? '',
            'Departamento' => $data['departamento'] ?? '',
            'Responsable' => $data['responsable'] ?? '',
        ]);

        return redirect()->route('usuarios.index')->with('status', 'Usuario agregado correctamente.');
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $usuario = Usuario::findOrFail($id);

        $data = $request->validate([
            'username' => ['required', 'string', 'max:100', 'unique:usuarios,Username,' . $id . ',ID'],
            'nombre' => ['required', 'string', 'max:150'],
            'correo' => ['required', 'email', 'max:150'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'rol' => ['required', 'string', 'in:' . implode(',', self::ROLES)],
            'departamento' => ['nullable', 'string', 'max:150'],
            'responsable' => ['nullable', 'string', 'max:50'],
        ]);

        $usuario->update([
            'Username' => $data['username'],
            'Rol' => $data['rol'],
            'Nombre' => $data['nombre'],
            'Correo' => $data['correo'],
            'Telefono' => $data['telefono'] ?? '',
            'Departamento' => $data['departamento'] ?? '',
            'Responsable' => $data['responsable'] ?? '',
        ]);

        return redirect()->route('usuarios.index')->with('status', 'Usuario actualizado correctamente.');
    }

    public function apiResponsables(Request $request): JsonResponse
    {
        $departamento = $request->input('departamento');

        if (empty($departamento)) {
            return response()->json([]);
        }

        $responsables = DB::table('Responsables as r')
            ->join('personales as p', 'r.Responsable', '=', 'p.N_Empleado')
            ->join('laborales_empleado as le', 'le.N_Empleado', '=', 'p.N_Empleado')
            ->where('r.Departamento', $departamento)
            ->select('r.ID', 'r.Responsable', 'p.Nombre_Completo', 'r.SubDepartamento', 'le.Fisica')
            ->get();

        return response()->json($responsables);
    }
}
