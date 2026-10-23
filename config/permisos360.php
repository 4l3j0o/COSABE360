<?php

/*
|--------------------------------------------------------------------------
| Catálogo de permisos de 360 (HU-85)
|--------------------------------------------------------------------------
| Subconjunto de los permisos históricos del sistema anterior, adaptado a
| los módulos que realmente existen en 360. Las reglas de propiedad
| (propio/curso/nivel/tutor) siguen viviendo en AccessScope y Policies.
*/

return [
    'permisos' => [
        'dashboard.ver' => 'Ver el panel principal',

        'usuarios.ver' => 'Ver usuarios',
        'usuarios.gestionar' => 'Crear y editar usuarios',
        'roles.gestionar' => 'Administrar roles y permisos',
        'auditoria.ver' => 'Ver historial de operaciones',

        'configuracion.ver' => 'Ver configuración del sistema',
        'configuracion.gestionar' => 'Modificar configuración del sistema',
        'niveles.gestionar' => 'Administrar niveles',
        'grados.gestionar' => 'Administrar grados',
        'paralelos.ver' => 'Ver paralelos',
        'paralelos.gestionar' => 'Administrar paralelos',
        'materias.ver' => 'Ver materias',
        'materias.gestionar' => 'Administrar materias',
        'planes.ver' => 'Ver planes de estudio',
        'planes.gestionar' => 'Administrar planes de estudio',
        'horarios.ver' => 'Ver horarios',
        'horarios.gestionar' => 'Administrar horarios',
        'docentes.ver' => 'Ver docentes',
        'docentes.gestionar' => 'Administrar docentes',

        'estudiantes.ver' => 'Ver estudiantes',
        'estudiantes.ver.propio' => 'Ver la propia ficha de estudiante',
        'estudiantes.ver.nivel' => 'Ver estudiantes del nivel a cargo',
        'estudiantes.ver.curso' => 'Ver estudiantes de los cursos asignados',
        'estudiantes.crear' => 'Registrar estudiantes',
        'estudiantes.editar' => 'Editar estudiantes',
        'estudiantes.eliminar' => 'Eliminar estudiantes',
        'estudiantes.restaurar' => 'Restaurar estudiantes eliminados',
        'inscripciones.ver' => 'Ver inscripciones',
        'inscripciones.gestionar' => 'Gestionar inscripciones',
        'matriculas.ver' => 'Ver matrículas',
        'matriculas.gestionar' => 'Gestionar matrículas',
        'expediente.ver' => 'Ver expedientes de estudiantes',
        'expediente.gestionar' => 'Gestionar expedientes de estudiantes',
        'rude.ver' => 'Ver RUDE',
        'rude.editar' => 'Editar RUDE',
        'rude.imprimir' => 'Imprimir RUDE',

        'pagos.ver' => 'Ver pagos y estados de cuenta',
        'pagos.registrar' => 'Registrar pagos',
        'pagos.aprobar' => 'Aprobar pagos en espera',
        'pagos.exportar' => 'Exportar pagos',
        'facturas.ver' => 'Ver facturas electrónicas',
        'facturas.gestionar' => 'Emitir y administrar facturas',
        'pagos.estado.propio' => 'Ver el propio estado de cuenta',

        'asistencia.ver' => 'Ver asistencias',
        'asistencia.registrar' => 'Registrar asistencias',
        'asistencia.corregir' => 'Corregir asistencias',
        'asistencia.exportar' => 'Exportar asistencias',
        'licencias.ver' => 'Ver licencias',
        'licencias.crear' => 'Registrar licencias',
        'licencias.autorizar' => 'Autorizar licencias',
        'licencias.ver.propia' => 'Ver las propias licencias',

        'evaluaciones.ver' => 'Ver evaluaciones y notas',
        'evaluaciones.registrar' => 'Registrar evaluaciones y notas',
        'evaluaciones.exportar' => 'Exportar evaluaciones',
        'boletines.ver' => 'Ver boletines',
        'boletines.publicar' => 'Publicar boletines',
        'boletines.ver.propio' => 'Ver el propio boletín',
        'historial.ver' => 'Ver historial académico',
        'historial.ver.propio' => 'Ver el propio historial académico',

        'conducta.ver' => 'Ver reportes de conducta',
        'conducta.registrar' => 'Registrar reportes de conducta',
        'conducta.exportar' => 'Exportar reportes de conducta',

        'diario.ver' => 'Ver el diario de aula',
        'diario.crear' => 'Crear bitácoras del diario de aula',
        'diario.editar' => 'Editar bitácoras del diario de aula',
        'diario.eliminar' => 'Eliminar bitácoras del diario de aula',
        'diario.ver.propio' => 'Ver el diario de aula del propio curso',

        'reportes.ver' => 'Ver reportes',
        'reportes.exportar' => 'Exportar reportes',
        'reportes.administrativos.ver' => 'Ver reportes administrativos',
        'reportes.academicos.ver' => 'Ver reportes académicos',
        'reportes.asistencia.ver' => 'Ver reportes de asistencia',
        'reportes.evaluacion.ver' => 'Ver reportes de evaluación',

        'comunicados.ver' => 'Ver comunicados',
        'comunicados.crear' => 'Crear comunicados',
        'comunicados.gestionar' => 'Editar y publicar comunicados',
    ],

    'roles' => [
        'Administrador' => ['*'],

        'Director' => [
            'dashboard.ver',
            'estudiantes.ver', 'estudiantes.ver.nivel',
            'paralelos.ver', 'materias.ver', 'docentes.ver', 'horarios.ver', 'horarios.gestionar',
            'inscripciones.ver', 'inscripciones.gestionar', 'matriculas.ver', 'matriculas.gestionar',
            'expediente.ver', 'expediente.gestionar', 'rude.ver',
            'pagos.ver', 'pagos.registrar', 'pagos.aprobar',
            'facturas.ver', 'facturas.gestionar',
            'asistencia.ver', 'licencias.ver',
            'evaluaciones.ver', 'boletines.ver', 'historial.ver',
            'conducta.ver', 'conducta.registrar', 'conducta.exportar',
            'diario.ver', 'diario.crear', 'diario.editar', 'diario.eliminar',
            'reportes.ver', 'reportes.exportar',
            'reportes.administrativos.ver', 'reportes.academicos.ver',
            'reportes.asistencia.ver', 'reportes.evaluacion.ver',
            'comunicados.ver', 'comunicados.crear', 'comunicados.gestionar',
        ],

        'Secretaría' => [
            'dashboard.ver',
            'estudiantes.ver', 'estudiantes.crear', 'estudiantes.editar',
            'inscripciones.ver', 'inscripciones.gestionar',
            'matriculas.ver', 'matriculas.gestionar',
            'expediente.ver', 'expediente.gestionar',
            'rude.ver', 'rude.editar', 'rude.imprimir',
            'pagos.ver', 'pagos.registrar', 'pagos.aprobar', 'pagos.exportar',
            'facturas.ver', 'facturas.gestionar',
            'horarios.ver', 'asistencia.ver', 'licencias.ver', 'boletines.ver',
            'reportes.ver', 'reportes.exportar', 'reportes.administrativos.ver',
            'comunicados.ver',
        ],

        'Docente' => [
            'dashboard.ver',
            'estudiantes.ver.curso',
            'horarios.ver', 'horarios.gestionar',
            'asistencia.ver', 'asistencia.registrar', 'asistencia.corregir',
            'licencias.ver',
            'evaluaciones.ver', 'evaluaciones.registrar', 'evaluaciones.exportar',
            'boletines.ver', 'historial.ver',
            'conducta.ver', 'conducta.registrar',
            'diario.ver', 'diario.crear', 'diario.editar', 'diario.eliminar',
            'reportes.ver', 'reportes.academicos.ver',
            'reportes.asistencia.ver', 'reportes.evaluacion.ver',
            'comunicados.ver', 'comunicados.crear',
        ],

        'Estudiante' => [
            'dashboard.ver',
            'estudiantes.ver.propio',
            'horarios.ver',
            'asistencia.ver',
            'licencias.ver.propia',
            'evaluaciones.ver',
            'boletines.ver.propio',
            'historial.ver.propio',
            'conducta.ver',
            'diario.ver.propio',
            'pagos.estado.propio',
            'comunicados.ver',
        ],

        'Regente' => [
            'dashboard.ver',
            'estudiantes.ver',
            'horarios.ver',
            'asistencia.ver', 'asistencia.registrar', 'asistencia.corregir', 'asistencia.exportar',
            'licencias.ver', 'licencias.crear', 'licencias.autorizar',
            'conducta.ver', 'conducta.registrar', 'conducta.exportar',
            'diario.ver',
            'reportes.ver', 'reportes.asistencia.ver',
            'comunicados.ver', 'comunicados.crear', 'comunicados.gestionar',
        ],
    ],

    /*
    | Mapa de compatibilidad para las comprobaciones antiguas por módulo.
    | canAccess('students') consultará estudiantes.ver, etc.
    */
    'modulos' => [
        'dashboard' => 'dashboard.ver',
        'students' => 'estudiantes.ver',
        'students.eliminados' => 'estudiantes.eliminar',
        'students.restaurar' => 'estudiantes.restaurar',
        'inscripciones' => 'inscripciones.ver',
        'pagos' => 'pagos.ver',
        'calificaciones' => 'evaluaciones.ver',
        'asistencia' => 'asistencia.ver',
        'horario' => 'horarios.ver',
        'diario' => 'diario.ver',
        'conducta' => 'conducta.ver',
        'comunicados' => 'comunicados.ver',
        'reportes' => 'reportes.ver',
        'configuracion' => 'configuracion.ver',
        'usuarios' => 'usuarios.ver',
    ],
];
