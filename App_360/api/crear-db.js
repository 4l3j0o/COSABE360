/**
 * crear-db.js - crea la base, las tablas y los datos iniciales.
 *
 * Uso:  npm run crear-db
 *
 * Es idempotente: si la base ya existe no borra nada; solo crea lo que falta
 * y carga los usuarios/pendientes de ejemplo cuando las tablas estan vacias.
 */
require('dotenv').config();

const fs = require('fs');
const path = require('path');

const bcrypt = require('bcryptjs');
const mysql = require('mysql2/promise');

const USUARIOS = [
  {
    nombre: 'Valeria Quispe Mamani',
    email: 'valeria.quispe.8640@cosabe.edu.bo',
    password: 'CSB2025',
    curso: '4º de Secundaria · B',
    codigo: '8640',
  },
  {
    nombre: 'Jose Alejandro Benitez Torrez',
    email: 'jose.benitez.8641@cosabe.edu.bo',
    password: 'CSB2025',
    curso: '4º de Secundaria · B',
    codigo: '8641',
  },
];

const PENDIENTES = [
  {
    titulo: 'Resolver ejercicios de ecuaciones',
    materia: 'Matemática',
    fecha: '2026-11-20',
    prioridad: 'alta',
    hecho: 0,
  },
  {
    titulo: 'Leer capítulo 4 y hacer resumen',
    materia: 'Lengua Castellana',
    fecha: '2026-11-21',
    prioridad: 'media',
    hecho: 0,
  },
  {
    titulo: 'Preparar exposición de ecosistemas',
    materia: 'Ciencias Naturales',
    fecha: '2026-11-18',
    prioridad: 'alta',
    hecho: 1,
  },
];

async function main() {
  const conexion = await mysql.createConnection({
    host: process.env.DB_HOST || '127.0.0.1',
    port: Number(process.env.DB_PORT || 3306),
    user: process.env.DB_USER || 'root',
    password: process.env.DB_PASSWORD || '',
    multipleStatements: true,
  });

  try {
    const esquema = fs.readFileSync(path.join(__dirname, 'esquema.sql'), 'utf8');
    await conexion.query(esquema);
    console.log('Base de datos y tablas listas.');

    // Pasar el arreglo de usuarios a la tabla usuarios (INSERT con ?).
    for (const usuario of USUARIOS) {
      const hash = await bcrypt.hash(usuario.password, 10);
      await conexion.execute(
        `INSERT INTO usuarios (nombre, email, password_hash, curso, codigo)
         VALUES (?, ?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE
           nombre = VALUES(nombre),
           password_hash = VALUES(password_hash),
           curso = VALUES(curso),
           codigo = VALUES(codigo)`,
        [usuario.nombre, usuario.email, hash, usuario.curso, usuario.codigo]
      );
      console.log(`Usuario listo: ${usuario.email} / ${usuario.password}`);
    }

    const [usuarios] = await conexion.execute(
      'SELECT id FROM usuarios WHERE email = ? LIMIT 1',
      [USUARIOS[0].email]
    );
    const usuarioId = usuarios[0].id;

    const [conteo] = await conexion.execute(
      'SELECT COUNT(*) AS total FROM pendientes WHERE usuario_id = ?',
      [usuarioId]
    );

    if (conteo[0].total === 0) {
      for (const p of PENDIENTES) {
        await conexion.execute(
          `INSERT INTO pendientes (usuario_id, titulo, materia, fecha, prioridad, hecho)
           VALUES (?, ?, ?, ?, ?, ?)`,
          [usuarioId, p.titulo, p.materia, p.fecha, p.prioridad, p.hecho]
        );
      }
      console.log(`Pendientes de ejemplo cargados: ${PENDIENTES.length}`);
    } else {
      console.log(`La tabla pendientes ya tiene ${conteo[0].total} fila(s); no se duplicó nada.`);
    }

    const [tablas] = await conexion.query('SHOW TABLES');
    console.log('\nTablas en cosabe360_movil:', tablas.map((t) => Object.values(t)[0]).join(', '));
    console.log('\nListo. Ahora ejecutá: npm start');
  } finally {
    await conexion.end();
  }
}

main().catch((error) => {
  console.error('\nNo se pudo crear la base de datos.');
  console.error(error.message);
  console.error('\nRevisá que MySQL esté iniciado en el panel de XAMPP y que .env tenga el usuario/clave correctos.');
  process.exit(1);
});
