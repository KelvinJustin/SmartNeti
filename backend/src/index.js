require('dotenv').config();
const express = require('express');
const cors = require('cors');
const helmet = require('helmet');
const session = require('express-session');
const { createClient } = require('redis');
const RedisStore = require('connect-redis').default;
const bcrypt = require('bcryptjs');
const crypto = require('crypto');
const path = require('path');
const fs = require('fs');
const { v4: uuidv4 } = require('uuid');
const { pool, rdPool } = require('./db');
const hotspotsRouter = require('./routes/hotspots');
const plansRouter = require('./routes/plans');
const vouchersRouter = require('./routes/vouchers');
const publicRouter = require('./routes/public');
const paymentsAdminRouter = require('./routes/payments');
const captiveRouter = require('./routes/captive');
const analyticsRouter = require('./routes/analytics');
const customersRouter = require('./routes/customers');
const settingsRouter = require('./routes/settings');
const { completePayment, MOCK_MODE } = require('./services/payments');

const app = express();

const PORT = process.env.PORT || process.env.API_PORT || 3001;
const FRONTEND_URL = process.env.FRONTEND_URL || 'http://localhost:3000';
const SESSION_SECRET = process.env.SESSION_SECRET || 'dev-secret-change-me';
const SESSION_MAX_AGE = Number(process.env.SESSION_MAX_AGE_MS || 24 * 60 * 60 * 1000);

const ALLOWED_ORIGINS = [
  FRONTEND_URL,
  'https://imprecatory-unobligative-genna.ngrok-free.dev',
].filter(Boolean);

const corsOptions = {
  origin: (origin, callback) => {
    if (!origin) return callback(null, true);
    if (origin.includes('localhost') || origin.includes('127.0.0.1') || origin.includes('ngrok')) {
      return callback(null, true);
    }
    if (ALLOWED_ORIGINS.includes(origin)) {
      return callback(null, true);
    }
    callback(new Error('Not allowed by CORS'));
  },
  credentials: true,
};

const redisClient = createClient({
  socket: {
    host: process.env.REDIS_HOST || 'redis',
    port: Number(process.env.REDIS_PORT || 6379),
  },
});
redisClient.connect().catch((err) => {
  console.error('Redis connection failed:', err.message);
});

app.use(helmet());
app.use(cors(corsOptions));

const useRedis = !!process.env.REDIS_HOST;
const sessionStore = useRedis ? new RedisStore({ client: redisClient }) : undefined;

if (!useRedis) {
  console.warn('[session] REDIS_HOST not set — using MemoryStore (sessions will not persist across restarts)');
}

app.use(
  session({
    name: 'smartneti.sid',
    store: sessionStore,
    secret: SESSION_SECRET,
    resave: false,
    saveUninitialized: false,
    cookie: {
      httpOnly: true,
      secure: false,
      sameSite: 'lax',
      maxAge: SESSION_MAX_AGE,
    },
  })
);

// Capture raw body alongside JSON parsing (needed for webhook HMAC verification)
app.use(express.json({
  verify: (req, res, buf) => {
    req.rawBody = buf.toString('utf8');
  }
}));

function requireAuth(req, res, next) {
  if (!req.session || !req.session.userId) {
    return res.status(401).json({ error: 'Unauthorized' });
  }
  next();
}

app.get('/health', (req, res) => {
  res.json({ status: 'ok', service: 'smartneti-api' });
});

app.get('/api/v1/users', (req, res) => {
  res.json({ message: 'Users endpoint placeholder' });
});

app.post('/api/v1/auth/login', async (req, res) => {
  const { email, password } = req.body;
  if (!email || !password) {
    return res.status(400).json({ error: 'Email and password are required' });
  }

  try {
    const [rows] = await pool.execute(
      'SELECT id, email, password_hash, full_name, role FROM smartneti_admins WHERE email = ?',
      [email]
    );

    if (rows.length === 0) {
      return res.status(401).json({ error: 'Invalid credentials' });
    }

    const admin = rows[0];
    const valid = await bcrypt.compare(password, admin.password_hash);
    if (!valid) {
      return res.status(401).json({ error: 'Invalid credentials' });
    }

    req.session.userId = admin.id;
    req.session.user = {
      id: admin.id,
      email: admin.email,
      fullName: admin.full_name,
      role: admin.role,
    };

    res.json({ user: req.session.user });
  } catch (err) {
    console.error('Login error:', err);
    res.status(500).json({ error: 'Internal server error' });
  }
});

