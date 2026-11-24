import { create } from 'zustand';

import * as datos from '@/data/datos';
import { useSesion } from '@/store/session';

export type Prioridad = 'alta' | 'media' | 'baja';

export type Pendiente = {
  id: number;
  titulo: string;
  materia: string;
  fecha: string; // dd/mm/aaaa
  prioridad: Prioridad;
  hecho: boolean;
};

export type NuevoPendiente = Omit<Pendiente, 'id' | 'hecho'>;

type State = {
  items: Pendiente[];
  cargando: boolean;
  guardando: boolean;
  error: string | null;
  cargar: () => Promise<void>;
  agregar: (p: NuevoPendiente) => Promise<boolean>;
  actualizar: (id: number, cambios: Omit<Pendiente, 'id'>) => Promise<boolean>;
  eliminar: (id: number) => Promise<boolean>;
  alternar: (id: number) => Promise<void>;
};

function mensajeDe(error: unknown) {
  return error instanceof Error ? error.message : 'No se pudo conectar con la base de datos.';
}

export const usePendientes = create<State>((set, get) => ({
  items: [],
  cargando: false,
  guardando: false,
  error: null,

  // SELECT: vuelve a leer la tabla para refrescar la pantalla.
  cargar: async () => {
    const usuarioId = useSesion.getState().usuarioId || 1;

    set({ cargando: true, error: null });
    try {
      const items = (await datos.obtener(usuarioId)) as Pendiente[];
      set({ items, cargando: false });
    } catch (error) {
      set({ cargando: false, error: mensajeDe(error) });
    }
  },

  // INSERT y luego recarga de la tabla.
  agregar: async (p) => {
    const usuarioId = useSesion.getState().usuarioId || 1;

    set({ guardando: true, error: null });
    try {
      await datos.agregar(usuarioId, p);
      await get().cargar();
      set({ guardando: false });
      return true;
    } catch (error) {
      set({ guardando: false, error: mensajeDe(error) });
      return false;
    }
  },

  // UPDATE ... WHERE y luego recarga de la tabla.
  actualizar: async (id, cambios) => {
    const usuarioId = useSesion.getState().usuarioId || 1;

    set({ guardando: true, error: null });
    try {
      await datos.modificar(usuarioId, id, cambios);
      await get().cargar();
      set({ guardando: false });
      return true;
    } catch (error) {
      set({ guardando: false, error: mensajeDe(error) });
      return false;
    }
  },

  // DELETE ... WHERE y luego recarga de la tabla.
  eliminar: async (id) => {
    const usuarioId = useSesion.getState().usuarioId || 1;

    set({ guardando: true, error: null });
    try {
      await datos.eliminar(usuarioId, id);
      await get().cargar();
      set({ guardando: false });
      return true;
    } catch (error) {
      set({ guardando: false, error: mensajeDe(error) });
      return false;
    }
  },

  // UPDATE del campo hecho y luego recarga.
  alternar: async (id) => {
    const usuarioId = useSesion.getState().usuarioId || 1;
    const actual = get().items.find((item) => item.id === id);
    if (!actual) return;

    set({ error: null });
    try {
      await datos.alternar(usuarioId, id, !actual.hecho);
      await get().cargar();
    } catch (error) {
      set({ error: mensajeDe(error) });
    }
  },
}));
