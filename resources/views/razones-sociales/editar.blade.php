@extends('layouts.app')

@section('title', 'Editar Razón Social')
@section('page_title', 'Editar Razón Social')

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
        min-height: 80px;
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

        <form method="POST" action="{{ route('razones-sociales.update', $razonSocial->ID) }}" style="display: flex; flex-direction: column; gap: 1.5rem;">
            @csrf
            @method('PUT')

            <div class="card">
                <h2>Identificación Fiscal y Patronal</h2>
                <div class="campos-grid">
                    <div class="campo"><label>Nomenclatura (Nombre corto) *</label><input type="text" name="Nomenclatura" value="{{ old('Nomenclatura', $razonSocial->Nomenclatura) }}" required placeholder="Ej: AIESA"></div>
                    <div class="campo"><label>Razón Social (IMSS) *</label><input type="text" name="Razon_Social_IMSS" value="{{ old('Razon_Social_IMSS', $razonSocial->Razon_Social_IMSS) }}" required placeholder="Ej: ASCENCIO INDUSTRIAL ELECTRICA SA DE CV"></div>
                    <div class="campo"><label>RFC de la Empresa *</label><input type="text" name="RFC_Empresa" value="{{ old('RFC_Empresa', $razonSocial->RFC_Empresa) }}" required placeholder="Ej: AIE0608084WA" max-length="13"></div>
                    <div class="campo"><label>Registro Patronal *</label><input type="text" name="Registro_Patronal" value="{{ old('Registro_Patronal', $razonSocial->Registro_Patronal) }}" required placeholder="Ej: R12-59510-10-2"></div>
                    <div class="campo"><label>Representante Legal *</label><input type="text" name="Representante_Legal" value="{{ old('Representante_Legal', $razonSocial->Representante_Legal) }}" required placeholder="Nombre completo"></div>
                </div>
            </div>

            <div class="card">
                <h2>Declaraciones Legales para Contratos</h2>
                <div class="campos-grid">
                    <div class="campo full-width">
                        <label>Declaración A (Datos de Acta Constitutiva) *</label>
                        <textarea name="Declaraciones_A" required placeholder="Detalles de la Escritura Pública y Registro Federal..." maxlength="500">{{ old('Declaraciones_A', $razonSocial->Declaraciones_A) }}</textarea>
                    </div>
                    <div class="campo full-width">
                        <label>Declaración B (Datos de Personalidad del Representante) *</label>
                        <textarea name="Declaraciones_B" required placeholder="Acreditación del Representante Legal ante Notario..." maxlength="500">{{ old('Declaraciones_B', $razonSocial->Declaraciones_B) }}</textarea>
                    </div>
                    <div class="campo full-width">
                        <label>Declaración F (Domicilio Legal Completo) *</label>
                        <textarea name="Declaraciones_F" required placeholder="Ej: Av. Gobernador Curiel número 2050 en la Colonia 8 de Julio..." maxlength="255">{{ old('Declaraciones_F', $razonSocial->Declaraciones_F) }}</textarea>
                    </div>
                </div>
            </div>

            <div style="display:flex; gap:0.75rem;">
                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                <a href="{{ route('razones-sociales.index') }}" class="btn btn-secundario">Cancelar</a>
            </div>
        </form>
    </div>
@endsection
