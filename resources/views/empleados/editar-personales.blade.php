@extends('layouts.app')

@section('title', 'Editar Datos Personales')
@section('page_title', 'Editar Datos Personales')

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

        <form method="POST" action="{{ route('empleados.personales.update', $empleado->N_Empleado) }}" style="display: flex; flex-direction: column; gap: 1.5rem;">
            @csrf
            @method('PUT')

            <div class="card">
                <h2>Datos Personales</h2>
                <div class="campos-grid">
                    <div class="campo"><label>Nombre Completo *</label><input type="text" name="Nombre_Completo" value="{{ old('Nombre_Completo', $empleado->Nombre_Completo) }}" required></div>
                    <div class="campo"><label>IMSS</label><input type="text" name="IMSS" value="{{ old('IMSS', $empleado->IMSS) }}"></div>
                    <div class="campo"><label>CURP</label><input type="text" name="CURP" value="{{ old('CURP', $empleado->CURP) }}"></div>
                    <div class="campo"><label>RFC</label><input type="text" name="RFC" value="{{ old('RFC', $empleado->RFC) }}"></div>
                    <div class="campo"><label>Sexo</label>
                        <select name="Sexo">
                            <option value="">Seleccione</option>
                            <option value="Masculino" @selected(old('Sexo', $empleado->Sexo)=='Masculino')>Masculino</option>
                            <option value="Femenino" @selected(old('Sexo', $empleado->Sexo)=='Femenino')>Femenino</option>
                        </select>
                    </div>
                    <div class="campo"><label>Fecha Nacimiento</label><input type="date" name="Fecha_Nacimiento" value="{{ old('Fecha_Nacimiento', $empleado->Fecha_Nacimiento) }}"></div>
                    <div class="campo"><label>Edad</label><input type="number" name="Edad" value="{{ old('Edad', $empleado->Edad) }}"></div>
                    <div class="campo"><label>Ciudad</label><input type="text" name="Ciudad" value="{{ old('Ciudad', $empleado->Ciudad) }}"></div>
                    <div class="campo"><label>Estado</label><input type="text" name="Estado" value="{{ old('Estado', $empleado->Estado) }}"></div>
                    <div class="campo"><label>Domicilio</label><input type="text" name="Domicilio" value="{{ old('Domicilio', $empleado->Domicilio) }}"></div>
                    <div class="campo"><label>Estado Civil</label><input type="text" name="Estado_Civil" value="{{ old('Estado_Civil', $empleado->Estado_Civil) }}"></div>
                    <div class="campo"><label>Teléfono</label><input type="text" name="Telefono" value="{{ old('Telefono', $empleado->Telefono) }}"></div>
                    <div class="campo"><label>Celular</label><input type="text" name="Celular" value="{{ old('Celular', $empleado->Celular) }}"></div>
                    <div class="campo"><label>Correo Electrónico</label><input type="email" name="Correo_Electronico" value="{{ old('Correo_Electronico', $empleado->Correo_Electronico) }}"></div>
                </div>
            </div>

            <div class="card">
                <h2>Beneficiario</h2>
                <div class="campos-grid">
                    <div class="campo"><label>Contacto de Emergencia</label><input type="text" name="Contacto_Emergencia" value="{{ old('Contacto_Emergencia', $empleado->Contacto_Emergencia) }}"></div>
                    <div class="campo"><label>Teléfono de Emergencia</label><input type="text" name="Tel_Emergencia" value="{{ old('Tel_Emergencia', $empleado->Tel_Emergencia) }}"></div>
                    <div class="campo"><label>Parentesco</label><input type="text" name="Parentesco_B" value="{{ old('Parentesco_B', $empleado->Parentesco_B) }}"></div>
                    <div class="campo"><label>Nombre Completo</label><input type="text" name="Nombre_Completo_B" value="{{ old('Nombre_Completo_B', $empleado->Nombre_Completo_B) }}"></div>
                    <div class="campo"><label>Domicilio</label><input type="text" name="Domicilio_B" value="{{ old('Domicilio_B', $empleado->Domicilio_B) }}"></div>
                    <div class="campo"><label>Teléfono</label><input type="text" name="Telefono_B" value="{{ old('Telefono_B', $empleado->Telefono_B) }}"></div>
                    <div class="campo"><label>Fecha Nacimiento</label><input type="date" name="F_NAC_B" value="{{ old('F_NAC_B', $empleado->F_NAC_B) }}"></div>
                </div>
            </div>

            <div style="display:flex; gap:0.75rem;">
                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                <a href="{{ route('empleados.show', $empleado->N_Empleado) }}" class="btn btn-secundario">Cancelar</a>
            </div>
        </form>
    </div>
@endsection
