-- ResearchForge schema
SET NAMES utf8mb4;
SET time_zone = '-03:00';

CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  theme VARCHAR(16) NOT NULL DEFAULT 'system',
  avatar VARCHAR(255) NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  last_login_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS projects (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  title VARCHAR(220) NOT NULL,
  summary TEXT NULL,
  status VARCHAR(32) NOT NULL DEFAULT 'active',
  discipline VARCHAR(120) NULL,
  confidentiality VARCHAR(32) NOT NULL DEFAULT 'private',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_projects_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  KEY idx_projects_user (user_id),
  KEY idx_projects_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS questions (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  project_id INT UNSIGNED NOT NULL,
  parent_id INT UNSIGNED NULL,
  prompt TEXT NOT NULL,
  kind VARCHAR(32) NOT NULL DEFAULT 'open',
  priority VARCHAR(16) NOT NULL DEFAULT 'medium',
  status VARCHAR(32) NOT NULL DEFAULT 'open',
  notes TEXT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_questions_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
  CONSTRAINT fk_questions_parent FOREIGN KEY (parent_id) REFERENCES questions(id) ON DELETE SET NULL,
  KEY idx_questions_project (project_id),
  KEY idx_questions_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS sources (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  project_id INT UNSIGNED NOT NULL,
  title VARCHAR(255) NOT NULL,
  source_type VARCHAR(40) NOT NULL DEFAULT 'article',
  authors VARCHAR(255) NULL,
  year SMALLINT NULL,
  publisher VARCHAR(180) NULL,
  url VARCHAR(500) NULL,
  doi VARCHAR(120) NULL,
  isbn VARCHAR(40) NULL,
  accessed_at DATE NULL,
  language VARCHAR(40) NULL,
  reliability TINYINT UNSIGNED NULL,
  bias_notes TEXT NULL,
  reading_status VARCHAR(32) NOT NULL DEFAULT 'to_read',
  notes TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_sources_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
  KEY idx_sources_project (project_id),
  KEY idx_sources_type (source_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS claims (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  project_id INT UNSIGNED NOT NULL,
  question_id INT UNSIGNED NULL,
  statement TEXT NOT NULL,
  claim_type VARCHAR(40) NOT NULL DEFAULT 'factual',
  status VARCHAR(32) NOT NULL DEFAULT 'hypothesis',
  confidence TINYINT UNSIGNED NOT NULL DEFAULT 50,
  notes TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_claims_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
  CONSTRAINT fk_claims_question FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE SET NULL,
  KEY idx_claims_project (project_id),
  KEY idx_claims_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS quotes (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  project_id INT UNSIGNED NOT NULL,
  source_id INT UNSIGNED NOT NULL,
  claim_id INT UNSIGNED NULL,
  excerpt TEXT NOT NULL,
  locator VARCHAR(120) NULL,
  context_note TEXT NULL,
  translation TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_quotes_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
  CONSTRAINT fk_quotes_source FOREIGN KEY (source_id) REFERENCES sources(id) ON DELETE CASCADE,
  CONSTRAINT fk_quotes_claim FOREIGN KEY (claim_id) REFERENCES claims(id) ON DELETE SET NULL,
  KEY idx_quotes_project (project_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS evidence_items (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  project_id INT UNSIGNED NOT NULL,
  claim_id INT UNSIGNED NOT NULL,
  quote_id INT UNSIGNED NULL,
  source_id INT UNSIGNED NULL,
  direction VARCHAR(24) NOT NULL DEFAULT 'supports',
  strength VARCHAR(16) NOT NULL DEFAULT 'moderate',
  evidence_type VARCHAR(40) NOT NULL DEFAULT 'document',
  summary TEXT NOT NULL,
  notes TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_evidence_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
  CONSTRAINT fk_evidence_claim FOREIGN KEY (claim_id) REFERENCES claims(id) ON DELETE CASCADE,
  CONSTRAINT fk_evidence_quote FOREIGN KEY (quote_id) REFERENCES quotes(id) ON DELETE SET NULL,
  CONSTRAINT fk_evidence_source FOREIGN KEY (source_id) REFERENCES sources(id) ON DELETE SET NULL,
  KEY idx_evidence_project (project_id),
  KEY idx_evidence_claim (claim_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS conclusions (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  project_id INT UNSIGNED NOT NULL,
  question_id INT UNSIGNED NULL,
  title VARCHAR(220) NOT NULL,
  body TEXT NOT NULL,
  conclusion_type VARCHAR(40) NOT NULL DEFAULT 'provisional',
  limitations TEXT NULL,
  next_steps TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_conclusions_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
  CONSTRAINT fk_conclusions_question FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE SET NULL,
  KEY idx_conclusions_project (project_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS dossiers (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  project_id INT UNSIGNED NOT NULL,
  title VARCHAR(220) NOT NULL,
  subtitle VARCHAR(255) NULL,
  mode VARCHAR(40) NOT NULL DEFAULT 'briefing',
  status VARCHAR(32) NOT NULL DEFAULT 'draft',
  executive_summary TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_dossiers_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
  KEY idx_dossiers_project (project_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS dossier_items (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  dossier_id INT UNSIGNED NOT NULL,
  section VARCHAR(40) NOT NULL,
  item_type VARCHAR(40) NOT NULL,
  item_id INT UNSIGNED NOT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_dossier_items_dossier FOREIGN KEY (dossier_id) REFERENCES dossiers(id) ON DELETE CASCADE,
  KEY idx_dossier_items_dossier (dossier_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS settings (
  user_id INT UNSIGNED NOT NULL,
  `key` VARCHAR(64) NOT NULL,
  `value` TEXT NULL,
  PRIMARY KEY (user_id, `key`),
  CONSTRAINT fk_settings_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
