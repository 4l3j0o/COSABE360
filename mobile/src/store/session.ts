import { borrar, guardar, leer } from '@/lib/storage';
import { create } from 'zustand';

import { DEMO_MODE, apiFetch } from '@/lib/api';
import { estudiante } from '@/data/mock';

const TOKEN_KEY = 'csb360.token';

export type Sesion = {
  token: string | null;
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
  signInDemo: () => Promise<void>;
  signOut: () => Promise<void>;
  clearError: () => void;
};

type LoginResponse = {
  token: string;
  user: { nombre: string; email: string; curso?: string; codigo?: string };
};

export const useSesion = create<Sesion & Acciones>((set) => ({
  token: null,
  nombre: '',
  email: '',
  curso: '',
  codigo: '',
  hydrated: false,
  loading: false,
  error: null,

  hydrate: async () => {
    const token = await leer(TOKEN_KEY);
    set({
      token,
      hydrated: true,
      nombre: token ? estudiante.nombres + ' ' + estudiante.apellidos : '',
      curso: token ? `${estudiante.curso} Â· ${estudiante.paralelo}` : '',
      codigo: token ? estudiante.codigo : '',
    });
  },

  signIn: async (email, password) => {
    set({ loading: true, error: null });
    try {
      if (DEMO_MODE) {
        if (!email.trim() || password.length < 4) {
          set({ loading: false, error: 'Ingresa tu correo y una contraseÃ±a de 4 caracteres.' });
          return false;
        }
        await guardar(TOKEN_KEY, 'demo-token');
        set({
          token: 'demo-token',
          email,
          nombre: `${estudiante.nombres} ${estudiante.apellidos}`,
          curso: `${estudiante.curso} Â· ${estudiante.paralelo}`,
          codigo: estudiante.codigo,
          loading: false,
        });
        return true;
      }

      const data = await apiFetch<LoginResponse>('/auth/login', {
        method: 'POST',
        body: { email, password, device_name: 'expo' },
      });
      await guardar(TOKEN_KEY, data.token);
      set({
        token: data.token,
        email: data.user.email,
        nombre: data.user.nombre,
        curso: data.user.curso ?? '',
        codigo: data.user.codigo ?? '',
        loading: false,
      });
      return true;
    } catch (error) {
      set({
        loading: false,
        error: error instanceof Error ? error.message : 'No se pudo iniciar sesiÃ³n.',
      });
      return false;
    }
  },

  signInDemo: async () => {
    await guardar(TOKEN_KEY, 'demo-token');
    set({
      token: 'demo-token',
      email: 'valeria.quispe.8640@cosabe.edu.bo',
      nombre: `${estudiante.nombres} ${estudiante.apellidos}`,
      curso: `${estudiante.curso} Â· ${estudiante.paralelo}`,
      codigo: estudiante.codigo,
    });
  },

  signOut: async () => {
    await borrar(TOKEN_KEY);
    set({ token: null, nombre: '', email: '', curso: '', codigo: '', error: null });
  },

  clearError: () => set({ error: null }),
}));

