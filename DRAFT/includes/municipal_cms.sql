-- ============================================================
--  Municipal CMS — Full Database Schema
--  Generated from admin PHP source files
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- ── Drop tables in safe order (dependents first) ─────────────

DROP TABLE IF EXISTS `approved_logs`;
DROP TABLE IF EXISTS `deleted_logs`;
DROP TABLE IF EXISTS `pending_approval`;
DROP TABLE IF EXISTS `announcements`;
DROP TABLE IF EXISTS `destinations`;
DROP TABLE IF EXISTS `profiles`;
DROP TABLE IF EXISTS `services`;
DROP TABLE IF EXISTS `faqs`;
DROP TABLE IF EXISTS `galleries`;
DROP TABLE IF EXISTS `users`;


-- ============================================================
--  1. users
--     Roles: admin | approver | editor
-- ============================================================
CREATE TABLE `users` (
    `id`            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `username`      VARCHAR(80)     NOT NULL UNIQUE,
    `password_hash` VARCHAR(255)    NOT NULL,
    `role`          ENUM('admin','approver','editor') NOT NULL DEFAULT 'editor',
    `created_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default admin account  (password: admin123 — change immediately)
INSERT INTO `users` (`username`, `password_hash`, `role`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');


-- ============================================================
--  2. announcements
--     Status lifecycle: pending → active | deleted (soft)
-- ============================================================
CREATE TABLE `announcements` (
    `id`          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `title`       VARCHAR(255)  NOT NULL,
    `description` TEXT          NOT NULL,
    `image`       VARCHAR(255)      NULL DEFAULT NULL,
    `status`      ENUM('pending','active','deleted') NOT NULL DEFAULT 'pending',
    `created_at`  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
--  3. destinations
--     Rich tourism content with JSON sub-fields
--     Status lifecycle: pending → active | deleted (soft)
-- ============================================================
CREATE TABLE `destinations` (
    `id`                  INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `name`                VARCHAR(255)  NOT NULL,
    `description`         TEXT              NULL,
    -- Newline-separated bullet points stored as JSON array
    `bullet_descriptions` JSON              NULL,
    `preview_image`       VARCHAR(255)      NULL DEFAULT NULL,
    -- Array of uploaded filenames (images / videos)
    `media_links`         JSON              NULL,
    `coordinators`        VARCHAR(255)      NULL DEFAULT NULL,
    `coordinator_links`   TEXT              NULL DEFAULT NULL,
    `homestay_links`      TEXT              NULL DEFAULT NULL,
    `analytics_visitors`  INT UNSIGNED  NOT NULL DEFAULT 0,
    `forecast_traffic`    INT UNSIGNED  NOT NULL DEFAULT 0,
    -- JSON array of trail objects
    -- Each: { name, jumpoff, difficulty (Easy|Moderate|Difficult|Expert), duration }
    `trails`              JSON              NULL,
    -- JSON array of camping-site objects
    -- Each: { name, location, capacity, image }
    `camping_sites`       JSON              NULL,
    `is_open`             TINYINT(1)    NOT NULL DEFAULT 1,
    `status`              ENUM('pending','active','deleted') NOT NULL DEFAULT 'pending',
    `created_at`          DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_status`  (`status`),
    KEY `idx_is_open` (`is_open`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
--  4. profiles
--     Organizational structure: officials, offices, barangays
-- ============================================================
CREATE TABLE `profiles` (
    `id`          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `name`        VARCHAR(255)  NOT NULL,
    `position`    VARCHAR(255)  NOT NULL,
    `category`    ENUM('Executive','Councilor','Barangay','Office') NOT NULL DEFAULT 'Office',
    `description` TEXT              NULL,
    `image`       VARCHAR(255)      NULL DEFAULT NULL,
    `created_at`  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
--  5. services
--     Municipal services with optional downloadable form link
-- ============================================================
CREATE TABLE `services` (
    `id`           INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `service_name` VARCHAR(255)  NOT NULL,
    `office`       VARCHAR(255)      NULL DEFAULT NULL,
    `form_link`    VARCHAR(512)      NULL DEFAULT NULL,
    `created_at`   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_office` (`office`(100))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
--  6. faqs
--     Frequently Asked Questions
-- ============================================================
CREATE TABLE `faqs` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `question`   VARCHAR(512) NOT NULL,
    `answer`     TEXT         NOT NULL,
    `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
--  7. galleries
--     Photo/image gallery with ordering and visibility toggle
-- ============================================================
CREATE TABLE `galleries` (
    `id`            INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `title`         VARCHAR(255)  NOT NULL,
    `image`         VARCHAR(255)  NOT NULL,
    `display_order` INT           NOT NULL DEFAULT 0,
    `is_active`     TINYINT(1)    NOT NULL DEFAULT 1,
    `created_at`    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_display_order` (`display_order`),
    KEY `idx_is_active`     (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
--  8. pending_approval
--     Queue for content that requires approver/admin sign-off
--     Status: pending → (approved via approved_logs) | rejected
-- ============================================================
CREATE TABLE `pending_approval` (
    `id`           INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `module_name`  VARCHAR(80)   NOT NULL,   -- e.g. 'announcements', 'destinations'
    `item_id`      INT UNSIGNED  NOT NULL,   -- FK to the relevant module's id
    `submitted_by` VARCHAR(80)   NOT NULL,   -- session role/username of submitter
    `status`       ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    `created_at`   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_module`  (`module_name`),
    KEY `idx_status`  (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
--  9. approved_logs
--     Immutable audit trail of every approval action
-- ============================================================
CREATE TABLE `approved_logs` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `module_name` VARCHAR(80)  NOT NULL,
    `item_id`     INT UNSIGNED NOT NULL,
    `approved_by` VARCHAR(80)  NOT NULL,   -- role/username of approver
    `approved_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_module` (`module_name`),
    KEY `idx_item`   (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 10. deleted_logs
--     Soft-delete audit trail; supports restore for announcements
-- ============================================================
CREATE TABLE `deleted_logs` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `module_name` VARCHAR(80)  NOT NULL,
    `item_id`     INT UNSIGNED NOT NULL,
    `deleted_by`  VARCHAR(80)  NOT NULL,   -- role/username of deleter
    `deleted_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_module` (`module_name`),
    KEY `idx_item`   (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
--  Sample / seed data (optional — remove for production)
-- ============================================================

-- Sample announcement
INSERT INTO `announcements` (`title`, `description`, `status`) VALUES
('Welcome to the Municipal Portal', 'This is the official content management system for the municipality. All announcements will appear here.', 'active');

-- Sample FAQ
INSERT INTO `faqs` (`question`, `answer`) VALUES
('How do I request a barangay clearance?', 'Visit your barangay hall with one valid government-issued ID and fill out the request form. Processing takes 1-2 business days.'),
('What are the office hours?', 'Monday to Friday, 8:00 AM – 5:00 PM. Closed on weekends and public holidays.');

-- Sample service
INSERT INTO `services` (`service_name`, `office`, `form_link`) VALUES
('Business Permit Renewal', 'Business Permits & Licensing Office', 'https://example.gov.ph/forms/business-permit'),
('Civil Registration', 'Local Civil Registry Office', NULL),
('Senior Citizen ID', 'OSCA', NULL);

-- Sample gallery entry
INSERT INTO `galleries` (`title`, `image`, `display_order`, `is_active`) VALUES
('Municipal Hall', 'municipal_hall.jpg', 1, 1),
('Town Plaza', 'town_plaza.jpg', 2, 1);
