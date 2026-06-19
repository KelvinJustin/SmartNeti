# Memory — SmartNeti PayChangu Integration Session

Last updated: 2026-06-19 20:04 UTC+2

## What was built

### PayChangu Payment Gateway (Full End-to-End)
- `frontend/src/portal/PortalBuy.jsx` — Added PayChangu inline checkout: jQuery loading before popup.js, snake_case config keys (`first_name`, `last_name`, `callback_url`, `return_url`), email input field, `<div id="wrapper"></div>` for iframe injection.
- `frontend/src/portal/PortalPaymentCallback.jsx` — New component handling PayChangu redirect after payment. Extracts `tx_ref` from URL, calls `verifyPayment()` backend endpoint, navigates to payment status page on success.
- `frontend/src/App.jsx` — Added `/portal/payment/callback` route before the parameterized status route to avoid collisions.
- `frontend/src/api/client.js` — Added `verifyPayment(txRef)` POST helper.
- `backend/src/routes/public.js` — PayChangu checkout config enrichment: snake_case keys, `callback_url` (success redirect) and `return_url` (cancel redirect) set via `FRONTEND_URL` env var.
- `backend/src/routes/public.js` — New `POST /payments/verify` endpoint: checks DB for payment status, supports MOCK_MODE auto-complete, calls PayChangu API verification with Bearer token, completes payment on success.
- `backend/src/index.js` — Webhook handler `POST /api/v1/payments/webhook/:gateway` with raw body capture via `express.json({ verify: ... })`, SHA-256 HMAC signature verification, `tx_ref` extraction from multiple payload locations.
- `backend/src/services/payments.js` — `completePayment` now selects `reference` and `customer_id` to return complete payment object.
- `frontend/vite.config.js` — Disabled HMR (`hmr: false`) to prevent WebSocket errors on HTTPS ngrok pages.
- `backend/src/index.js` — CORS now allows `localhost`, `127.0.0.1`, and any `ngrok` domain for development.
- `backend/src/routes/settings.js` & `frontend/src/pages/Settings.jsx` — Added `gateway_paychangu_public_key` setting field.

### Settings Page Fixes
- `frontend/src/pages/Settings.jsx` — Moved `Field` and `SectionCard` components outside main `Settings` component to prevent React remounting causing "dead inputs".
- `backend/src/routes/settings.js` — Added `gateway_paychangu_public_key` to default settings.
- Removed amber warning banner about unimplemented webhooks (PayChangu HMAC is now implemented).

## Decisions made

- **Raw body capture for HMAC**: `express.json({ verify: (req, res, buf) => { req.rawBody = buf.toString('utf8'); } })` captures the exact raw payload before parsing. This is required because PayChangu signs the raw JSON bytes, not a re-stringified object.
- **Webhook secret ≠ API key**: PayChangu uses a separate "Webhook Secret" for signing webhooks, distinct from the API/Secret Key used for checkout. Both must be configured independently in Settings.
- **Callback page handles missing status**: PayChangu redirects to `callback_url` on success without a `?status=success` parameter. The callback page only rejects if status is explicitly non-success, otherwise proceeds to verify via API.
- **Webhook tx_ref extraction priority**: PayChangu webhook payload uses `reference` for their internal charge ID, and may include `tx_ref` for the merchant reference. The webhook handler searches `data.tx_ref`, `data.meta?.tx_ref`, `data.custom?.tx_ref`, then falls back to `data.reference`. If no `tx_ref` is found, the webhook skips gracefully (callback flow handles it).
- **ngrok HMR disabled**: Vite's WebSocket HMR (`ws://`) is blocked on HTTPS pages. Disabled entirely for ngrok dev testing. Re-enable only for localhost development.

## Problems solved

- **"$ is not defined" from popup.js**: Fixed by loading jQuery before PayChangu's popup.js script.
- **403 Forbidden from PayChangu API**: Fixed by changing checkout config keys from camelCase to snake_case per PayChangu docs (`first_name`, `last_name`, `callback_url`, etc).
- **"Cannot read properties of null (reading 'appendChild')"**: Fixed by adding `<div id="wrapper"></div>` that PayChangu's popup.js injects its iframe into.
- **React inputs losing focus / dead inputs on Settings**: Fixed by moving `Field` and `SectionCard` component definitions outside the `Settings` component so they don't re-create on every render.
- **Callback redirecting to localhost**: Fixed by setting `FRONTEND_URL` env var to the ngrok URL so `callback_url` points to the correct public domain.
- **Webhook signature mismatch**: Root cause was `express.raw()` being overwritten by `express.json()`. Fixed by using `express.json({ verify })` to capture raw body alongside parsed JSON.
- **"Payment not found" on webhook**: PayChangu webhook sends their internal `reference` (e.g., `68470271148`), not our `tx_ref`. Fixed to look for `tx_ref` in payload first.
- **"Payment status: unknown" on callback**: PayChangu doesn't include `?status=success` in callback URL. Fixed to only error on explicit non-success status.
- **CORS blocking login on ngrok/localhost**: Temporarily allowed all origins, then restricted to `localhost`, `127.0.0.1`, and `ngrok` domains.
- **Vite WebSocket errors on ngrok HTTPS**: `ws://` blocked by browser on HTTPS pages. Fixed by disabling HMR.

## Current state

**Fully working:**
- Admin dashboard, hotspots, plans, vouchers, payments, analytics, users/customers, captive portal (all from previous sessions)
- PayChangu inline checkout popup from portal buy page
- PayChangu payment callback → frontend verifies with backend → shows voucher on success
- PayChangu webhook → backend HMAC verification → auto-completes payment and generates voucher
- Full flow: click Pay → PayChangu popup → enter mobile money PIN → webhook completes payment → callback page shows success → voucher displayed
- Both localhost and ngrok HTTPS access working

**Locked / intentionally disabled:**
- Airtel Money and TNM Mpamba gateways are hard-disabled in backend (`DISABLED_GATEWAYS` Set) and removed from Settings UI. They cannot be enabled even by DB toggle.

**Remaining:**
- Settings page is functional but could be polished (PayChangu fields only, save action works)
- Voucher PDF export not yet built
- `PAYMENT_MOCK_MODE` is active in development — disable for production
- `FRONTEND_URL` is set to ngrok for dev — must update to production domain before deploying

## Next session starts with

1. **Production readiness checklist**:
   - Update `FRONTEND_URL` to actual production domain
   - Set `PAYMENT_MOCK_MODE=false` and `NODE_ENV=production`
   - Lock down CORS to specific production domain instead of ngrok wildcard
   - Re-enable Vite HMR for local dev only (currently disabled globally)
   - Ensure PayChangu dashboard webhook URL and callback URL point to production domain
2. **Voucher PDF export** — bulk printable sheet with QR codes.
3. **Settings polish** — clean up any remaining gateway references in settings defaults.

## Open questions

- Does PayChangu support test/sandbox mode separate from live? Should we add a toggle in Settings?
- Should successful webhook payments also trigger an email/SMS to the customer with the voucher code?
