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
                <a href="{{ route('students.edit', $student) }}" class="inline-flex items-center gap-2 bg-primary text-primary-foreground px-4 py-2 rounded-lg text-sm font-medium hover:opacity-90 transition-opacity">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Editar
                </a>
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
                        <dd class="text-foreground">{{ $student->correo ?: '—' }}</dd>
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

            @if($student->parentGuardians->count())
                <div class="bg-card border border-border rounded-xl p-6">
                    <h3 class="text-sm font-semibold text-foreground uppercase tracking-wider mb-4">Padres / Tutores</h3>
                    <ul class="space-y-2">
                        @foreach($student->parentGuardians as $padre)
                            <li class="text-sm">
                                <span class="text-foreground font-medium">{{ $padre->nombres }} {{ $padre->apellidos }}</span>
                                <span class="text-muted-foreground block text-xs">{{ $padre->pivot->tipo_relacion }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>
@endsection
