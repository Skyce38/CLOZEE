-- CLOZEE (BUU / Bangsaen clothing marketplace)
-- DRAFT schema v0.3 for MySQL / MariaDB review, not deployed or tested.
-- Select/create a dedicated database in phpMyAdmin first, then import this file.
-- Does not contain CREATE/DROP DATABASE and never deletes existing data.
-- IMPORTANT: review names, DB version, and existing schema before executing.

SET NAMES utf8mb4;

CREATE TABLE users (
    user_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    full_name VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    phone VARCHAR(30) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id),
    UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE shops (
    shop_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    owner_user_id BIGINT UNSIGNED NOT NULL,
    shop_name VARCHAR(150) NOT NULL,
    description TEXT NULL,
    pickup_location VARCHAR(255) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (shop_id),
    UNIQUE KEY uq_shops_owner (owner_user_id),
    CONSTRAINT fk_shops_owner FOREIGN KEY (owner_user_id)
      REFERENCES users(user_id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE products (
    product_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    shop_id BIGINT UNSIGNED NOT NULL,
    product_name VARCHAR(180) NOT NULL,
    description TEXT NULL,
    category VARCHAR(80) NULL,
    condition_type ENUM('new','secondhand') NOT NULL,
    image_path VARCHAR(255) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (product_id),
    KEY idx_products_shop (shop_id),
    KEY idx_products_condition_active (condition_type, is_active),
    CONSTRAINT fk_products_shop FOREIGN KEY (shop_id)
      REFERENCES shops(shop_id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE product_variants (
    variant_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    product_id BIGINT UNSIGNED NOT NULL,
    size_label VARCHAR(50) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    stock_quantity INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (variant_id),
    UNIQUE KEY uq_variants_product_size (product_id, size_label),
    CONSTRAINT fk_variants_product FOREIGN KEY (product_id)
      REFERENCES products(product_id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE orders (
    order_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    buyer_user_id BIGINT UNSIGNED NOT NULL,
    shop_id BIGINT UNSIGNED NOT NULL,
    fulfillment_method ENUM('pickup','delivery') NOT NULL,
    fulfillment_details TEXT NOT NULL,
    shipping_fee DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending','confirmed','preparing','ready_for_pickup','out_for_delivery','completed','cancelled') NOT NULL DEFAULT 'pending',
    buyer_note TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (order_id),
    KEY idx_orders_buyer (buyer_user_id, created_at),
    KEY idx_orders_shop (shop_id, created_at),
    CONSTRAINT fk_orders_buyer FOREIGN KEY (buyer_user_id)
      REFERENCES users(user_id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_orders_shop FOREIGN KEY (shop_id)
      REFERENCES shops(shop_id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE order_items (
    order_item_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    order_id BIGINT UNSIGNED NOT NULL,
    variant_id BIGINT UNSIGNED NOT NULL,
    product_name_snapshot VARCHAR(180) NOT NULL,
    size_label_snapshot VARCHAR(50) NOT NULL,
    quantity INT UNSIGNED NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    PRIMARY KEY (order_item_id),
    KEY idx_order_items_order (order_id),
    KEY idx_order_items_variant (variant_id),
    CONSTRAINT fk_order_items_order FOREIGN KEY (order_id)
      REFERENCES orders(order_id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_order_items_variant FOREIGN KEY (variant_id)
      REFERENCES product_variants(variant_id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE conversations (
    conversation_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    buyer_user_id BIGINT UNSIGNED NOT NULL,
    shop_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    last_message_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (conversation_id),
    KEY idx_conversations_buyer (buyer_user_id),
    KEY idx_conversations_shop (shop_id),
    KEY idx_conversations_product (product_id),
    CONSTRAINT fk_conversations_buyer FOREIGN KEY (buyer_user_id)
      REFERENCES users(user_id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_conversations_shop FOREIGN KEY (shop_id)
      REFERENCES shops(shop_id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_conversations_product FOREIGN KEY (product_id)
      REFERENCES products(product_id) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE messages (
    message_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    conversation_id BIGINT UNSIGNED NOT NULL,
    sender_user_id BIGINT UNSIGNED NOT NULL,
    message_text TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (message_id),
    KEY idx_messages_conversation_time (conversation_id, created_at),
    KEY idx_messages_sender (sender_user_id),
    CONSTRAINT fk_messages_conversation FOREIGN KEY (conversation_id)
      REFERENCES conversations(conversation_id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_messages_sender FOREIGN KEY (sender_user_id)
      REFERENCES users(user_id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Business rules that also require PHP validation:
-- - Only one seller shop per account (UNIQUE enforced above).
-- - All order_items must come from products owned by orders.shop_id.
-- - Users may only mutate their own shops/products and read their own orders.
-- - Chat sender must be conversation's buyer or shop owner.
-- - Quantities > 0, prices >= 0; application must validate.
-- - Checkout: SELECT ... FOR UPDATE + transactional stock update/order insert.
-- - Status transitions depend on fulfillment method; validate in PHP.
-- - No hard delete of products/shops referenced by order history.
