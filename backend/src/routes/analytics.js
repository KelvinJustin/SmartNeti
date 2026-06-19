const express = require('express');
const { pool, rdPool } = require('../db');

const router = express.Router();

function requireAuth(req, res, next) {
  if (!req.session || !req.session.userId) {
    return res.status(401).json({ error: 'Unauthorized' });
  }
  next();
}

router.get('/summary', requireAuth, async (req, res) => {
  try {
    const [[{ totalCustomers }]] = await pool.execute(
      'SELECT COUNT(*) AS totalCustomers FROM smartneti_customers'
    );

    const [[{ totalRevenue }]] = await pool.execute(
      'SELECT COALESCE(SUM(amount), 0) AS totalRevenue FROM smartneti_payments WHERE status = "paid"'
    );

    const [[{ totalVouchers }]] = await pool.execute(
      'SELECT COUNT(*) AS totalVouchers FROM smartneti_vouchers'
    );

    const [[{ usedVouchers }]] = await pool.execute(
      'SELECT COUNT(*) AS usedVouchers FROM smartneti_vouchers WHERE status = "used"'
    );

    const [topPlans] = await pool.execute(
      `SELECT pl.name AS plan_name, COUNT(p.id) AS sales, COALESCE(SUM(p.amount), 0) AS revenue
       FROM smartneti_payments p
       JOIN smartneti_plans pl ON p.plan_id = pl.id
       WHERE p.status = "paid"
       GROUP BY pl.id, pl.name
       ORDER BY sales DESC
       LIMIT 5`
    );

    const [dailyRevenue] = await pool.execute(
      `SELECT DATE(paid_at) AS date, COALESCE(SUM(amount), 0) AS revenue, COUNT(*) AS sales
       FROM smartneti_payments
       WHERE status = "paid" AND paid_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
       GROUP BY DATE(paid_at)
       ORDER BY date DESC`
    );

    const [[{ onlineUsers }]] = await rdPool.execute(
      'SELECT COUNT(DISTINCT username) AS onlineUsers FROM radacct WHERE acctstoptime IS NULL'
    );

    res.json({
      totalCustomers: totalCustomers || 0,
      totalRevenue: totalRevenue || 0,
      totalVouchers: totalVouchers || 0,
      usedVouchers: usedVouchers || 0,
      onlineUsers: onlineUsers || 0,
      topPlans,
      dailyRevenue,
    });
  } catch (err) {
    console.error('Failed to load analytics:', err);
    res.status(500).json({ error: 'Internal server error' });
  }
});

module.exports = router;
