const express = require('express');
const router = express.Router();
const Subscription = require('../models/Subscription');

// GET all subscriptions
router.get('/', async (req, res) => {
  try {
    const subscriptions = await Subscription.getAll();
    res.json(subscriptions);
  } catch (error) {
    res.status(500).json({ error: error.message });
  }
});

// GET subscription by ID
router.get('/:id', async (req, res) => {
  try {
    const subscription = await Subscription.getById(req.params.id);
    if (!subscription) {
      return res.status(404).json({ error: 'Subscription not found' });
    }
    res.json(subscription);
  } catch (error) {
    res.status(500).json({ error: error.message });
  }
});

module.exports = router;