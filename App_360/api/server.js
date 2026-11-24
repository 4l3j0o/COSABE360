/**
 * API local de CSB 360 (Node + Express + MySQL/XAMPP).
 *
 * La app Expo no puede abrir MySQL directamente, por eso consume estos
 * endpoints. Toda la logica SQL vive en datos.js con parametros `?`.
 */
require('dotenv').config();

const crypto = require('crypto');
const os = require('os');

const cors = require('cors');
const express = require('express');

const datos = require('./datos');
const pool = require('./db');

const app = express();
const PORT = Number(process.env.PORT || 4000);

app.use(cors());
app.use(express.json());

/** El usuario llega en el encabezado X-Usuario-Id (lo envia la app). */
function usuarioDe(req) {
  const id = Number(req.header('x-usuario-id') || req.query.usuario_id || 1);
  return Number.isInteger(id) && id > 0 ? id : 1;
}

function idDe(req) {
  const id = Number(req.params.id);
  if (!Number.isInteger(id) || id <= 0) {
    const error = new Error('El id del pendiente no es valido.');
    error.status = 400;
    throw error;
  }
  return id;
}

app.get('/api/salud', async (_req, res) => {
  const [filas] = await pool.query('SELECT 1 AS ok');
  res.json({ ok: filas[0].ok === 1, servicio: 'cosabe360-api' });
});

/** Login: SELECT del usuario + comparacion bcrypt. */
app.post('/api/auth/login', async (req, res) => {
  const { email, password } = req.body || {};
  if (!email || !password) {
    return res.status(400).json({ message: 'Ingresa tu correo y contrasena.' });
  }

  const usuario = await datos.validarUsuario(email, password);
  if (!usuario) {
    return res.status(401).json({ message: 'Correo o contrasena incorrectos.' });
  }

  const token = crypto.randomBytes(24).toString('hex');
  return res.json({ token, user: usuario });
});

/** SELECT de todos los pendientes. */
app.get('/api/pendientes', async (req, res) => {
  res.json(await datos.obtener(usuarioDe(req)));
});

/** SELECT de un pendiente. */
app.get('/api/pendientes/:id', async (req, res) => {
  const pendiente = await datos.obtenerPorId(usuarioDe(req), idDe(req));
  if (!pendiente) return res.status(404).json({ message: 'Pendiente no encontrado.' });
  return res.json(pendiente);
});

/** INSERT de un pendiente. */
app.post('/api/pendientes', async (req, res) => {
  const creado = await datos.agregar(usuarioDe(req), req.body || {});
  res.status(201).json(creado);
});

/** UPDATE ... WHERE id = ? */
app.put('/api/pendientes/:id', async (req, res) => {
  const actualizado = await datos.modificar(usuarioDe(req), idDe(req), req.body || {});
  if (!actualizado) return res.status(404).json({ message: 'Pendiente no encontrado.' });
  return res.json(actualizado);
});

/** UPDATE del estado hecho/pendiente. */
app.patch('/api/pendientes/:id/hecho', async (req, res) => {
  const hecho = Boolean(req.body?.hecho);
  const actualizado = await datos.alternar(usuarioDe(req), idDe(req), hecho);
  if (!actualizado) return res.status(404).json({ message: 'Pendiente no encontrado.' });
  return res.json({ id: idDe(req), hecho });
});

/** DELETE ... WHERE id = ? */
app.delete('/api/pendientes/:id', async (req, res) => {
  const eliminado = await datos.eliminar(usuarioDe(req), idDe(req));
  if (!eliminado) return res.status(404).json({ message: 'Pendiente no encontrado.' });
  return res.json({ eliminado: true });
});

app.use((req, res) => {
  res.status(404).json({ message: `Ruta no encontrada: ${req.method} ${req.path}` });
});

app.use((error, _req, res, _next) => {
  console.error('[api]', error.message);
  const esValidacion = /formato|fecha|titulo|materia|prioridad|caracteres|obligatoria/i.test(
    error.message || ''
  );
  const status = error.status || (esValidacion ? 400 : 500);
  res.status(status).json({ message: error.message || 'Error interno del servidor.' });
});

const servidor = app.listen(PORT, '0.0.0.0', () => {
  console.log(`\nCSB 360 API escuchando en http://localhost:${PORT}/api`);
  console.log(`Base de datos: ${process.env.DB_NAME || 'cosabe360_movil'} en ${process.env.DB_HOST || '127.0.0.1'}:${process.env.DB_PORT || 3306}`);
  for (const redes of Object.values(os.networkInterfaces())) {
    for (const red of redes || []) {
      if (red.family === 'IPv4' && !red.internal) {
        console.log(`Desde el celular (misma Wi-Fi): http://${red.address}:${PORT}/api`);
      }
    }
  }
  console.log('');
});

async function cerrar() {
  servidor.close();
  await pool.end();
  process.exit(0);
}

process.on('SIGINT', cerrar);
process.on('SIGTERM', cerrar);
