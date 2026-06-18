# Vendor Pinned Dependencies

This file locks the exact versions of external dependencies used by SmartNeti. Do not upgrade these without full testing.

## RadiusDesk (rdcore)

- **Repository:** https://github.com/RADIUSdesk/rdcore.git
- **Pinned commit:** `109192d8e56e20f13c30c74237edc39500f414d0`
- **Reason:** RadiusDesk is a CakePHP monolith. Upgrading silently can break production hotspots.

## Ubuntu

- **Target version:** Ubuntu Server 22.04 LTS
- **Reason:** Better tested for RadiusDesk install scripts and PHP 8.1/CakePHP 4.x stability.

## Docker Images

- **MariaDB:** bitnami/mariadb:10.5
- **Redis:** redis:7-alpine
- **Nginx:** nginx:stable-alpine

## Update Process

1. Test the new version in a separate environment.
2. Update this file with the new version.
3. Commit the change.
4. Deploy to production only after staging validation.
