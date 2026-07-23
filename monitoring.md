# Ubuntu Server Management, Health & Monitoring Guide
## SmartNeti / PHPNuxBill / FreeRADIUS Production Server

> Version: 1.0
> Target OS: Ubuntu Server 24.04 LTS
> Environment: Dedicated Laptop Server
> Purpose: Ensure long-term stability, reliability, and proactive monitoring for a SmartNeti (PHPNuxBill) ISP billing server.

---

# Overview

A production server should not only host services—it should continuously monitor its own health, detect failures early, and provide sufficient diagnostics to prevent downtime.

For a SmartNeti deployment running:

- Apache/Nginx
- PHP
- MariaDB/MySQL
- FreeRADIUS
- Cron
- MikroTik API integrations

the following utilities provide an excellent lightweight monitoring stack without consuming significant system resources.

---

# Installed Utilities

| Tool | Purpose | Resource Usage |
|-------|----------|---------------|
| TLP | Laptop power management | Very Low |
| lm-sensors | Temperature monitoring | Very Low |
| htop | Process monitor | Very Low |
| smartmontools | Disk health (SMART) | Very Low |
| nvme-cli | NVMe SSD diagnostics | Very Low |

---

# 1. TLP

## Purpose

TLP automatically optimizes laptop hardware for efficiency.

Although primarily intended for battery-powered systems, it also reduces:

- heat generation
- unnecessary power draw
- fan noise
- hardware wear

This is especially beneficial when using a laptop as a 24/7 dedicated server.

---

## Service

```bash
sudo systemctl status tlp
```

---

## Start

```bash
sudo systemctl start tlp
```

---

## Enable on boot

```bash
sudo systemctl enable tlp
```

---

## View Status

```bash
sudo tlp-stat -s
```

Example

```
Status = enabled

Mode = AC
```

---

## View Configuration

```bash
sudo tlp-stat -c
```

---

## View Everything

```bash
sudo tlp-stat
```

---

## Recommended Server Configuration

Edit

```bash
sudo nano /etc/tlp.conf
```

Append:

```ini
CPU_SCALING_GOVERNOR_ON_AC=performance

WIFI_PWR_ON_AC=off

USB_AUTOSUSPEND=0

PCIE_ASPM_ON_AC=default

RUNTIME_PM_ON_AC=on
```

Restart

```bash
sudo systemctl restart tlp
```

---

# 2. lm-sensors

## Purpose

Reads motherboard temperature sensors.

Provides monitoring for:

- CPU temperature
- Core temperatures
- Motherboard sensors
- Fan speeds
- Voltage readings

---

## Initial Detection

Run once:

```bash
sudo sensors-detect
```

Answer **YES** to all prompts.

---

## View Temperatures

```bash
sensors
```

Example

```
Package id 0:

+44°C

Core 0

+41°C

Core 1

+42°C
```

---

## Live Monitoring

```bash
watch -n 2 sensors
```

Updates every two seconds.

Stop

```
Ctrl+C
```

---

## Healthy CPU Temperatures

| State | Temperature |
|----------|------------|
| Idle | 35–50°C |
| Normal Load | 50–70°C |
| Heavy Load | 70–85°C |
| Critical | Above 90°C |

---

# 3. htop

## Purpose

Interactive process manager.

Useful for monitoring:

- CPU usage
- RAM usage
- Swap
- Running processes
- Process priority
- Load averages

---

Launch

```bash
htop
```

---

Useful Keys

| Key | Function |
|------|----------|
| F3 | Search |
| F4 | Filter |
| F5 | Tree View |
| F6 | Sort |
| F9 | Kill Process |
| F10 | Quit |

---

Look for

- Apache processes
- MariaDB
- FreeRADIUS
- PHP
- CPU spikes
- Memory leaks

---

# 4. smartmontools

## Purpose

Reads SMART data from HDDs and SSDs.

Can detect:

- failing drives
- excessive writes
- bad sectors
- overheating
- drive age
- predicted failures

---

Identify disks

```bash
lsblk
```

Example

```
sda

nvme0n1
```

---

View SMART Health

For SATA

```bash
sudo smartctl -H /dev/sda
```

For NVMe

```bash
sudo smartctl -H /dev/nvme0
```

Expected

```
PASSED
```

---

