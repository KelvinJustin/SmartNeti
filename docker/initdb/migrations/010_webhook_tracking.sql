-- Add webhook tracking to tbl_payment_gateway
ALTER TABLE `tbl_payment_gateway` 
ADD COLUMN `webhook_received` TINYINT(1) DEFAULT 0 COMMENT '0=not received, 1=received',
ADD COLUMN `webhook_received_date` DATETIME NULL,
ADD INDEX `idx_webhook_received` (`webhook_received`);
