@extends('layouts.app')
@section('titulo', 'Atención de Enfermería')

@section('sidebar')
    @include('dashboard.partials._sidebar-by-rol')
@endsection

@section('contenido')
    @php
        $coloresEstado = [
            'ATENDIDO' => 'success',
            'EN_OBSERVACION' => 'warning',
            'DERIVADO' => 'info',
            'ANULADO' => 'error',
        ];
        $student = $atencion->student;
        $ficha = $student?->medicalRecord;
    @endphp

    <div class="mb-4">
        @include('layouts._volver', ['href' => route('enfermeria.index'), 'texto' => 'Volver a Enfermería'])
    </div>

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
        <div>
            <h1 class="text-xl font-bold text-foreground">Atención de {{ $student ? trim($student->apellidos.' '.$student->nombres) : 'estudiante' }}</h1>
            <p class="text-sm text-muted-foreground">{{ $atencion->fecha_hora?->format('d/m/Y H:i') }} · {{ $atencion->motivo?->nombre }}</p>
        </div>
        <div class="flex items-center gap-2">
            @if(auth()->user()->puede('enfermeria.editar'))
                <a href="{{ route('enfermeria.edit', $atencion) }}" class="inline-flex items-center gap-1.5 bg-primary text-primary-foreground px-4 py-2 rounded-lg text-sm font-medium hover:opacity-90">
                    Editar
                </a>
            @endif
            @if(auth()->user()->puede('enfermeria.anular') && $atencion->estado !== 'ANULADO')
                <form method="POST" action="{{ route('enfermeria.anular', $atencion) }}" onsubmit="return confirm('¿Anular esta atención?')">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="px-4 py-2 rounded-lg text-sm font-medium border border-error/30 text-error hover:bg-error/10">Anular</button>
                </form>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-5">
        <div class="xl:col-span-2 space-y-5">
            <div class="bg-card border border-border rounded-xl p-5">
                <div class="flex items-center justify-between gap-3 mb-4">
                    <h2 class="text-sm font-bold text-foreground uppercase tracking-wider">Atención</h2>
                    <x-ui.badge :color="$coloresEstado[$atencion->estado] ?? 'neutral'">{{ strtolower(str_replace('_', ' ', $atencion->estado)) }}</x-ui.badge>
                </div>
                <dl class="space-y-3 text-sm">
                    <div><dt class="text-muted-foreground">Indicación</dt><dd class="mt-1 whitespace-pre-wrap">{{ $atencion->atencion_indicacion }}</dd></div>
                    <div><dt class="text-muted-foreground">Observaciones internas</dt><dd class="mt-1 whitespace-pre-wrap">{{ $atencion->observaciones_internas ?: '—' }}</dd></div>
                    <div><dt class="text-muted-foreground">Derivación</dt><dd class="mt-1">{{ $atencion->tipo_derivacion ? str_replace('_', ' ', $atencion->tipo_derivacion) : '—' }}</dd></div>
                    <div><dt class="text-muted-foreground">Registrada por</dt><dd class="mt-1">{{ $atencion->nurse?->nombre }} {{ $atencion->nurse?->apellido }}</dd></div>
                </dl>
            </div>

            <div class="bg-card border border-border rounded-xl p-5">
                <h2 class="text-sm font-bold text-foreground uppercase tracking-wider mb-4">Datos del estudiante</h2>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                    <div><dt class="text-muted-foreground">Código</dt><dd class="font-mono">{{ $student?->codigo ?? '—' }}</dd></div>
                    <div><dt class="text-muted-foreground">Documento</dt><dd>{{ $student?->numero_documento ?? '—' }}</dd></div>
                    <div><dt class="text-muted-foreground">Fecha de nacimiento</dt><dd>{{ $student?->fecha_nacimiento?->format('d/m/Y') ?? '—' }}</dd></div>
                    <div><dt class="text-muted-foreground">Curso</dt><dd>{{ $atencion->section?->codigoCorto() ?? '—' }}</dd></div>
                    <div><dt class="text-muted-foreground">Sexo</dt><dd>{{ $student?->sexo ?? '—' }}</dd></div>
                    <div><dt class="text-muted-foreground">Estado</dt><dd>{{ $student?->estado ?? '—' }}</dd></div>
                </dl>
            </div>

            <div class="bg-card border border-border rounded-xl p-5">
                <h2 class="text-sm font-bold text-foreground uppercase tracking-wider mb-4">Tutores / padres</h2>
                <div class="space-y-2 text-sm">
                    @forelse($student?->parentGuardians ?? [] as $tutor)
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1 rounded-lg border border-border p-3">
                            <div>
                                <span class="font-medium text-foreground">{{ $tutor->nombres }} {{ $tutor->apellidos }}</span>
                                <span class="text-muted-foreground"> · {{ $tutor->pivot?->tipo_relacion }}</span>
                            </div>
                            <div class="text-xs text-muted-foreground">{{ $tutor->telefono ?: 'Sin teléfono' }} · {{ $tutor->email ?: 'Sin email' }}</div>
                        </div>
                    @empty
                        <p class="text-muted-foreground">Sin tutores registrados.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="space-y-5">
            <form method="POST" action="{{ route('enfermeria.ficha.update', $student) }}" class="bg-card border border-border rounded-xl p-5 space-y-4">
                @csrf
                @method('PUT')
                <h2 class="text-sm font-bold text-foreground uppercase tracking-wider">Ficha médica</h2>

                @php($editable = auth()->user()->puede('salud.editar'))
                @foreach([
                    'grupo_sanguineo' => 'Grupo sanguíneo',
                    'alergias' => 'Alergias',
                    'medicamentos' => 'Medicamentos',
                    'condiciones_especiales' => 'Condiciones especiales',
                    'contacto_emergencia_nombre' => 'Contacto de emergencia',
                    'contacto_emergencia_telefono' => 'Teléfono de emergencia',
                    'contacto_emergencia_parentesco' => 'Parentesco de emergencia',
                ] as $campo => $label)
                    <div>
                        <label class="block text-[11px] font-semibold uppercase tracking-wider text-neutral-600 mb-1">{{ $label }}</label>
                        <input name="{{ $campo }}" value="{{ old($campo, $ficha?->{$campo} ?? '') }}" @disabled(!$editable)
                               class="w-full px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm disabled:opacity-60 focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                        @error($campo) <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                    </div>
                @endforeach

                @if($editable)
                    <button type="submit" class="w-full bg-primary text-primary-foreground px-4 py-2 rounded-lg text-sm font-medium hover:opacity-90">Guardar ficha médica</button>
                @endif
            </form>
        </div>
    </div>
@endsection
