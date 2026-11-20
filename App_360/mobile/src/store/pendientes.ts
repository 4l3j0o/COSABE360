import { create } from 'zustand';

export type Prioridad = 'alta' | 'media' | 'baja';

export type Pendiente = {
  id: string;
  titulo: string;
  materia: string;
  fecha: string; // dd/mm/aaaa
  prioridad: Prioridad;
  hecho: boolean;
};

const iniciales: Pendiente[] = [
  {
    id: 'p1',
    titulo: 'Resolver ejercicios de ecuaciones',
    materia: 'Matemática',
    fecha: '20/11/2026',
    prioridad: 'alta',
    hecho: false,
  },
  {
    id: 'p2',
    titulo: 'Leer capítulo 4 y hacer resumen',
    materia: 'Lengua Castellana',
    fecha: '21/11/2026',
    prioridad: 'media',
    hecho: false,
  },
  {
    id: 'p3',
    titulo: 'Preparar exposición de ecosistemas',
    materia: 'Ciencias Naturales',
    fecha: '18/11/2026',
    prioridad: 'alta',
    hecho: true,
  },
];

type State = {
  items: Pendiente[];
  agregar: (p: Omit<Pendiente, 'id' | 'hecho'>) => void;
  actualizar: (id: string, cambios: Omit<Pendiente, 'id'>) => void;
  eliminar: (id: string) => void;
  alternar: (id: string) => void;
};

export const usePendientes = create<State>((set) => ({
  items: iniciales,
  agregar: (p) =>
    set((s) => ({
      items: [
        ...s.items,
        { ...p, id: `p${Date.now()}`, hecho: false },
      ],
    })),
  actualizar: (id, cambios) =>
    set((s) => ({
      items: s.items.map((item) => (item.id === id ? { ...cambios, id } : item)),
    })),
  eliminar: (id) => set((s) => ({ items: s.items.filter((i) => i.id !== id) })),
  alternar: (id) =>
    set((s) => ({
      items: s.items.map((i) => (i.id === id ? { ...i, hecho: !i.hecho } : i)),
    })),
}));
