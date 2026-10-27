{{-- Página genérica para secciones del legacy que todavía no se migran a DRRHH (por ejemplo Inventario) --}}
@extends('layouts.app')

@section('title', ($seccion ?? 'Sección') . ' - En mantenimiento')
@section('page_title', $seccion ?? 'Sección en mantenimiento')

@push('styles')
<style>
    .mant-card {
        max-width: 560px;
        margin: 3rem auto;
        text-align: center;
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 2.5rem 2rem;
        box-shadow: var(--shadow-md);
    }
    .mant-card h2 { margin: 0 0 0.5rem; font-size: 1.4rem; color: var(--text-main); }
    .mant-card p { margin: 0 0 1.75rem; color: var(--text-muted); }
    .mant-icon { color: var(--primary); margin-bottom: 1rem; }
    .mant-acciones { display: flex; gap: 0.75rem; justify-content: center; flex-wrap: wrap; }
    .mant-btn {
        text-decoration: none;
        padding: 0.6rem 1.2rem;
        border-radius: 8px;
        font-weight: 600;
        border: 1px solid var(--border-color);
        color: var(--text-main);
        background: var(--bg-card);
    }
    .mant-btn:hover { background: var(--bg-card-hover); }
    .mant-btn-primario { background: var(--primary); border-color: var(--primary); color: #fff; }
    .mant-btn-primario:hover { background: var(--primary-hover); }
</style>
@endpush

@section('content')
<div class="mant-card">
    {{-- Ícono de herramienta --}}
    <svg class="mant-icon" xmlns="http://www.w3.org/2000/svg" width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
        <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path>
    </svg>

    <h2>{{ $seccion ?? 'Sección' }}</h2>
    <p>Esta sección se encuentra en mantenimiento.</p>

    <div class="mant-acciones">
        {{-- Regresa a la página de donde vino (por ejemplo el detalle del empleado) --}}
        <a href="{{ url()->previous() }}" class="mant-btn">Volver</a>
        <a href="{{ url('/') }}" class="mant-btn mant-btn-primario">Ir al inicio</a>
    </div>
</div>
@endsection
