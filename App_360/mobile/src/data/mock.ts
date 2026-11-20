/**
 * Datos de ejemplo mientras la API v1 no está conectada.
 * Los nombres y el formato (notas sobre 100, Bs, dd/mm/aaaa) son los reales del sistema 360.
 */

export type Estudiante = {
  nombres: string;
  apellidos: string;
  codigo: string;
  curso: string;
  paralelo: string;
  turno: string;
  promedio: number;
  asistenciaPct: number;
  materias: number;
  trimestre: string;
};

export const estudiante: Estudiante = {
  nombres: 'Valeria',
  apellidos: 'Quispe Mamani',
  codigo: '8640',
  curso: '4º de Secundaria',
  paralelo: 'B',
  turno: 'Mañana',
  promedio: 82,
  asistenciaPct: 94,
  materias: 9,
  trimestre: '2do Trimestre',
};

export type Materia = {
  id: string;
  nombre: string;
  docente: string;
  promedio: number;
  nota?: number;
  color: string;
};

export const materias: Materia[] = [
  { id: 'mat', nombre: 'Matemática', docente: 'Prof. Marco Suárez', promedio: 87, color: '#1A8C1A' },
  { id: 'len', nombre: 'Lengua Castellana', docente: 'Prof. Ana Rodríguez', promedio: 76, color: '#2563EB' },
  { id: 'soc', nombre: 'Ciencias Sociales', docente: 'Prof. Luis Fernández', promedio: 64, color: '#D97706' },
  { id: 'nat', nombre: 'Ciencias Naturales', docente: 'Prof. Carla Méndez', promedio: 92, color: '#0E710E' },
  { id: 'ing', nombre: 'Inglés', docente: 'Prof. Diego Ortiz', promedio: 70, color: '#7C3AED' },
  { id: 'efi', nombre: 'Educación Física', docente: 'Prof. Juan Pérez', promedio: 58, color: '#0891B2' },
  { id: 'art', nombre: 'Artes Plásticas', docente: 'Prof. Silvia Rojas', promedio: 81, color: '#DB2777' },
  { id: 'inf', nombre: 'Informática', docente: 'Prof. Rodrigo Vargas', promedio: 51, color: '#4F46E5' },
  { id: 'rel', nombre: 'Religión', docente: 'Prof. Teresa Salinas', promedio: 42, color: '#EA580C' },
];

export type NotaActividad = {
  id: string;
  titulo: string;
  criterio: 'SE' | 'SA' | 'HA' | 'AU';
  peso: number;
  nota: number;
  fecha: string;
};

export const notasMatematica: NotaActividad[] = [
  { id: 'n1', titulo: 'Evaluación diagnóstica', criterio: 'SE', peso: 10, nota: 9, fecha: '12/06/2026' },
  { id: 'n2', titulo: 'Práctica de ecuaciones', criterio: 'SA', peso: 45, nota: 40, fecha: '26/06/2026' },
  { id: 'n3', titulo: 'Proyecto de funciones', criterio: 'HA', peso: 40, nota: 33, fecha: '10/07/2026' },
  { id: 'n4', titulo: 'Participación en clase', criterio: 'AU', peso: 5, nota: 5, fecha: '18/07/2026' },
];

export type RegistroAsistencia = {
  id: string;
  fecha: string;
  materia: string;
  estado: 'PRESENTE' | 'AUSENTE' | 'RETRASO' | 'LICENCIA' | 'RETIRO_JUSTIFICADO';
  justificacion?: string;
};

export const asistencias: RegistroAsistencia[] = [
  { id: 'a1', fecha: '14/07/2026', materia: 'Matemática', estado: 'PRESENTE' },
  { id: 'a2', fecha: '13/07/2026', materia: 'Ciencias Sociales', estado: 'AUSENTE', justificacion: 'Certificado médico' },
  { id: 'a3', fecha: '10/07/2026', materia: 'Lengua Castellana', estado: 'RETRASO' },
  { id: 'a4', fecha: '09/07/2026', materia: 'Inglés', estado: 'PRESENTE' },
  { id: 'a5', fecha: '08/07/2026', materia: 'Educación Física', estado: 'LICENCIA', justificacion: 'Comisión deportiva' },
  { id: 'a6', fecha: '07/07/2026', materia: 'Informática', estado: 'PRESENTE' },
];

export type Comunicado = {
  id: string;
  titulo: string;
  resumen: string;
  cuerpo: string[];
  autor: string;
  fecha: string;
  destacado: boolean;
  leido: boolean;
  categoria: string;
};

