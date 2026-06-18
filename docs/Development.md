# Development Guide

## Quick Start

1. Ensure Docker is installed and running.
2. Clone or update the vendored RadiusDesk:
   ```bash
   bash scripts/clone-rdcore.sh
   ```
3. Start the development environment:
   ```bash
   docker-compose up -d
   ```
4. Wait for MariaDB to initialize (first run takes ~60 seconds).
5. Access the services:
   - RadiusDesk: http://localhost
   - SmartNeti Frontend: http://localhost:3000
   - SmartNeti API: http://localhost:3001
   - SmartNeti Analytics: http://localhost:3002

## Testing RADIUS without a MikroTik

Use the `radtest` command inside the RadiusDesk container:

```bash
docker exec -it radiusdesk radtest username password localhost 0 testing123
```

For FreeRADIUS test mode:

```bash
docker exec -it radiusdesk freeradius -X
```

## Project Structure

- `backend/` - SmartNeti API, Session Manager, Analytics service
- `frontend/` - React admin dashboard and customer portal
- `radiusdesk/` - Vendored RadiusDesk (rdcore)
- `branding/` - Logo, CSS, theme files
- `payments/` - Payment gateway adapters
- `analytics/` - Analytics dashboard components
- `docker/` - Docker files and database initialization
- `scripts/` - Helper scripts
- `docs/` - Documentation

## Environment Variables

Copy `backend/.env.example` to `backend/.env` and adjust for your environment.

## Common Commands

```bash
# View logs
docker-compose logs -f

# Rebuild radiusdesk image
docker-compose build radiusdesk

# Stop all services
docker-compose down

# Reset database (deletes all data)
docker-compose down -v
```
