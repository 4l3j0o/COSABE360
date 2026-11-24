import { create } from 'zustand';

export type TipoNotificacion = 'CIRCULAR' | 'TAREA' | 'FERIADO' | 'ACADEMICO' | 'SISTEMA';

export type Notificacion = {
  id: string;
  titulo: string;
  resumen: string;
  cuerpo?: string[];
  autor: string;
  fecha: string;
  hora?: string;
  categoria: TipoNotificacion;
  destacado?: boolean;
  leido: boolean;
  accionUrl?: string;
};

const notificacionesIniciales: Notificacion[] = [
  {
    id: 'notif-1',
    titulo: 'Entrega de libretas y boletines del segundo trimestre',
    resumen: 'La entrega oficial de libretas se realizará el viernes 24 de julio de 08:00 a 12:00 en cada aula.',
    cuerpo: [
      'Estimados estudiantes y tutores:',
      'La entrega de libretas y avances pedagógicos del 2º Trimestre se llevará a cabo el viernes 24 de julio en el aula de cada curso.',
      'Requisito indispensable: presentar el carnet de estudiante o cédula del tutor responsable.',
      'Dirección Académica · Colegio San Bernardo',
    ],
    autor: 'Dirección Académica',
    fecha: '15/07/2026',
    hora: '08:30',
    categoria: 'ACADEMICO',
    destacado: true,
    leido: false,
    accionUrl: '/(tabs)/notas',
  },
  {
    id: 'notif-2',
    titulo: 'Inscripciones abiertas para Olimpiadas de Matemática 2026',
    resumen: 'Representa al Colegio San Bernardo en la fase departamental de Tarija. Inscripción hasta el 22 de julio.',
    cuerpo: [
      'Convocatoria oficial para estudiantes de 1º a 6º de Secundaria.',
      'Temario: Álgebra, Geometría plana y Resolución de problemas lógicos.',
      'Inscripciones sin costo en Secretaría de Dirección con el Prof. Marco Suárez.',
    ],
    autor: 'Prof. Marco Suárez',
    fecha: '12/07/2026',
    hora: '10:15',
    categoria: 'CIRCULAR',
    destacado: true,
    leido: false,
  },
  {
    id: 'notif-3',
    titulo: 'Recordatorio de Tarea: Ejercicios de Geometría Analítica',
    resumen: 'Fecha límite de entrega fijada en MySQL para el 25/11/2026 en Matemática.',
    cuerpo: [
      'Recuerda completar los ejercicios pares del capítulo 4 antes de la clase del viernes.',
      'Se evaluará en el criterio SABER y HACER (Ley 070).',
    ],
    autor: 'Prof. Marco Suárez',
    fecha: '10/07/2026',
    hora: '14:20',
    categoria: 'TAREA',
    destacado: false,
    leido: false,
    accionUrl: '/(tabs)/pendientes',
  },
  {
    id: 'notif-4',
    titulo: 'Próximo Feriado Oficial sincronizado desde Nager.Date',
    resumen: 'El calendario nacional de Bolivia tiene feriados próximos disponibles para guardar en tu base de datos.',
    cuerpo: [
      'Integración en tiempo real con la API internacional Nager.Date.',
      'Podés presionar «Guardar en BD» desde la pestaña Feriados para añadirlo a tus pendientes de MySQL.',
    ],
    autor: 'Sistema CSB 360',
    fecha: '08/07/2026',
    hora: '09:00',
    categoria: 'FERIADO',
    destacado: false,
    leido: true,
    accionUrl: '/(tabs)/feriados',
  },
  {
    id: 'notif-5',
    titulo: 'Actualización de horario: Educación Física',
    resumen: 'A partir del próximo lunes, la clase pasa al bloque de 10:05 a 11:20 con uniforme oficial.',
    cuerpo: [
      'Se informa a los estudiantes que por motivo de remodelación de la cancha techada, la clase de Ed. Física se traslada al tercer bloque.',
      'Traer toalla personal y termo de agua.',
    ],
    autor: 'Coordinación Académica',
    fecha: '05/07/2026',
    hora: '11:45',
    categoria: 'CIRCULAR',
    destacado: false,
    leido: true,
  },
];

interface NotificacionesState {
  notificaciones: Notificacion[];
  marcarLeida: (id: string) => void;
  marcarTodasLeidas: () => void;
  alternarLectura: (id: string) => void;
  eliminar: (id: string) => void;
  agregar: (item: Omit<Notificacion, 'id' | 'fecha' | 'leido'>) => void;
  conteoNoLeidos: () => number;
}

export const useNotificaciones = create<NotificacionesState>((set, get) => ({
  notificaciones: notificacionesIniciales,

  marcarLeida: (id: string) =>
    set((state) => ({
      notificaciones: state.notificaciones.map((n) =>
        n.id === id ? { ...n, leido: true } : n
      ),
    })),

  marcarTodasLeidas: () =>
    set((state) => ({
      notificaciones: state.notificaciones.map((n) => ({ ...n, leido: true })),
    })),

  alternarLectura: (id: string) =>
    set((state) => ({
      notificaciones: state.notificaciones.map((n) =>
        n.id === id ? { ...n, leido: !n.leido } : n
      ),
    })),

  eliminar: (id: string) =>
    set((state) => ({
      notificaciones: state.notificaciones.filter((n) => n.id !== id),
    })),

  agregar: (item) =>
    set((state) => {
      const nueva: Notificacion = {
        ...item,
        id: `notif-${Date.now()}`,
        fecha: new Date().toLocaleDateString('es-BO'),
        hora: new Date().toLocaleTimeString('es-BO', { hour: '2-digit', minute: '2-digit' }),
        leido: false,
      };
      return { notificaciones: [nueva, ...state.notificaciones] };
    }),

  conteoNoLeidos: () => get().notificaciones.filter((n) => !n.leido).length,
}));
