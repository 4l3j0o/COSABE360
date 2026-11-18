/**
 * Cliente HTTP de la app. Cuando la API v1 (Laravel + Sanctum) esté levantada,
 * basta con definir EXPO_PUBLIC_API_URL en el archivo .env.
 *
 * Sin esa variable la app funciona en "modo demo" con los datos de src/data/mock.ts.
 */

export const API_URL = process.env.EXPO_PUBLIC_API_URL ?? '';
export const DEMO_MODE = API_URL.length === 0;

type RequestOptions = {
  method?: 'GET' | 'POST' | 'PUT' | 'PATCH' | 'DELETE';
  body?: unknown;
  token?: string | null;
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
    throw new ApiError('La API no está configurada (modo demo).', 0);
  }

  const { method = 'GET', body, token, formData } = options;
  const headers: Record<string, string> = { Accept: 'application/json' };

  if (token) headers.Authorization = `Bearer ${token}`;
  if (body !== undefined) headers['Content-Type'] = 'application/json';

  const response = await fetch(`${API_URL}${path}`, {
    method,
    headers,
    body: formData ?? (body !== undefined ? JSON.stringify(body) : undefined),
  });

  const payload = await response.json().catch(() => null);

  if (!response.ok) {
    throw new ApiError(payload?.message ?? 'Error de conexión con el servidor.', response.status);
  }

  return payload as T;
}
