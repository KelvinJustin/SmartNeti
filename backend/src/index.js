require('dotenv').config();
const express = require('express');
const cors = require('cors');
const helmet = require('helmet');

const app = express();
const PORT = process.env.API_PORT || 3001;

app.use(helmet());
app.use(cors());
app.use(express.json());

app.get('/health', (req, res) => {
  res.json({ status: 'ok', service: 'smartneti-api' });
});

app.get('/api/v1/users', (req, res) => {
  res.json({ message: 'Users endpoint placeholder' });
});

app.post('/api/v1/payments/webhook/:gateway', (req, res) => {
  res.json({ message: `Webhook received for ${req.params.gateway}` });
});

app.listen(PORT, '0.0.0.0', () => {
  console.log(`SmartNeti API listening on port ${PORT}`);
});
