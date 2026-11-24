/**
 * datos.js - capa de acceso a datos de la entidad principal (pendientes).
 *
 * Todas las operaciones usan sentencias preparadas de MySQL con parametros
 * `?`. Nunca se concatena texto del usuario dentro del SQL: asi se evita la
 * inyeccion SQL y MySQL reutiliza el plan de ejecucion.
 *
 * Operaciones de la Jornada 3:
 *   obtener()    -> SELECT
 *   agregar()    -> INSERT
 *   modificar()  -> UPDATE ... WHERE
 *   eliminar()   -> DELETE ... WHERE
 */
const bcrypt = require('bcryptjs');

const pool = require('./db');

const PRIORIDADES = ['alta', 'media', 'baja'];

/** Convierte dd/mm/aaaa (formato de la app) a aaaa-mm-dd (formato DATE de MySQL). */
function aFechaSql(fecha) {
  const m = /^(\d{2})\/(\d{2})\/(\d{4})$/.exec(String(fecha || '').trim());
  if (!m) {
    throw new Error('La fecha debe tener el formato dd/mm/aaaa.');
  }
  const dia = Number(m[1]);
  const mes = Number(m[2]);
  const anio = Number(m[3]);
  const prueba = new Date(anio, mes - 1, dia);
  if (
    prueba.getFullYear() !== anio ||
    prueba.getMonth() !== mes - 1 ||
    prueba.getDate() !== dia
  ) {
    throw new Error('La fecha no existe en el calendario.');
  }
  return `${m[3]}-${m[2]}-${m[1]}`;
}

function validarPendiente(datos = {}) {
  const titulo = String(datos.titulo || '').trim();
  const materia = String(datos.materia || '').trim();
  const prioridad = String(datos.prioridad || 'media').trim().toLowerCase();

  if (titulo.length < 3) {
    throw new Error('El titulo debe tener al menos 3 caracteres.');
  }
  if (titulo.length > 150) {
    throw new Error('El titulo no puede superar los 150 caracteres.');
  }
  if (!materia) {
    throw new Error('La materia es obligatoria.');
  }
  if (!PRIORIDADES.includes(prioridad)) {
    throw new Error('La prioridad debe ser alta, media o baja.');
  }

  return { titulo, materia, prioridad, fecha: aFechaSql(datos.fecha) };
}

function mapear(fila) {
  return {
    id: fila.id,
    titulo: fila.titulo,
    materia: fila.materia,
    fecha: fila.fecha,
    prioridad: fila.prioridad,
    hecho: Boolean(fila.hecho),
  };
}

/**
 * SELECT de todos los pendientes del usuario.
 * Se ordenan primero los no hechos y luego por fecha.
 */
async function obtener(usuarioId) {
  const [filas] = await pool.execute(
    `SELECT id, titulo, materia,
            DATE_FORMAT(fecha, '%d/%m/%Y') AS fecha,
            prioridad, hecho
       FROM pendientes
      WHERE usuario_id = ?
      ORDER BY hecho ASC, fecha ASC, id DESC`,
    [usuarioId]
  );
  return filas.map(mapear);
}

/** SELECT de un pendiente por clave primaria. */
async function obtenerPorId(usuarioId, id) {
  const [filas] = await pool.execute(
    `SELECT id, titulo, materia,
            DATE_FORMAT(fecha, '%d/%m/%Y') AS fecha,
            prioridad, hecho
       FROM pendientes
      WHERE id = ? AND usuario_id = ?`,
    [id, usuarioId]
  );
  return filas.length ? mapear(filas[0]) : null;
}

/** INSERT de un pendiente nuevo. Devuelve el registro creado. */
async function agregar(usuarioId, datos) {
  const p = validarPendiente(datos);
  const [resultado] = await pool.execute(
    `INSERT INTO pendientes (usuario_id, titulo, materia, fecha, prioridad, hecho)
     VALUES (?, ?, ?, ?, ?, 0)`,
    [usuarioId, p.titulo, p.materia, p.fecha, p.prioridad]
  );
  return obtenerPorId(usuarioId, resultado.insertId);
}

/** UPDATE ... WHERE id = ? del pendiente. Devuelve el registro actualizado o null. */
async function modificar(usuarioId, id, datos) {
  const actual = await obtenerPorId(usuarioId, id);
  if (!actual) return null;

  const p = validarPendiente(datos);
  await pool.execute(
    `UPDATE pendientes
        SET titulo = ?, materia = ?, fecha = ?, prioridad = ?
      WHERE id = ? AND usuario_id = ?`,
    [p.titulo, p.materia, p.fecha, p.prioridad, id, usuarioId]
  );
  return obtenerPorId(usuarioId, id);
}

/** DELETE ... WHERE id = ? del pendiente. Devuelve true si borro una fila. */
async function eliminar(usuarioId, id) {
  const [resultado] = await pool.execute(
    'DELETE FROM pendientes WHERE id = ? AND usuario_id = ?',
    [id, usuarioId]
  );
  return resultado.affectedRows > 0;
}

/** UPDATE del estado hecho/pendiente (casilla de la lista). */
async function alternar(usuarioId, id, hecho) {
  const [resultado] = await pool.execute(
    'UPDATE pendientes SET hecho = ? WHERE id = ? AND usuario_id = ?',
    [hecho ? 1 : 0, id, usuarioId]
  );
  return resultado.affectedRows > 0;
}

/**
 * SELECT del usuario por correo y validacion de la contrasena con bcrypt.
 * La contrasena nunca se guarda en texto plano: la tabla usuarios guarda
 * el hash generado con bcrypt.
 */
async function validarUsuario(email, password) {
  const [filas] = await pool.execute(
    `SELECT id, nombre, email, curso, codigo, password_hash
       FROM usuarios
      WHERE email = ?
      LIMIT 1`,
    [String(email || '').trim().toLowerCase()]
  );

  if (!filas.length) return null;

  const usuario = filas[0];
  const coincide = await bcrypt.compare(String(password || ''), usuario.password_hash);
  if (!coincide) return null;

  return {
    id: usuario.id,
    nombre: usuario.nombre,
    email: usuario.email,
    curso: usuario.curso,
    codigo: usuario.codigo,
  };
}

/** INSERT de usuario (se usa en crear-db.js para pasar el arreglo a la tabla). */
async function crearUsuario({ nombre, email, password, curso = '', codigo = '' }) {
  const hash = await bcrypt.hash(String(password), 10);
  const [resultado] = await pool.execute(
    `INSERT INTO usuarios (nombre, email, password_hash, curso, codigo)
     VALUES (?, ?, ?, ?, ?)`,
    [nombre, String(email).trim().toLowerCase(), hash, curso, codigo]
  );
  return resultado.insertId;
}

module.exports = {
  obtener,
  obtenerPorId,
  agregar,
  modificar,
  eliminar,
  alternar,
  validarUsuario,
  crearUsuario,
};
