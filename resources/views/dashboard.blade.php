<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - San Bernardo</title>
</head>
<body>
    <h1>Dashboard</h1>

    <p>Bienvenido, {{ auth()->user()->nombre }} {{ auth()->user()->apellido }}</p>
    <p>Rol: {{ auth()->user()->rol->nombre }}</p>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit">Cerrar sesión</button>
    </form>
</body>
</html>
