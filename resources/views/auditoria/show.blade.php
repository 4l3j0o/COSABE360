@extends('layouts.app')
@section('content')
<h1>Detalle Operación #{{ $operationHistory->id }}</h1>
<p>Usuario: {{ $operationHistory->user->nombre ?? 'N/A' }}</p>
<p>Acción: {{ $operationHistory->accion }}</p>
<p>Módulo: {{ $operationHistory->modulo }}</p>
<p>Modelo: {{ $operationHistory->modelo }} #{{ $operationHistory->modelo_id }}</p>
<p>Detalle: {{ $operationHistory->detalle }}</p>
<p>IP: {{ $operationHistory->ip_address }}</p>
<p>Fecha: {{ $operationHistory->created_at }}</p>
@endsection
