const { v4: uuidv4 } = require('uuid');
const { pool, rdPool } = require('../db');

const MOCK_MODE = process.env.PAYMENT_MOCK_MODE === 'true' || process.env.NODE_ENV === 'development';

function generateReference() {
  return 'SN-' + Date.now().toString(36).toUpperCase() + '-' + Math.random().toString(36).substring(2, 6).toUpperCase();
}

async function createCustomer(phone, email, fullName) {
  const [existing] = await pool.execute(
    'SELECT id, full_name FROM smartneti_customers WHERE phone = ?',
    [phone]
  );

  if (existing.length > 0) {
    if (fullName && !existing[0].full_name) {
      await pool.execute(
        'UPDATE smartneti_customers SET full_name = ? WHERE id = ?',
        [fullName, existing[0].id]
      );
    }
    return existing[0].id;
  }

  const id = uuidv4();
  await pool.execute(
    'INSERT INTO smartneti_customers (id, phone, email, full_name) VALUES (?, ?, ?, ?)',
    [id, phone, email || null, fullName || null]
  );
  return id;
}

async function initiatePayment({ gateway, planId, customerPhone, customerEmail, customerName }) {
  if (!gateway || !planId || !customerPhone) {
    throw new Error('Gateway, planId, and customerPhone are required');
  }

  const [plans] = await pool.execute(
    'SELECT id, name, price, currency, duration_minutes FROM smartneti_plans WHERE id = ? AND status = "active"',
    [planId]
  );

  if (plans.length === 0) {
    throw new Error('Plan not found or inactive');
  }

  const plan = plans[0];
  const customerId = await createCustomer(customerPhone, customerEmail, customerName);
  const reference = generateReference();

  const paymentId = uuidv4();
  await pool.execute(
    'INSERT INTO smartneti_payments (id, customer_id, plan_id, gateway, amount, currency, status, reference) VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
    [paymentId, customerId, planId, gateway, plan.price, plan.currency || 'MWK', 'pending', reference]
  );

  if (MOCK_MODE && gateway !== 'paychangu') {
    // In mock mode, auto-complete after a short delay for testing (non-PayChangu only)
    setTimeout(() => completePayment(reference).catch(() => {}), 3000);
  }

  return { paymentId, reference, amount: plan.price, currency: plan.currency || 'MWK', status: 'pending' };
}

async function generateVoucherForPlan(planId, customerId) {
  const ALPHABET = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';
  function genCode(length = 8) {
    let code = '';
    for (let i = 0; i < length; i++) {
      code += ALPHABET.charAt(Math.floor(Math.random() * ALPHABET.length));
    }
    return code;
  }

  let code;
  let attempts = 0;
  while (attempts < 10) {
    code = genCode();
    const [existing] = await pool.execute('SELECT id FROM smartneti_vouchers WHERE code = ?', [code]);
    if (existing.length === 0) break;
    attempts++;
  }

  const id = uuidv4();
  const radiusUsername = code;
  const radiusPassword = Math.random().toString(36).substring(2, 14);

  await pool.execute(
    'INSERT INTO smartneti_vouchers (id, plan_id, customer_id, code, radius_username, radius_password, status) VALUES (?, ?, ?, ?, ?, ?, ?)',
    [id, planId, customerId || null, code, radiusUsername, radiusPassword, 'available']
  );

  await rdPool.execute(
    'INSERT INTO radcheck (username, attribute, op, value) VALUES (?, ?, ?, ?)',
    [radiusUsername, 'Cleartext-Password', ':=', radiusPassword]
  );

  return { id, code, radiusUsername, radiusPassword };
}

async function completePayment(reference, gatewayReference) {
  const [payments] = await pool.execute(
    'SELECT id, plan_id, customer_id, status, reference FROM smartneti_payments WHERE reference = ?',
    [reference]
  );

  if (payments.length === 0) {
    throw new Error('Payment not found');
  }

  const payment = payments[0];
  if (payment.status === 'paid') {
    return payment;
  }

  // Generate a voucher for the customer
  const voucher = await generateVoucherForPlan(payment.plan_id, payment.customer_id);

  await pool.execute(
    'UPDATE smartneti_payments SET status = ?, paid_at = NOW(), voucher_id = ?, gateway_reference = ? WHERE id = ?',
    ['paid', voucher.id, gatewayReference || null, payment.id]
  );

  return { ...payment, status: 'paid', voucher };
}

async function getPaymentStatus(reference) {
  const [rows] = await pool.execute(
    `SELECT p.id, p.reference, p.status, p.amount, p.currency, p.gateway, p.paid_at, p.created_at,
            p.voucher_id, v.code AS voucher_code, pl.name AS plan_name, pl.duration_minutes
     FROM smartneti_payments p
     LEFT JOIN smartneti_vouchers v ON p.voucher_id = v.id
     LEFT JOIN smartneti_plans pl ON p.plan_id = pl.id
     WHERE p.reference = ?`,
    [reference]
  );

  if (rows.length === 0) {
    throw new Error('Payment not found');
  }

  return rows[0];
}

module.exports = {
  initiatePayment,
  completePayment,
  getPaymentStatus,
  MOCK_MODE,
};
