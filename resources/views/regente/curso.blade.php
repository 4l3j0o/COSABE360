@extends('layouts.app')
@section('titulo', 'Regente - ' . $course->nombre)
@section('sidebar') @include('dashboard.partials._sidebar-by-rol') @endsection

@section('contenido')
@php
    $estilos = [
        'PRESENTE' => 'bg-success/10 text-success border-success/30',
        'FALTA' => 'bg-error/10 text-error border-error/30',
        'ATRASO' => 'bg-warning/10 text-warning border-warning/30',
        'PERMISO' => 'bg-info/10 text-info border-info/30',
        'JUSTIFICADO' => 'bg-blue-500/10 text-blue-600 border-blue-500/30',
        'LICENCIA' => 'bg-violet-500/10 text-violet-600 border-violet-500/30',
    ];
@endphp
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
    <div>
        <a href="{{ route('regente.cursos') }}" class="inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Volver a cursos
        </a>
        <h1 class="text-xl font-bold text-foreground mt-2">{{ $course->nombre }}</h1>
        <p class="text-sm text-muted-foreground mt-1">{{ $course->grade?->nombre }} {{ $course->section?->nombre }} · Gestión {{ $course->gestion?->nombre }}</p>
    </div>
    <form method="GET" class="flex items-center gap-2">
        <input type="date" name="fecha" value="{{ $fecha->toDateString() }}" class="px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
        <button type="submit" class="px-4 py-2 bg-primary text-primary-foreground rounded-lg text-sm">Ir</button>
    </form>
</div>

{{-- Formulario de asistencia masiva del día --}}
<div class="bg-card border border-border rounded-xl overflow-hidden shadow-sm mb-6">
    <div class="px-5 py-4 border-b border-border flex items-center justify-between">
        <div>
            <h3 class="font-semibold text-foreground">Lista de asistencia — {{ $fecha->format('d/m/Y') }}</h3>
            <p class="text-xs text-muted-foreground mt-0.5">Como Regente puede modificar la lista a su criterio. Marque LICENCIA y se notificará a los docentes del curso.</p>
        </div>
        <span class="text-xs text-muted-foreground">{{ count($students) }} estudiantes</span>
    </div>
    <form method="POST" action="{{ route('regente.guardarAsistencia', $course) }}">
        @csrf
        <input type="hidden" name="fecha" value="{{ $fecha->toDateString() }}">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-border bg-muted/50 text-left">
                        <th class="px-4 py-3 font-semibold text-foreground">#</th>
                        <th class="px-4 py-3 font-semibold text-foreground">Estudiante</th>
                        <th class="px-4 py-3 font-semibold text-foreground">Estado</th>
                        <th class="px-4 py-3 font-semibold text-foreground">Justificación</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($students as $i => $student)
                        @php $estadoActual = $asistencias->get($student->id)?->estado ?? 'PRESENTE'; @endphp
                        <tr class="border-b border-border last:border-b-0 hover:bg-muted/30">
                            <td class="px-4 py-2 text-muted-foreground">{{ $i + 1 }}</td>
                            <td class="px-4 py-2 font-medium text-foreground">{{ $student->apellidos }} {{ $student->nombres }}</td>
                            <td class="px-4 py-2">
                                <select name="asistencias[{{ $student->id }}][estado]" class="px-2 py-1.5 border border-input rounded-lg bg-background text-foreground text-xs">
                                    @foreach(\App\Models\Attendance::ESTADOS as $estado)
                                        <option value="{{ $estado }}" {{ $estadoActual === $estado ? 'selected' : '' }}>{{ ucfirst(mb_strtolower($estado)) }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="px-4 py-2">
                                <input type="text" name="asistencias[{{ $student->id }}][justificacion]" value="{{ $asistencias->get($student->id)?->justificacion ?? '' }}" placeholder="Motivo (opcional)" class="w-full px-2 py-1.5 border border-input rounded-lg bg-background text-foreground text-xs">
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-5 py-4 border-t border-border flex justify-end">
            <button type="submit" class="px-5 py-2.5 rounded-lg bg-primary text-primary-foreground text-sm font-medium hover:opacity-90 cursor-pointer">Guardar lista del día</button>
        </div>
    </form>
</div>

{{-- Registro de licencia por estudiante --}}
<div class="bg-card border border-border rounded-xl p-5">
    <h3 class="font-semibold text-foreground mb-3">Registrar licencia a un estudiante</h3>
    <form method="POST" action="{{ route('regente.licencia', ['course' => $course, 'student' => ':student_id']) }}" id="form-licencia">
        @csrf
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 items-end">
            <div>
                <label class="block text-xs font-semibold text-muted-foreground mb-1">Estudiante</label>
                <select name="student_id" id="licencia-student" class="w-full px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                    @foreach($students as $student)
                        <option value="{{ $student->id }}">{{ $student->apellidos }} {{ $student->nombres }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-muted-foreground mb-1">Desde</label>
                <input type="date" name="fecha_inicio" value="{{ $fecha->toDateString() }}" class="w-full px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-muted-foreground mb-1">Hasta</label>
                <input type="date" name="fecha_fin" value="{{ $fecha->toDateString() }}" class="w-full px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
            </div>
            <button type="submit" class="px-5 py-2.5 rounded-lg bg-violet-600 text-white text-sm font-medium hover:opacity-90 cursor-pointer">Registrar licencia</button>
        </div>
        <div class="mt-3">
            <label class="block text-xs font-semibold text-muted-foreground mb-1">Motivo</label>
            <input type="text" name="motivo" maxlength="500" placeholder="Motivo de la licencia (se incluirá en el comunicado)" class="w-full px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('form-licencia').addEventListener('submit', function (e) {
    var studentId = document.getElementById('licencia-student').value;
    this.action = this.action.replace(':student_id', studentId);
});
</script>
@endpush