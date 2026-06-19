const express = require('express');
const { v4: uuidv4 } = require('uuid');
const { pool } = require('../db');

const router = express.Router();

function requireAuth(req, res, next) {
  if (!req.session || !req.session.userId) {
    return res.status(401).json({ error: 'Unauthorized' });
  }
  next();
}

router.get('/', requireAuth, async (req, res) => {
  try {
    const [rows] = await pool.execute(
      'SELECT id, name, description, duration_minutes, speed_down_kbps, speed_up_kbps, price, currency, status, created_at, updated_at FROM smartneti_plans ORDER BY created_at DESC'
    );
    res.json({ plans: rows });
  } catch (err) {
    console.error('Failed to list plans:', err);
    res.status(500).json({ error: 'Internal server error' });
  }
});

router.get('/:id', requireAuth, async (req, res) => {
  try {
    const [rows] = await pool.execute(
      'SELECT id, name, description, duration_minutes, speed_down_kbps, speed_up_kbps, price, currency, status, created_at, updated_at FROM smartneti_plans WHERE id = ?',
      [req.params.id]
    );

    if (rows.length === 0) {
      return res.status(404).json({ error: 'Plan not found' });
    }

    res.json({ plan: rows[0] });
  } catch (err) {
    console.error('Failed to get plan:', err);
    res.status(500).json({ error: 'Internal server error' });
  }
});

router.post('/', requireAuth, async (req, res) => {
  const { name, description, durationMinutes, speedDownKbps, speedUpKbps, price, currency, status } = req.body;

  if (!name || durationMinutes == null || price == null) {
    return res.status(400).json({ error: 'Name, duration, and price are required' });
  }

  const id = uuidv4();

  try {
    await pool.execute(
      'INSERT INTO smartneti_plans (id, name, description, duration_minutes, speed_down_kbps, speed_up_kbps, price, currency, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)',
      [
        id,
        name,
        description || null,
        durationMinutes,
        speedDownKbps || null,
        speedUpKbps || null,
        price,
        currency || 'MWK',
        status || 'active',
      ]
    );

    const [rows] = await pool.execute(
      'SELECT id, name, description, duration_minutes, speed_down_kbps, speed_up_kbps, price, currency, status, created_at, updated_at FROM smartneti_plans WHERE id = ?',
      [id]
    );

    res.status(201).json({ plan: rows[0] });
  } catch (err) {
    console.error('Failed to create plan:', err);
    res.status(500).json({ error: 'Internal server error' });
  }
});

router.patch('/:id', requireAuth, async (req, res) => {
  const { name, description, durationMinutes, speedDownKbps, speedUpKbps, price, currency, status } = req.body;

  try {
    const [existing] = await pool.execute(
      'SELECT id, name, description, duration_minutes, speed_down_kbps, speed_up_kbps, price, currency, status FROM smartneti_plans WHERE id = ?',
      [req.params.id]
    );

    if (existing.length === 0) {
      return res.status(404).json({ error: 'Plan not found' });
    }

    const plan = existing[0];

    await pool.execute(
      'UPDATE smartneti_plans SET name = ?, description = ?, duration_minutes = ?, speed_down_kbps = ?, speed_up_kbps = ?, price = ?, currency = ?, status = ? WHERE id = ?',
      [
        name ?? plan.name,
        description ?? plan.description,
        durationMinutes ?? plan.duration_minutes,
        speedDownKbps ?? plan.speed_down_kbps,
        speedUpKbps ?? plan.speed_up_kbps,
        price ?? plan.price,
        currency ?? plan.currency,
        status ?? plan.status,
        req.params.id,
      ]
    );

    const [rows] = await pool.execute(
      'SELECT id, name, description, duration_minutes, speed_down_kbps, speed_up_kbps, price, currency, status, created_at, updated_at FROM smartneti_plans WHERE id = ?',
      [req.params.id]
    );

    res.json({ plan: rows[0] });
  } catch (err) {
    console.error('Failed to update plan:', err);
    res.status(500).json({ error: 'Internal server error' });
  }
});

router.delete('/:id', requireAuth, async (req, res) => {
  try {
    const [result] = await pool.execute(
      'DELETE FROM smartneti_plans WHERE id = ?',
      [req.params.id]
    );

    if (result.affectedRows === 0) {
      return res.status(404).json({ error: 'Plan not found' });
    }

    res.json({ status: 'ok' });
  } catch (err) {
    console.error('Failed to delete plan:', err);
    res.status(500).json({ error: 'Internal server error' });
  }
});

module.exports = router;
