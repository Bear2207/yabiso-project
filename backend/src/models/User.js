const db = require('../config/database');

const User = {
  getAll: async () => {
    const result = await db.query('SELECT * FROM utilisateurs');
    return result.rows;
  },
  
  getById: async (id) => {
    const result = await db.query('SELECT * FROM utilisateurs WHERE utilisateur_id = $1', [id]);
    return result.rows[0];
  },
  
  getByEmail: async (email) => {
    const result = await db.query('SELECT * FROM utilisateurs WHERE email = $1', [email]);
    return result.rows[0];
  },
  
  create: async (userData) => {
    const { nom, prenom, email, mot_de_passe, role } = userData;
    const result = await db.query(
      'INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role) VALUES ($1, $2, $3, $4, $5) RETURNING *',
      [nom, prenom, email, mot_de_passe, role]
    );
    return result.rows[0];
  },
  
  update: async (id, userData) => {
    const { nom, prenom, email, role } = userData;
    const result = await db.query(
      'UPDATE utilisateurs SET nom = $1, prenom = $2, email = $3, role = $4 WHERE utilisateur_id = $5 RETURNING *',
      [nom, prenom, email, role, id]
    );
    return result.rows[0];
  },
  
  delete: async (id) => {
    const result = await db.query('DELETE FROM utilisateurs WHERE utilisateur_id = $1 RETURNING *', [id]);
    return result.rows[0];
  }
};

module.exports = User;