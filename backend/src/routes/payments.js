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
  try {
    const [rows] = await pool.execute(
      `SELECT p.id, p.reference, p.gateway, p.amount, p.currency, p.status,
              p.paid_at, p.created_at, c.phone AS customer_phone, pl.name AS plan_name,
              v.code AS voucher_code
       FROM smartneti_payments p
       LEFT JOIN smartneti_customers c ON p.customer_id = c.id
       LEFT JOIN smartneti_plans pl ON p.plan_id = pl.id
       LEFT JOIN smartneti_vouchers v ON p.voucher_id = v.id
       ORDER BY p.created_at DESC`
    );
    res.json({ payments: rows });
  } catch (err) {
    console.error('Failed to list payments:', err);
    res.status(500).json({ error: 'Internal server error' });
  }
});

module.exports = router;
