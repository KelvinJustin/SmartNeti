# SmartNeti

Commercial WiFi hotspot management platform built on top of RadiusDesk.

## Architecture

SmartNeti treats RadiusDesk as a backend AAA service. All custom branding, payments, analytics, and user-facing portals are built as separate services that integrate with RadiusDesk through the database and RADIUS protocol.

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

## Development

This project uses Docker for local development on Windows and native Ubuntu for production.

### Quick Start

1. Clone this repository
2. Run `docker-compose up -d`
3. Access RadiusDesk at http://localhost
4. Access SmartNeti frontend at http://localhost:3000

See `docs/Development.md` for detailed setup.

## Production

Production deployment uses native Ubuntu 22.04 LTS. See `docs/Deployment.md`.

## License

Proprietary - SmartNeti commercial platform.
