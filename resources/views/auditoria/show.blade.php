@extends('layouts.app')
@section('titulo', 'Detalle de Operación')

@section('sidebar')
    @include('dashboard.partials._sidebar-admin')
@endsection

@section('contenido')
    <div class="max-w-3xl space-y-6">
        <div>
            @include('layouts._volver', ['href' => route('auditoria.index'), 'texto' => 'Volver al historial'])
            <h1 class="text-xl font-bold text-foreground mt-1">Detalle de Operación #{{ $operationHistory->id }}</h1>
        </div>

        <div class="bg-card border border-border rounded-xl overflow-hidden">
            <div class="px-5 py-3 border-b border-border bg-primary/10">
                <h3 class="text-sm font-semibold text-primary">Información de la operación</h3>
            </div>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 p-5 text-sm">
                <div>
                    <dt class="text-muted-foreground">Usuario</dt>
                    <dd class="font-medium">{{ $operationHistory->user->nombre ?? 'Sistema' }} {{ $operationHistory->user->apellido ?? '' }}</dd>
                    @if($operationHistory->user?->rol)
                        <dd class="text-xs text-muted-foreground">{{ $operationHistory->user->rol->nombre }}</dd>
                    @endif
                </div>
                <div>
                    <dt class="text-muted-foreground">Fecha y hora</dt>
                    <dd class="font-medium">{{ $operationHistory->created_at->format('d/m/Y H:i') }}</dd>
                </div>
                <div>
                    <dt class="text-muted-foreground">Acción</dt>
                    <dd class="font-medium">{{ $operationHistory->accion }}</dd>
                </div>
                <div>
                    <dt class="text-muted-foreground">Módulo</dt>
                    <dd class="font-medium">{{ $operationHistory->modulo }}</dd>
                </div>
                @if($operationHistory->modelo)
                    <div>
                        <dt class="text-muted-foreground">Entidad</dt>
                        <dd class="font-medium">{{ class_basename($operationHistory->modelo) }} #{{ $operationHistory->modelo_id }}</dd>
                    </div>
                @endif
                <div>
                    <dt class="text-muted-foreground">Dirección IP</dt>
                    <dd class="font-medium">{{ $operationHistory->ip_address ?? '—' }}</dd>
                </div>
                @if($operationHistory->detalle)
                    <div class="sm:col-span-2">
                        <dt class="text-muted-foreground">Detalle</dt>
                        <dd class="mt-1 p-3 bg-muted/50 border border-border rounded-lg text-foreground">{{ $operationHistory->detalle }}</dd>
                    </div>
                @endif
            </dl>
        </div>

        <div class="flex items-start gap-3 bg-warning/10 border border-warning/20 text-warning rounded-lg px-4 py-3 text-sm">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <span>El historial de auditoría es de solo lectura: no puede ser editado ni eliminado por ningún usuario.</span>
        </div>
    </div>
@endsection