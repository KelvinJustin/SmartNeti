# SmartNeti Architecture

## Overview

SmartNeti is a commercial WiFi hotspot management platform. RadiusDesk is used as an untouched backend AAA (Authentication, Authorization, Accounting) service. All custom branding, payments, analytics, and user-facing portals are built as separate services.

## High-Level Architecture

```
                 Internet
                     │
              MikroTik Router
                     │
             Captive Portal
                     │
        ┌────────────┴────────────┐
        │                         │
   SmartNeti Frontend        RadiusDesk
        │                         │
        │                    FreeRADIUS
        │                         │
        ├────────────┬────────────┤
        │            │            │
   Payments     Analytics     User API
        │            │            │
        └────────────┴────────────┘
                     │
                  MariaDB
```

## Components

### RadiusDesk (Vendored)
- Untouched backend service
- Provides FreeRADIUS, MariaDB, and web UI
- SmartNeti writes directly to its `nas`, `users`, `radcheck`, `radreply` tables

### SmartNeti API
- REST API layer for all external communication
- Handles authentication, user management, payments
- Integrates with RadiusDesk database

### Session Manager
- Critical service for session lifecycle
- Payment confirmed → create RADIUS user
- Send CoA to MikroTik when session starts
- Monitor Interim-Update accounting
- Send Disconnect-Request when session expires

### Payment Adapter
- Pluggable adapters for Airtel Money, TNM Mpamba, cards, PayPal, Stripe
- Webhooks are idempotent and queued via Redis

### Analytics Service
- Separate service with read-only access to RadiusDesk database
- Provides dashboards and reporting

### Frontend
- Admin dashboard, customer portal, captive portal
- Communicates with SmartNeti API

## Integration Strategy

SmartNeti writes directly to RadiusDesk's MariaDB tables rather than calling RadiusDesk's internal CakePHP endpoints. This is more reliable for our scale, though it couples us to RadiusDesk's schema. The targeted schema version is documented in `VENDOR_PINS.md`.
