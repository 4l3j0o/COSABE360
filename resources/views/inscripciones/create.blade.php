@extends('layouts.app')
@section('titulo', 'Nueva Inscripción')
@section('sidebar') @include('dashboard.partials._sidebar-by-rol') @endsection

@section('contenido')
<div class="mb-6">
    @include('layouts._volver', ['href' => route('students.index'), 'texto' => 'Volver al listado'])
    <h1 class="text-xl font-bold text-foreground mt-2">Nueva Inscripción — Preinscripción</h1>
    <p class="text-sm text-muted-foreground mt-1">Período {{ $periodo?->nombre ?? '—' }}. El estudiante queda en preinscripción hasta verificar el pago en contabilidad.</p>
</div>

{{-- Los errores de validación se muestran como tarjetas abajo a la derecha (layouts._toasts). --}}

{{-- Búsqueda por CI --}}
<div class="bg-card border border-border rounded-xl p-4 mb-5">
    <h3 class="font-semibold text-foreground mb-1">1. Validar por carnet de identidad</h3>
    <p class="text-xs text-muted-foreground mb-3">Si el estudiante ya tenía código, se autocompleta el anterior.</p>
    <div class="flex flex-wrap gap-2 items-end">
        <div>
            <label class="block text-xs font-semibold text-muted-foreground mb-1">N° CI</label>
            <input type="text" id="buscar-ci" maxlength="8" class="px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm" placeholder="Ej: 1234567">
        </div>
        <div>
            <label class="block text-xs font-semibold text-muted-foreground mb-1">Complemento</label>
            <input type="text" id="buscar-compl" class="px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm w-20" placeholder="—">
        </div>
        <button type="button" onclick="buscarPorCi()" class="px-4 py-2 bg-primary text-primary-foreground rounded-lg text-sm hover:bg-primary/90">Buscar</button>
        <span id="buscar-resultado" class="text-sm"></span>
    </div>
</div>