Full Drive Report

```bash
sudo smartctl -a /dev/sda
```

---

Run Short Test

```bash
sudo smartctl -t short /dev/sda
```

---

Run Long Test

```bash
sudo smartctl -t long /dev/sda
```

---

View Results

```bash
sudo smartctl -a /dev/sda
```

---

Important SMART Values

- Reallocated Sectors
- Pending Sectors
- Offline Uncorrectable
- Temperature
- Power On Hours
- Wear Level

---

# 5. nvme-cli

## Purpose

Advanced diagnostics for NVMe SSDs.

Provides:

- SSD health
- Remaining lifespan
- Temperature
- Error logs
- Firmware version
- Power cycles

---

List NVMe Drives

```bash
sudo nvme list
```

---

Health Information

```bash
sudo nvme smart-log /dev/nvme0
```

Displays

- percentage used
- available spare
- temperature
- media errors
- unsafe shutdowns

---

Controller Information

```bash
sudo nvme id-ctrl /dev/nvme0
```

---

Error Log

```bash
sudo nvme error-log /dev/nvme0
```

---

# Additional Useful Commands

## Memory

```bash
free -h
```

---

## Disk Usage

```bash
df -h
```

---

## Mounted Drives

```bash
lsblk -f
```

---

## Running Services

```bash
systemctl --type=service --state=running
```

---

## Failed Services

```bash
systemctl --failed
```

---

## Network Interfaces

```bash
ip addr
```

---

## Listening Ports

```bash
sudo ss -tulpn
```

---

## CPU Information

```bash
lscpu
```

---

## Uptime

```bash
uptime
```

---

## Kernel Version

```bash
uname -r
```

---

# Monitoring SmartNeti Services

Verify Apache

```bash
sudo systemctl status apache2
```

---

Verify MariaDB

```bash
sudo systemctl status mysql
```

or

```bash
sudo systemctl status mariadb
```

---

Verify FreeRADIUS

```bash
sudo systemctl status freeradius
```

---

View Apache Logs

```bash
sudo journalctl -u apache2
```

---

View FreeRADIUS Logs

```bash
sudo journalctl -u freeradius
```

---

View MySQL Logs

```bash
sudo journalctl -u mysql
```

---

# Daily Health Checklist

✔ CPU temperature below 70°C

✔ RAM usage below 80%

✔ Disk usage below 85%

✔ SMART status PASSED

✔ No failed services

✔ Apache active

✔ MariaDB active

✔ FreeRADIUS active

✔ Network reachable

✔ Internet connectivity verified

---

# Weekly Checklist

- Review SMART data
- Check available storage
- Install security updates
- Review system logs
- Verify database backups
- Restart after kernel updates if required
- Confirm FreeRADIUS accounting is functioning
- Test customer authentication
- Test MikroTik API communication
- Validate hotspot login flow

---

# Recommended Future Additions

As your SmartNeti deployment grows, consider adding:

| Tool | Purpose |
|------|---------|
| btop | Modern replacement for htop with graphs |
| glances | Comprehensive web-based system monitoring |
| vnStat | Long-term network traffic statistics |
| fail2ban | Automatic protection against brute-force attacks |
| UFW | Host firewall management |
| logrotate | Automatic log rotation (usually enabled by default) |
| unattended-upgrades | Automatic installation of security updates |
| Prometheus Node Exporter | Metrics for Prometheus |
| Grafana | Dashboards and visualization |
| Netdata | Real-time monitoring with a web UI |

---

# Recommended Monitoring Stack for SmartNeti

## Essential

- TLP
- lm-sensors
- htop
- smartmontools
- nvme-cli

## Security

- UFW
- Fail2Ban

## Networking

- vnStat

## Logs

- journalctl
- logrotate

## Advanced Monitoring

- Netdata
- Grafana
- Prometheus

---

# Conclusion

This lightweight monitoring stack provides excellent visibility into the health of a dedicated Ubuntu Server laptop hosting SmartNeti, PHPNuxBill, FreeRADIUS, and related services. It enables proactive maintenance through hardware health checks, temperature monitoring, process inspection, and storage diagnostics while keeping resource usage minimal. As the deployment grows, additional tools like Netdata, Grafana, and Prometheus can be introduced to build a comprehensive production-grade monitoring solution.