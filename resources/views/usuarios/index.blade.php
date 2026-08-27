<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Usuarios y Roles</title>
</head>
<body>
    <h1>Gestión de Roles de Usuarios</h1>

    @if (session('success'))
        <p style="color: green;">{{ session('success') }}</p>
    @endif

    <table border="1" cellpadding="8">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Correo</th>
                <th>Rol actual</th>
                <th>Cambiar rol</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($usuarios as $usuario)
                <tr>
                    <td>{{ $usuario->nombre }} {{ $usuario->apellido }}</td>
                    <td>{{ $usuario->correo }}</td>
                    <td>{{ $usuario->rol->nombre ?? 'Sin rol' }}</td>
                    <td>
                        <form action="{{ route('usuarios.update', $usuario->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <select name="rol_id" onchange="this.form.submit()">
                                @foreach ($roles as $rol)
                                    <option value="{{ $rol->id }}" @selected($usuario->rol_id == $rol->id)>
                                        {{ $rol->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>