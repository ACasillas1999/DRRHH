{{-- Lista de documentos subidos de UNA sección (Ficha Nómina, Carta Oferta, Confidencialidad, Política, Folio Baja).
     Igual que el legacy: cada sección muestra debajo de sus botones sus propios registros de documentos_generados
     (WHERE Nombre_Documento = '...' ORDER BY Fecha_Subida DESC), con ID, N° Empleado, Nombre, Fecha y "Descargar".
     Parámetros: $documentos (colección ya filtrada por Nombre_Documento) y $mensajeVacio (texto del legacy). --}}
<div class="table-wrap" style="margin-top: 1rem;">
    <table class="premium-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>N° Empleado</th>
                <th>Nombre del Documento</th>
                <th>Fecha de Subida</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($documentos as $doc)
                <tr>
                    <td>{{ $doc->ID }}</td>
                    <td>{{ $doc->N_Empleado }}</td>
                    <td class="font-semibold">{{ $doc->Nombre_Documento }}</td>
                    <td>{{ $doc->Fecha_Subida }}</td>
                    <td>
                        {{-- LegacyFile::url resuelve la ruta relativa del legacy (uploads/documentos/...) --}}
                        <a class="action-btn action-btn-download" href="{{ \App\Support\LegacyFile::url($doc->Ruta_Documento) }}" target="_blank" title="Descargar documento">
                            Descargar
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    {{-- Mismo texto que muestra el legacy en esta sección --}}
                    <td colspan="5" class="vacio-row">{{ $mensajeVacio }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