app.post('/api/v1/auth/logout', (req, res) => {
  req.session.destroy((err) => {
    if (err) {
      console.error('Logout error:', err);
      return res.status(500).json({ error: 'Failed to logout' });
    }
    res.clearCookie('smartneti.sid');
    res.json({ status: 'ok' });
  });
});

app.get('/api/v1/auth/me', requireAuth, (req, res) => {
  res.json({ user: req.session.user });
});

app.get('/api/v1/dashboard/stats', requireAuth, async (req, res) => {
  try {
    const [[hotspotRow]] = await pool.execute(
      'SELECT COUNT(*) AS activeHotspots FROM smartneti_hotspots WHERE status = "active"'
    );

    const [[onlineRow]] = await rdPool.execute(
      'SELECT COUNT(DISTINCT username) AS onlineUsers FROM radacct WHERE acctstoptime IS NULL'
    );

    const [[vouchersSoldRow]] = await pool.execute(
      'SELECT COUNT(*) AS vouchersSold FROM smartneti_payments WHERE status = "paid" AND paid_at >= CURDATE()'
    );

    const [[revenueRow]] = await pool.execute(
      'SELECT COALESCE(SUM(amount), 0) AS revenue FROM smartneti_payments WHERE status = "paid" AND paid_at >= CURDATE()'
    );

    const [activityRows] = await pool.execute(
      `SELECT p.id, p.amount, p.currency, p.status, p.paid_at, p.created_at,
              c.full_name AS customer_name, c.phone AS customer_phone,
              pl.name AS plan_name, v.code AS voucher_code
       FROM smartneti_payments p
       LEFT JOIN smartneti_customers c ON p.customer_id = c.id
       LEFT JOIN smartneti_plans pl ON p.plan_id = pl.id
       LEFT JOIN smartneti_vouchers v ON p.voucher_id = v.id
       ORDER BY p.created_at DESC
       LIMIT 10`
    );

    res.json({
      onlineUsers: onlineRow.onlineUsers || 0,
      activeHotspots: hotspotRow.activeHotspots || 0,
      vouchersSold: vouchersSoldRow.vouchersSold || 0,
      revenue: revenueRow.revenue || 0,
      recentActivity: activityRows,
    });
  } catch (err) {
    console.error('Failed to load dashboard stats:', err);
    res.status(500).json({ error: 'Internal server error' });
  }
});

app.use('/api/v1/hotspots', hotspotsRouter);
app.use('/api/v1/plans', plansRouter);
app.use('/api/v1/vouchers', vouchersRouter);
app.use('/api/v1/public', publicRouter);
app.use('/api/v1/captive', captiveRouter);
app.use('/api/v1/payments', paymentsAdminRouter);
app.use('/api/v1/analytics', analyticsRouter);
app.use('/api/v1/customers', customersRouter);
app.use('/api/v1/settings', settingsRouter);

const ALLOWED_WEBHOOK_GATEWAYS = ['airtel-money', 'tnm-mpamba', 'paychangu'];

async function getWebhookSecret(gateway) {
  const envKey = `WEBHOOK_SECRET_${gateway.toUpperCase().replace(/-/g, '_')}`;
  const envSecret = process.env[envKey];
  if (envSecret) return envSecret;

  try {
    const [rows] = await pool.execute(
      'SELECT setting_value FROM smartneti_settings WHERE setting_key = ?',
      [`gateway_${gateway}_webhook_secret`]
    );
    if (rows.length > 0) return rows[0].setting_value;
  } catch (err) {
    console.error('Failed to load webhook secret from settings:', err);
  }
  return undefined;
}

function verifyWebhookSignature(gateway, payload, signature, secret) {
  if (gateway === 'paychangu') {
    const computed = crypto.createHmac('sha256', secret).update(payload).digest('hex');
    console.log(`[webhook:${gateway}] sig check: received=${signature?.substring(0, 20)}... computed=${computed?.substring(0, 20)}... secret=${secret?.substring(0, 8)}... payload_snippet=${payload?.substring(0, 100)?.replace(/\n/g, ' ')}`);
    return computed === signature;
  }
  // TODO: implement other gateway-specific verification
  return false;
}

