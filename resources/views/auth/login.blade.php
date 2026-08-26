<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - San Bernardo</title>
</head>
<body>
    <h1>Sistema de Gestión Escolar — U.E. San Bernardo</h1>

    <form method="POST" action="{{ route('login.attempt') }}">
        @csrf

        <label for="correo">Correo electrónico</label>
        <input type="email" id="correo" name="correo" value="{{ old('correo') }}" required autofocus>

        @error('correo')
            <p style="color: red;">{{ $message }}</p>
        @enderror

        <label for="password">Contraseña</label>
        <input type="password" id="password" name="password" required>

        @error('password')
            <p style="color: red;">{{ $message }}</p>
        @enderror

        <button type="submit">Iniciar sesión</button>
    </form>
</body>
</html>
