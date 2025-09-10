const express = require('express');
const cors = require('cors');
const { Pool } = require('pg');

const app = express();
const port = 3000;

// Middleware
app.use(cors());
app.use(express.json());

// Configuration de la base de données
const pool = new Pool({
  user: process.env.DB_USER || 'lulu',
  host: process.env.DB_HOST || 'localhost',
  database: process.env.DB_NAME || 'yabiso',
  password: process.env.DB_PASSWORD || '23525689',
  port: process.env.DB_PORT || 5432,
});

// Routes de base pour tester
app.get('/api/users', async (req, res) => {
  try {
    const result = await pool.query('SELECT * FROM utilisateurs');
    res.json(result.rows);
  } catch (err) {
    console.error(err);
    res.status(500).json({ error: 'Erreur serveur' });
  }
});

app.get('/api/subscriptions', async (req, res) => {
  try {
    const result = await pool.query('SELECT * FROM abonnements');
    res.json(result.rows);
  } catch (err) {
    console.error(err);
    res.status(500).json({ error: 'Erreur serveur' });
  }
});

// Démarrer le serveur
app.listen(port, () => {
  console.log(`Serveur backend démarré sur le port ${port}`);
});