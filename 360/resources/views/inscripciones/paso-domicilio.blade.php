@extends('layouts.app')
@section('titulo', 'Paso 2 — Domicilio')
@section('sidebar') @include('dashboard.partials._sidebar-by-rol') @endsection

@section('contenido')
<div class="mb-6">
    @include('layouts._volver', ['href' => route('inscripciones.mi-inscripcion'), 'texto' => 'Volver a Mi Inscripción'])
    <h1 class="text-xl font-bold text-foreground mt-2">Paso 2 — Ubicación del domicilio y fachada</h1>
    <p class="text-sm text-muted-foreground mt-1">Marque con el pin la ubicación exacta de su domicilio y cargue una fotografía de la fachada.</p>
</div>

{{-- Los errores de validación se muestran como tarjetas abajo a la derecha (layouts._toasts). --}}

<form method="POST" action="{{ route('inscripciones.domicilio.guardar') }}" enctype="multipart/form-data" class="bg-card border border-border rounded-xl p-4 mb-5">
    @csrf
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <div id="mapa" class="w-full rounded-lg border border-border" style="height: 380px;"></div>
    <p class="text-xs text-muted-foreground mt-2">El mapa inicia en Tarija - Cercado - Tarija. Haga clic para mover el pin a su domicilio.</p>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-3">
        <div>
            <label class="block text-xs font-semibold text-muted-foreground mb-1">Latitud *</label>
            <input type="text" id="lat" name="dir_latitud" value="{{ old('dir_latitud', $rude?->dir_latitud ?? '-21.5314') }}" required readonly class="px-3 py-2 border border-input rounded-lg bg-muted text-foreground text-sm w-full">
        </div>
        <div>
            <label class="block text-xs font-semibold text-muted-foreground mb-1">Longitud *</label>
            <input type="text" id="lng" name="dir_longitud" value="{{ old('dir_longitud', $rude?->dir_longitud ?? '-64.7314') }}" required readonly class="px-3 py-2 border border-input rounded-lg bg-muted text-foreground text-sm w-full">
        </div>
        <div class="sm:col-span-2">
            <label class="block text-xs font-semibold text-muted-foreground mb-1">Fotografía de la fachada (JPG/PNG, máx 5MB)</label>
            <input type="file" name="foto_fachada" accept=".jpg,.jpeg,.png" {{ $rude?->foto_fachada ? '' : 'required' }} class="text-sm text-muted-foreground file:mr-3 file:px-3 file:py-2 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-primary/10 file:text-primary hover:file:bg-primary/20 cursor-pointer">
            @if($rude?->foto_fachada)
                <p class="text-xs text-muted-foreground mt-1">Actual: <a href="{{ \App\Support\ArchivoPrivado::url($rude->foto_fachada) }}" target="_blank" class="underline">ver foto</a> (subir otra la reemplaza).</p>
            @endif
        </div>
    </div>

    <button type="submit" class="mt-4 px-6 py-2.5 bg-primary text-primary-foreground rounded-lg text-sm font-medium hover:bg-primary/90">Guardar ubicación</button>
</form>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
(function () {
    var lat0 = parseFloat(document.getElementById('lat').value) || -21.5314;
    var lng0 = parseFloat(document.getElementById('lng').value) || -64.7314;
    var map = L.map('mapa').setView([lat0, lng0], 15);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '© OpenStreetMap' }).addTo(map);
    var pin = L.marker([lat0, lng0], { draggable: true }).addTo(map);
    function fijar(latlng) {
        document.getElementById('lat').value = latlng.lat.toFixed(7);
        document.getElementById('lng').value = latlng.lng.toFixed(7);
    }
    pin.on('dragend', function () { fijar(pin.getLatLng()); });
    map.on('click', function (e) { pin.setLatLng(e.latlng); fijar(e.latlng); });
})();
</script>
@endsection
