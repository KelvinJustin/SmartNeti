USE smartneti;

SET @add_customer_id = IF(
  EXISTS (
    SELECT 1 FROM information_schema.columns
    WHERE table_schema = 'smartneti'
      AND table_name = 'smartneti_vouchers'
      AND column_name = 'customer_id'
  ),
  'SET @dummy = 0',
  'ALTER TABLE smartneti_vouchers ADD COLUMN customer_id CHAR(36) NULL AFTER plan_id'
);
PREPARE stmt FROM @add_customer_id;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @add_generated_by = IF(
  EXISTS (
    SELECT 1 FROM information_schema.columns
    WHERE table_schema = 'smartneti'
      AND table_name = 'smartneti_vouchers'
      AND column_name = 'generated_by'
  ),
  'SET @dummy = 0',
  'ALTER TABLE smartneti_vouchers ADD COLUMN generated_by CHAR(36) NULL AFTER customer_id'
);
PREPARE stmt FROM @add_generated_by;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

ALTER TABLE smartneti_vouchers
  ADD INDEX idx_customer_id (customer_id),
  ADD INDEX idx_generated_by (generated_by),
  ADD CONSTRAINT fk_vouchers_customer FOREIGN KEY (customer_id) REFERENCES smartneti_customers(id) ON DELETE SET NULL,
  ADD CONSTRAINT fk_vouchers_admin FOREIGN KEY (generated_by) REFERENCES smartneti_admins(id) ON DELETE SET NULL;
