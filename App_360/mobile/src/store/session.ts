import { borrar, guardar, leer } from '@/lib/storage';
import { create } from 'zustand';

import { iniciarSesion } from '@/data/datos';

const TOKEN_KEY = 'csb360.token';
const USER_KEY = 'csb360.usuario';

export type UsuarioSesion = {
  id: number;
  nombre: string;
  email: string;
  curso: string;
  codigo: string;
};

export type Sesion = {
  token: string | null;
  usuarioId: number | null;
  nombre: string;
  email: string;
  curso: string;
  codigo: string;
  hydrated: boolean;
  loading: boolean;
  error: string | null;
};

type Acciones = {
  hydrate: () => Promise<void>;
  signIn: (email: string, password: string) => Promise<boolean>;
  signInDemo: () => Promise<boolean>;
  signOut: () => Promise<void>;
  clearError: () => void;
};

type LoginResponse = {
  token: string;
  user: UsuarioSesion;
};

export const useSesion = create<Sesion & Acciones>((set, get) => ({
  token: null,
  usuarioId: null,
  nombre: '',
  email: '',
  curso: '',
  codigo: '',
  hydrated: false,
  loading: false,
  error: null,

  hydrate: async () => {
    const token = await leer(TOKEN_KEY);
    const guardado = await leer(USER_KEY);
    let usuario: UsuarioSesion | null = null;

    try {
      usuario = guardado ? (JSON.parse(guardado) as UsuarioSesion) : null;
    } catch {
      usuario = null;
    }

    if (usuario && usuario.id) {
      set({
        token: token || 'token-activo',
        usuarioId: usuario.id,
        nombre: usuario.nombre,
        email: usuario.email,
        curso: usuario.curso,
        codigo: usuario.codigo,
        hydrated: true,
      });
      return;
    }

    // Usuario por defecto para desarrollo y demostración continua (Valeria Quispe Mamani)
    set({
      token: 'demo-token',
      usuarioId: 1,
      nombre: 'Valeria Quispe Mamani',
      email: 'valeria.quispe.8640@cosabe.edu.bo',
      curso: '4º de Secundaria · B',
      codigo: '8640',
      hydrated: true,
    });
  },

  signIn: async (email, password) => {
    set({ loading: true, error: null });
    try {
      const data = await iniciarSesion(email.trim(), password);
      await guardar(TOKEN_KEY, data.token);
      await guardar(USER_KEY, JSON.stringify(data.user));
      set({
        token: data.token,
        usuarioId: data.user.id,
        email: data.user.email,
        nombre: data.user.nombre,
        curso: data.user.curso,
        codigo: data.user.codigo,
        loading: false,
      });
      return true;
    } catch (error) {
      set({
        loading: false,
        error: error instanceof Error ? error.message : 'No se pudo iniciar sesión.',
      });
      return false;
    }
  },

  // Acceso de prueba: usa el usuario cargado en la tabla usuarios de MySQL.
  signInDemo: async () => get().signIn('valeria.quispe.8640@cosabe.edu.bo', 'CSB2025'),

  signOut: async () => {
    await borrar(TOKEN_KEY);
    await borrar(USER_KEY);
    set({
      token: null,
      usuarioId: null,
      nombre: '',
      email: '',
      curso: '',
      codigo: '',
      error: null,
    });
  },

  clearError: () => set({ error: null }),
}));
