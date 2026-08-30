@extends('layouts.app')
@section('content')
<h1>{{ $announcement->titulo }}</h1>
<p>{{ $announcement->contenido }}</p>
<p>Estado: {{ mb_strtolower($announcement->estado) }}</p>
@endsection
