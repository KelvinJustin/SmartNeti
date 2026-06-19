require('dotenv').config();
const express = require('express');
const cors = require('cors');
const helmet = require('helmet');

const app = express();
const PORT = process.env.ANALYTICS_PORT || 3002;
const FRONTEND_URL = process.env.FRONTEND_URL || 'http://localhost:3000';

const corsOptions = {
  origin: FRONTEND_URL,
  credentials: true,
};

app.use(helmet());
app.use(cors(corsOptions));
app.use(express.json());

app.get('/health', (req, res) => {
  res.json({ status: 'ok', service: 'smartneti-analytics' });
});

app.get('/api/v1/analytics/sessions', (req, res) => {
  res.json({ message: 'Session analytics placeholder' });
});

app.listen(PORT, '0.0.0.0', () => {
  console.log(`SmartNeti Analytics listening on port ${PORT}`);
});
