-- ─────────────────────────────────────────────────────────────────────────────
-- Municipal CMS — Database Migration
-- Run this once to add new columns & tables required by the updated PHP files.
-- ─────────────────────────────────────────────────────────────────────────────

-- 1. destinations: add new columns (safe — uses IF NOT EXISTS workaround via ALTER IGNORE)
ALTER TABLE destinations
  ADD COLUMN IF NOT EXISTS bullet_descriptions   TEXT        DEFAULT NULL COMMENT 'JSON array of bullet-point highlights',
  ADD COLUMN IF NOT EXISTS coordinator_links     TEXT        DEFAULT NULL COMMENT 'Contact URLs or phone for coordinators',
  ADD COLUMN IF NOT EXISTS trails                LONGTEXT    DEFAULT NULL COMMENT 'JSON array of trail objects',
  ADD COLUMN IF NOT EXISTS camping_sites         LONGTEXT    DEFAULT NULL COMMENT 'JSON array of camping site objects',
  ADD COLUMN IF NOT EXISTS is_open               TINYINT(1)  NOT NULL DEFAULT 1 COMMENT '1 = open to public, 0 = closed',
  ADD COLUMN IF NOT EXISTS status                VARCHAR(20) NOT NULL DEFAULT 'active' COMMENT 'active | pending | deleted';

-- 2. announcements: add status column if missing
ALTER TABLE announcements
  ADD COLUMN IF NOT EXISTS status VARCHAR(20) NOT NULL DEFAULT 'active' COMMENT 'active | pending | deleted';

-- 3. Ensure deleted_logs table exists
CREATE TABLE IF NOT EXISTS deleted_logs (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  module_name  VARCHAR(100) NOT NULL,
  item_id      INT UNSIGNED NOT NULL,
  deleted_by   VARCHAR(100) NOT NULL,
  deleted_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX (module_name, item_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Ensure approved_logs table exists
CREATE TABLE IF NOT EXISTS approved_logs (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  module_name  VARCHAR(100) NOT NULL,
  item_id      INT UNSIGNED NOT NULL,
  approved_by  VARCHAR(100) NOT NULL,
  approved_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX (module_name, item_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Ensure pending_approval table exists
CREATE TABLE IF NOT EXISTS pending_approval (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  module_name  VARCHAR(100) NOT NULL,
  item_id      INT UNSIGNED NOT NULL,
  submitted_by VARCHAR(100) NOT NULL,
  status       ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX (module_name, item_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
