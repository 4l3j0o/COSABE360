<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentRudeRecord extends Model
{
    public const ESTADOS = ['PENDIENTE', 'ENVIADO', 'APROBADO', 'RECHAZADO'];

    public const TURNOS = ['MANANA', 'TARDE', 'NOCTURNO'];

    public const ZONAS = ['URBANA', 'RURAL', 'PERIFERIA'];

    /** Código SIE de la Unidad Educativa (configuración del colegio). */
    public const SIE_UE = '81730184';

    public const DOCUMENTOS = [
        'CI' => 'Cédula de Identidad',
        'CERTIFICADO_NACIMIENTO' => 'Certificado de Nacimiento',
        'DNI' => 'Documento Nacional de Identidad (DNI)',
        'PASAPORTE' => 'Pasaporte',
        'DECLARACION_JURADA' => 'Declaración Jurada de Buena Fe',
    ];

    public const SEXOS = ['MASCULINO', 'FEMENINO'];

    public const IDIOMAS = [
        'Castellano', 'Quechua', 'Aymara', 'Guaraní', 'Mojeño', 'Chiquitano',
        'Araona', 'Esse Ejja', 'Uru-Chipaya', 'Movima', 'Yuracaré', 'Weenhayek',
        'Lengua de Señas Boliviana (LSB)', 'Otro',
    ];

    public const AUTOIDENTIFICACIONES = [
        'Nación o Pueblo Indígena Originario', 'Afroboliviano', 'Campesino',
        'Intercultural', 'Ninguna',
    ];

    public const SEGUROS_SALUD = [
        'SUS', 'Otra caja o seguro de salud', 'Establecimiento de salud público',
        'Establecimiento de salud privado', 'No, no accede',
    ];

    public const BANOS = [
        'Propio', 'Cedido por servicios de agua', 'Letrina',
        'Alcantarillado', 'No tiene',
    ];

    public const TIPOS_VIVIENDA = [
        'Propia', 'Alquilada', 'Anticrético', 'Contrato Mixto', 'Cedida', 'Otra',
    ];

    public const FRECUENCIAS_INTERNET = [
        'Diariamente', 'Más de una vez por semana', 'Una vez por semana', 'Una vez al mes',
    ];

    public const ACTIVIDADES_LABORALES = [
        'Vendedor(a)', 'Dependiente', 'Trabajador(a) del hogar', 'Ayudante del hogar',
        'Ayuda en comercio o ventas', 'Cuidador(a) de niños', 'Peón / Agricultura',
        'Ganadería', 'Pesca', 'Minería', 'Construcción', 'Zafra', 'Transporte',
        'Mecánica', 'Otra',
    ];

    public const FRECUENCIAS_LABORAL = [
        'Todos los días', 'Días hábiles', 'Fines de semana', 'Días festivos',
        'Eventual / Esporádico', 'En vacaciones',
    ];

    public const MEDIOS_TRANSPORTE = [
        'A pie', 'Transporte público', 'Bicicleta', 'Vehículo propio', 'Otro',
    ];

    public const TIEMPOS_TRANSPORTE = [
        'Menos de media hora', 'Entre media hora y una hora', 'Entre una y dos horas', 'Más de dos horas',
    ];

    public const RAZONES_ABANDONO = [
        'Trabajo', 'Embarazo o paternidad', 'Discapacidad', 'Enfermedad',
        'Violencia física', 'Violencia psicológica', 'Violencia sexual',
        'Cambio de domicilio', 'La UE está lejos', 'Falta de dinero',
        'Falta de interés', 'Otro',
    ];

    public const TIPOS_DISCAPACIDAD = [
        'Auditiva', 'Visual', 'Intelectual', 'Física / Motora', 'Psíquica-Mental', 'Múltiple',
    ];

    public const GRADOS_DISCAPACIDAD = ['Leve', 'Moderado', 'Grave', 'Muy grave'];

    public const DIFICULTADES_APRENDIZAJE = ['Lectura y Escritura', 'Cálculo Matemático'];

    public const CONVIVENCIA = [
        'Padre y Madre', 'Solo Padre', 'Solo Madre', 'Tutor(a)', 'Solo(a)',
        'En tutela extraordinaria',
    ];

    protected $fillable = [
        'student_id',
        'codigo_rude',
        'codigo_sie_ue',
        'fecha_registro',
        'ue_localidad',
        'ue_nombres',
        'ue_apellido_paterno',
        'ue_apellido_materno',
        'ue_pais',
        'ue_departamento',
        'ue_provincia',
        'ue_municipio',
        'ue_comunidad',
        'ue_zona',
        'ue_calle',
        'ue_nro_vivienda',
        'ue_telefono',
        'ue_celular',
        'nro_registro_discapacidad',
        'dir_departamento',
        'dir_provincia',
        'dir_municipio',
        'dir_comunidad',
        'dir_zona',
        'dir_calle',
        'dir_nro_vivienda',
        'dir_telefono',
        'dir_celular',
        'idioma_aprendio',
        'idiomas_uso_frecuente',
        'autoidentificacion_cultural',
        'salud_centro_cercano',
        'salud_atendido_anio',
        'salud_veces',
        'salud_seguro',
        'serv_agua_red',
        'serv_bano',
        'serv_alcantarillado',
        'serv_electricidad',
        'serv_recojo_basura',
        'serv_vivienda_tipo',
        'internet_lugares',
        'internet_frecuencia',
        'laboral_trabajo',
        'laboral_actividad',
        'laboral_turnos',
        'laboral_frecuencia',
        'laboral_recibio_pago',
        'transporte_medio',
        'transporte_tiempo',
        'abandono_escolar',
        'abandono_razones',
        'discapacidad_tipos',
        'discapacidad_grado',
        'dificultad_aprendizaje',
        'convive_con',
        'turno',
        'transporte',
        'zona_residencial',
        'procedencia_ie',
        'nuevo_ingreso',
        'idioma_originario',
        'salud_discapacidad',
        'oficialia_nacimiento',
        'libro_nacimiento',
        'partida_nacimiento',
        'folio_nacimiento',
        'anio_nacimiento',
        'estado',
        'fecha_envio',
        'fecha_respuesta',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'transporte' => 'boolean',
            'nuevo_ingreso' => 'boolean',
            'fecha_envio' => 'date',
            'fecha_respuesta' => 'date',
            'fecha_registro' => 'date',
            'idiomas_uso_frecuente' => 'array',
            'internet_lugares' => 'array',
            'laboral_turnos' => 'array',
            'abandono_razones' => 'array',
            'discapacidad_tipos' => 'array',
            'dificultad_aprendizaje' => 'array',
            'convive_con' => 'array',
            'salud_centro_cercano' => 'boolean',
            'salud_atendido_anio' => 'boolean',
            'serv_agua_red' => 'boolean',
            'serv_alcantarillado' => 'boolean',
            'serv_electricidad' => 'boolean',
            'serv_recojo_basura' => 'boolean',
            'laboral_trabajo' => 'boolean',
            'laboral_recibio_pago' => 'boolean',
            'abandono_escolar' => 'boolean',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function estaPendiente(): bool
    {
        return $this->estado === 'PENDIENTE';
    }

    public function estaEnviado(): bool
    {
        return $this->estado === 'ENVIADO';
    }

    /**
     * Devuelve un array de selección múltiple como texto legible (", " separado).
     */
    public static function listar(array|string|null $valores): string
    {
        if ($valores === null) {
            return '';
        }
        if (is_string($valores)) {
            return $valores;
        }

        return implode(', ', array_filter($valores));
    }

    /**
     * Valida CI boliviano: 7-8 dígitos, complemento opcional 1-2 caracteres alfanum.
     * Retorna true si es válido.
     */
    public static function validarCI(string $numero, ?string $complemento = null): bool
    {
        if (! preg_match('/^\d{7,8}$/', $numero)) {
            return false;
        }
        if ($complemento !== null && $complemento !== '' && ! preg_match('/^[A-Za-z0-9]{1,5}$/', $complemento)) {
            return false;
        }

        return true;
    }

    /**
     * Valida certificado de nacimiento: si uno de los campos está presente, todos deben estar.
     */
    public static function validarCertificado(array $datos): bool
    {
        $campos = ['oficialia_nacimiento', 'libro_nacimiento', 'partida_nacimiento', 'folio_nacimiento', 'anio_nacimiento'];
        $presentes = array_filter($datos, fn ($v) => $v !== null && $v !== '');
        $count = count(array_intersect_key(array_flip($campos), $datos)) > 0 ? count(array_filter(array_intersect_key($datos, array_flip($campos)), fn ($v) => $v !== null && $v !== '')) : 0;

        // Si ninguno está, es válido (no obligatorio)
        // Si alguno está, todos deben estar
        $filled = 0;
        foreach ($campos as $c) {
            if (! empty($datos[$c])) {
                $filled++;
            }
        }
        if ($filled === 0) {
            return true;
        }

        return $filled === count($campos);
    }

    /**
     * Valida código SIE UE: 8 dígitos (configurable, por defecto 8).
     */
    public static function validarCodigoSIE(string $codigo): bool
    {
        return preg_match('/^\d{8}$/', $codigo) === 1;
    }
}