export const comunicados: Comunicado[] = [
  {
    id: 'c1',
    titulo: 'Entrega de libretas del segundo trimestre',
    resumen: 'La entrega se realizará el viernes 24 de julio de 08:00 a 12:00.',
    cuerpo: [
      'Estimados estudiantes y familias:',
      'La entrega de libretas correspondiente al segundo trimestre se realizará el viernes 24 de julio, de 08:00 a 12:00, en el aula de cada paralelo.',
      'Recuerden presentar el carnet de estudiante o la cédula del tutor para retirar el documento.',
      'Dirección Académica',
    ],
    autor: 'Dirección Académica',
    fecha: '15/07/2026',
    destacado: true,
    leido: false,
    categoria: 'Académico',
  },
  {
    id: 'c2',
    titulo: 'Olimpiadas de Matemática 2026',
    resumen: 'Inscripciones abiertas hasta el 22 de julio en Secretaría.',
    cuerpo: [
      'Se encuentran abiertas las inscripciones para las Olimpiadas de Matemática 2026.',
      'Podrán participar estudiantes de 1º a 6º de secundaria. La inscripción se realiza en Secretaría hasta el 22 de julio.',
      'Prof. Marco Suárez',
    ],
    autor: 'Prof. Marco Suárez',
    fecha: '12/07/2026',
    destacado: false,
    leido: true,
    categoria: 'Actividades',
  },
  {
    id: 'c3',
    titulo: 'Cambio de horario de Educación Física',
    resumen: 'A partir del lunes, la clase pasa al bloque de 10:05.',
    cuerpo: [
      'A partir del lunes 20 de julio, la clase de Educación Física del 4º B se dictará en el bloque de 10:05 a 11:20.',
      'Presentarse con el uniforme deportivo completo.',
      'Coordinación Académica',
    ],
    autor: 'Coordinación Académica',
    fecha: '09/07/2026',
    destacado: false,
    leido: false,
    categoria: 'Horarios',
  },
];

export type BloqueHorario = {
  id: string;
  inicio: string;
  fin: string;
  materia: string;
  docente: string;
  aula: string;
  dia: 'Lunes' | 'Martes' | 'Miércoles' | 'Jueves' | 'Viernes';
};

export const horario: BloqueHorario[] = [
  { id: 'h1', inicio: '07:45', fin: '09:00', materia: 'Matemática', docente: 'Prof. Marco Suárez', aula: 'Aula 12', dia: 'Lunes' },
  { id: 'h2', inicio: '09:00', fin: '10:05', materia: 'Lengua Castellana', docente: 'Prof. Ana Rodríguez', aula: 'Aula 12', dia: 'Lunes' },
  { id: 'h3', inicio: '10:05', fin: '11:20', materia: 'Ciencias Naturales', docente: 'Prof. Carla Méndez', aula: 'Lab. 2', dia: 'Lunes' },
  { id: 'h4', inicio: '11:20', fin: '12:30', materia: 'Informática', docente: 'Prof. Rodrigo Vargas', aula: 'Sala 3', dia: 'Lunes' },
  { id: 'h5', inicio: '07:45', fin: '09:00', materia: 'Inglés', docente: 'Prof. Diego Ortiz', aula: 'Aula 08', dia: 'Martes' },
  { id: 'h6', inicio: '09:00', fin: '10:05', materia: 'Ciencias Sociales', docente: 'Prof. Luis Fernández', aula: 'Aula 12', dia: 'Martes' },
  { id: 'h7', inicio: '10:05', fin: '11:20', materia: 'Educación Física', docente: 'Prof. Juan Pérez', aula: 'Cancha', dia: 'Martes' },
  { id: 'h8', inicio: '07:45', fin: '09:00', materia: 'Matemática', docente: 'Prof. Marco Suárez', aula: 'Aula 12', dia: 'Miércoles' },
  { id: 'h9', inicio: '09:00', fin: '10:05', materia: 'Artes Plásticas', docente: 'Prof. Silvia Rojas', aula: 'Taller', dia: 'Miércoles' },
  { id: 'h10', inicio: '10:05', fin: '11:20', materia: 'Religión', docente: 'Prof. Teresa Salinas', aula: 'Aula 12', dia: 'Jueves' },
  { id: 'h11', inicio: '07:45', fin: '09:00', materia: 'Lengua Castellana', docente: 'Prof. Ana Rodríguez', aula: 'Aula 12', dia: 'Viernes' },
];

export type Mensualidad = {
  id: string;
  mes: string;
  monto: number;
  estado: 'PAGADO' | 'PENDIENTE' | 'VENCIDO';
  fechaPago?: string;
};

export const mensualidades: Mensualidad[] = [
  { id: 'm1', mes: 'Marzo', monto: 785.5, estado: 'PAGADO', fechaPago: '05/03/2026' },
  { id: 'm2', mes: 'Abril', monto: 785.5, estado: 'PAGADO', fechaPago: '06/04/2026' },
  { id: 'm3', mes: 'Mayo', monto: 785.5, estado: 'PAGADO', fechaPago: '05/05/2026' },
  { id: 'm4', mes: 'Junio', monto: 785.5, estado: 'PAGADO', fechaPago: '08/06/2026' },
  { id: 'm5', mes: 'Julio', monto: 785.5, estado: 'PENDIENTE' },
  { id: 'm6', mes: 'Agosto', monto: 785.5, estado: 'VENCIDO' },
];

export type DocumentoInscripcion = {
  id: string;
  nombre: string;
  estado: 'PENDIENTE' | 'ENTREGADO' | 'RECHAZADO';
};

export const documentosInscripcion: DocumentoInscripcion[] = [
  { id: 'd1', nombre: 'Cédula de identidad (anverso)', estado: 'ENTREGADO' },
  { id: 'd2', nombre: 'Cédula de identidad (reverso)', estado: 'ENTREGADO' },
  { id: 'd3', nombre: 'Certificado de nacimiento', estado: 'ENTREGADO' },
  { id: 'd4', nombre: 'Cédula de los padres', estado: 'PENDIENTE' },
  { id: 'd5', nombre: 'Comprobante de pago', estado: 'PENDIENTE' },
];
