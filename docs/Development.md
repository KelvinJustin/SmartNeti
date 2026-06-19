# Development Guide

## Prerequisites

- **Docker Desktop** installed and running (Windows)
- **Docker Compose** available (included with Docker Desktop)
- **Git** installed
- At least **4 GB RAM** allocated to Docker Desktop
- At least **10 GB free disk space** for images and volumes

## Starting the Project with Docker

### 1. Clone the RadiusDesk dependency

RadiusDesk is vendored and pinned. Run the helper script to clone it at the correct commit:

```bash
bash scripts/clone-rdcore.sh
```

On Windows (if Git Bash is unavailable):

```powershell
git clone https://github.com/RADIUSdesk/rdcore.git radiusdesk/rdcore
cd radiusdesk/rdcore
git checkout 109192d8e56e20f13c30c74237edc39500f414d0
cd ../..
```

### 2. Start all services

From the project root:

```bash
docker-compose up -d
```

The first run will:
- Pull `mariadb:10.5`, `redis:7-alpine`, and `node:20-slim` images
- Build the `smartneti-radiusdesk` image from `docker/Dockerfile-radiusdesk`
- Initialize the MariaDB database with RadiusDesk tables and patches

This can take **10–20 minutes** on first run. The radiusdesk image build alone is large because it copies the entire vendored RadiusDesk source.

### 3. Wait for MariaDB to be healthy

MariaDB initialization imports a large SQL file and applies many patches. Check readiness:

```bash
docker ps
```

Wait until `radiusdesk-mariadb` shows `(healthy)`.

### 4. Verify all services

```bash
curl http://localhost:3001/health
curl http://localhost:3002/health
curl http://localhost:3000/health
curl http://localhost
```

Expected results:

| Service | URL | Expected |
|---------|-----|----------|
| RadiusDesk | http://localhost | HTML login page |
| SmartNeti Frontend | http://localhost:3000 | HTML placeholder |
| SmartNeti API | http://localhost:3001/health | `{"status":"ok"}` |
| SmartNeti Analytics | http://localhost:3002/health | `{"status":"ok"}` |
| MariaDB | localhost:3306 | Available via `rd`/`rd` |
| Redis | localhost:6379 | Available |

### 5. Default credentials

- **RadiusDesk database user:** `rd` / `rd`
- **MariaDB root:** no password (empty)
- **FreeRADIUS test secret:** `testing123`

## Stopping the Project

```bash
# Stop containers but keep data
docker-compose down

# Stop containers and delete all data (volumes)
docker-compose down -v
```

## Restarting After Code Changes

```bash
# Restart all services
docker-compose restart

# Restart a specific service
docker-compose restart smartneti-api
```

For Node.js services, the container runs `npm install` on startup, so dependency changes are picked up automatically.

## Viewing Logs

```bash
# All services
docker-compose logs -f

# Specific service
docker-compose logs -f radiusdesk
docker-compose logs -f smartneti-api
docker-compose logs -f radiusdesk-mariadb

# Last 50 lines
docker-compose logs --tail 50 radiusdesk
```

## Troubleshooting

### Docker daemon not reachable

If you see `failed to connect to the docker API`, start Docker Desktop and wait for it to fully initialize.

### MariaDB stays unhealthy

The database initialization takes several minutes. If it fails, reset and retry:

```bash
docker-compose down -v
docker-compose up -d
```

### Port already in use

The project uses ports **80, 3306, 6379, 3000, 3001, 3002, 1812, 1813**. Close any local services using these ports, or edit `docker-compose.yml` to change them.

### Rebuilding the radiusdesk image

After changing `docker/Dockerfile-radiusdesk` or RadiusDesk files:

```bash
docker-compose build radiusdesk
docker-compose up -d
```

## Testing RADIUS without a MikroTik

Use the `radtest` command inside the RadiusDesk container:

```bash
docker exec -it radiusdesk radtest username password localhost 0 testing123
```

For FreeRADIUS debug mode:

```bash
docker exec -it radiusdesk freeradius -X
```

## Project Structure

- `backend/` - SmartNeti API, Session Manager, Analytics service
- `frontend/` - Admin dashboard and customer portal placeholder
- `radiusdesk/` - Vendored RadiusDesk (rdcore)
- `branding/` - Logo, CSS, theme files
- `payments/` - Payment gateway adapters
- `analytics/` - Analytics dashboard components
- `docker/` - Docker files and database initialization
- `scripts/` - Helper scripts
- `docs/` - Documentation

## Environment Variables

Copy `backend/.env.example` to `backend/.env` and adjust for your environment. The container mounts the backend directory, so changes are picked up on restart.
