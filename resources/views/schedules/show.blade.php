@extends('layouts.app')
@section('titulo', 'Horario - ' . $course->nombre)

@section('sidebar')
    @include('dashboard.partials._sidebar-admin')
@endsection

@section('contenido')
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
        <div class="flex items-center gap-3 min-w-0">
            <a href="{{ route('schedules.index') }}" class="shrink-0 inline-flex items-center gap-1.5 p-2 rounded-lg border border-border hover:bg-muted transition-colors text-muted-foreground hover:text-foreground" title="Volver a paralelos">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <div class="min-w-0">
                <h1 class="text-xl font-bold text-foreground truncate">{{ $course->nombre }} - {{ $course->aula ?? 's/a' }}</h1>
                <p class="text-sm text-muted-foreground">Horario semanal de clases</p>
            </div>
        </div>
        <button onclick="openModal('modal-create')" class="inline-flex items-center gap-2 bg-primary text-primary-foreground px-4 py-2.5 rounded-lg text-sm font-medium hover:opacity-90 transition-opacity cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Nuevo Horario
        </button>
    </div>

    @php
        $diasLabel = [
            'LUNES' => 'Lunes',
            'MARTES' => 'Martes',
            'MIERCOLES' => 'Miércoles',
            'JUEVES' => 'Jueves',
            'VIERNES' => 'Viernes',
            'SABADO' => 'Sábado',
        ];
        $claveFranja = fn ($s) => $s->hora_inicio->format('H:i') . '-' . $s->hora_fin->format('H:i');
    @endphp

    @if($schedules->isEmpty())
        <div class="bg-card border border-border rounded-xl p-12 text-center">
            <svg class="w-12 h-12 text-muted-foreground mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/></svg>
            <p class="text-muted-foreground text-sm">Este paralelo aún no tiene horario asignado.</p>
            <button onclick="openModal('modal-create')" class="mt-4 inline-flex items-center gap-2 bg-primary text-primary-foreground px-4 py-2 rounded-lg text-sm font-medium hover:opacity-90 transition-opacity cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Asignar clases
            </button>
        </div>
    @else
        <div class="bg-card rounded-xl overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-sm border-collapse">
                    <thead>
                        <tr class="bg-primary-700 text-white">
                            <th class="px-4 py-3 text-left font-semibold whitespace-nowrap w-28">
                                <span class="inline-flex items-center gap-1.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Hora
                                </span>
                            </th>
                            @foreach($dias as $dia)
                                <th class="px-3 py-3 text-center font-semibold whitespace-nowrap min-w-[10rem] border-l border-white/15">
                                    {{ $diasLabel[$dia] }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($horas as $hora)
                            @php
                                $clave = $claveFranja($hora);
                            @endphp
                            <tr class="{{ !$loop->last ? 'border-b border-border' : '' }}">
                                <td class="px-4 py-2 alignment-middle whitespace-nowrap bg-muted/40 align-middle">
                                    <div class="text-sm font-bold text-foreground">{{ $hora->hora_inicio->format('H:i') }}</div>
                                    <div class="text-[11px] text-muted-foreground">{{ $hora->hora_fin->format('H:i') }}</div>
                                </td>
                                @foreach($dias as $dia)
                                    @php
                                        $celda = ($tabla[$dia][$clave] ?? collect())->first();
                                    @endphp
                                    <td class="p-2 align-top border-l border-border/60">
                                        @if($celda)
                                            <div class="h-full rounded-lg bg-card border border-border/70 px-2.5 py-2 shadow-sm">
                                                <div class="text-[13px] font-bold leading-tight text-foreground">{{ $celda->subject->nombre ?? '—' }}</div>
                                                @if($celda->teacher)
                                                    <div class="mt-1 text-[11px] leading-snug text-muted-foreground">{{ $celda->teacher->nombres }} {{ $celda->teacher->apellidos }}</div>
                                                @endif
                                                @if($celda->aula)
                                                    <div class="mt-1 text-[11px] leading-snug text-muted-foreground">{{ $celda->aula }}</div>
                                                @endif
                                            </div>
                                        @else
                                            <div class="h-full min-h-[3.5rem] rounded-lg bg-muted/20"></div>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if(session('mensaje'))
        <div class="fixed bottom-4 right-4 bg-success text-success-foreground px-4 py-3 rounded-lg shadow-lg z-50">
            {{ session('mensaje') }}
        </div>
    @endif
@endsection

@push('modals')
    <div id="modal-create" style="display:none; position:fixed; inset:0; z-index:9999; overflow-y:auto; background:rgba(0,0,0,0.3); backdrop-filter:blur(2px); -webkit-backdrop-filter:blur(2px);">
        <div style="min-height:100%; padding:2rem 1rem; display:flex; align-items:flex-start; justify-content:center;">
            <div style="width:100%; max-width:48rem; background:var(--card); border:1px solid var(--border); border-radius:1rem; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);">
                <div style="display:flex; align-items:center; justify-content:space-between; padding:1rem 1.5rem; border-bottom:1px solid var(--border); border-radius:1rem 1rem 0 0; background:var(--card);">
                    <h2 style="font-size:1rem; font-weight:700; color:var(--foreground);">Nuevo Horario</h2>
                    <button onclick="closeModal('modal-create')" style="padding:0.375rem; border-radius:0.5rem; cursor:pointer; color:var(--muted-foreground); background:none; border:none;" onmouseover="this.style.background='var(--muted)'" onmouseout="this.style.background='none'">
                        <svg style="width:1.25rem; height:1.25rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form method="POST" action="{{ route('schedules.store') }}" style="padding:1.5rem;">
                    @csrf

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Curso <span class="text-error">*</span></label>
                            <select name="course_id" required class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                                <option value="{{ $course->id }}" selected>{{ $course->nombre }} - {{ $course->aula ?? 's/a' }}</option>
                                @foreach($courses ?? [] as $courseOpt)
                                    @if($courseOpt->id != $course->id)
                                        <option value="{{ $courseOpt->id }}">{{ $courseOpt->nombre }} - {{ $courseOpt->aula ?? 's/a' }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Materia <span class="text-error">*</span></label>
                            <select name="subject_id" required class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                                <option value="">Seleccionar...</option>
                                @foreach($subjects ?? [] as $subject)
                                    <option value="{{ $subject->id }}">{{ $subject->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Docente <span class="text-error">*</span></label>
                            <select name="teacher_id" required class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                                <option value="">Seleccionar...</option>
                                @foreach($teachers ?? [] as $teacher)
                                    <option value="{{ $teacher->id }}">{{ $teacher->nombres }} {{ $teacher->apellidos }}</option>
                                @endforeach
                            </select>
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
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Hora Inicio <span class="text-error">*</span></label>
                            <input type="time" name="hora_inicio" required class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Hora Fin <span class="text-error">*</span></label>
                            <input type="time" name="hora_fin" required class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Aula</label>
                            <input type="text" name="aula" maxlength="50" value="{{ $course->aula }}" readonly class="w-full px-3 py-2.5 border border-input rounded-lg bg-muted/50 text-foreground text-sm">
                            <p class="text-xs text-muted-foreground mt-1">Se usa el aula del paralelo ({{ $course->aula ?? 'sin asignar' }}).</p>
                        </div>
                    </div>

                    <div style="display:flex; align-items:center; justify-content:flex-end; gap:0.75rem; padding-top:1rem; border-top:1px solid var(--border); margin-top:1rem;">
                        <button type="button" onclick="closeModal('modal-create')" style="padding:0.625rem 1.25rem; border-radius:0.5rem; font-size:0.875rem; font-weight:500; border:1px solid var(--border); background:var(--background); color:var(--foreground); cursor:pointer;">Cancelar</button>
                        <button type="submit" style="padding:0.625rem 1.25rem; border-radius:0.5rem; font-size:0.875rem; font-weight:500; border:none; background:var(--primary); color:var(--primary-foreground); cursor:pointer;">Crear Horario</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endpush

@push('scripts')
    <script>
        function openModal(id) {
            var el = document.getElementById(id);
            el.style.display = 'block';
            document.body.style.overflow = 'hidden';
        }
        function closeModal(id) {
            var el = document.getElementById(id);
            el.style.display = 'none';
            document.body.style.overflow = '';
        }
    </script>
@endpush
