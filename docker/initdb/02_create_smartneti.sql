CREATE DATABASE IF NOT EXISTS smartneti
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE smartneti;

CREATE TABLE IF NOT EXISTS smartneti_admins (
  id CHAR(36) NOT NULL PRIMARY KEY,
  email VARCHAR(255) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  full_name VARCHAR(255) NOT NULL,
  role VARCHAR(50) NOT NULL DEFAULT 'admin',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS smartneti_hotspots (
  id CHAR(36) NOT NULL PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  location VARCHAR(255),
  nas_identifier VARCHAR(255),
  nas_ip VARCHAR(255),
  radius_secret VARCHAR(255) NOT NULL,
  status VARCHAR(50) NOT NULL DEFAULT 'active',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_status (status),
  INDEX idx_nas_ip (nas_ip)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS smartneti_plans (
  id CHAR(36) NOT NULL PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  description VARCHAR(255),
  duration_minutes INT NOT NULL,
  speed_down_kbps INT,
  speed_up_kbps INT,
  price DECIMAL(10,2) NOT NULL,
  currency VARCHAR(10) NOT NULL DEFAULT 'MWK',
  status VARCHAR(50) NOT NULL DEFAULT 'active',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS smartneti_customers (
  id CHAR(36) NOT NULL PRIMARY KEY,
  phone VARCHAR(20) NOT NULL,
  email VARCHAR(255),
  full_name VARCHAR(255),
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_phone (phone)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS smartneti_vouchers (
  id CHAR(36) NOT NULL PRIMARY KEY,
  plan_id CHAR(36) NOT NULL,
  customer_id CHAR(36) NULL,
  generated_by CHAR(36) NULL,
  code VARCHAR(10) NOT NULL UNIQUE,
  radius_username VARCHAR(64) NOT NULL,
  radius_password VARCHAR(64) NOT NULL,
  status VARCHAR(50) NOT NULL DEFAULT 'available',
  sold_at TIMESTAMP NULL,
  used_at TIMESTAMP NULL,
  expires_at TIMESTAMP NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_plan_id (plan_id),
  INDEX idx_customer_id (customer_id),
  INDEX idx_generated_by (generated_by),
  INDEX idx_status (status),
  INDEX idx_code (code),
  CONSTRAINT fk_vouchers_plan FOREIGN KEY (plan_id) REFERENCES smartneti_plans(id) ON DELETE CASCADE,
  CONSTRAINT fk_vouchers_customer FOREIGN KEY (customer_id) REFERENCES smartneti_customers(id) ON DELETE SET NULL,
  CONSTRAINT fk_vouchers_admin FOREIGN KEY (generated_by) REFERENCES smartneti_admins(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS smartneti_payments (
  id CHAR(36) NOT NULL PRIMARY KEY,
  customer_id CHAR(36),
  plan_id CHAR(36),
  voucher_id CHAR(36),
  gateway VARCHAR(50) NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  currency VARCHAR(10) NOT NULL DEFAULT 'MWK',
  status VARCHAR(50) NOT NULL DEFAULT 'pending',
  reference VARCHAR(255) UNIQUE,
  gateway_reference VARCHAR(255),
  paid_at TIMESTAMP NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_customer_id (customer_id),
  INDEX idx_status (status),
  INDEX idx_reference (reference),
  CONSTRAINT fk_payments_plan FOREIGN KEY (plan_id) REFERENCES smartneti_plans(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS smartneti_captive_sessions (
  id CHAR(36) NOT NULL PRIMARY KEY,
  voucher_id CHAR(36) NOT NULL,
  mac_address VARCHAR(17) NULL,
  ip_address VARCHAR(45) NULL,
  nas_identifier VARCHAR(255) NULL,
  started_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  ended_at TIMESTAMP NULL,
  INDEX idx_voucher_id (voucher_id),
  INDEX idx_mac_address (mac_address),
  CONSTRAINT fk_sessions_voucher FOREIGN KEY (voucher_id) REFERENCES smartneti_vouchers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS smartneti_settings (
  id CHAR(36) NOT NULL PRIMARY KEY,
  setting_key VARCHAR(255) NOT NULL UNIQUE,
  setting_value TEXT,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_setting_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
