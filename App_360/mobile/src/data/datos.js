/**
 * datos.js (cliente de la app)
 *
 * Una función por operación sobre la entidad principal:
 *   obtener, agregar, modificar y eliminar.
 *
 * En la consigna original estas funciones ejecutarían SQL con `?` sobre
 * SQLite. Como la base de este proyecto es MySQL local (XAMPP), la app no
 * puede abrir la base directamente: cada función llama a la API local y la
 * sentencia preparada con `?` se ejecuta en `api/datos.js`.
 *
 *   obtener()   -> GET    /pendientes
 *   agregar()   -> POST   /pendientes
 *   modificar() -> PUT    /pendientes/:id
 *   eliminar()  -> DELETE /pendientes/:id
 */
import { apiFetch } from '@/lib/api';

/**
 * @typedef {Object} Pendiente
 * @property {number} id
 * @property {string} titulo
 * @property {string} materia
 * @property {string} fecha
 * @property {'alta'|'media'|'baja'} prioridad
 * @property {boolean} hecho
 */

/** SELECT de todos los pendientes del usuario. */
export function obtener(usuarioId) {
  return apiFetch('/pendientes', { usuarioId });
}

/** SELECT de un pendiente por id. */
export function obtenerUno(usuarioId, id) {
  return apiFetch(`/pendientes/${id}`, { usuarioId });
}

/** INSERT de un pendiente. */
export function agregar(usuarioId, pendiente) {
  return apiFetch('/pendientes', { method: 'POST', body: pendiente, usuarioId });
}

/** UPDATE ... WHERE id = ? */
export function modificar(usuarioId, id, pendiente) {
  return apiFetch(`/pendientes/${id}`, { method: 'PUT', body: pendiente, usuarioId });
}

/** DELETE ... WHERE id = ? */
export function eliminar(usuarioId, id) {
  return apiFetch(`/pendientes/${id}`, { method: 'DELETE', usuarioId });
}

/** UPDATE del campo hecho (marcar como hecho / pendiente). */
export function alternar(usuarioId, id, hecho) {
  return apiFetch(`/pendientes/${id}/hecho`, {
    method: 'PATCH',
    body: { hecho },
    usuarioId,
  });
}

/** SELECT del usuario + validación de contraseña en la API. */
export function iniciarSesion(email, password) {
  return apiFetch('/auth/login', { method: 'POST', body: { email, password } });
}
