const express = require('express');
const { v4: uuidv4 } = require('uuid');
const { pool, rdPool } = require('../db');

const router = express.Router();

const CODE_LENGTH = 8;
const AMBIGUOUS = '0O1Il';
const ALPHABET = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';

function generateCode(length = CODE_LENGTH) {
  let code = '';
  for (let i = 0; i < length; i++) {
    code += ALPHABET.charAt(Math.floor(Math.random() * ALPHABET.length));
  }
  return code;
}

function generatePassword(length = 12) {
  const alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
  let password = '';
  for (let i = 0; i < length; i++) {
    password += alphabet.charAt(Math.floor(Math.random() * alphabet.length));
  }
  return password;
}

async function createRadiusUser(voucher) {
  await rdPool.execute(
    'INSERT INTO radcheck (username, attribute, op, value) VALUES (?, ?, ?, ?)',
    [voucher.radius_username, 'Cleartext-Password', ':=', voucher.radius_password]
  );
}

async function generateUniqueCode() {
  let attempts = 0;
  while (attempts < 10) {
    const code = generateCode();
    const [existing] = await pool.execute(
      'SELECT id FROM smartneti_vouchers WHERE code = ?',
      [code]
    );
    if (existing.length === 0) {
      return code;
    }
    attempts++;
  }
  throw new Error('Unable to generate a unique voucher code');
}

function requireAuth(req, res, next) {
  if (!req.session || !req.session.userId) {
    return res.status(401).json({ error: 'Unauthorized' });
  }
  next();
}

router.get('/', requireAuth, async (req, res) => {
  const { planId, status } = req.query;

  let where = [];
  const params = [];
  if (planId) {
    where.push('v.plan_id = ?');
    params.push(planId);
  }
  if (status) {
    where.push('v.status = ?');
    params.push(status);
  }
  const whereClause = where.length ? `WHERE ${where.join(' AND ')}` : '';

  try {
    const [rows] = await pool.execute(
      `SELECT v.id, v.plan_id, v.customer_id, v.generated_by, v.code, v.radius_username, v.radius_password, v.status,
              v.sold_at, v.used_at, v.expires_at, v.created_at, v.updated_at,
              p.name AS plan_name, p.duration_minutes AS plan_duration_minutes,
              c.full_name AS customer_name, c.phone AS customer_phone,
              a.full_name AS generated_by_name
       FROM smartneti_vouchers v
       JOIN smartneti_plans p ON v.plan_id = p.id
       LEFT JOIN smartneti_customers c ON v.customer_id = c.id
       LEFT JOIN smartneti_admins a ON v.generated_by = a.id
       ${whereClause}
       ORDER BY v.created_at DESC`,
      params
    );
    res.json({
      vouchers: rows.map((v) => ({
        ...v,
        source: v.customer_id ? 'purchased' : v.generated_by ? 'bulk' : 'unknown',
      })),
    });
  } catch (err) {
    console.error('Failed to list vouchers:', err);
    res.status(500).json({ error: 'Internal server error' });
  }
});

router.get('/:id', requireAuth, async (req, res) => {
  try {
    const [rows] = await pool.execute(
      `SELECT v.id, v.plan_id, v.customer_id, v.generated_by, v.code, v.radius_username, v.radius_password, v.status,
              v.sold_at, v.used_at, v.expires_at, v.created_at, v.updated_at,
              p.name AS plan_name, p.duration_minutes AS plan_duration_minutes,
              c.full_name AS customer_name, c.phone AS customer_phone,
              a.full_name AS generated_by_name
       FROM smartneti_vouchers v
       JOIN smartneti_plans p ON v.plan_id = p.id
       LEFT JOIN smartneti_customers c ON v.customer_id = c.id
       LEFT JOIN smartneti_admins a ON v.generated_by = a.id
       WHERE v.id = ?`,
      [req.params.id]
    );

    if (rows.length === 0) {
      return res.status(404).json({ error: 'Voucher not found' });
    }

    const voucher = rows[0];
    voucher.source = voucher.customer_id ? 'purchased' : voucher.generated_by ? 'bulk' : 'unknown';
    res.json({ voucher });
  } catch (err) {
    console.error('Failed to get voucher:', err);
    res.status(500).json({ error: 'Internal server error' });
  }
});

router.post('/generate', requireAuth, async (req, res) => {
  const { planId, count } = req.body;
  const voucherCount = Math.min(Number(count) || 0, 100);
  const generatedBy = req.session?.userId || null;

  if (!planId || voucherCount <= 0) {
    return res.status(400).json({ error: 'Plan ID and a positive count are required' });
  }

  try {
    const [plans] = await pool.execute(
      'SELECT id, name, duration_minutes FROM smartneti_plans WHERE id = ? AND status = "active"',
      [planId]
    );

    if (plans.length === 0) {
      return res.status(404).json({ error: 'Plan not found or inactive' });
    }

    const created = [];
    for (let i = 0; i < voucherCount; i++) {
      const code = await generateUniqueCode();
      const id = uuidv4();
      const radiusUsername = code;
      const radiusPassword = generatePassword();

      await pool.execute(
        'INSERT INTO smartneti_vouchers (id, plan_id, generated_by, code, radius_username, radius_password, status) VALUES (?, ?, ?, ?, ?, ?, ?)',
        [id, planId, generatedBy, code, radiusUsername, radiusPassword, 'available']
      );

      const voucher = { id, plan_id: planId, generated_by: generatedBy, code, radius_username: radiusUsername, radius_password: radiusPassword };
      try {
        await createRadiusUser(voucher);
      } catch (radiusErr) {
        console.error('Failed to create RADIUS user for voucher, rolling back voucher:', radiusErr);
        await pool.execute('DELETE FROM smartneti_vouchers WHERE id = ?', [id]);
        throw radiusErr;
      }

      created.push(voucher);
    }

    res.status(201).json({ vouchers: created });
  } catch (err) {
    console.error('Failed to generate vouchers:', err);
    res.status(500).json({ error: 'Internal server error' });
  }
});

module.exports = router;
