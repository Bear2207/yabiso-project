const { Pool } = require('pg');

const pool = new Pool({
  user: process.env.DB_USER || 'lulu',
  host: process.env.DB_HOST || 'db',
  database: process.env.DB_NAME || 'yabiso',
  password: process.env.DB_PASSWORD || '23525689',
  port: process.env.DB_PORT || 5432,
});

module.exports = {
  query: (text, params) => pool.query(text, params),
  pool
};