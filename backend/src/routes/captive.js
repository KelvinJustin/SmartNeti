const express = require('express');
const { pool } = require('../db');

const router = express.Router();

router.post('/authorize', async (req, res) => {
  const { code, mac, ip } = req.body;

  if (!code) {
    return res.status(400).json({ error: 'Voucher code is required' });
  }

  const connection = await pool.getConnection();
  try {
    await connection.beginTransaction();

    const [rows] = await connection.execute(
      `SELECT v.id, v.code, v.status, v.plan_id, v.radius_username, v.radius_password,
              v.used_at, v.expires_at, p.name AS plan_name, p.duration_minutes
       FROM smartneti_vouchers v
       JOIN smartneti_plans p ON v.plan_id = p.id
       WHERE v.code = ?
       FOR UPDATE`,
      [code.toUpperCase().trim()]
    );

    if (rows.length === 0) {
      await connection.rollback();
      return res.status(404).json({ error: 'Invalid voucher code' });
    }

    const voucher = rows[0];

    if (voucher.status === 'expired') {
      await connection.rollback();
      return res.status(400).json({ error: 'Voucher has expired' });
    }

    if (voucher.status === 'used') {
      await connection.rollback();
      return res.status(400).json({ error: 'Voucher has already been used' });
    }

    // Mark voucher as used
    await connection.execute(
      'UPDATE smartneti_vouchers SET status = ?, used_at = NOW() WHERE id = ?',
      ['used', voucher.id]
    );

    // Log captive session (optional, for analytics/debugging)
    try {
      await connection.execute(
        'INSERT INTO smartneti_captive_sessions (id, voucher_id, mac_address, ip_address, created_at) VALUES (UUID(), ?, ?, ?, NOW())',
        [voucher.id, mac || null, ip || null]
      );
    } catch (err) {
      // Non-fatal; table may not exist yet
      console.warn('Failed to log captive session:', err.message);
    }

    await connection.commit();

    res.json({
      success: true,
      username: voucher.radius_username,
      password: voucher.radius_password,
      plan: voucher.plan_name,
      durationMinutes: voucher.duration_minutes,
    });
  } catch (err) {
    await connection.rollback();
    console.error('Captive authorization error:', err);
    res.status(500).json({ error: 'Internal server error' });
  } finally {
    connection.release();
  }
});

module.exports = router;
