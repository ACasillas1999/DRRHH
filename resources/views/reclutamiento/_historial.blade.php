@if($historial->isEmpty())
    Sin historial de cambios.
@else
    <ul style="padding-left: 20px; margin: 0;">
        @foreach($historial as $h)
            <li><b>{{ $h->fecha }}</b> - Estado cambiado a <b>{{ $h->estado }}</b> por <b>{{ $h->usuario->Nombre ?? 'Desconocido' }}</b></li>
        @endforeach
    </ul>
@endif
