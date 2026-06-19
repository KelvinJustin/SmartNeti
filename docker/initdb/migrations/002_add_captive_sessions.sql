USE smartneti;

SET @add_table = IF(
  EXISTS (
    SELECT 1 FROM information_schema.tables
    WHERE table_schema = 'smartneti'
      AND table_name = 'smartneti_captive_sessions'
  ),
  'SET @dummy = 0',
  'CREATE TABLE smartneti_captive_sessions (
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
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
);
PREPARE stmt FROM @add_table;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
