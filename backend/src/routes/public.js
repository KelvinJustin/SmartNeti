const express = require('express');
const { pool } = require('../db');
const { initiatePayment, getPaymentStatus, completePayment, MOCK_MODE } = require('../services/payments');

const FRONTEND_URL = process.env.FRONTEND_URL || 'http://localhost:3000';

const router = express.Router();

const GATEWAY_LIST = [
  { key: 'paychangu', label: 'PayChangu' },
];

const DISABLED_GATEWAYS = new Set(['airtel-money', 'tnm-mpamba']);

async function getEnabledGateways() {
  const [rows] = await pool.execute(
    `SELECT setting_key, setting_value FROM smartneti_settings WHERE setting_key LIKE 'gateway_%_enabled'`
  );
  const enabled = new Set();
  for (const row of rows) {
    const gatewayKey = row.setting_key.replace('gateway_', '').replace('_enabled', '');
    if (row.setting_value === 'true' && !DISABLED_GATEWAYS.has(gatewayKey)) {
      enabled.add(gatewayKey);
    }
  }
  return enabled;
}

router.get('/plans', async (req, res) => {
  try {
    const [rows] = await pool.execute(
      'SELECT id, name, description, duration_minutes, speed_down_kbps, speed_up_kbps, price, currency FROM smartneti_plans WHERE status = "active" ORDER BY price ASC'
    );
    res.json({ plans: rows });
  } catch (err) {
    console.error('Failed to list public plans:', err);
    res.status(500).json({ error: 'Internal server error' });
  }
});

router.get('/gateways', async (req, res) => {
  try {
    const enabled = await getEnabledGateways();
    const gateways = GATEWAY_LIST.filter((g) => enabled.has(g.key));
    res.json({ gateways });
  } catch (err) {
    console.error('Failed to list gateways:', err);
    res.status(500).json({ error: 'Internal server error' });
  }
});

async function getSetting(key) {
  const [rows] = await pool.execute(
    'SELECT setting_value FROM smartneti_settings WHERE setting_key = ?',
    [key]
  );
  return rows.length > 0 ? rows[0].setting_value : undefined;
}

router.post('/payments/initiate', async (req, res) => {
  const { gateway, planId, customerPhone, customerEmail, customerName } = req.body;

  if (!gateway || !planId || !customerPhone) {
    return res.status(400).json({ error: 'Gateway, planId, and customerPhone are required' });
  }

  try {
    const enabled = await getEnabledGateways();
    if (!enabled.has(gateway)) {
      return res.status(400).json({ error: 'Unsupported or disabled gateway' });
    }

    const result = await initiatePayment({ gateway, planId, customerPhone, customerEmail, customerName });

    if (gateway === 'paychangu') {
      const publicKey = await getSetting('gateway_paychangu_public_key');
      if (!publicKey) {
        return res.status(400).json({ error: 'PayChangu public key not configured' });
      }
      result.checkout = {
        public_key: publicKey,
        tx_ref: result.reference,
        amount: Number(result.amount),
        currency: result.currency || 'MWK',
        callback_url: `${FRONTEND_URL}/portal/payment/callback`,
        return_url: `${FRONTEND_URL}/portal/plans`,
        customization: {
          title: 'SmartNeti Payment',
          description: 'Internet voucher purchase',
        },
      };
    }

    res.status(201).json(result);
  } catch (err) {
    console.error('Failed to initiate payment:', err);
    res.status(400).json({ error: err.message });
  }
});

router.get('/payments/status/:reference', async (req, res) => {
  try {
    const status = await getPaymentStatus(req.params.reference);
    res.json({ payment: status });
  } catch (err) {
    console.error('Failed to get payment status:', err);
    res.status(404).json({ error: err.message });
  }
});

