/**
 * Conexion a MySQL (XAMPP).
 *
 * En la Jornada 3 la consigna original usa SQLiteProvider. Como la base de
 * datos de este proyecto es MySQL local, el equivalente es este pool de
 * conexiones: se configura una sola vez y datos.js lo reutiliza.
 */
require('dotenv').config();

const mysql = require('mysql2/promise');

const pool = mysql.createPool({
  host: process.env.DB_HOST || '127.0.0.1',
  port: Number(process.env.DB_PORT || 3306),
  user: process.env.DB_USER || 'root',
  password: process.env.DB_PASSWORD || '',
  database: process.env.DB_NAME || 'cosabe360_movil',
  waitForConnections: true,
  connectionLimit: 10,
  queueLimit: 0,
  charset: 'utf8mb4',
  dateStrings: true,
});

module.exports = pool;
