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

const DEFAULT_SETTINGS = {
  company_name: 'SmartNeti',
  company_logo_url: '',
  support_phone: '',
  support_email: '',
  gateway_airtel_money_enabled: 'false',
  gateway_airtel_money_api_key: '',
  gateway_airtel_money_webhook_secret: '',
  gateway_tnm_mpamba_enabled: 'false',
  gateway_tnm_mpamba_api_key: '',
  gateway_tnm_mpamba_webhook_secret: '',
  gateway_paychangu_enabled: 'false',
  gateway_paychangu_public_key: '',
  gateway_paychangu_api_key: '',
  gateway_paychangu_webhook_secret: '',
  notifications_email_enabled: 'false',
  notifications_sms_enabled: 'false',
};

async function ensureDefaults() {
  const [existing] = await pool.execute('SELECT setting_key FROM smartneti_settings');
  const existingKeys = new Set(existing.map((r) => r.setting_key));
  const missing = Object.entries(DEFAULT_SETTINGS).filter(([key]) => !existingKeys.has(key));

  for (const [key, value] of missing) {
    await pool.execute(
      'INSERT IGNORE INTO smartneti_settings (id, setting_key, setting_value) VALUES (?, ?, ?)',
      [uuidv4(), key, value]
    );
  }
}

router.get('/', requireAuth, async (req, res) => {
  try {
    await ensureDefaults();
    const [rows] = await pool.execute('SELECT setting_key, setting_value FROM smartneti_settings');
    const settings = {};
    for (const row of rows) {
      settings[row.setting_key] = row.setting_value;
    }
    res.json({ settings });
  } catch (err) {
    console.error('Failed to load settings:', err);
    res.status(500).json({ error: 'Internal server error' });
  }
});

router.put('/', requireAuth, async (req, res) => {
  const updates = req.body;
  if (!updates || typeof updates !== 'object') {
    return res.status(400).json({ error: 'Invalid settings payload' });
  }

  try {
    await ensureDefaults();
    const keys = Object.keys(updates);
    for (const key of keys) {
      await pool.execute(
        'UPDATE smartneti_settings SET setting_value = ? WHERE setting_key = ?',
        [String(updates[key]), key]
      );
    }

    const [rows] = await pool.execute('SELECT setting_key, setting_value FROM smartneti_settings');
    const settings = {};
    for (const row of rows) {
      settings[row.setting_key] = row.setting_value;
    }
    res.json({ settings });
  } catch (err) {
    console.error('Failed to save settings:', err);
    res.status(500).json({ error: 'Internal server error' });
  }
});

module.exports = router;
