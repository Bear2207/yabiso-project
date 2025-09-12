const db = require('../config/database');

const Subscription = {
  getAll: async () => {
    const result = await db.query('SELECT * FROM abonnements');
    return result.rows;
  },
  
  getById: async (id) => {
    const result = await db.query('SELECT * FROM abonnements WHERE abonnement_id = $1', [id]);
    return result.rows[0];
  },
  
  create: async (subscriptionData) => {
    const { nom_abonnement, prix, duree, description } = subscriptionData;
    const result = await db.query(
      'INSERT INTO abonnements (nom_abonnement, prix, duree, description) VALUES ($1, $2, $3, $4) RETURNING *',
      [nom_abonnement, prix, duree, description]
    );
    return result.rows[0];
  }
};

module.exports = Subscription;