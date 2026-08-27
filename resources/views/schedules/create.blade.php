@extends('layouts.app')
@section('titulo', 'Crear Horario')

@section('sidebar')
    @include('dashboard.partials._sidebar-admin')
@endsection

@section('contenido')
    <div class="fixed inset-0 z-20 flex items-start justify-center pt-4 pb-8 px-4 overflow-y-auto" style="margin: -1.5rem; padding: 1.5rem;">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="window.location='{{ route('schedules.index') }}'"></div>

        <div class="relative w-full max-w-3xl bg-card border border-border rounded-2xl shadow-xl z-30">
            <div class="sticky top-0 flex items-center justify-between px-6 py-4 border-b border-border rounded-t-2xl bg-card">
                <h2 class="text-base font-bold text-foreground">Nuevo Horario</h2>
                <a href="{{ route('schedules.index') }}" class="p-1.5 rounded-lg hover:bg-muted transition-colors text-muted-foreground hover:text-foreground">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </a>
            </div>

            <form method="POST" action="{{ route('schedules.store') }}" class="p-6 space-y-6">
                @csrf

                <div class="bg-muted/30 rounded-xl p-5 border border-border/50">
                    <h3 class="text-xs font-bold text-primary uppercase tracking-wider mb-4">Información del Horario</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Curso <span class="text-error">*</span></label>
                            <select name="curso_id" required class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                                <option value="">Seleccionar...</option>
                                @foreach($courses as $course)
                                    <option value="{{ $course->id }}">{{ $course->nombre }}</option>
                                @endforeach
                            </select>
                            @error('curso_id') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Materia <span class="text-error">*</span></label>
                            <select name="materia_id" required class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                                <option value="">Seleccionar...</option>
                                @foreach($subjects as $subject)
                                    <option value="{{ $subject->id }}">{{ $subject->nombre }}</option>
                                @endforeach
                            </select>
                            @error('materia_id') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Docente <span class="text-error">*</span></label>
                            <select name="docente_id" required class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                                <option value="">Seleccionar...</option>
                                @foreach($teachers as $teacher)
                                    <option value="{{ $teacher->id }}">{{ $teacher->nombres }} {{ $teacher->apellidos }}</option>
                                @endforeach
                            </select>
                            @error('docente_id') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Día <span class="text-error">*</span></label>
                            <select name="dia" required class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                                <option value="">Seleccionar...</option>
                                <option value="LUNES">Lunes</option>
                                <option value="MARTES">Martes</option>
                                <option value="MIERCOLES">Miércoles</option>
                                <option value="JUEVES">Jueves</option>
                                <option value="VIERNES">Viernes</option>
                                <option value="SABADO">Sábado</option>
                            </select>
                            @error('dia') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Hora Inicio <span class="text-error">*</span></label>
                            <input type="time" name="hora_inicio" required class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                            @error('hora_inicio') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Hora Fin <span class="text-error">*</span></label>
                            <input type="time" name="hora_fin" required class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                            @error('hora_fin') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Aula</label>
                            <input type="text" name="aula" maxlength="50" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary" placeholder="Ej: Aula 101">
                            @error('aula') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                @if($errors->any())
                    <div class="bg-error/10 border border-error/20 rounded-xl p-4">
                        <p class="text-sm font-medium text-error mb-2">Por favor, corrige los siguientes errores:</p>
                        <ul class="text-sm text-error list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="sticky bottom-0 flex items-center justify-end gap-3 pt-4 border-t border-border bg-card rounded-b-2xl -mx-6 -mb-6 px-6 pb-6">
                    <a href="{{ route('schedules.index') }}" class="px-5 py-2.5 rounded-lg text-sm font-medium border border-border hover:bg-muted transition-colors">Cancelar</a>
                    <button type="submit" class="bg-primary text-primary-foreground px-5 py-2.5 rounded-lg text-sm font-medium hover:opacity-90 transition-opacity cursor-pointer">Crear Horario</button>
                </div>
            </form>
        </div>
    </div>
@endsection
