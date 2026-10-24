-- ================================================================
-- SPORTZONE — Database Schema
-- MySQL 8.0.16+ | InnoDB | utf8mb4_unicode_ci
-- Timezone: Asia/Ho_Chi_Minh (+07:00)
-- ================================================================

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+07:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS,   UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE,
    SQL_MODE='STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */;

-- ----------------------------------------------------------------
-- Tạo database
-- ----------------------------------------------------------------
CREATE DATABASE IF NOT EXISTS `sportzone`
    DEFAULT CHARACTER SET utf8mb4
    DEFAULT COLLATE utf8mb4_unicode_ci;

USE `sportzone`;

-- ================================================================
-- SECTION 1: USER MANAGEMENT
-- ================================================================

-- ----------------------------------------------------------------
-- users: Tất cả tài khoản (customer & admin)
-- Mật khẩu lưu bằng password_hash() với PASSWORD_BCRYPT
-- ----------------------------------------------------------------
CREATE TABLE `users` (
    `id`                INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `name`              VARCHAR(100)    NOT NULL,
    `email`             VARCHAR(150)    NOT NULL,
    `password`          VARCHAR(255)    NOT NULL        COMMENT 'bcrypt hash via password_hash(PASSWORD_BCRYPT)',
    `phone`             VARCHAR(20)     NULL DEFAULT NULL,
    `avatar`            VARCHAR(255)    NULL DEFAULT NULL,
    `role`              ENUM('customer','admin') NOT NULL DEFAULT 'customer',
    `is_active`         TINYINT(1)      NOT NULL DEFAULT 1,
    `email_verified_at` TIMESTAMP       NULL DEFAULT NULL,
    `created_at`        TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_users_email` (`email`),
    KEY `idx_users_role_active` (`role`, `is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Tài khoản người dùng (customer và admin)';

-- ----------------------------------------------------------------
-- password_resets: Token đặt lại mật khẩu
-- ----------------------------------------------------------------
CREATE TABLE `password_resets` (
    `id`            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `user_id`       INT UNSIGNED    NOT NULL,
    `token`         VARCHAR(100)    NOT NULL        COMMENT 'SHA-256 hex token',
    `expires_at`    TIMESTAMP       NOT NULL,
    `used_at`       TIMESTAMP       NULL DEFAULT NULL,
    `created_at`    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_pr_token` (`token`),
    KEY `idx_pr_user` (`user_id`),
    KEY `idx_pr_expires` (`expires_at`),
    CONSTRAINT `fk_pr_user`
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------
-- user_addresses: Địa chỉ giao hàng
-- ----------------------------------------------------------------
CREATE TABLE `user_addresses` (
    `id`                INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `user_id`           INT UNSIGNED    NOT NULL,
    `recipient_name`    VARCHAR(100)    NOT NULL,
    `phone`             VARCHAR(20)     NOT NULL,
    `address_line`      VARCHAR(255)    NOT NULL,
    `ward`              VARCHAR(100)    NOT NULL,
    `district`          VARCHAR(100)    NOT NULL,
    `province`          VARCHAR(100)    NOT NULL,
    `is_default`        TINYINT(1)      NOT NULL DEFAULT 0,
    `created_at`        TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_ua_user` (`user_id`),
    KEY `idx_ua_default` (`user_id`, `is_default`),
    CONSTRAINT `fk_ua_user`
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================
-- SECTION 2: CATALOG
-- ================================================================

-- ----------------------------------------------------------------
-- categories: Danh mục 2 cấp (parent_id = NULL là danh mục gốc)
-- ON DELETE SET NULL: xóa parent không kéo theo con
-- ----------------------------------------------------------------
CREATE TABLE `categories` (
    `id`            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `parent_id`     INT UNSIGNED    NULL DEFAULT NULL,
    `name`          VARCHAR(100)    NOT NULL,
    `slug`          VARCHAR(120)    NOT NULL,
    `image`         VARCHAR(255)    NULL DEFAULT NULL,
    `description`   TEXT            NULL DEFAULT NULL,
    `sort_order`    SMALLINT        NOT NULL DEFAULT 0,
    `is_active`     TINYINT(1)      NOT NULL DEFAULT 1,
    `created_at`    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_categories_slug` (`slug`),
    KEY `idx_cat_parent` (`parent_id`),
    KEY `idx_cat_active_sort` (`is_active`, `sort_order`),
    CONSTRAINT `fk_cat_parent`
        FOREIGN KEY (`parent_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------
-- brands: Thương hiệu
-- ----------------------------------------------------------------
CREATE TABLE `brands` (
    `id`            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `name`          VARCHAR(100)    NOT NULL,
    `slug`          VARCHAR(120)    NOT NULL,
    `logo`          VARCHAR(255)    NULL DEFAULT NULL,
    `description`   TEXT            NULL DEFAULT NULL,
    `is_active`     TINYINT(1)      NOT NULL DEFAULT 1,
    `sort_order`    SMALLINT        NOT NULL DEFAULT 0,
    `created_at`    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_brands_slug` (`slug`),
    KEY `idx_brands_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------
-- products: Sản phẩm chính (không lưu giá/stock ở đây)
-- Giá và stock quản lý tại product_variants
-- FULLTEXT index cho tìm kiếm
-- ----------------------------------------------------------------
CREATE TABLE `products` (
    `id`                INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `category_id`       INT UNSIGNED    NOT NULL,
    `brand_id`          INT UNSIGNED    NULL DEFAULT NULL,
    `name`              VARCHAR(255)    NOT NULL,
    `slug`              VARCHAR(280)    NOT NULL,
    `short_description` VARCHAR(500)    NULL DEFAULT NULL,
    `description`       LONGTEXT        NULL DEFAULT NULL,
    `status`            ENUM('draft','published','archived') NOT NULL DEFAULT 'draft',
    `is_featured`       TINYINT(1)      NOT NULL DEFAULT 0,
    `created_at`        TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_products_slug` (`slug`),
    KEY `idx_prod_category` (`category_id`),
    KEY `idx_prod_brand` (`brand_id`),
    KEY `idx_prod_status_feat` (`status`, `is_featured`),
    KEY `idx_prod_created` (`created_at`),
    FULLTEXT KEY `ft_products` (`name`, `short_description`),
    CONSTRAINT `fk_prod_category`
        FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_prod_brand`
        FOREIGN KEY (`brand_id`) REFERENCES `brands`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------
-- product_images: Ảnh sản phẩm (nhiều ảnh/sản phẩm)
-- CASCADE: xóa SP → tất cả ảnh bị xóa theo
-- ----------------------------------------------------------------
CREATE TABLE `product_images` (
    `id`            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `product_id`    INT UNSIGNED    NOT NULL,
    `image_path`    VARCHAR(255)    NOT NULL,
    `alt_text`      VARCHAR(255)    NULL DEFAULT NULL,
    `sort_order`    SMALLINT        NOT NULL DEFAULT 0,
    `is_primary`    TINYINT(1)      NOT NULL DEFAULT 0,
    `created_at`    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_pi_product_sort` (`product_id`, `sort_order`),
    CONSTRAINT `fk_pi_product`
        FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================
-- SECTION 3: ATTRIBUTE & VARIANT SYSTEM (EAV-lite)
-- ================================================================

-- ----------------------------------------------------------------
-- attributes: Loại thuộc tính (e.g., "Size", "Màu sắc")
-- ----------------------------------------------------------------
CREATE TABLE `attributes` (
    `id`    INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `name`  VARCHAR(50)     NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_attributes_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------
-- attribute_values: Giá trị cụ thể (e.g., "38", "Đen", "XL")
-- hex_code: chỉ dùng cho attribute "Màu sắc"
-- ----------------------------------------------------------------
CREATE TABLE `attribute_values` (
    `id`            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `attribute_id`  INT UNSIGNED    NOT NULL,
    `value`         VARCHAR(100)    NOT NULL,
    `hex_code`      VARCHAR(7)      NULL DEFAULT NULL    COMMENT 'Hex color #RRGGBB (nullable)',
    `sort_order`    SMALLINT        NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_av_attr_value` (`attribute_id`, `value`),
    KEY `idx_av_attribute` (`attribute_id`),
    CONSTRAINT `fk_av_attribute`
        FOREIGN KEY (`attribute_id`) REFERENCES `attributes`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------
-- product_variants: Mỗi biến thể = 1 SKU + giá + tồn kho
-- DECIMAL(15,0): VND không có xu; tối đa 999 tỷ
-- CHECK sale_price < price: không được sale cao hơn giá gốc
-- CHECK stock >= 0: không cho tồn kho âm
-- ----------------------------------------------------------------
CREATE TABLE `product_variants` (
    `id`                    INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `product_id`            INT UNSIGNED    NOT NULL,
    `sku`                   VARCHAR(100)    NOT NULL,
    `price`                 DECIMAL(15,0)   NOT NULL        COMMENT 'Giá gốc VND',
    `sale_price`            DECIMAL(15,0)   NULL DEFAULT NULL COMMENT 'NULL = không giảm giá',
    `stock_quantity`        INT             NOT NULL DEFAULT 0,
    `low_stock_threshold`   INT             NOT NULL DEFAULT 5  COMMENT 'Cảnh báo tồn thấp',
    `is_active`             TINYINT(1)      NOT NULL DEFAULT 1,
    `created_at`            TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`            TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_variants_sku` (`sku`),
    KEY `idx_pv_product` (`product_id`),
    KEY `idx_pv_stock` (`stock_quantity`),
    KEY `idx_pv_active` (`product_id`, `is_active`),
    CONSTRAINT `fk_pv_product`
        FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `chk_pv_price`
        CHECK (`price` > 0),
    CONSTRAINT `chk_pv_stock`
        CHECK (`stock_quantity` >= 0),
    CONSTRAINT `chk_pv_sale_price`
        CHECK (`sale_price` IS NULL OR (`sale_price` > 0 AND `sale_price` < `price`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------
-- variant_attribute_values: Junction — biến thể ↔ thuộc tính
-- PK kết hợp ngăn gán trùng attribute_value cho 1 variant
-- ----------------------------------------------------------------
CREATE TABLE `variant_attribute_values` (
    `variant_id`            INT UNSIGNED    NOT NULL,
    `attribute_value_id`    INT UNSIGNED    NOT NULL,
    PRIMARY KEY (`variant_id`, `attribute_value_id`),
    KEY `idx_vav_attr_val` (`attribute_value_id`),
    CONSTRAINT `fk_vav_variant`
        FOREIGN KEY (`variant_id`) REFERENCES `product_variants`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_vav_av`
        FOREIGN KEY (`attribute_value_id`) REFERENCES `attribute_values`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================
-- SECTION 4: CART
-- ================================================================

-- ----------------------------------------------------------------
-- carts: Một giỏ hàng / session (guest hoặc logged-in)
-- user_id NULL = guest cart; SET NULL khi user xóa (giữ lịch sử)
-- ----------------------------------------------------------------
CREATE TABLE `carts` (
    `id`            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `user_id`       INT UNSIGNED    NULL DEFAULT NULL,
    `session_id`    VARCHAR(128)    NOT NULL    COMMENT 'PHP session ID',
    `created_at`    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_carts_session` (`session_id`),
    KEY `idx_carts_user` (`user_id`),
    KEY `idx_carts_updated` (`updated_at`)  COMMENT 'Dùng cho cleanup job cũ',
    CONSTRAINT `fk_cart_user`
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------
-- cart_items: Sản phẩm trong giỏ
-- UNIQUE (cart_id, variant_id): không add duplicate variant
-- ----------------------------------------------------------------
CREATE TABLE `cart_items` (
    `id`            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `cart_id`       INT UNSIGNED    NOT NULL,
    `variant_id`    INT UNSIGNED    NOT NULL,
    `quantity`      INT             NOT NULL DEFAULT 1,
    `added_at`      TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_ci_cart_variant` (`cart_id`, `variant_id`),
    KEY `idx_ci_variant` (`variant_id`),
    CONSTRAINT `fk_ci_cart`
        FOREIGN KEY (`cart_id`) REFERENCES `carts`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_ci_variant`
        FOREIGN KEY (`variant_id`) REFERENCES `product_variants`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `chk_ci_qty`
        CHECK (`quantity` > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================
-- SECTION 5: COUPON / DISCOUNT
-- ================================================================

-- ----------------------------------------------------------------
-- coupons: Mã giảm giá
-- product_id/category_id NULL = áp dụng toàn bộ đơn hàng
-- max_discount: giới hạn số tiền giảm tối đa (cho loại percent)
-- max_uses NULL = không giới hạn số lần dùng
-- ----------------------------------------------------------------
CREATE TABLE `coupons` (
    `id`                INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `code`              VARCHAR(50)     NOT NULL,
    `name`              VARCHAR(100)    NOT NULL,
    `type`              ENUM('percent','fixed') NOT NULL,
    `value`             DECIMAL(15,0)   NOT NULL        COMMENT '% hoặc số tiền VND',
    `max_discount`      DECIMAL(15,0)   NULL DEFAULT NULL COMMENT 'Giới hạn giảm tối đa cho loại %',
    `min_order_value`   DECIMAL(15,0)   NOT NULL DEFAULT 0,
    `product_id`        INT UNSIGNED    NULL DEFAULT NULL COMMENT 'NULL = áp dụng tất cả',
    `category_id`       INT UNSIGNED    NULL DEFAULT NULL COMMENT 'NULL = áp dụng tất cả',
    `max_uses`          INT             NULL DEFAULT NULL COMMENT 'NULL = không giới hạn',
    `used_count`        INT             NOT NULL DEFAULT 0,
    `starts_at`         TIMESTAMP       NULL DEFAULT NULL,
    `ends_at`           TIMESTAMP       NULL DEFAULT NULL,
    `is_active`         TINYINT(1)      NOT NULL DEFAULT 1,
    `created_at`        TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_coupons_code` (`code`),
    KEY `idx_coupons_active` (`is_active`, `ends_at`),
    KEY `idx_coupons_product` (`product_id`),
    KEY `idx_coupons_category` (`category_id`),
    CONSTRAINT `fk_coupon_product`
        FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_coupon_category`
        FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `chk_coupon_value`
        CHECK (`value` > 0),
    CONSTRAINT `chk_coupon_percent`
        CHECK (`type` != 'percent' OR `value` <= 100),
    CONSTRAINT `chk_coupon_used`
        CHECK (`used_count` >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================
-- SECTION 6: ORDER MANAGEMENT
-- ================================================================

-- ----------------------------------------------------------------
-- orders: Đơn hàng
-- coupon_id: FK (nullable, SET NULL nếu coupon bị xóa)
-- coupon_code: snapshot mã coupon đã dùng
-- order_number: SZ-YYYYMMDD-NNNNN (không lộ sequence)
-- ----------------------------------------------------------------
CREATE TABLE `orders` (
    `id`                INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `user_id`           INT UNSIGNED    NULL DEFAULT NULL,
    `order_number`      VARCHAR(30)     NOT NULL,
    `subtotal`          DECIMAL(15,0)   NOT NULL            COMMENT 'Tổng trước giảm/ship',
    `discount_amount`   DECIMAL(15,0)   NOT NULL DEFAULT 0  COMMENT 'Số tiền đã giảm',
    `shipping_fee`      DECIMAL(15,0)   NOT NULL DEFAULT 0,
    `total`             DECIMAL(15,0)   NOT NULL            COMMENT 'subtotal - discount + shipping',
    `coupon_id`         INT UNSIGNED    NULL DEFAULT NULL,
    `coupon_code`       VARCHAR(50)     NULL DEFAULT NULL    COMMENT 'Snapshot mã coupon',
    `payment_method`    ENUM('cod','bank_transfer') NOT NULL DEFAULT 'cod',
    `payment_status`    ENUM('pending','paid','refunded') NOT NULL DEFAULT 'pending',
    `status`            ENUM('pending','confirmed','shipping','delivered','completed','cancelled','refunded')
                        NOT NULL DEFAULT 'pending',
    `notes`             TEXT            NULL DEFAULT NULL,
    `cancelled_reason`  TEXT            NULL DEFAULT NULL,
    `created_at`        TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_orders_number` (`order_number`),
    KEY `idx_orders_user` (`user_id`),
    KEY `idx_orders_status` (`status`),
    KEY `idx_orders_payment` (`payment_status`),
    KEY `idx_orders_created` (`created_at`),
    CONSTRAINT `fk_order_user`
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_order_coupon`
        FOREIGN KEY (`coupon_id`) REFERENCES `coupons`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `chk_order_subtotal`
        CHECK (`subtotal` >= 0),
    CONSTRAINT `chk_order_discount`
        CHECK (`discount_amount` >= 0),
    CONSTRAINT `chk_order_total`
        CHECK (`total` >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------
-- order_items: Chi tiết sản phẩm trong đơn — SNAPSHOT PATTERN
-- product_id, variant_id: nullable (SET NULL nếu bị xóa)
-- Các trường *_name, *_sku, *_info, unit_price: bất biến sau khi tạo
-- ----------------------------------------------------------------
CREATE TABLE `order_items` (
    `id`                INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `order_id`          INT UNSIGNED    NOT NULL,
    `product_id`        INT UNSIGNED    NULL DEFAULT NULL    COMMENT 'Nullable nếu SP bị xóa',
    `variant_id`        INT UNSIGNED    NULL DEFAULT NULL    COMMENT 'Nullable nếu variant bị xóa',
    -- SNAPSHOT FIELDS (không được UPDATE sau khi INSERT)
    `product_name`      VARCHAR(255)    NOT NULL            COMMENT 'Tên SP tại thời điểm mua',
    `variant_sku`       VARCHAR(100)    NOT NULL            COMMENT 'SKU tại thời điểm mua',
    `variant_info`      VARCHAR(255)    NOT NULL            COMMENT 'VD: Size: 42 · Màu: Đen',
    `unit_price`        DECIMAL(15,0)   NOT NULL            COMMENT 'Giá thực tế đã trả (sau sale)',
    `quantity`          INT             NOT NULL,
    `subtotal`          DECIMAL(15,0)   NOT NULL            COMMENT 'unit_price × quantity',
    PRIMARY KEY (`id`),
    KEY `idx_oi_order` (`order_id`),
    KEY `idx_oi_product` (`product_id`),
    KEY `idx_oi_variant` (`variant_id`),
    CONSTRAINT `fk_oi_order`
        FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_oi_product`
        FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_oi_variant`
        FOREIGN KEY (`variant_id`) REFERENCES `product_variants`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `chk_oi_qty`
        CHECK (`quantity` > 0),
    CONSTRAINT `chk_oi_price`
        CHECK (`unit_price` >= 0),
    CONSTRAINT `chk_oi_subtotal`
        CHECK (`subtotal` >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------
-- order_shipping: Địa chỉ giao hàng (1-to-1 với orders)
-- Tách bảng: không phụ thuộc user_addresses (snapshot)
-- ----------------------------------------------------------------
CREATE TABLE `order_shipping` (
    `id`                INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `order_id`          INT UNSIGNED    NOT NULL,
    `recipient_name`    VARCHAR(100)    NOT NULL,
    `phone`             VARCHAR(20)     NOT NULL,
    `address_line`      VARCHAR(255)    NOT NULL,
    `ward`              VARCHAR(100)    NOT NULL,
    `district`          VARCHAR(100)    NOT NULL,
    `province`          VARCHAR(100)    NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_os_order` (`order_id`)  COMMENT '1-to-1 với orders',
    CONSTRAINT `fk_os_order`
        FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------
-- order_status_history: Lịch sử thay đổi trạng thái đơn hàng
-- ----------------------------------------------------------------
CREATE TABLE `order_status_history` (
    `id`            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `order_id`      INT UNSIGNED    NOT NULL,
    `changed_by`    INT UNSIGNED    NULL DEFAULT NULL    COMMENT 'Admin user_id; NULL = system/customer',
    `old_status`    ENUM('pending','confirmed','shipping','delivered','completed','cancelled','refunded')
                    NULL DEFAULT NULL,
    `new_status`    ENUM('pending','confirmed','shipping','delivered','completed','cancelled','refunded')
                    NOT NULL,
    `note`          TEXT            NULL DEFAULT NULL,
    `created_at`    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_osh_order` (`order_id`),
    KEY `idx_osh_created` (`created_at`),
    CONSTRAINT `fk_osh_order`
        FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_osh_admin`
        FOREIGN KEY (`changed_by`) REFERENCES `users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================
-- SECTION 7: INVENTORY MANAGEMENT
-- ================================================================

-- ----------------------------------------------------------------
-- inventory_logs: Nhật ký xuất/nhập kho
-- type:
--   in          = nhập hàng thủ công (admin)
--   out         = xuất kho khi đặt hàng
--   adjustment  = điều chỉnh tồn kho (kiểm kê)
--   reserved    = dự trữ (tương lai)
--   released    = giải phóng dự trữ / hoàn kho khi hủy đơn
-- ----------------------------------------------------------------
CREATE TABLE `inventory_logs` (
    `id`                INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `variant_id`        INT UNSIGNED    NOT NULL,
    `order_id`          INT UNSIGNED    NULL DEFAULT NULL,
    `performed_by`      INT UNSIGNED    NULL DEFAULT NULL    COMMENT 'Admin thực hiện; NULL = system',
    `type`              ENUM('in','out','adjustment','reserved','released') NOT NULL,
    `quantity_change`   INT             NOT NULL            COMMENT 'Số dương = nhập; Số âm = xuất',
    `quantity_after`    INT             NOT NULL            COMMENT 'Tồn kho sau khi thay đổi',
    `note`              VARCHAR(255)    NULL DEFAULT NULL,
    `created_at`        TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_il_variant_created` (`variant_id`, `created_at`),
    KEY `idx_il_order` (`order_id`),
    KEY `idx_il_type` (`type`),
    CONSTRAINT `fk_il_variant`
        FOREIGN KEY (`variant_id`) REFERENCES `product_variants`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_il_order`
        FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_il_admin`
        FOREIGN KEY (`performed_by`) REFERENCES `users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================
-- STORED PROCEDURE: create_order
-- Tạo đơn hàng an toàn trong transaction
-- Tham số:
--   p_user_id         : INT UNSIGNED
--   p_coupon_id       : INT UNSIGNED (0 = không dùng)
--   p_payment_method  : VARCHAR (cod|bank_transfer)
--   p_shipping_json   : JSON object địa chỉ giao hàng
--   p_items_json      : JSON array [{variant_id, quantity}]
--   p_notes           : TEXT
-- Output:
--   p_order_id        : INT UNSIGNED (OUT)
--   p_order_number    : VARCHAR (OUT)
--   p_error           : VARCHAR (OUT, rỗng = thành công)
-- ================================================================


-- ================================================================
-- ================================================================
-- SEED DATA — Dữ liệu mẫu
-- ================================================================
-- ================================================================

-- ================================================================
-- USERS
-- Mật khẩu: "Admin@1234"  → hash bên dưới (bcrypt cost=12)
-- Mật khẩu: "Customer@1234" → hash bên dưới
-- ⚠️  Trong môi trường thực: tạo lại hash bằng PHP password_hash()
-- ================================================================
INSERT INTO `users`
    (`id`, `name`, `email`, `password`, `phone`, `role`, `is_active`, `email_verified_at`)
VALUES
    (1, 'Admin SPORTZONE', 'admin@sportzone.vn',
     '$2y$12$LzxoJVkmGoE5XQUedTFjxeqPNI2u2WG9T3Kk5RzMaOb0GxLHqPCRm',
     '0901234567', 'admin', 1, NOW()),

    (2, 'Nguyễn Văn An', 'nguyenvanan@gmail.com',
     '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
     '0912345678', 'customer', 1, NOW()),

    (3, 'Trần Thị Bình', 'tranthiib@gmail.com',
     '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
     '0987654321', 'customer', 1, NOW());

-- ================================================================
-- USER ADDRESSES
-- ================================================================
INSERT INTO `user_addresses`
    (`user_id`, `recipient_name`, `phone`, `address_line`, `ward`, `district`, `province`, `is_default`)
VALUES
    (2, 'Nguyễn Văn An', '0912345678',
     '123 Đường Nguyễn Huệ', 'Phường Bến Nghé', 'Quận 1', 'TP. Hồ Chí Minh', 1),

    (3, 'Trần Thị Bình', '0987654321',
     '456 Đường Hai Bà Trưng', 'Phường Tân Định', 'Quận 1', 'TP. Hồ Chí Minh', 1);

-- ================================================================
-- CATEGORIES
-- ================================================================
INSERT INTO `categories`
    (`id`, `parent_id`, `name`, `slug`, `sort_order`, `is_active`)
VALUES
    -- Danh mục gốc
    (1,  NULL, 'Giày thể thao',  'giay-the-thao',  1, 1),
    (2,  NULL, 'Áo thể thao',    'ao-the-thao',    2, 1),
    (3,  NULL, 'Quần thể thao',  'quan-the-thao',  3, 1),
    (4,  NULL, 'Phụ kiện',       'phu-kien',       4, 1),
    -- Danh mục con của Giày
    (5,  1,    'Giày chạy bộ',   'giay-chay-bo',   1, 1),
    (6,  1,    'Giày đá bóng',   'giay-da-bong',   2, 1),
    (7,  1,    'Giày tập gym',   'giay-tap-gym',   3, 1),
    (8,  1,    'Giày đi bộ',     'giay-di-bo',     4, 1),
    -- Danh mục con của Áo
    (9,  2,    'Áo chạy bộ',     'ao-chay-bo',     1, 1),
    (10, 2,    'Áo tập gym',     'ao-tap-gym',     2, 1),
    (11, 2,    'Áo polo thể thao','ao-polo-the-thao',3,1),
    -- Danh mục con của Quần
    (12, 3,    'Quần chạy bộ',   'quan-chay-bo',   1, 1),
    (13, 3,    'Quần đá bóng',   'quan-da-bong',   2, 1);

-- ================================================================
-- BRANDS
-- ================================================================
INSERT INTO `brands`
    (`id`, `name`, `slug`, `is_active`, `sort_order`)
VALUES
    (1, 'Nike',          'nike',          1, 1),
    (2, 'Adidas',        'adidas',        1, 2),
    (3, 'Puma',          'puma',          1, 3),
    (4, 'Under Armour',  'under-armour',  1, 4),
    (5, 'New Balance',   'new-balance',   1, 5),
    (6, 'Asics',         'asics',         1, 6);

-- ================================================================
-- ATTRIBUTES & VALUES
-- ================================================================
INSERT INTO `attributes` (`id`, `name`) VALUES
    (1, 'Size'),
    (2, 'Màu sắc');

-- Size values (giày — EU)
INSERT INTO `attribute_values` (`id`, `attribute_id`, `value`, `hex_code`, `sort_order`) VALUES
    (1,  1, '36', NULL, 1),
    (2,  1, '37', NULL, 2),
    (3,  1, '38', NULL, 3),
    (4,  1, '39', NULL, 4),
    (5,  1, '40', NULL, 5),
    (6,  1, '41', NULL, 6),
    (7,  1, '42', NULL, 7),
    (8,  1, '43', NULL, 8),
    (9,  1, '44', NULL, 9),
    (10, 1, '45', NULL, 10),
    -- Size áo
    (11, 1, 'XS',  NULL, 11),
    (12, 1, 'S',   NULL, 12),
    (13, 1, 'M',   NULL, 13),
    (14, 1, 'L',   NULL, 14),
    (15, 1, 'XL',  NULL, 15),
    (16, 1, 'XXL', NULL, 16),
    -- Màu sắc
    (17, 2, 'Đen',       '#000000', 1),
    (18, 2, 'Trắng',     '#FFFFFF', 2),
    (19, 2, 'Đỏ',        '#DC2626', 3),
    (20, 2, 'Xanh Navy', '#1E3A5F', 4),
    (21, 2, 'Xám',       '#6B7280', 5),
    (22, 2, 'Cam',       '#EA580C', 6),
    (23, 2, 'Xanh Lá',   '#16A34A', 7),
    (24, 2, 'Tím',       '#7C3AED', 8);

-- ================================================================
-- PRODUCTS
-- ================================================================
INSERT INTO `products`
    (`id`, `category_id`, `brand_id`, `name`, `slug`, `short_description`, `status`, `is_featured`)
VALUES
    (1,  5, 1, 'Nike Air Zoom Pegasus 41',
     'nike-air-zoom-pegasus-41',
     'Giày chạy bộ cao cấp với công nghệ Air Zoom, nhẹ và bền bỉ, lý tưởng cho mọi cự ly.',
     'published', 1),

    (2,  5, 2, 'Adidas Ultraboost 24',
     'adidas-ultraboost-24',
     'Giày chạy bộ sử dụng đế BOOST™ tối ưu năng lượng phản hồi, mang lại cảm giác chạy thoải mái nhất.',
     'published', 1),

    (3,  7, 1, 'Nike Metcon 9',
     'nike-metcon-9',
     'Giày tập gym chắc chắn với đế phẳng ổn định, lý tưởng cho nâng tạ và HIIT.',
     'published', 1),

    (4,  9, 2, 'Adidas Techfit Long Sleeve Tee',
     'adidas-techfit-long-sleeve-tee',
     'Áo dài tay Techfit với vải co giãn 4 chiều, thoáng khí và khô nhanh.',
     'published', 0),

    (5,  10, 3, 'Puma Fit Logo Training Tee',
     'puma-fit-logo-training-tee',
     'Áo tập gym nhẹ với công nghệ DryCell giúp thấm hút mồ hôi hiệu quả.',
     'published', 0),

    (6,  12, 4, 'Under Armour Run Shorts 5"',
     'under-armour-run-shorts-5',
     'Quần short chạy bộ 5 inch với công nghệ HeatGear, co giãn tốt và có túi kéo.',
     'published', 1),

    (7,  6, 2, 'Adidas X Speedportal.3 FG',
     'adidas-x-speedportal-3-fg',
     'Giày đá bóng sân cỏ tự nhiên với mũi giày rộng và đế nhẹ, tăng tốc vượt trội.',
     'published', 0),

    (8,  5, 5, 'New Balance Fresh Foam X 1080v14',
     'new-balance-fresh-foam-x-1080v14',
     'Giày chạy đường dài với đế Fresh Foam X siêu êm, lý tưởng cho marathon.',
     'published', 1);

-- ================================================================
-- PRODUCT IMAGES (placeholder paths — sẽ replace bằng ảnh thật)
-- ================================================================
INSERT INTO `product_images` (`product_id`, `image_path`, `alt_text`, `sort_order`, `is_primary`) VALUES
    -- Nike Air Zoom Pegasus 41
    (1, 'uploads/products/p1-pegasus41-1.webp', 'Nike Air Zoom Pegasus 41 - Chính diện', 0, 1),
    (1, 'uploads/products/p1-pegasus41-2.webp', 'Nike Air Zoom Pegasus 41 - Góc nghiêng', 1, 0),
    (1, 'uploads/products/p1-pegasus41-3.webp', 'Nike Air Zoom Pegasus 41 - Đế giày', 2, 0),

    -- Adidas Ultraboost 24
    (2, 'uploads/products/p2-ultraboost24-1.webp', 'Adidas Ultraboost 24 - Chính diện', 0, 1),
    (2, 'uploads/products/p2-ultraboost24-2.webp', 'Adidas Ultraboost 24 - Side view', 1, 0),

    -- Nike Metcon 9
    (3, 'uploads/products/p3-metcon9-1.webp', 'Nike Metcon 9 - Chính diện', 0, 1),
    (3, 'uploads/products/p3-metcon9-2.webp', 'Nike Metcon 9 - Top view', 1, 0),

    -- Adidas Techfit Tee
    (4, 'uploads/products/p4-techfit-1.webp', 'Adidas Techfit Long Sleeve Tee', 0, 1),

    -- Puma Training Tee
    (5, 'uploads/products/p5-puma-tee-1.webp', 'Puma Fit Logo Training Tee', 0, 1),

    -- Under Armour Run Shorts
    (6, 'uploads/products/p6-ua-shorts-1.webp', 'Under Armour Run Shorts 5"', 0, 1),

    -- Adidas X Speedportal
    (7, 'uploads/products/p7-speedportal-1.webp', 'Adidas X Speedportal.3 FG', 0, 1),

    -- New Balance 1080v14
    (8, 'uploads/products/p8-nb1080-1.webp', 'New Balance Fresh Foam X 1080v14', 0, 1),
    (8, 'uploads/products/p8-nb1080-2.webp', 'New Balance 1080v14 - Top view', 1, 0);

-- ================================================================
-- PRODUCT VARIANTS
-- Format SKU: {BRAND_CODE}-{PRODUCT_CODE}-{SIZE}-{COLOR_CODE}
-- ================================================================

-- ---------------------------------------------------------------
-- Product 1: Nike Air Zoom Pegasus 41
-- Màu: Đen (#17), Trắng (#18) × Size: 39-44
-- Giá gốc: 3.500.000đ | Sale: 2.800.000đ
-- ---------------------------------------------------------------
INSERT INTO `product_variants`
    (`id`, `product_id`, `sku`, `price`, `sale_price`, `stock_quantity`, `low_stock_threshold`)
VALUES
    -- Màu Đen
    (1,  1, 'NK-PEG41-39-BLK', 3500000, 2800000, 15, 5),
    (2,  1, 'NK-PEG41-40-BLK', 3500000, 2800000, 20, 5),
    (3,  1, 'NK-PEG41-41-BLK', 3500000, 2800000, 25, 5),
    (4,  1, 'NK-PEG41-42-BLK', 3500000, 2800000, 30, 5),
    (5,  1, 'NK-PEG41-43-BLK', 3500000, 2800000, 18, 5),
    (6,  1, 'NK-PEG41-44-BLK', 3500000, 2800000, 10, 5),
    -- Màu Trắng
    (7,  1, 'NK-PEG41-39-WHT', 3500000, NULL,    12, 5),
    (8,  1, 'NK-PEG41-40-WHT', 3500000, NULL,    18, 5),
    (9,  1, 'NK-PEG41-41-WHT', 3500000, NULL,    22, 5),
    (10, 1, 'NK-PEG41-42-WHT', 3500000, NULL,    28, 5),
    (11, 1, 'NK-PEG41-43-WHT', 3500000, NULL,    15, 5),
    (12, 1, 'NK-PEG41-44-WHT', 3500000, NULL,     8, 5),

-- ---------------------------------------------------------------
-- Product 2: Adidas Ultraboost 24
-- Màu: Xanh Navy (#20), Trắng (#18) × Size: 39-44
-- Giá: 4.500.000đ (không sale)
-- ---------------------------------------------------------------
    (13, 2, 'AD-UB24-39-NVY', 4500000, NULL, 10, 5),
    (14, 2, 'AD-UB24-40-NVY', 4500000, NULL, 15, 5),
    (15, 2, 'AD-UB24-41-NVY', 4500000, NULL, 20, 5),
    (16, 2, 'AD-UB24-42-NVY', 4500000, NULL, 25, 5),
    (17, 2, 'AD-UB24-43-NVY', 4500000, NULL, 12, 5),
    (18, 2, 'AD-UB24-44-NVY', 4500000, NULL,  6, 5),
    (19, 2, 'AD-UB24-40-WHT', 4500000, NULL, 10, 5),
    (20, 2, 'AD-UB24-42-WHT', 4500000, NULL, 12, 5),

-- ---------------------------------------------------------------
-- Product 3: Nike Metcon 9
-- Màu: Đen (#17) × Size: 39-44 | Giá: 3.200.000đ | Sale: 2.560.000đ
-- ---------------------------------------------------------------
    (21, 3, 'NK-MC9-39-BLK',  3200000, 2560000, 8,  5),
    (22, 3, 'NK-MC9-40-BLK',  3200000, 2560000, 12, 5),
    (23, 3, 'NK-MC9-41-BLK',  3200000, 2560000, 15, 5),
    (24, 3, 'NK-MC9-42-BLK',  3200000, 2560000, 18, 5),
    (25, 3, 'NK-MC9-43-BLK',  3200000, 2560000, 10, 5),
    (26, 3, 'NK-MC9-44-BLK',  3200000, 2560000,  5, 3),

-- ---------------------------------------------------------------
-- Product 4: Adidas Techfit Tee
-- Màu: Đen (#17), Xanh Navy (#20) × Size S,M,L,XL
-- Giá: 850.000đ | Sale: 680.000đ
-- ---------------------------------------------------------------
    (27, 4, 'AD-TF-S-BLK',  850000, 680000, 20, 5),
    (28, 4, 'AD-TF-M-BLK',  850000, 680000, 30, 5),
    (29, 4, 'AD-TF-L-BLK',  850000, 680000, 25, 5),
    (30, 4, 'AD-TF-XL-BLK', 850000, 680000, 15, 5),
    (31, 4, 'AD-TF-S-NVY',  850000, NULL,   18, 5),
    (32, 4, 'AD-TF-M-NVY',  850000, NULL,   25, 5),
    (33, 4, 'AD-TF-L-NVY',  850000, NULL,   20, 5),
    (34, 4, 'AD-TF-XL-NVY', 850000, NULL,   12, 5),

-- ---------------------------------------------------------------
-- Product 5: Puma Training Tee
-- Màu: Đỏ (#19), Xám (#21) × Size S,M,L,XL
-- Giá: 650.000đ (không sale)
-- ---------------------------------------------------------------
    (35, 5, 'PM-FT-S-RED',  650000, NULL, 25, 5),
    (36, 5, 'PM-FT-M-RED',  650000, NULL, 30, 5),
    (37, 5, 'PM-FT-L-RED',  650000, NULL, 20, 5),
    (38, 5, 'PM-FT-XL-RED', 650000, NULL, 10, 5),
    (39, 5, 'PM-FT-S-GRY',  650000, NULL, 20, 5),
    (40, 5, 'PM-FT-M-GRY',  650000, NULL, 25, 5),
    (41, 5, 'PM-FT-L-GRY',  650000, NULL, 18, 5),
    (42, 5, 'PM-FT-XL-GRY', 650000, NULL,  8, 5),

-- ---------------------------------------------------------------
-- Product 6: Under Armour Run Shorts
-- Màu: Đen (#17), Xanh Navy (#20) × Size S,M,L,XL
-- Giá: 750.000đ | Sale: 600.000đ
-- ---------------------------------------------------------------
    (43, 6, 'UA-RS-S-BLK',  750000, 600000, 20, 5),
    (44, 6, 'UA-RS-M-BLK',  750000, 600000, 28, 5),
    (45, 6, 'UA-RS-L-BLK',  750000, 600000, 22, 5),
    (46, 6, 'UA-RS-XL-BLK', 750000, 600000, 15, 5),
    (47, 6, 'UA-RS-S-NVY',  750000, NULL,   15, 5),
    (48, 6, 'UA-RS-M-NVY',  750000, NULL,   20, 5),
    (49, 6, 'UA-RS-L-NVY',  750000, NULL,   18, 5),
    (50, 6, 'UA-RS-XL-NVY', 750000, NULL,   10, 5),

-- ---------------------------------------------------------------
-- Product 7: Adidas X Speedportal.3 FG
-- Màu: Xanh Lá (#23), Đen (#17) × Size 38-44
-- Giá: 2.200.000đ | Sale: 1.760.000đ
-- ---------------------------------------------------------------
    (51, 7, 'AD-SP3-38-GRN', 2200000, 1760000, 8,  3),
    (52, 7, 'AD-SP3-39-GRN', 2200000, 1760000, 12, 3),
    (53, 7, 'AD-SP3-40-GRN', 2200000, 1760000, 15, 3),
    (54, 7, 'AD-SP3-41-GRN', 2200000, 1760000, 18, 3),
    (55, 7, 'AD-SP3-42-GRN', 2200000, 1760000, 10, 3),
    (56, 7, 'AD-SP3-43-GRN', 2200000, 1760000,  6, 3),
    (57, 7, 'AD-SP3-44-GRN', 2200000, 1760000,  4, 3),

-- ---------------------------------------------------------------
-- Product 8: New Balance 1080v14
-- Màu: Trắng (#18), Cam (#22) × Size 39-44
-- Giá: 4.200.000đ (không sale)
-- ---------------------------------------------------------------
    (58, 8, 'NB-1080-39-WHT', 4200000, NULL, 10, 5),
    (59, 8, 'NB-1080-40-WHT', 4200000, NULL, 15, 5),
    (60, 8, 'NB-1080-41-WHT', 4200000, NULL, 18, 5),
    (61, 8, 'NB-1080-42-WHT', 4200000, NULL, 20, 5),
    (62, 8, 'NB-1080-43-WHT', 4200000, NULL, 12, 5),
    (63, 8, 'NB-1080-44-WHT', 4200000, NULL,  6, 5),
    (64, 8, 'NB-1080-40-ONG', 4200000, NULL, 10, 5),
    (65, 8, 'NB-1080-42-ONG', 4200000, NULL, 12, 5);

-- ================================================================
-- VARIANT ATTRIBUTE VALUES
-- (variant_id, attribute_value_id)
-- attribute_values: Size IDs 1-16, Màu IDs 17-24
-- ================================================================

-- -- Helper: Build variant_attribute_values --
-- Product 1: Nike Pegasus 41 — Màu Đen (ID 17) + Size 39-44 (ID 4-9)
INSERT INTO `variant_attribute_values` VALUES
    (1, 4),(1, 17),   -- Size 39, Đen
    (2, 5),(2, 17),   -- Size 40, Đen
    (3, 6),(3, 17),   -- Size 41, Đen
    (4, 7),(4, 17),   -- Size 42, Đen
    (5, 8),(5, 17),   -- Size 43, Đen
    (6, 9),(6, 17),   -- Size 44, Đen
    -- Màu Trắng (ID 18) + Size 39-44
    (7, 4),(7, 18),
    (8, 5),(8, 18),
    (9, 6),(9, 18),
    (10, 7),(10, 18),
    (11, 8),(11, 18),
    (12, 9),(12, 18),

    -- Product 2: Ultraboost 24 — Xanh Navy (ID 20)
    (13, 4),(13, 20),
    (14, 5),(14, 20),
    (15, 6),(15, 20),
    (16, 7),(16, 20),
    (17, 8),(17, 20),
    (18, 9),(18, 20),
    -- Trắng (18)
    (19, 5),(19, 18),
    (20, 7),(20, 18),

    -- Product 3: Metcon 9 — Đen (17)
    (21, 4),(21, 17),
    (22, 5),(22, 17),
    (23, 6),(23, 17),
    (24, 7),(24, 17),
    (25, 8),(25, 17),
    (26, 9),(26, 17),

    -- Product 4: Techfit Tee — Đen (17), Navy (20) × S(12),M(13),L(14),XL(15)
    (27, 12),(27, 17),
    (28, 13),(28, 17),
    (29, 14),(29, 17),
    (30, 15),(30, 17),
    (31, 12),(31, 20),
    (32, 13),(32, 20),
    (33, 14),(33, 20),
    (34, 15),(34, 20),

    -- Product 5: Puma Tee — Đỏ (19), Xám (21) × S,M,L,XL
    (35, 12),(35, 19),
    (36, 13),(36, 19),
    (37, 14),(37, 19),
    (38, 15),(38, 19),
    (39, 12),(39, 21),
    (40, 13),(40, 21),
    (41, 14),(41, 21),
    (42, 15),(42, 21),

    -- Product 6: UA Run Shorts — Đen (17), Navy (20) × S,M,L,XL
    (43, 12),(43, 17),
    (44, 13),(44, 17),
    (45, 14),(45, 17),
    (46, 15),(46, 17),
    (47, 12),(47, 20),
    (48, 13),(48, 20),
    (49, 14),(49, 20),
    (50, 15),(50, 20),

    -- Product 7: Speedportal — Xanh Lá (23) × Size 38-44 (3-9)
    (51, 3),(51, 23),
    (52, 4),(52, 23),
    (53, 5),(53, 23),
    (54, 6),(54, 23),
    (55, 7),(55, 23),
    (56, 8),(56, 23),
    (57, 9),(57, 23),

    -- Product 8: NB 1080 — Trắng (18), Cam (22) × Size 39-44
    (58, 4),(58, 18),
    (59, 5),(59, 18),
    (60, 6),(60, 18),
    (61, 7),(61, 18),
    (62, 8),(62, 18),
    (63, 9),(63, 18),
    (64, 5),(64, 22),
    (65, 7),(65, 22);

-- ================================================================
-- COUPONS
-- ================================================================
INSERT INTO `coupons`
    (`id`, `code`, `name`, `type`, `value`, `max_discount`, `min_order_value`,
     `max_uses`, `used_count`, `starts_at`, `ends_at`, `is_active`)
VALUES
    (1, 'WELCOME10', 'Chào mừng khách hàng mới - Giảm 10%',
     'percent', 10, 200000, 500000,
     1000, 5,
     '2025-01-01 00:00:00', '2026-12-31 23:59:59', 1),

    (2, 'SPORT50K', 'Giảm 50.000đ cho đơn từ 1 triệu',
     'fixed', 50000, NULL, 1000000,
     500, 12,
     '2025-01-01 00:00:00', '2025-12-31 23:59:59', 1),

    (3, 'FLASH30', 'Flash Sale - Giảm 30% tối đa 500k',
     'percent', 30, 500000, 2000000,
     100, 87,
     '2025-10-01 00:00:00', '2025-10-31 23:59:59', 1),

    (4, 'NIKE20', 'Giảm 20% cho giày Nike',
     'percent', 20, 600000, 1000000,
     NULL, 0,
     '2025-01-01 00:00:00', NULL, 0);  -- inactive

-- ================================================================
-- SAMPLE ORDER (Đơn hàng mẫu)
-- User 2 mua: Nike Pegasus 41 Size 42 Đen (variant_id=4) × 1
--             Adidas Techfit Tee Size M Đen (variant_id=28) × 2
-- Dùng coupon SPORT50K (id=2, giảm 50k)
-- ================================================================
INSERT INTO `orders`
    (`id`, `user_id`, `order_number`, `subtotal`, `discount_amount`, `shipping_fee`, `total`,
     `coupon_id`, `coupon_code`, `payment_method`, `payment_status`, `status`)
VALUES
    (1, 2, 'SZ-20251014-00001',
     4160000,   -- 2800000 + (680000 × 2)
     50000,     -- SPORT50K
     30000,
     4140000,   -- 4160000 - 50000 + 30000
     2, 'SPORT50K', 'cod', 'pending', 'confirmed');

INSERT INTO `order_shipping`
    (`order_id`, `recipient_name`, `phone`, `address_line`, `ward`, `district`, `province`)
VALUES
    (1, 'Nguyễn Văn An', '0912345678',
     '123 Đường Nguyễn Huệ', 'Phường Bến Nghé', 'Quận 1', 'TP. Hồ Chí Minh');

INSERT INTO `order_items`
    (`order_id`, `product_id`, `variant_id`, `product_name`, `variant_sku`,
     `variant_info`, `unit_price`, `quantity`, `subtotal`)
VALUES
    (1, 1, 4,  'Nike Air Zoom Pegasus 41', 'NK-PEG41-42-BLK',
     'Size: 42 · Màu sắc: Đen',  2800000, 1, 2800000),
    (1, 4, 28, 'Adidas Techfit Long Sleeve Tee', 'AD-TF-M-BLK',
     'Size: M · Màu sắc: Đen',    680000, 2, 1360000);

INSERT INTO `order_status_history` (`order_id`, `changed_by`, `old_status`, `new_status`, `note`)
VALUES
    (1, NULL, NULL,      'pending',   'Đơn hàng được tạo tự động'),
    (1, 1,    'pending', 'confirmed', 'Admin đã xác nhận đơn hàng');

-- Cập nhật kho sau đơn mẫu
UPDATE `product_variants` SET `stock_quantity` = `stock_quantity` - 1 WHERE `id` = 4;
UPDATE `product_variants` SET `stock_quantity` = `stock_quantity` - 2 WHERE `id` = 28;
UPDATE `coupons` SET `used_count` = `used_count` + 1 WHERE `id` = 2;

INSERT INTO `inventory_logs`
    (`variant_id`, `order_id`, `performed_by`, `type`, `quantity_change`, `quantity_after`, `note`)
VALUES
    (4,  1, NULL, 'out', -1, 29, 'Đơn hàng: SZ-20251014-00001'),
    (28, 1, NULL, 'out', -2, 28, 'Đơn hàng: SZ-20251014-00001');

-- ================================================================
-- Restore session settings
-- ================================================================
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