router.post('/payments/mock-complete/:reference', async (req, res) => {
  if (!MOCK_MODE) {
    return res.status(403).json({ error: 'Mock mode is disabled' });
  }

  try {
    const result = await completePayment(req.params.reference, 'mock-gateway-ref');
    res.json({ payment: result });
  } catch (err) {
    console.error('Failed to mock-complete payment:', err);
    res.status(400).json({ error: err.message });
  }
});

router.post('/payments/verify', async (req, res) => {
  const { txRef } = req.body;
  if (!txRef) {
    return res.status(400).json({ error: 'txRef is required' });
  }

  try {
    const [payments] = await pool.execute(
      'SELECT id, reference, status, amount, currency FROM smartneti_payments WHERE reference = ?',
      [txRef]
    );
    if (payments.length === 0) {
      return res.status(404).json({ error: 'Payment not found' });
    }
    const payment = payments[0];

    if (payment.status === 'paid') {
      return res.json({ verified: true, payment: { reference: payment.reference, status: payment.status } });
    }

    if (MOCK_MODE) {
      // In dev/mock mode, auto-complete without calling PayChangu API
      const completed = await completePayment(txRef, 'mock-paychangu-ref');
      return res.json({ verified: true, payment: { reference: completed.reference, status: completed.status } });
    }

    const secretKey = await getSetting('gateway_paychangu_api_key');
    if (!secretKey) {
      return res.status(400).json({ error: 'PayChangu secret key not configured' });
    }

    const verifyRes = await fetch(`https://api.paychangu.com/verify-payment/${txRef}`, {
      method: 'GET',
      headers: {
        Accept: 'application/json',
        Authorization: `Bearer ${secretKey}`,
      },
    });

    if (!verifyRes.ok) {
      return res.status(400).json({ verified: false, error: 'PayChangu verification failed' });
    }

    const verifyData = await verifyRes.json();
    if (verifyData.status !== 'success' || verifyData.data?.status !== 'success') {
      return res.status(400).json({ verified: false, error: 'Transaction not successful' });
    }

    const amountMatch = Number(verifyData.data.amount) >= Number(payment.amount);
    const currencyMatch = (verifyData.data.currency || 'MWK') === (payment.currency || 'MWK');
    if (!amountMatch || !currencyMatch) {
      return res.status(400).json({ verified: false, error: 'Amount or currency mismatch' });
    }

    const completed = await completePayment(txRef, verifyData.data.reference || verifyData.data.tx_ref);
    res.json({ verified: true, payment: { reference: completed.reference, status: completed.status } });
  } catch (err) {
    console.error('Failed to verify payment:', err);
    res.status(500).json({ error: 'Internal server error' });
  }
});

router.post('/voucher/validate', async (req, res) => {
  const { code } = req.body;

  if (!code) {
    return res.status(400).json({ error: 'Voucher code is required' });
  }

  try {
    const [rows] = await pool.execute(
      `SELECT v.id, v.code, v.status, v.plan_id, v.radius_username, v.radius_password,
              v.used_at, v.expires_at, p.name AS plan_name, p.duration_minutes
       FROM smartneti_vouchers v
       JOIN smartneti_plans p ON v.plan_id = p.id
       WHERE v.code = ?`,
      [code.toUpperCase().trim()]
    );

    if (rows.length === 0) {
      return res.status(404).json({ error: 'Invalid voucher code' });
    }

    const voucher = rows[0];

    if (voucher.status === 'expired') {
      return res.status(400).json({ error: 'Voucher has expired' });
    }

    if (voucher.status === 'used') {
      return res.status(400).json({ error: 'Voucher has already been used' });
    }

    res.json({
      valid: true,
      voucher: {
        id: voucher.id,
        code: voucher.code,
        plan: voucher.plan_name,
        durationMinutes: voucher.duration_minutes,
        status: voucher.status,
      },
    });
  } catch (err) {
    console.error('Failed to validate voucher:', err);
    res.status(500).json({ error: 'Internal server error' });
  }
});

module.exports = router;
