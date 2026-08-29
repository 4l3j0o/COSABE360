@extends('layouts.app')
@section('titulo', 'Editar Estudiante')

@section('sidebar')
    @include('dashboard.partials._sidebar-admin')
@endsection

@section('contenido')
    <div class="fixed inset-0 z-20 flex items-start justify-center pt-4 pb-8 px-4 overflow-y-auto" style="margin: -1.5rem; padding: 1.5rem;">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="window.location='{{ route('students.show', $student) }}'"></div>

        <div class="relative w-full max-w-3xl bg-card border border-border rounded-2xl shadow-xl z-30">
            <div class="sticky top-0 flex items-center justify-between px-6 py-4 border-b border-border rounded-t-2xl bg-card">
                <h2 class="text-base font-bold text-foreground">Editar: {{ $student->apellidos }}, {{ $student->nombres }}</h2>
                <a href="{{ route('students.show', $student) }}" class="p-1.5 rounded-lg hover:bg-muted transition-colors text-muted-foreground hover:text-foreground">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </a>
            </div>

            <form method="POST" action="{{ route('students.update', $student) }}" enctype="multipart/form-data" class="p-6 space-y-6">
                @csrf @method('PUT')

                <div class="bg-muted/30 rounded-xl p-5 border border-border/50">
                    <h3 class="text-xs font-bold text-primary uppercase tracking-wider mb-4">Datos Personales</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Nombres <span class="text-error">*</span></label>
                            <input type="text" name="nombres" value="{{ old('nombres', $student->nombres) }}" required class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                            @error('nombres') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Apellidos <span class="text-error">*</span></label>
                            <input type="text" name="apellidos" value="{{ old('apellidos', $student->apellidos) }}" required class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                            @error('apellidos') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Fecha de Nacimiento <span class="text-error">*</span></label>
                            <input type="date" name="fecha_nacimiento" value="{{ old('fecha_nacimiento', $student->fecha_nacimiento->format('Y-m-d')) }}" required class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                            @error('fecha_nacimiento') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Lugar de Nacimiento</label>
                            <input type="text" name="lugar_nacimiento" value="{{ old('lugar_nacimiento', $student->lugar_nacimiento) }}" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                            @error('lugar_nacimiento') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Sexo <span class="text-error">*</span></label>
                            <select name="sexo" required class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                                <option value="M" {{ old('sexo', $student->sexo) === 'M' ? 'selected' : '' }}>Masculino</option>
                                <option value="F" {{ old('sexo', $student->sexo) === 'F' ? 'selected' : '' }}>Femenino</option>
                            </select>
                            @error('sexo') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Estado <span class="text-error">*</span></label>
                            <select name="estado" required class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                                <option value="ACTIVO" {{ old('estado', $student->estado) === 'ACTIVO' ? 'selected' : '' }}>Activo</option>
                                <option value="INACTIVO" {{ old('estado', $student->estado) === 'INACTIVO' ? 'selected' : '' }}>Inactivo</option>
                                <option value="EGRESADO" {{ old('estado', $student->estado) === 'EGRESADO' ? 'selected' : '' }}>Egresado</option>
                                <option value="TRASLADADO" {{ old('estado', $student->estado) === 'TRASLADADO' ? 'selected' : '' }}>Trasladado</option>
                            </select>
                            @error('estado') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <div class="bg-muted/30 rounded-xl p-5 border border-border/50">
                    <h3 class="text-xs font-bold text-primary uppercase tracking-wider mb-4">Documento de Identidad</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Tipo <span class="text-error">*</span></label>
                            <select name="tipo_documento" required class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                                <option value="CI" {{ old('tipo_documento', $student->tipo_documento) === 'CI' ? 'selected' : '' }}>Cédula de Identidad</option>
                                <option value="PASAPORTE" {{ old('tipo_documento', $student->tipo_documento) === 'PASAPORTE' ? 'selected' : '' }}>Pasaporte</option>
                                <option value="OTRO" {{ old('tipo_documento', $student->tipo_documento) === 'OTRO' ? 'selected' : '' }}>Otro</option>
                            </select>
                            @error('tipo_documento') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Número <span class="text-error">*</span></label>
                            <input type="text" name="numero_documento" value="{{ old('numero_documento', $student->numero_documento) }}" required class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                            @error('numero_documento') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Complemento</label>
                            <input type="text" name="complemento" value="{{ old('complemento', $student->complemento) }}" maxlength="5" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                            @error('complemento') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <div class="bg-muted/30 rounded-xl p-5 border border-border/50">
                    <h3 class="text-xs font-bold text-primary uppercase tracking-wider mb-4">Contacto</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Dirección</label>
                            <input type="text" name="direccion" value="{{ old('direccion', $student->direccion) }}" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                            @error('direccion') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Teléfono</label>
                            <input type="text" name="telefono" value="{{ old('telefono', $student->telefono) }}" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                            @error('telefono') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Correo Electrónico</label>
                            <input type="email" name="email" value="{{ old('email', $student->email) }}" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                            @error('email') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <div class="bg-muted/30 rounded-xl p-5 border border-border/50">
                    <h3 class="text-xs font-bold text-primary uppercase tracking-wider mb-4">Información Adicional</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Idioma</label>
                            <input type="text" name="idioma" value="{{ old('idioma', $student->idioma) }}" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                            @error('idioma') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Cultura</label>
                            <input type="text" name="cultura" value="{{ old('cultura', $student->cultura) }}" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                            @error('cultura') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Discapacidad</label>
                            <input type="text" name="discapacidad" value="{{ old('discapacidad', $student->discapacidad) }}" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                            @error('discapacidad') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div class="sm:col-span-3">
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Fotografía</label>
                            @if($student->fotografia)
                                <img src="{{ Storage::url($student->fotografia) }}" alt="Actual" class="h-20 rounded-lg mb-2 object-cover">
                            @endif
                            <input type="file" name="fotografia" accept="image/*" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-primary/10 file:text-primary hover:file:bg-primary/20 cursor-pointer">
                            <p class="text-xs text-muted-foreground mt-1">Dejar vacío para mantener la actual.</p>
                            @error('fotografia') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <div class="sticky bottom-0 flex items-center justify-end gap-3 pt-4 border-t border-border bg-card rounded-b-2xl -mx-6 -mb-6 px-6 pb-6">
                    <a href="{{ route('students.show', $student) }}" class="px-5 py-2.5 rounded-lg text-sm font-medium border border-border hover:bg-muted transition-colors">Cancelar</a>
                    <button type="submit" class="bg-primary text-primary-foreground px-5 py-2.5 rounded-lg text-sm font-medium hover:opacity-90 transition-opacity cursor-pointer">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
@endsection