<form method="POST" action="{{ route('inscripciones.store') }}">
    @csrf
    <input type="hidden" name="reserva_id" value="{{ $reserva->id }}">

    {{-- Datos del estudiante --}}
    <div class="bg-card border border-border rounded-xl p-4 mb-5">
        <h3 class="font-semibold text-foreground mb-3">2. Datos del estudiante</h3>
        <p class="text-xs text-muted-foreground mb-3">Código <strong>{{ $siguienteCodigo }}</strong> reservado para esta inscripción (se libera en 30 min si no se concreta).</p>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div>
                <label class="block text-xs font-semibold text-muted-foreground mb-1">Código (correlativo)</label>
                <input type="text" value="{{ $siguienteCodigo }}" readonly class="px-3 py-2 border border-input rounded-lg bg-muted text-foreground text-sm font-bold">
            </div>
            <div>
                <label class="block text-xs font-semibold text-muted-foreground mb-1">Nombres *</label>
                <input type="text" name="nombres" id="f-nombres" value="{{ old('nombres') }}" required maxlength="150" class="px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm w-full">
            </div>
            <div>
                <label class="block text-xs font-semibold text-muted-foreground mb-1">Apellido paterno *</label>
                <input type="text" name="apellido_paterno" id="f-paterno" value="{{ old('apellido_paterno') }}" required maxlength="75" class="px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm w-full">
            </div>
            <div>
                <label class="block text-xs font-semibold text-muted-foreground mb-1">Apellido materno</label>
                <input type="text" name="apellido_materno" id="f-materno" value="{{ old('apellido_materno') }}" maxlength="75" class="px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm w-full">
            </div>
            <div>
                <label class="block text-xs font-semibold text-muted-foreground mb-1">Fecha nacimiento *</label>
                <input type="date" name="fecha_nacimiento" id="f-fecha" value="{{ old('fecha_nacimiento') }}" required class="px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm w-full">
            </div>
            <div>
                <label class="block text-xs font-semibold text-muted-foreground mb-1">Sexo *</label>
                <select name="sexo" id="f-sexo" required class="px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm w-full">
                    <option value="M" {{ old('sexo') === 'M' ? 'selected' : '' }}>Masculino</option>
                    <option value="F" {{ old('sexo') === 'F' ? 'selected' : '' }}>Femenino</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-muted-foreground mb-1">Tipo documento *</label>
                <select name="tipo_documento" required class="px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm w-full">
                    <option value="CI" {{ old('tipo_documento', 'CI') === 'CI' ? 'selected' : '' }}>CI</option>
                    <option value="PASAPORTE" {{ old('tipo_documento') === 'PASAPORTE' ? 'selected' : '' }}>Pasaporte</option>
                    <option value="OTRO" {{ old('tipo_documento') === 'OTRO' ? 'selected' : '' }}>Otro</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-muted-foreground mb-1">N° documento *</label>
                <input type="text" name="numero_documento" id="f-ci" value="{{ old('numero_documento') }}" required maxlength="8" pattern="[0-9]{7,8}" title="7 u 8 dígitos" class="px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm w-full">
            </div>
            <div>
                <label class="block text-xs font-semibold text-muted-foreground mb-1">Complemento</label>
                <select name="complemento" class="px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm w-full">
                    <option value="">Sin complemento</option>
                    @foreach(config('bolivia.complementos') as $comp)
                        <option value="{{ $comp }}" {{ old('complemento') === $comp ? 'selected' : '' }}>{{ $comp }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-muted-foreground mb-1">Departamento nac. *</label>
                <select name="nac_departamento" id="nac-depto" required class="px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm w-full">
                    <option value="">Seleccionar…</option>
                    @foreach(array_keys(config('bolivia.departamentos')) as $depto)
                        <option value="{{ $depto }}" {{ old('nac_departamento') === $depto ? 'selected' : '' }}>{{ $depto }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-muted-foreground mb-1">Provincia nac. *</label>
                <select name="nac_provincia" id="nac-prov" required class="px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm w-full">
                    <option value="">Primero elija departamento…</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-muted-foreground mb-1">Ciudad nac. *</label>
                <select name="nac_ciudad" id="nac-ciudad" required class="px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm w-full">
                    <option value="">Primero elija provincia…</option>
                </select>
            </div>
            <div id="nac-otra-wrap" class="hidden">
                <label class="block text-xs font-semibold text-muted-foreground mb-1">Otra ciudad *</label>
                <input type="text" name="nac_ciudad_otra" value="{{ old('nac_ciudad_otra') }}" maxlength="100" placeholder="Escriba la ciudad" class="px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm w-full">
            </div>
            <div>
                <label class="block text-xs font-semibold text-muted-foreground mb-1">Dirección</label>
                <input type="text" name="direccion" value="{{ old('direccion') }}" maxlength="255" class="px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm w-full">
            </div>
            <div>
                <label class="block text-xs font-semibold text-muted-foreground mb-1">Teléfono</label>
                <input type="text" name="telefono" value="{{ old('telefono') }}" maxlength="20" class="px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm w-full">
            </div>
            <div>
                <label class="block text-xs font-semibold text-muted-foreground mb-1">Email Google (automático)</label>
                <div class="px-3 py-2 border border-input rounded-lg bg-muted text-foreground text-sm font-medium" id="email-preview">{{ $siguienteCodigo ? '—' : '' }}</div>
                <p class="text-[10px] text-muted-foreground mt-1">Inicial + apellido paterno + código correlativo (@cosabe.edu.bo). Se crea su cuenta automáticamente.</p>
            </div>
            <div>
                <label class="block text-xs font-semibold text-muted-foreground mb-1">Código RUDE</label>
                <input type="text" name="codigo_rude" value="{{ old('codigo_rude') }}" maxlength="50" class="px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm w-full">
            </div>
            <div>
                <label class="block text-xs font-semibold text-muted-foreground mb-1">Curso *</label>
                <select name="course_id" required class="px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm w-full">
                    <option value="">Seleccionar curso…</option>
                    @foreach($cursos as $curso)
                        <option value="{{ $curso->id }}" {{ old('course_id') == $curso->id ? 'selected' : '' }}>{{ $curso->grade->level->nombre }} — {{ $curso->codigoCorto() }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- Padre / Madre / Tutor --}}
    <div class="bg-card border border-border rounded-xl p-4 mb-5">
        <h3 class="font-semibold text-foreground mb-1">3. Padre, madre y tutor</h3>
        <p class="text-xs text-muted-foreground mb-3">Solo es obligatorio el grupo marcado como tutor principal (firma el contrato). Los demás son opcionales.</p>
        <div class="mb-3 flex flex-wrap gap-4 text-sm">
            <label class="inline-flex items-center gap-2 cursor-pointer"><input type="radio" name="tutor_principal" value="padre" {{ old('tutor_principal') === 'padre' ? 'checked' : '' }} class="accent-primary"> Tutor: padre</label>
            <label class="inline-flex items-center gap-2 cursor-pointer"><input type="radio" name="tutor_principal" value="madre" {{ old('tutor_principal') === 'madre' ? 'checked' : '' }} class="accent-primary"> Tutor: madre</label>
            <label class="inline-flex items-center gap-2 cursor-pointer"><input type="radio" name="tutor_principal" value="tutor" {{ old('tutor_principal', 'tutor') === 'tutor' ? 'checked' : '' }} class="accent-primary"> Tutor: apoderado</label>
        </div>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            @foreach(['padre' => 'Padre', 'madre' => 'Madre', 'tutor' => 'Tutor / Apoderado'] as $grupo => $titulo)
            <div class="border border-border rounded-lg p-3" id="grupo-{{ $grupo }}">
                <h4 class="font-bold text-foreground text-sm mb-2">{{ $titulo }} <span class="marca-obligatoria text-error" data-grupo="{{ $grupo }}"></span></h4>
                <div class="flex flex-col gap-2">
                    <input type="text" name="{{ $grupo }}_nombres" data-obligatorio="{{ $grupo }}" value="{{ old($grupo.'_nombres') }}" placeholder="Nombres" maxlength="150" class="px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                    <input type="text" name="{{ $grupo }}_apellidos" data-obligatorio="{{ $grupo }}" value="{{ old($grupo.'_apellidos') }}" placeholder="Apellidos" maxlength="150" class="px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                    <div class="flex gap-2">
                        <input type="text" name="{{ $grupo }}_num_doc" data-obligatorio="{{ $grupo }}" value="{{ old($grupo.'_num_doc') }}" placeholder="CI" maxlength="8" pattern="[0-9]{7,8}" title="7 u 8 dígitos" class="px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm flex-1">
                        <select name="{{ $grupo }}_complemento" title="Complemento" class="px-2 py-2 border border-input rounded-lg bg-background text-foreground text-sm w-24">
                            <option value="">Comp.</option>
                            @foreach(config('bolivia.complementos') as $comp)
                                <option value="{{ $comp }}" {{ old($grupo.'_complemento') === $comp ? 'selected' : '' }}>{{ $comp }}</option>
                            @endforeach
                        </select>
                    </div>
                    <input type="text" name="{{ $grupo }}_telefono" value="{{ old($grupo.'_telefono') }}" placeholder="Teléfono / celular" maxlength="20" class="px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                    <input type="email" name="{{ $grupo }}_email" value="{{ old($grupo.'_email') }}" placeholder="Email" maxlength="150" class="px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                    <input type="text" name="{{ $grupo }}_direccion" value="{{ old($grupo.'_direccion') }}" placeholder="Domicilio" maxlength="255" class="px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                    <input type="text" name="{{ $grupo }}_ocupacion" value="{{ old($grupo.'_ocupacion') }}" placeholder="Ocupación" maxlength="100" class="px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <button type="submit" class="px-6 py-2.5 bg-primary text-primary-foreground rounded-lg text-sm font-medium hover:bg-primary/90">Registrar preinscripción</button>
</form>

<script>
// Marca obligatorio (nombres, apellidos, CI) solo el grupo del tutor principal
function actualizarObligatorios() {
    var sel = document.querySelector('input[name="tutor_principal"]:checked');
    var principal = sel ? sel.value : 'tutor';
    document.querySelectorAll('[data-obligatorio]').forEach(function (input) {
        input.required = input.dataset.obligatorio === principal;
    });
    document.querySelectorAll('.marca-obligatoria').forEach(function (span) {
        span.textContent = span.dataset.grupo === principal ? '*' : '';
    });
}
document.querySelectorAll('input[name="tutor_principal"]').forEach(function (r) {
    r.addEventListener('change', actualizarObligatorios);
});
actualizarObligatorios();

const UBICACION = @json(config('bolivia.departamentos'));
const CODIGO_CORRELATIVO = @json($siguienteCodigo);
const selDepto = document.getElementById('nac-depto');
const selProv = document.getElementById('nac-prov');
const selCiudad = document.getElementById('nac-ciudad');
const otraWrap = document.getElementById('nac-otra-wrap');

function llenarSelect(sel, opciones, placeholder) {
    sel.innerHTML = '';
    const ph = document.createElement('option');
    ph.value = ''; ph.textContent = placeholder;
    sel.appendChild(ph);
    opciones.forEach(function (o) {
        const opt = document.createElement('option');
        opt.value = o; opt.textContent = o;
        sel.appendChild(opt);
    });
}

selDepto.addEventListener('change', function () {
    const d = UBICACION[selDepto.value];
    llenarSelect(selProv, d ? d.provincias : [], 'Seleccionar provincia…');
    llenarSelect(selCiudad, [], 'Primero elija provincia…');
    otraWrap.classList.add('hidden');
});
selProv.addEventListener('change', function () {
    const d = UBICACION[selDepto.value];
    const ciudades = (d ? d.ciudades.slice() : []);
    ciudades.push('Otra');
    llenarSelect(selCiudad, ciudades, 'Seleccionar ciudad…');
    otraWrap.classList.add('hidden');
});
selCiudad.addEventListener('change', function () {
    otraWrap.classList.toggle('hidden', selCiudad.value !== 'Otra');
});

// Preview del email automático: inicial + paterno + código
function ascii(s) { return (s || '').normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase().replace(/[^a-z0-9]/g, ''); }
function actualizarEmail() {
    const nom = document.getElementById('f-nombres').value.trim();
    const pat = document.getElementById('f-paterno').value.trim();
    const prev = document.getElementById('email-preview');
    if (nom && pat) {
        prev.textContent = ascii(nom.charAt(0) + pat) + '.' + CODIGO_CORRELATIVO + '@cosabe.edu.bo';
    } else {
        prev.textContent = '—';
    }
}
document.getElementById('f-nombres').addEventListener('input', actualizarEmail);
document.getElementById('f-paterno').addEventListener('input', actualizarEmail);
actualizarEmail();

async function buscarPorCi() {
    const ci = document.getElementById('buscar-ci').value.trim();
    const compl = document.getElementById('buscar-compl').value.trim();
    const out = document.getElementById('buscar-resultado');
    if (!ci) { out.textContent = 'Ingrese el CI.'; out.className = 'text-sm text-error'; return; }
    out.textContent = 'Buscando…'; out.className = 'text-sm text-muted-foreground';
    try {
        const r = await fetch('{{ route('inscripciones.buscar-ci') }}?numero_documento=' + encodeURIComponent(ci) + '&complemento=' + encodeURIComponent(compl));
        const d = await r.json();
        if (!d.encontrado) {
            out.textContent = 'Estudiante nuevo: continúe con el registro.';
            out.className = 'text-sm text-success';
            document.getElementById('f-ci').value = ci;
            return;
        }
        document.getElementById('f-nombres').value = d.nombres || '';
        document.getElementById('f-paterno').value = d.apellido_paterno || '';
        document.getElementById('f-materno').value = d.apellido_materno || '';
        actualizarEmail();
        if (d.fecha_nacimiento) document.getElementById('f-fecha').value = d.fecha_nacimiento;
        if (d.sexo) document.getElementById('f-sexo').value = d.sexo;
        document.getElementById('f-ci').value = ci;
        if (d.ya_inscrito) {
            out.innerHTML = 'El estudiante ya tiene código <strong>' + d.codigo + '</strong> y ya está inscrito este período. <a class="underline" href="' + d.ver_url + '">Ver ficha</a>';
        } else {
            out.innerHTML = 'Código anterior encontrado: <strong>' + d.codigo + '</strong>. Datos autocompletados. <a class="underline" href="' + d.ver_url + '">Ver ficha</a>';
        }
        out.className = 'text-sm text-warning';
    } catch (e) {
        out.textContent = 'Error al buscar.';
        out.className = 'text-sm text-error';
    }
}
</script>
@endsection
