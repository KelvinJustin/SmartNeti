const express = require('express');
const { v4: uuidv4 } = require('uuid');
const { pool, rdPool } = require('../db');

const router = express.Router();

async function syncNas(hotspot) {
  if (!hotspot.nas_ip) return;

  try {
    const [existing] = await rdPool.execute(
      'SELECT id FROM nas WHERE nasname = ?',
      [hotspot.nas_ip]
    );

    if (existing.length > 0) {
      await rdPool.execute(
        'UPDATE nas SET shortname = ?, secret = ?, description = ? WHERE id = ?',
        [hotspot.name, hotspot.radius_secret, hotspot.name, existing[0].id]
      );
    } else {
      await rdPool.execute(
        'INSERT INTO nas (nasname, shortname, type, secret, description, connection_type) VALUES (?, ?, ?, ?, ?, ?)',
        [hotspot.nas_ip, hotspot.name, 'other', hotspot.radius_secret, hotspot.name, 'direct']
      );
    }
  } catch (err) {
    console.error('Failed to sync hotspot to RadiusDesk nas:', err);
  }
}

function requireAuth(req, res, next) {
  if (!req.session || !req.session.userId) {
    return res.status(401).json({ error: 'Unauthorized' });
  }
  next();
}

router.get('/', requireAuth, async (req, res) => {
  try {
    const [rows] = await pool.execute(
      'SELECT id, name, location, nas_identifier, nas_ip, radius_secret, status, created_at, updated_at FROM smartneti_hotspots ORDER BY created_at DESC'
    );
    res.json({ hotspots: rows });
  } catch (err) {
    console.error('Failed to list hotspots:', err);
    res.status(500).json({ error: 'Internal server error' });
  }
});

router.get('/:id', requireAuth, async (req, res) => {
  try {
    const [rows] = await pool.execute(
      'SELECT id, name, location, nas_identifier, nas_ip, radius_secret, status, created_at, updated_at FROM smartneti_hotspots WHERE id = ?',
      [req.params.id]
    );

    if (rows.length === 0) {
      return res.status(404).json({ error: 'Hotspot not found' });
    }

    res.json({ hotspot: rows[0] });
  } catch (err) {
    console.error('Failed to get hotspot:', err);
    res.status(500).json({ error: 'Internal server error' });
  }
});

router.post('/', requireAuth, async (req, res) => {
  const { name, location, nasIdentifier, nasIp, radiusSecret, status } = req.body;

  if (!name || !radiusSecret) {
    return res.status(400).json({ error: 'Name and RADIUS secret are required' });
  }

  const id = uuidv4();

  try {
    await pool.execute(
      'INSERT INTO smartneti_hotspots (id, name, location, nas_identifier, nas_ip, radius_secret, status) VALUES (?, ?, ?, ?, ?, ?, ?)',
      [id, name, location || null, nasIdentifier || null, nasIp || null, radiusSecret, status || 'active']
    );

    const [rows] = await pool.execute(
      'SELECT id, name, location, nas_identifier, nas_ip, radius_secret, status, created_at, updated_at FROM smartneti_hotspots WHERE id = ?',
      [id]
    );

    await syncNas(rows[0]);

    res.status(201).json({ hotspot: rows[0] });
  } catch (err) {
    console.error('Failed to create hotspot:', err);
    res.status(500).json({ error: 'Internal server error' });
  }
});

router.patch('/:id', requireAuth, async (req, res) => {
  const { name, location, nasIdentifier, nasIp, radiusSecret, status } = req.body;

  try {
    const [existing] = await pool.execute(
      'SELECT id FROM smartneti_hotspots WHERE id = ?',
      [req.params.id]
    );

    if (existing.length === 0) {
      return res.status(404).json({ error: 'Hotspot not found' });
    }

    await pool.execute(
      'UPDATE smartneti_hotspots SET name = ?, location = ?, nas_identifier = ?, nas_ip = ?, radius_secret = ?, status = ? WHERE id = ?',
      [
        name ?? existing[0].name,
        location ?? existing[0].location,
        nasIdentifier ?? existing[0].nas_identifier,
        nasIp ?? existing[0].nas_ip,
        radiusSecret ?? existing[0].radius_secret,
        status ?? existing[0].status,
        req.params.id,
      ]
    );

    const [rows] = await pool.execute(
      'SELECT id, name, location, nas_identifier, nas_ip, radius_secret, status, created_at, updated_at FROM smartneti_hotspots WHERE id = ?',
      [req.params.id]
    );

    await syncNas(rows[0]);

    res.json({ hotspot: rows[0] });
  } catch (err) {
    console.error('Failed to update hotspot:', err);
    res.status(500).json({ error: 'Internal server error' });
  }
});

router.delete('/:id', requireAuth, async (req, res) => {
  try {
    const [result] = await pool.execute(
      'DELETE FROM smartneti_hotspots WHERE id = ?',
      [req.params.id]
    );

    if (result.affectedRows === 0) {
      return res.status(404).json({ error: 'Hotspot not found' });
    }

    res.json({ status: 'ok' });
  } catch (err) {
    console.error('Failed to delete hotspot:', err);
    res.status(500).json({ error: 'Internal server error' });
  }
});

module.exports = router;
