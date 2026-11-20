import * as SecureStore from 'expo-secure-store';

/**
 * Almacenamiento de la sesión.
 *
 * Usa SecureStore (cifrado) cuando el binario lo soporta. En Expo Go con una
 * versión desactualizada el módulo nativo no expone los métodos y la app
 * revienta, así que caemos a memoria para no bloquear el arranque.
 */
const memoria = new Map<string, string>();
let soportado: boolean | null = null;

async function haySecureStore() {
  if (soportado !== null) return soportado;
  try {
    if (typeof SecureStore.getItemAsync !== 'function') {
      soportado = false;
      return false;
    }
    await SecureStore.getItemAsync('__csb_probe__');
    soportado = true;
  } catch {
    soportado = false;
  }
  return soportado;
}

export async function guardar(clave: string, valor: string) {
  if (await haySecureStore()) {
    try {
      await SecureStore.setItemAsync(clave, valor);
      return;
    } catch {
      soportado = false;
    }
  }
  memoria.set(clave, valor);
}

export async function leer(clave: string) {
  if (await haySecureStore()) {
    try {
      return await SecureStore.getItemAsync(clave);
    } catch {
      soportado = false;
    }
  }
  return memoria.get(clave) ?? null;
}

export async function borrar(clave: string) {
  if (await haySecureStore()) {
    try {
      await SecureStore.deleteItemAsync(clave);
    } catch {
      soportado = false;
    }
  }
  memoria.delete(clave);
}
