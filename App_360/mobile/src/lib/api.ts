/**
 * Cliente HTTP de la app.
 *
 * La app Expo no puede conectarse directo a MySQL. La base local de XAMPP se
 * expone mediante la API de `App_360/api`, y este cliente consume esa API.
 *
 * La URL se toma de EXPO_PUBLIC_API_URL. Si no está definida, se deduce la IP
 * de la computadora a partir del servidor de Expo (hostUri), de modo que en
 * Expo Go funciona sin configurar nada mientras la PC y el celular estén en la
 * misma red Wi-Fi.
 */
import Constants from 'expo-constants';
import { Platform } from 'react-native';

function resolverApiUrl() {
  const configurada = process.env.EXPO_PUBLIC_API_URL?.trim();
  if (configurada) return configurada.replace(/\/+$/, '');

  if (Platform.OS === 'web' && typeof window !== 'undefined' && window.location) {
    const host = window.location.hostname || 'localhost';
    return `http://${host}:4000/api`;
  }

  const hostUri =
    Constants.expoConfig?.hostUri ??
    (Constants as { expoGoConfig?: { debuggerHost?: string } }).expoGoConfig?.debuggerHost ??
    '';
  const host = String(hostUri).split(':')[0];

  if (host && host !== 'localhost' && host !== '127.0.0.1') {
    return `http://${host}:4000/api`;
  }
  return 'http://localhost:4000/api';
}

export const API_URL = resolverApiUrl();
export const DEMO_MODE = API_URL.length === 0;

type RequestOptions = {
  method?: 'GET' | 'POST' | 'PUT' | 'PATCH' | 'DELETE';
  body?: unknown;
  token?: string | null;
  usuarioId?: number | null;
  formData?: FormData;
};

export class ApiError extends Error {
  status: number;
  constructor(message: string, status: number) {
    super(message);
    this.name = 'ApiError';
    this.status = status;
  }
}

export async function apiFetch<T>(path: string, options: RequestOptions = {}): Promise<T> {
  if (DEMO_MODE) {
    throw new ApiError(
      'No se encontró la API local. Iniciá MySQL en XAMPP y ejecutá la API en App_360/api.',
      0
    );
  }

  const { method = 'GET', body, token, usuarioId, formData } = options;
  const headers: Record<string, string> = { Accept: 'application/json' };

  if (token) headers.Authorization = `Bearer ${token}`;
  if (usuarioId) headers['X-Usuario-Id'] = String(usuarioId);
  if (body !== undefined) headers['Content-Type'] = 'application/json';

  let response: Response;
  try {
    response = await fetch(`${API_URL}${path}`, {
      method,
      headers,
      body: formData ?? (body !== undefined ? JSON.stringify(body) : undefined),
    });
  } catch {
    throw new ApiError(
      `No se pudo conectar con la base de datos. Verificá que la API esté encendida en ${API_URL}.`,
      0
    );
  }

  const payload = await response.json().catch(() => null);

  if (!response.ok) {
    throw new ApiError(payload?.message ?? 'Error de conexión con el servidor.', response.status);
  }

  return payload as T;
}
