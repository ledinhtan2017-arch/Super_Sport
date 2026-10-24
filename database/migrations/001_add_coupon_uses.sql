-- ============================================================
-- Migration: add_coupon_uses_table.sql
-- Adds coupon_uses table for tracking coupon usage per user
-- ============================================================

CREATE TABLE IF NOT EXISTS `coupon_uses` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `coupon_id`  INT UNSIGNED NOT NULL,
    `user_id`    INT UNSIGNED NOT NULL,
    `order_id`   INT UNSIGNED,
    `used_at`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_coupon_user` (`coupon_id`, `user_id`),
    CONSTRAINT `fk_cu_coupon` FOREIGN KEY (`coupon_id`) REFERENCES `coupons`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_cu_user`   FOREIGN KEY (`user_id`)   REFERENCES `users`(`id`)   ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;