app.post('/api/v1/payments/webhook/:gateway', async (req, res) => {
  const { gateway } = req.params;

  if (!ALLOWED_WEBHOOK_GATEWAYS.includes(gateway)) {
    return res.status(400).json({ error: 'Unsupported gateway' });
  }

  const signature = req.headers['signature'] || req.headers['x-webhook-signature'] || req.headers['x-signature'];
  const secret = await getWebhookSecret(gateway);
  const payload = req.rawBody || (req.body && req.body.toString ? req.body.toString('utf8') : JSON.stringify(req.body));

  console.log(`[webhook:${gateway}] received. signature=${signature ? 'present' : 'MISSING'} secret=${secret ? 'present' : 'MISSING'} payload_len=${payload?.length || 0}`);

  if (!MOCK_MODE) {
    if (!secret) {
      console.log(`[webhook:${gateway}] rejected: secret not configured`);
      return res.status(401).json({ error: 'Webhook secret not configured' });
    }
    if (!signature) {
      console.log(`[webhook:${gateway}] rejected: missing signature`);
      return res.status(401).json({ error: 'Missing webhook signature' });
    }
    if (!verifyWebhookSignature(gateway, payload, signature, secret)) {
      console.log(`[webhook:${gateway}] rejected: signature mismatch`);
      return res.status(401).json({ error: 'Invalid webhook signature' });
    }
    console.log(`[webhook:${gateway}] signature verified OK`);
  } else {
    console.log(`[webhook:${gateway}] MOCK_MODE: skipping signature check`);
  }

  try {
    const data = JSON.parse(payload);
    console.log(`[webhook:${gateway}] payload:`, JSON.stringify(data, null, 2));
    // PayChangu webhook uses 'reference' for their internal ref; our tx_ref may be in tx_ref, meta, or charge_id
    const txRef = data.tx_ref || data.meta?.tx_ref || data.custom?.tx_ref || data.reference;
    const gatewayRef = data.charge_id || data.reference || data.transactionId || null;
    console.log(`[webhook:${gateway}] txRef=${txRef} gatewayRef=${gatewayRef} status=${data.status}`);

    if (!txRef) {
      console.log(`[webhook:${gateway}] no tx_ref found, skipping (callback flow will handle)`);
      return res.json({ status: 'ok', note: 'no tx_ref' });
    }

    if (data.status === 'success' || data.status === 'completed') {
      try {
        const completed = await completePayment(txRef, gatewayRef);
        console.log(`[webhook:${gateway}] payment completed:`, completed.reference);
      } catch (err) {
        if (err.message === 'Payment not found') {
          console.log(`[webhook:${gateway}] payment ${txRef} not found yet, callback may handle it`);
        } else {
          throw err;
        }
      }
    } else {
      console.log(`[webhook:${gateway}] no action: status=${data.status}`);
    }
  } catch (err) {
    console.error('Webhook processing error:', err);
  }

  res.json({ status: 'ok', gateway });
});

// Serve built frontend static files
app.use(express.static(path.join(__dirname, '../dist')));

// SPA fallback — serve index.html for any non-API, non-asset route
app.get('*', (req, res) => {
  if (req.path.startsWith('/api/v1/') || req.path.startsWith('/assets/')) {
    return res.status(404).json({ error: 'Not found' });
  }
  const indexPath = path.join(__dirname, '../dist/index.html');
  if (!fs.existsSync(indexPath)) {
    return res.status(404).json({ error: 'Frontend not built. dist/index.html missing.' });
  }
  res.sendFile(indexPath);
});

app.use((err, req, res, next) => {
  console.error('Unhandled error:', err);
  res.status(500).json({ error: 'Internal server error' });
});

// Startup diagnostics for static files
const distPath = path.join(__dirname, '../dist');
if (fs.existsSync(distPath)) {
  console.log(`[startup] dist exists at ${distPath}`);
  try {
    const files = fs.readdirSync(distPath);
    console.log(`[startup] dist contents:`, files.slice(0, 20));
    const assetsPath = path.join(distPath, 'assets');
    if (fs.existsSync(assetsPath)) {
      console.log(`[startup] assets contents:`, fs.readdirSync(assetsPath).slice(0, 20));
    } else {
      console.warn(`[startup] assets/ not found in dist`);
    }
  } catch (e) {
    console.error('[startup] failed to read dist:', e.message);
  }
} else {
  console.warn(`[startup] dist NOT FOUND at ${distPath}. Frontend will not be served.`);
}

app.listen(PORT, '0.0.0.0', () => {
  console.log(`SmartNeti API listening on port ${PORT}`);
});
