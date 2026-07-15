@extends('layouts.app')

@section('title', 'Registrar Puesto')
@section('page_title', 'Registrar Puesto de Trabajo')

@push('styles')
<style>
    .container { display: flex; flex-direction: column; gap: 1.5rem; max-width: 1000px; margin: 0 auto; width: 100%; }

    .card { background-color: var(--bg-card); border: 1px solid var(--border-color); border-radius: 20px; padding: 1.75rem; box-shadow: var(--shadow-sm); }
    h2 { font-size: 1.05rem; font-weight: 600; margin-bottom: 1rem; }

    .errores { background-color: rgba(220,38,38,0.1); border: 1px solid var(--baja); color: var(--baja); border-radius: 12px; padding: 1rem 1.25rem; font-size: 0.85rem; margin-bottom: 1rem; }
    .errores ul { margin-left: 1.25rem; margin-top: 0.35rem; }

    .campos-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; }
    .campo label { display: block; font-size: 0.75rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.03em; margin-bottom: 0.35rem; }
    
    .campo input, .campo select, .campo textarea {
        width: 100%; padding: 0.5rem 0.65rem; border-radius: 8px; border: 1px solid var(--border-color);
        background-color: var(--bg-page); color: var(--text-main); font-family: inherit; font-size: 0.85rem;
    }

    .campo textarea {
        resize: vertical;
        min-height: 100px;
    }

    .campo.full-width {
        grid-column: 1 / -1;
    }

    .btn { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.65rem 1.2rem; border-radius: 10px; font-weight: 600; font-size: 0.9rem; text-decoration: none; border: 1px solid transparent; cursor: pointer; transition: all 0.2s ease; }
    .btn-primary { background-color: var(--primary); color: white; }
    .btn-primary:hover { opacity: 0.9; }
    .btn-secundario { background-color: var(--bg-page); border: 1px solid var(--border-color); color: var(--text-main); }
    .btn-secundario:hover { background-color: var(--bg-card-hover); }
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

        <form method="POST" action="{{ route('puestos.store') }}" style="display: flex; flex-direction: column; gap: 1.5rem;">
            @csrf

            <div class="card">
                <h2>Información del Puesto</h2>
                <div class="campos-grid">
                    <div class="campo">
                        <label>Nombre del Puesto *</label>
                        <input type="text" name="Nombre_Puesto" value="{{ old('Nombre_Puesto') }}" required placeholder="Ej: AUXILIAR DE RECURSOS HUMANOS" list="lista_nombres_puestos">
                        <datalist id="lista_nombres_puestos">
                            @foreach($nombresPuestos as $np)
                                <option value="{{ $np }}">
                            @endforeach
                        </datalist>
                    </div>
                    <div class="campo">
                        <label>Departamento *</label>
                        <input type="text" name="Departamento" value="{{ old('Departamento') }}" required placeholder="Ej: RECURSOS HUMANOS" list="lista_departamentos">
                        <datalist id="lista_departamentos">
                            @foreach($departamentos as $d)
                                <option value="{{ $d }}">
                            @endforeach
                        </datalist>
                    </div>
                    <div class="campo">
                        <label>SubDepartamento</label>
                        <input type="text" name="SubDepartamento" value="{{ old('SubDepartamento') }}" placeholder="Ej: NOMINAS" list="lista_subdepartamentos">
                        <datalist id="lista_subdepartamentos">
                            @foreach($subdepartamentos as $sd)
                                <option value="{{ $sd }}">
                            @endforeach
                        </datalist>
                    </div>
                    
                    <div class="campo full-width" style="margin-top:0.5rem;">
                        <label>Descripción / Perfil del Puesto</label>
                        <textarea name="Descripcion" placeholder="Describe las funciones del puesto..." maxlength="2000">{{ old('Descripcion') }}</textarea>
                    </div>
                </div>
            </div>

            <div style="display:flex; gap:0.75rem;">
                <button type="submit" class="btn btn-primary">Registrar Puesto</button>
                <a href="{{ route('puestos.index', ['tab' => 'puestos']) }}" class="btn btn-secundario">Cancelar</a>
            </div>
        </form>
    </div>
@endsection
