<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Matriz de permisos por rol - HU05 Control de permisos
    |--------------------------------------------------------------------------
    | Define qué módulos/rutas puede acceder cada rol.
    | '*' significa acceso total.
    | Estilos heredados de app.css (variables --primary, --error, etc.)
    */
    'roles' => [
        'Administrador' => ['*'],
        'Director'      => ['dashboard', 'reportes', 'comunicados', 'acceso.denegado'],
        'Secretaría'    => ['dashboard', 'inscripciones', 'pagos', 'students', 'students.eliminados', 'students.restaurar', 'acceso.denegado'],
        'Docente'       => ['dashboard', 'calificaciones', 'asistencia', 'horario', 'diario', 'conducta', 'acceso.denegado'],
        'Estudiante'    => ['dashboard', 'calificaciones', 'horario', 'asistencia', 'diario', 'conducta', 'acceso.denegado'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Módulos del sistema (para ocultar menús/botones)
    |--------------------------------------------------------------------------
    */
    'modulos' => [
        'students'          => ['Administrador', 'Secretaría'],
        'configuracion'     => ['Administrador'],
        'usuarios'          => ['Administrador'],
        'reportes'          => ['Administrador', 'Director'],
        'inscripciones'     => ['Administrador', 'Secretaría'],
        'pagos'             => ['Administrador', 'Secretaría'],
        'calificaciones'    => ['Administrador', 'Docente'],
        'asistencia'        => ['Administrador', 'Docente', 'Estudiante'],
        'diario'            => ['Administrador', 'Director', 'Secretaría', 'Docente', 'Estudiante'],
        'conducta'          => ['Administrador', 'Director', 'Secretaría', 'Regente', 'Docente', 'Estudiante'],
        'comunicados'       => ['Administrador', 'Director'],
    ],
];
