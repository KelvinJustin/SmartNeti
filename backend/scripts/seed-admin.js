require('dotenv').config();
const bcrypt = require('bcryptjs');
const { v4: uuidv4 } = require('uuid');
const { pool } = require('../src/db');

const DEFAULT_EMAIL = process.env.SEED_ADMIN_EMAIL || 'admin@smartneti.com';
const DEFAULT_PASSWORD = process.env.SEED_ADMIN_PASSWORD || 'changeme';
const DEFAULT_NAME = process.env.SEED_ADMIN_NAME || 'Administrator';

async function seedAdmin() {
  const [existing] = await pool.execute(
    'SELECT id FROM smartneti_admins LIMIT 1'
  );

  if (existing.length > 0) {
    console.log('Admin account already exists. Skipping seed.');
    await pool.end();
    return;
  }

  const hash = await bcrypt.hash(DEFAULT_PASSWORD, 12);
  const id = uuidv4();

  await pool.execute(
    'INSERT INTO smartneti_admins (id, email, password_hash, full_name, role) VALUES (?, ?, ?, ?, ?)',
    [id, DEFAULT_EMAIL, hash, DEFAULT_NAME, 'admin']
  );

  console.log(`Created admin account: ${DEFAULT_EMAIL} / ${DEFAULT_PASSWORD}`);
  await pool.end();
}

seedAdmin().catch((err) => {
  console.error('Failed to seed admin:', err);
  process.exit(1);
});
