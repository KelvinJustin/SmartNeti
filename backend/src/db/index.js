const mysql = require('mysql2/promise');

const sslConfig = process.env.DB_SSL === 'true' ? { rejectUnauthorized: true } : undefined;

const pool = mysql.createPool({
  host: process.env.DB_HOST || 'rdmariadb',
  port: Number(process.env.DB_PORT || 3306),
  user: process.env.DB_USER || 'rd',
  password: process.env.DB_PASSWORD || 'rd',
  database: process.env.DB_NAME_SMARTNETI || 'smartneti',
  ssl: sslConfig,
  waitForConnections: true,
  connectionLimit: 5,
  queueLimit: 0,
  connectTimeout: 10000,
  acquireTimeout: 10000,
});

const rdPool = mysql.createPool({
  host: process.env.DB_HOST || 'rdmariadb',
  port: Number(process.env.DB_PORT || 3306),
  user: process.env.DB_USER || 'rd',
  password: process.env.DB_PASSWORD || 'rd',
  database: process.env.DB_NAME_RD || 'rd',
  ssl: sslConfig,
  waitForConnections: true,
  connectionLimit: 5,
  queueLimit: 0,
  connectTimeout: 10000,
  acquireTimeout: 10000,
});

module.exports = { pool, rdPool };
