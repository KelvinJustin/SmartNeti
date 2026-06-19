# SmartNeti Build Roadmap

Last updated: 2026-06-19

## Current Status

- Admin dashboard shell is built (Vite + React + Tailwind + React Router).
- Dark mode is enabled and is the default theme.
- Sidebar navigation is in place for Dashboard, Hotspots, Vouchers, Users, Payments, Analytics, Settings.
- Dashboard overview page loads live stats from `/api/v1/dashboard/stats` (currently hardcoded placeholders).
- Backend API is an Express scaffold with CORS locked to `FRONTEND_URL` and webhook validation placeholders.

## Next Steps

### Priority 1: Authentication

- Build a login page at `/login`.
- Add a backend auth endpoint (`/api/v1/auth/login`) with password hashing (bcrypt) and signed tokens (JWT or sessions).
- Add protected routes in the React app so unauthenticated users are redirected to `/login`.
- Add a logout button to the admin header.

### Priority 2: Database Schema & Real Data Layer

- Decide on the database split: SmartNeti tables vs. direct RadiusDesk tables.
- Define core tables:
  - `hotspots` — id, name, location, nas_ip, radius_secret, status, created_at
  - `plans` — id, name, speed_limit, duration_minutes, price, created_at
  - `vouchers` — id, code, plan_id, status, used_at, created_at
  - `payments` — id, gateway, amount, currency, status, reference, created_at
  - `customers` — id, phone, email, created_at
- Wire the dashboard stats endpoint to real SQL queries.

### Priority 3: Hotspot Management

- Complete the `/hotspots` page:
  - Data table with search and pagination
  - Add/edit/delete forms
  - Status toggle
- Backend CRUD endpoints: `GET /api/v1/hotspots`, `POST /api/v1/hotspots`, `PATCH /api/v1/hotspots/:id`, `DELETE /api/v1/hotspots/:id`.
- Synchronize hotspot NAS configuration to the RadiusDesk `nas` table.

### Priority 4: Voucher & Plan Management

- Plan builder page (`/plans` or `/vouchers/plans`).
- Voucher generation with batch codes (`/vouchers/generate`).
- Write vouchers to RadiusDesk `radcheck` and/or `users` tables.
- Printable voucher page / PDF export.

### Priority 5: Customer Portal

- Public-facing portal (separate route or subdomain) where users can:
  - View available plans
  - Purchase a plan via Airtel Money / TNM Mpamba
  - Enter a voucher code to get online

### Priority 6: Captive Portal + MikroTik Integration

- Simple `/captive` page for MikroTik hotspot redirect.
- Voucher validation endpoint (`/api/v1/captive/authorize`).
- Integration with FreeRADIUS for authorization and accounting.

### Priority 7: Harden Payment Webhooks

- Replace the placeholder `verifyWebhookSignature` in `backend/src/index.js` with gateway-specific HMAC verification.
- Implement idempotency for webhook events (queue via Redis, store processed references).

### Priority 8: Remaining Code Review Items

- Fix duplicate `PHP_INI_SCAN_DIR` value in `docker-compose.yml`.
- Remove FreeRADIUS `-X` debug mode from `docker/supervisord.conf` for production.
- Clean up `session-manager` interval cleanup on shutdown.
- Add error handling and 404/500 handlers in the backend API.

## Recommended Next Task

Start with **Priority 1: Authentication** and **Priority 2: Database Schema**. These unlock the rest of the app and make the dashboard a real, secure product.
