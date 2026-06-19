USE smartneti;

SET @add_table = IF(
  EXISTS (
    SELECT 1 FROM information_schema.tables
    WHERE table_schema = 'smartneti'
      AND table_name = 'smartneti_settings'
  ),
  'SET @dummy = 0',
  'CREATE TABLE smartneti_settings (
    id CHAR(36) NOT NULL PRIMARY KEY,
    setting_key VARCHAR(255) NOT NULL UNIQUE,
    setting_value TEXT,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_setting_key (setting_key)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
);
PREPARE stmt FROM @add_table;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
