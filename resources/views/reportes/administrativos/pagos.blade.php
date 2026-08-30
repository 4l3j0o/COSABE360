@extends('layouts.app')
@section('content')
<h1>Reporte Pagos y Deudas</h1>
<p>Deuda total: {{ $deudaTotal }}</p>
@foreach($pagos as $p)
<div>{{ $p->registration->enrollment->student->nombres ?? '' }} - {{ $p->mes }}/{{ $p->anio }} - {{ mb_strtolower($p->estado) }} - {{ $p->monto_esperado }}</div>
@endforeach
{{ $pagos->links() }}
@endsection
