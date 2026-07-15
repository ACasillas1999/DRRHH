@extends('layouts.app')

@section('title', 'Editar Datos Laborales')
@section('page_title', 'Editar Datos Laborales')

@push('styles')
<style>
    .container { display: flex; flex-direction: column; gap: 1.5rem; max-width: 1000px; margin: 0 auto; width: 100%; }

    .card { background-color: var(--bg-card); border: 1px solid var(--border-color); border-radius: 20px; padding: 1.75rem; box-shadow: var(--shadow-sm); }
    h2 { font-size: 1.05rem; font-weight: 600; margin-bottom: 1rem; }

    .errores { background-color: rgba(220,38,38,0.1); border: 1px solid var(--baja); color: var(--baja); border-radius: 12px; padding: 1rem 1.25rem; font-size: 0.85rem; margin-bottom: 1rem; }
    .errores ul { margin-left: 1.25rem; margin-top: 0.35rem; }

    .campos-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; }
    .campo label { display: block; font-size: 0.75rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.03em; margin-bottom: 0.35rem; }
    .campo input, .campo select { width: 100%; padding: 0.5rem 0.65rem; border-radius: 8px; border: 1px solid var(--border-color); background-color: var(--bg-page); color: var(--text-main); font-family: inherit; font-size: 0.85rem; }
    .campo .fijo { padding: 0.5rem 0.65rem; background-color: var(--bg-page); border: 1px solid var(--border-color); border-radius: 8px; color: var(--text-muted); font-size: 0.85rem; }

    .btn { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.65rem 1.2rem; border-radius: 10px; font-weight: 600; font-size: 0.9rem; text-decoration: none; border: 1px solid transparent; cursor: pointer; }
    .btn-primary { background-color: var(--primary); color: white; }
    .btn-primary:hover { opacity: 0.9; }
    .btn-secundario { background-color: var(--bg-page); border: 1px solid var(--border-color); color: var(--text-main); }
</style>
@endpush

@section('content')
    <div class="container">
        @if ($errors->any())
            <div class="errores">
                Corrige los siguientes errores:
                <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <form method="POST" action="{{ route('empleados.laborales.update', $laboral->N_Empleado) }}" style="display: flex; flex-direction: column; gap: 1.5rem;">
            @csrf
            @method('PUT')

            <div class="card">
                <h2>Datos Laborales</h2>
                <div class="campos-grid">
                    <div class="campo"><label>Departamento (fijo)</label><div class="fijo">{{ $laboral->puesto->Departamento ?? '—' }}</div></div>
                    <div class="campo"><label>Reclutador (fijo)</label><div class="fijo">{{ $laboral->Reclutador ?: '—' }}</div></div>

                    <div class="campo"><label>Puesto *</label>
                        <select name="ID_Puesto" required>
                            @foreach($puestos as $p)
                                <option value="{{ $p->ID }}" @selected(old('ID_Puesto', $laboral->ID_Puesto)==$p->ID)>{{ $p->Nombre_Puesto }} ({{ $p->Departamento }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="campo"><label>Nomenclatura (Razón Social)</label>
                        <select name="Nomeclatura">
                            <option value="">Seleccione</option>
                            @foreach($razonesSociales as $rs)
                                <option value="{{ $rs->Nomenclatura }}" @selected(old('Nomeclatura', $laboral->Nomeclatura)==$rs->Nomenclatura)>{{ $rs->Nomenclatura }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="campo"><label>Sucursal Física</label><input type="text" name="Fisica" value="{{ old('Fisica', $laboral->Fisica) }}"></div>
                    <div class="campo"><label>Horario</label><input type="text" name="Horario" value="{{ old('Horario', $laboral->Horario) }}"></div>
                    <div class="campo"><label>Salario Diario Bruto</label><input type="number" step="0.01" name="Salario_Diario" value="{{ old('Salario_Diario', $laboral->Salario_Diario) }}"></div>
                    <div class="campo"><label>Sueldo Bruto</label><input type="number" step="0.01" name="Sueldo" value="{{ old('Sueldo', $laboral->Sueldo) }}"></div>
                    <div class="campo"><label>Salario Diario Neto</label><input type="number" step="0.01" name="Salario_Diario_Neto" value="{{ old('Salario_Diario_Neto', $laboral->Salario_Diario_Neto) }}"></div>
                    <div class="campo"><label>Sueldo Neto</label><input type="number" step="0.01" name="Sueldo_Neto" value="{{ old('Sueldo_Neto', $laboral->Sueldo_Neto) }}"></div>
                    <div class="campo"><label>Compensación</label><input type="text" name="Hora_Comida" value="{{ old('Hora_Comida', $laboral->Hora_Comida) }}"></div>
                    <div class="campo"><label>Efectivo</label><input type="number" step="0.01" name="Efectivo" value="{{ old('Efectivo', $laboral->Efectivo) }}"></div>
                    <div class="campo"><label>Comisión</label><input type="number" step="0.01" name="Comision" value="{{ old('Comision', $laboral->Comision) }}"></div>
                    <div class="campo"><label>KPI</label><input type="number" step="0.01" name="KPI" value="{{ old('KPI', $laboral->KPI) }}"></div>
                    <div class="campo"><label>Teléfono Empresarial</label><input type="text" name="Telefono_Empresarial" value="{{ old('Telefono_Empresarial', $laboral->Telefono_Empresarial) }}"></div>
                    <div class="campo"><label>Extensión</label><input type="text" name="Extension" value="{{ old('Extension', $laboral->Extension) }}"></div>
                    <div class="campo"><label>Correo Empresarial</label><input type="email" name="Correo_Empresarial" value="{{ old('Correo_Empresarial', $laboral->Correo_Empresarial) }}"></div>
                </div>
            </div>

            <div style="display:flex; gap:0.75rem;">
                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                <a href="{{ route('empleados.show', $laboral->N_Empleado) }}" class="btn btn-secundario">Cancelar</a>
            </div>
        </form>
    </div>
@endsection
