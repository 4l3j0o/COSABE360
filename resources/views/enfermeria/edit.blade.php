@extends('layouts.app')
@section('titulo', 'Editar atención de Enfermería')

@section('sidebar')
    @include('dashboard.partials._sidebar-by-rol')
@endsection

@section('contenido')
    <div class="mb-4">
        @include('layouts._volver', ['href' => route('enfermeria.show', $atencion), 'texto' => 'Volver a la atención'])
    </div>

    <x-layout.page-header title="Editar atención" :description="'Estudiante: '.trim(($atencion->student?->apellidos ?? '').' '.($atencion->student?->nombres ?? ''))" />

    <form method="POST" action="{{ route('enfermeria.update', $atencion) }}" class="bg-card border border-border rounded-xl p-6 space-y-5">
        @csrf
        @method('PUT')
        <input type="hidden" name="student_id" value="{{ $atencion->student_id }}">

        @include('enfermeria._fields', ['atencion' => $atencion, 'motivos' => $motivos])

        <div class="flex items-center justify-end gap-3 pt-2 border-t border-border">
            <a href="{{ route('enfermeria.show', $atencion) }}" class="px-4 py-2 rounded-lg text-sm font-medium border border-border hover:bg-muted transition-colors">Cancelar</a>
            <button type="submit" class="bg-primary text-primary-foreground px-5 py-2 rounded-lg text-sm font-medium hover:opacity-90 transition-opacity">Guardar cambios</button>
        </div>
    </form>
@endsection
