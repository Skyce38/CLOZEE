-- Generated application table prefix: clozee_
-- Add product photos/details without changing existing prices, stock or orders.
-- Review the configured database and table prefix before applying.
ALTER TABLE `clozee_products`
 ADD COLUMN brand VARCHAR(100) NULL,
 ADD COLUMN chest_cm DECIMAL(4,1) NULL,
 ADD COLUMN length_cm DECIMAL(4,1) NULL,
 ADD COLUMN condition_notes VARCHAR(1000) NULL,
 ADD CONSTRAINT `clozee_ck_products_chest` CHECK (chest_cm IS NULL OR (chest_cm > 0 AND chest_cm <= 300)),
 ADD CONSTRAINT `clozee_ck_products_length` CHECK (length_cm IS NULL OR (length_cm > 0 AND length_cm <= 300));

CREATE TABLE `clozee_product_images` (
 image_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
 product_id BIGINT UNSIGNED NOT NULL,
 storage_name VARCHAR(64) NOT NULL,
 mime_type VARCHAR(32) NOT NULL,
 byte_size INT UNSIGNED NOT NULL,
 image_data MEDIUMBLOB NOT NULL,
 width SMALLINT UNSIGNED NOT NULL,
 height SMALLINT UNSIGNED NOT NULL,
 sort_order TINYINT UNSIGNED NOT NULL DEFAULT 0,
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT `clozee_ck_product_images_bytes` CHECK (byte_size > 0 AND byte_size <= 1572864 AND OCTET_LENGTH(image_data) = byte_size),
 CONSTRAINT `clozee_ck_product_images_dimensions` CHECK (width BETWEEN 1 AND 1600 AND height BETWEEN 1 AND 1600),
 UNIQUE KEY uq_product_images_storage (storage_name),
 KEY idx_product_images_order (product_id,sort_order,image_id),
 CONSTRAINT `clozee_fk_product_images_product` FOREIGN KEY (product_id)
  REFERENCES `clozee_products`(product_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
