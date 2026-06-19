const express = require('express');
const { pool } = require('../db');

const router = express.Router();

function requireAuth(req, res, next) {
  if (!req.session || !req.session.userId) {
    return res.status(401).json({ error: 'Unauthorized' });
  }
  next();
}

router.get('/', requireAuth, async (req, res) => {
  const { search } = req.query;
  try {
    let sql = `SELECT id, phone, email, full_name, created_at, updated_at FROM smartneti_customers`;
    const params = [];

    if (search) {
      sql += ` WHERE phone LIKE ? OR email LIKE ? OR full_name LIKE ?`;
      const like = `%${search}%`;
      params.push(like, like, like);
    }

    sql += ` ORDER BY created_at DESC`;

    const [rows] = await pool.execute(sql, params);
    res.json({ customers: rows });
  } catch (err) {
    console.error('Failed to list customers:', err);
    res.status(500).json({ error: 'Internal server error' });
  }
});

router.get('/:id', requireAuth, async (req, res) => {
  try {
    const [customers] = await pool.execute(
      'SELECT id, phone, email, full_name, created_at, updated_at FROM smartneti_customers WHERE id = ?',
      [req.params.id]
    );

    if (customers.length === 0) {
      return res.status(404).json({ error: 'Customer not found' });
    }

    const [payments] = await pool.execute(
      `SELECT p.id, p.amount, p.currency, p.status, p.reference, p.paid_at, p.created_at,
              pl.name AS plan_name, v.code AS voucher_code
       FROM smartneti_payments p
       LEFT JOIN smartneti_plans pl ON p.plan_id = pl.id
       LEFT JOIN smartneti_vouchers v ON p.voucher_id = v.id
       WHERE p.customer_id = ?
       ORDER BY p.created_at DESC`,
      [req.params.id]
    );

    res.json({ customer: customers[0], payments });
  } catch (err) {
    console.error('Failed to get customer:', err);
    res.status(500).json({ error: 'Internal server error' });
  }
});

module.exports = router;
