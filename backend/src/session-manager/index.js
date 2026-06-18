require('dotenv').config();

const TICK_INTERVAL_MS = 10000;

console.log('SmartNeti Session Manager starting...');
console.log('Responsible for: payment -> RADIUS user, CoA, session expiry, disconnect');

// Placeholder loop
setInterval(() => {
  console.log('Session manager tick: checking active sessions...');
}, TICK_INTERVAL_MS);

process.on('SIGTERM', () => {
  console.log('Session Manager shutting down');
  process.exit(0);
});
