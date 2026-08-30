@extends('layouts.app')
@section('titulo', $student->apellidos . ', ' . $student->nombres)

@section('sidebar')
    @include('dashboard.partials._sidebar-admin')
@endsection

@section('contenido')
    <div class="mb-6">
        <a href="{{ route('students.index') }}" class="text-sm text-muted-foreground hover:text-foreground transition-colors">← Volver al listado</a>
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mt-1">
            <h1 class="text-xl font-bold text-foreground">{{ $student->apellidos }}, {{ $student->nombres }}</h1>
            <div class="flex items-center gap-2">
                <a href="{{ route('estudiantes.matriculacion', $student) }}" class="inline-flex items-center gap-2 bg-success text-success-foreground px-4 py-2 rounded-lg text-sm font-medium hover:opacity-90 transition-opacity">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Matricular
                </a>
                <button type="button" onclick="editarEstudiante(this)" data-id="{{ $student->id }}" class="inline-flex items-center gap-2 bg-primary text-primary-foreground px-4 py-2 rounded-lg text-sm font-medium hover:opacity-90 transition-opacity cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Editar
                </button>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-card border border-border rounded-xl p-6">
                <h3 class="text-sm font-semibold text-foreground uppercase tracking-wider mb-4">Datos Personales</h3>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                    <div>
                        <dt class="text-muted-foreground">Código</dt>
                        <dd class="font-mono text-foreground font-medium">{{ $student->codigo }}</dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground">Estado</dt>
                        <dd>
                            @php
                                $colores = ['ACTIVO' => 'bg-success/10 text-success', 'INACTIVO' => 'bg-warning/10 text-warning', 'EGRESADO' => 'bg-info/10 text-info', 'TRASLADADO' => 'bg-neutral-100 text-neutral-600', 'ELIMINADO' => 'bg-error/10 text-error'];
                            @endphp
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $colores[$student->estado] ?? '' }}">{{ $student->estado }}</span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground">Nombres</dt>
                        <dd class="text-foreground">{{ $student->nombres }}</dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground">Apellidos</dt>
                        <dd class="text-foreground">{{ $student->apellidos }}</dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground">Fecha de Nacimiento</dt>
                        <dd class="text-foreground">{{ $student->fecha_nacimiento->format('d/m/Y') }} ({{ $student->fecha_nacimiento->age }} años)</dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground">Lugar de Nacimiento</dt>
                        <dd class="text-foreground">{{ $student->lugar_nacimiento ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground">Sexo</dt>
                        <dd class="text-foreground">{{ $student->sexo === 'M' ? 'Masculino' : 'Femenino' }}</dd>
                    </div>
                </dl>
            </div>

            <div class="bg-card border border-border rounded-xl p-6">
                <h3 class="text-sm font-semibold text-foreground uppercase tracking-wider mb-4">Documento de Identidad</h3>
                <dl class="grid grid-cols-1 sm:grid-cols-3 gap-x-6 gap-y-3 text-sm">
                    <div>
                        <dt class="text-muted-foreground">Tipo</dt>
                        <dd class="text-foreground">{{ $student->tipo_documento }}</dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground">Número</dt>
                        <dd class="text-foreground font-medium">{{ $student->numero_documento }}</dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground">Complemento</dt>
                        <dd class="text-foreground">{{ $student->complemento ?: '—' }}</dd>
                    </div>
                </dl>
            </div>

            <div class="bg-card border border-border rounded-xl p-6">
                <h3 class="text-sm font-semibold text-foreground uppercase tracking-wider mb-4">Contacto e Información Adicional</h3>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                    <div>
                        <dt class="text-muted-foreground">Dirección</dt>
                        <dd class="text-foreground">{{ $student->direccion ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground">Teléfono</dt>
                        <dd class="text-foreground">{{ $student->telefono ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground">Correo</dt>
                        <dd class="text-foreground">{{ $student->email ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground">Idioma</dt>
                        <dd class="text-foreground">{{ $student->idioma ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground">Cultura</dt>
                        <dd class="text-foreground">{{ $student->cultura ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground">Discapacidad</dt>
                        <dd class="text-foreground">{{ $student->discapacidad ?: '—' }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-card border border-border rounded-xl p-6">
                <h3 class="text-sm font-semibold text-foreground uppercase tracking-wider mb-4">Fotografía</h3>
                @if($student->fotografia)
                    <img src="{{ Storage::url($student->fotografia) }}" alt="{{ $student->nombres }}" class="w-full rounded-lg object-cover aspect-[3/4]">
                @else
                    <div class="w-full aspect-[3/4] bg-muted rounded-lg flex items-center justify-center">
                        <svg class="w-16 h-16 text-muted-foreground/50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </div>
                @endif
            </div>

            <div class="bg-card border border-border rounded-xl p-6">
                <h3 class="text-sm font-semibold text-foreground uppercase tracking-wider mb-4">Registro</h3>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-muted-foreground">Creado</dt>
                        <dd class="text-foreground">{{ $student->created_at->format('d/m/Y H:i') }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-muted-foreground">Actualizado</dt>
                        <dd class="text-foreground">{{ $student->updated_at->format('d/m/Y H:i') }}</dd>
                    </div>
                </dl>
            </div>

            @php
                $pgs = $student->parentGuardians;
                $etiquetas = [
                    'PADRE' => 'Padre', 'MADRE' => 'Madre', 'TIO' => 'Tío', 'TIA' => 'Tía',
                    'ABUELO' => 'Abuelo', 'ABUELA' => 'Abuela', 'HERMANO' => 'Hermano',
                    'HERMANA' => 'Hermana', 'OTRO' => 'Otro familiar', 'TUTOR' => 'Tutor',
                ];
            @endphp
            @if($pgs->isNotEmpty())
                <div class="bg-card border border-border rounded-xl p-6">
                    <h3 class="text-sm font-semibold text-foreground uppercase tracking-wider mb-4">Familia y Tutor</h3>
                    <ul class="space-y-2">
                        @foreach($pgs as $persona)
                            @php
                                $rol = $persona->pivot->tipo_relacion;
                                $esTutor = $persona->pivot->es_principal;
                                $etiqueta = $etiquetas[$rol] ?? $rol;
                            @endphp
                            <li class="p-3 rounded-lg border {{ $esTutor ? 'border-primary/30 bg-primary/5' : 'border-border' }}">
                                <div class="text-xs font-semibold uppercase tracking-wider {{ $esTutor ? 'text-primary' : 'text-muted-foreground' }}">{{ $etiqueta }}{{ $esTutor ? ' · Tutor' : '' }}</div>
                                <div class="text-foreground font-medium">{{ $persona->nombres }} {{ $persona->apellidos }}</div>
                                <div class="text-muted-foreground text-xs">{{ $persona->numero_documento ? 'CI ' . $persona->numero_documento : '' }}{{ $esTutor && $persona->email ? ' · ' . $persona->email : '' }}</div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>

    <div class="mt-6 bg-card border border-border rounded-xl p-6">
        <h3 class="text-sm font-semibold text-foreground uppercase tracking-wider mb-4">Documentación</h3>

        @if($documentos->isEmpty())
            <p class="text-sm text-muted-foreground">Este estudiante no tiene documentos registrados en su expediente.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-border bg-muted/50">
                            <th class="text-left px-4 py-2.5 font-semibold text-foreground">Tipo de Documento</th>
                            <th class="text-left px-4 py-2.5 font-semibold text-foreground">Estado</th>
                            <th class="text-left px-4 py-2.5 font-semibold text-foreground">Fecha de entrega</th>
                            <th class="text-right px-4 py-2.5 font-semibold text-foreground">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($documentos as $doc)
                            <tr class="border-b border-border last:border-b-0 hover:bg-muted/30 transition-colors">
                                <td class="px-4 py-2.5 font-medium text-foreground">{{ $doc->tipo_documento }}</td>
                                <td class="px-4 py-2.5">
                                    @php
                                        $colores = [
                                            'PENDIENTE' => 'bg-warning/10 text-warning',
                                            'ENTREGADO' => 'bg-success/10 text-success',
                                            'VERIFICADO' => 'bg-info/10 text-info',
                                        ];
                                    @endphp
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $colores[$doc->estado] ?? 'bg-muted text-muted-foreground' }}">{{ $doc->estado }}</span>
                                </td>
                                <td class="px-4 py-2.5 text-muted-foreground text-xs">{{ $doc->fecha_entrega ? $doc->fecha_entrega->format('d/m/Y') : '—' }}</td>
                                <td class="px-4 py-2.5">
                                    <form method="POST" action="{{ route('student_documents.updateStatus', $doc) }}" class="inline">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="estado" value="{{ $doc->estado === 'PENDIENTE' ? 'ENTREGADO' : 'PENDIENTE' }}">
                                        <button type="submit" class="p-1.5 rounded-lg hover:bg-muted transition-colors text-muted-foreground hover:text-foreground cursor-pointer" title="{{ $doc->estado === 'PENDIENTE' ? 'Marcar como entregado' : 'Marcar como pendiente' }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="mt-6 bg-card border border-border rounded-xl p-6">
        <h3 class="text-sm font-semibold text-foreground uppercase tracking-wider mb-4">Historial de Cambios</h3>

        @if($historial->isEmpty())
            <p class="text-sm text-muted-foreground">No se registraron cambios para este estudiante.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-border bg-muted/50">
                            <th class="text-left px-4 py-2.5 font-semibold text-foreground">Fecha</th>
                            <th class="text-left px-4 py-2.5 font-semibold text-foreground">Usuario</th>
                            <th class="text-left px-4 py-2.5 font-semibold text-foreground">Acción</th>
                            <th class="text-left px-4 py-2.5 font-semibold text-foreground">Detalle</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($historial as $registro)
                            <tr class="border-b border-border last:border-b-0">
                                <td class="px-4 py-2.5 text-muted-foreground whitespace-nowrap">{{ $registro->created_at->format('d/m/Y H:i') }}</td>
                                <td class="px-4 py-2.5 text-foreground">
                                    @if($registro->user)
                                        {{ $registro->user->nombre }} {{ $registro->user->apellido }}
                                    @else
                                        <span class="text-muted-foreground">Sistema</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2.5">
                                    @php
                                        $coloresBtn = ['EDITAR' => 'bg-primary/10 text-primary', 'CREAR' => 'bg-success/10 text-success', 'ELIMINAR' => 'bg-error/10 text-error'];
                                    @endphp
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $coloresBtn[$registro->accion] ?? 'bg-muted text-muted-foreground' }}">{{ $registro->accion }}</span>
                                </td>
                                <td class="px-4 py-2.5 text-muted-foreground">{{ $registro->detalle }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        var estudiantesMap = {
            {{ $student->id }}: @json($student->toArray())
        };
    </script>
@endpush

@include('students.partials._modal-editar')
