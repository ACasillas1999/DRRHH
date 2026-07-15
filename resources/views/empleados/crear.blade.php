@extends('layouts.app')

@section('title', 'Agregar Empleado')
@section('page_title', 'Agregar Empleado')

@push('styles')
<style>
    .container { display: flex; flex-direction: column; gap: 1.5rem; max-width: 1000px; margin: 0 auto; width: 100%; }

    .card { background-color: var(--bg-card); border: 1px solid var(--border-color); border-radius: 20px; padding: 1.75rem; box-shadow: var(--shadow-sm); }
    h2 { font-size: 1.05rem; font-weight: 600; margin-bottom: 1rem; }

    .errores { background-color: var(--baja); background-color: rgba(220,38,38,0.1); border: 1px solid var(--baja); color: var(--baja); border-radius: 12px; padding: 1rem 1.25rem; font-size: 0.85rem; margin-bottom: 1rem; }
    .errores ul { margin-left: 1.25rem; margin-top: 0.35rem; }

    .campos-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; }
    .campo label { display: block; font-size: 0.75rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.03em; margin-bottom: 0.35rem; }
    .campo input, .campo select, .campo textarea {
        width: 100%; padding: 0.5rem 0.65rem; border-radius: 8px; border: 1px solid var(--border-color);
        background-color: var(--bg-page); color: var(--text-main); font-family: inherit; font-size: 0.85rem;
    }

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
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('empleados.store') }}" style="display: flex; flex-direction: column; gap: 1.5rem;">
            @csrf

            <div class="card">
                <h2>Datos Personales</h2>
                <div class="campos-grid">
                    <div class="campo"><label>N° Empleado *</label><input type="number" name="N_Empleado" value="{{ old('N_Empleado') }}" required></div>
                    <div class="campo"><label>Nombre Completo *</label><input type="text" name="Nombre_Completo" value="{{ old('Nombre_Completo') }}" required></div>
                    <div class="campo"><label>IMSS</label><input type="text" name="IMSS" value="{{ old('IMSS') }}"></div>
                    <div class="campo"><label>CURP</label><input type="text" name="CURP" value="{{ old('CURP') }}"></div>
                    <div class="campo"><label>RFC</label><input type="text" name="RFC" value="{{ old('RFC') }}"></div>
                    <div class="campo"><label>Sexo</label>
                        <select name="Sexo">
                            <option value="">Seleccione</option>
                            <option value="Masculino" @selected(old('Sexo')=='Masculino')>Masculino</option>
                            <option value="Femenino" @selected(old('Sexo')=='Femenino')>Femenino</option>
                        </select>
                    </div>
                    <div class="campo"><label>Fecha Nacimiento</label><input type="date" name="Fecha_Nacimiento" value="{{ old('Fecha_Nacimiento') }}"></div>
                    <div class="campo"><label>Edad</label><input type="number" name="Edad" value="{{ old('Edad') }}"></div>
                    <div class="campo"><label>Ciudad</label><input type="text" name="Ciudad" value="{{ old('Ciudad') }}"></div>
                    <div class="campo"><label>Estado</label><input type="text" name="Estado" value="{{ old('Estado') }}"></div>
                    <div class="campo"><label>Domicilio</label><input type="text" name="Domicilio" value="{{ old('Domicilio') }}"></div>
                    <div class="campo"><label>Estado Civil</label><input type="text" name="Estado_Civil" value="{{ old('Estado_Civil') }}"></div>
                    <div class="campo"><label>Teléfono</label><input type="text" name="Telefono" value="{{ old('Telefono') }}"></div>
                    <div class="campo"><label>Celular</label><input type="text" name="Celular" value="{{ old('Celular') }}"></div>
                    <div class="campo"><label>Correo Electrónico</label><input type="email" name="Correo_Electronico" value="{{ old('Correo_Electronico') }}"></div>
                </div>
            </div>

            <div class="card">
                <h2>Beneficiario</h2>
                <div class="campos-grid">
                    <div class="campo"><label>Contacto de Emergencia</label><input type="text" name="Contacto_Emergencia" value="{{ old('Contacto_Emergencia') }}"></div>
                    <div class="campo"><label>Teléfono de Emergencia</label><input type="text" name="Tel_Emergencia" value="{{ old('Tel_Emergencia') }}"></div>
                    <div class="campo"><label>Parentesco</label><input type="text" name="Parentesco_B" value="{{ old('Parentesco_B') }}"></div>
                    <div class="campo"><label>Nombre Completo</label><input type="text" name="Nombre_Completo_B" value="{{ old('Nombre_Completo_B') }}"></div>
                    <div class="campo"><label>Domicilio</label><input type="text" name="Domicilio_B" value="{{ old('Domicilio_B') }}"></div>
                    <div class="campo"><label>Teléfono</label><input type="text" name="Telefono_B" value="{{ old('Telefono_B') }}"></div>
                    <div class="campo"><label>Fecha Nacimiento</label><input type="date" name="F_NAC_B" value="{{ old('F_NAC_B') }}"></div>
                </div>
            </div>

            <div class="card">
                <h2>Datos Laborales</h2>
                <div class="campos-grid">
                    <div class="campo"><label>Puesto *</label>
                        <select name="ID_Puesto" required>
                            <option value="">Seleccione</option>
                            @foreach($puestos as $p)
                                <option value="{{ $p->ID }}" @selected(old('ID_Puesto')==$p->ID)>{{ $p->Nombre_Puesto }} ({{ $p->Departamento }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="campo"><label>Nomenclatura (Razón Social)</label>
                        <select name="Nomeclatura">
                            <option value="">Seleccione</option>
                            @foreach($razonesSociales as $rs)
                                <option value="{{ $rs->Nomenclatura }}" @selected(old('Nomeclatura')==$rs->Nomenclatura)>{{ $rs->Nomenclatura }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="campo"><label>Sucursal Física</label><input type="text" name="Fisica" value="{{ old('Fisica') }}"></div>
                    <div class="campo"><label>Horario</label><input type="text" name="Horario" value="{{ old('Horario') }}"></div>
                    <div class="campo"><label>Salario Diario Bruto</label><input type="number" step="0.01" name="Salario_Diario" value="{{ old('Salario_Diario') }}"></div>
                    <div class="campo"><label>Sueldo Bruto</label><input type="number" step="0.01" name="Sueldo" value="{{ old('Sueldo') }}"></div>
                    <div class="campo"><label>Salario Diario Neto</label><input type="number" step="0.01" name="Salario_Diario_Neto" value="{{ old('Salario_Diario_Neto') }}"></div>
                    <div class="campo"><label>Sueldo Neto</label><input type="number" step="0.01" name="Sueldo_Neto" value="{{ old('Sueldo_Neto') }}"></div>
                    <div class="campo"><label>Compensación</label><input type="text" name="Hora_Comida" value="{{ old('Hora_Comida') }}"></div>
                    <div class="campo"><label>Efectivo</label><input type="text" name="Efectivo" value="{{ old('Efectivo') }}"></div>
                    <div class="campo"><label>Comisión</label><input type="text" name="Comision" value="{{ old('Comision') }}"></div>
                    <div class="campo"><label>KPI</label><input type="text" name="KPI" value="{{ old('KPI') }}"></div>
                    <div class="campo"><label>Reclutador</label>
                        <select name="Reclutador">
                            <option value="">Seleccione</option>
                            @foreach($reclutadores as $r)
                                <option value="{{ $r->Nombre }}" @selected(old('Reclutador')==$r->Nombre)>{{ $r->Nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="campo"><label>Asesor de Ingreso</label>
                        <select name="Asesor_Ingreso">
                            <option value="">Seleccione</option>
                            @foreach($reclutadores as $r)
                                <option value="{{ $r->Nombre }}" @selected(old('Asesor_Ingreso')==$r->Nombre)>{{ $r->Nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="campo"><label>Entrevista Operativa</label><input type="text" name="Entrevista" value="{{ old('Entrevista') }}"></div>
                </div>
            </div>

            <div class="card">
                <h2>Contratación</h2>
                <div class="campos-grid">
                    <div class="campo"><label>Fecha de Ingreso *</label><input type="date" name="fecha_ingreso" value="{{ old('fecha_ingreso') }}" required></div>
                    <div class="campo"><label>Tipo de Contrato *</label>
                        <select name="Tipo_de_Contrato" required>
                            <option value="">Seleccione</option>
                            <option value="Determinado" @selected(old('Tipo_de_Contrato')=='Determinado')>Determinado (3 periodos de 29 días + Indeterminado)</option>
                            <option value="Indeterminado" @selected(old('Tipo_de_Contrato')=='Indeterminado')>Indeterminado</option>
                        </select>
                    </div>
                </div>
            </div>

            <div style="display:flex; gap:0.75rem;">
                <button type="submit" class="btn btn-primary">Registrar Empleado</button>
                <a href="{{ route('empleados.index') }}" class="btn btn-secundario">Cancelar</a>
            </div>
        </form>
    </div>
@endsection
