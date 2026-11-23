CREATE TABLE users (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(150) NOT NULL,
 email VARCHAR(190) NOT NULL UNIQUE,
 mobile VARCHAR(20) NULL UNIQUE,
 password_hash VARCHAR(255) NOT NULL,
 role ENUM('customer','admin','nutritionist','support','affiliate') NOT NULL DEFAULT 'customer',
 status ENUM('active','blocked','pending') NOT NULL DEFAULT 'active',
 email_verified_at DATETIME NULL,
 mobile_verified_at DATETIME NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE user_profiles (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 user_id BIGINT UNSIGNED NOT NULL UNIQUE,
 dob DATE NULL, gender VARCHAR(30) NULL, city VARCHAR(100) NULL,
 preferred_language VARCHAR(10) NOT NULL DEFAULT 'hi',
 height_cm DECIMAL(5,2) NULL, weight_kg DECIMAL(5,2) NULL,
 food_preference VARCHAR(30) NULL, activity_level VARCHAR(30) NULL, lifestyle VARCHAR(50) NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_profiles_user FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE user_consents (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, user_id BIGINT UNSIGNED NOT NULL,
 consent_type VARCHAR(80) NOT NULL, granted BOOLEAN NOT NULL,
 policy_version VARCHAR(30) NOT NULL, ip_hash CHAR(64) NULL,
 created_at DATETIME NOT NULL,
 FOREIGN KEY(user_id) REFERENCES users(id), INDEX(user_id,consent_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE products (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, name VARCHAR(150) NOT NULL,
 slug VARCHAR(160) NOT NULL UNIQUE, price_paise BIGINT UNSIGNED NOT NULL,
 duration_days INT NULL, description TEXT NULL, features JSON NULL,
 is_active BOOLEAN NOT NULL DEFAULT TRUE, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE assessments (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, user_id BIGINT UNSIGNED NOT NULL,
 assessment_code VARCHAR(40) NOT NULL UNIQUE, goal VARCHAR(100) NOT NULL,
 daily_food_budget INT NULL, region VARCHAR(100) NULL, answers JSON NOT NULL,
 screening_status ENUM('pending','eligible','professional_review','referred','rejected') NOT NULL DEFAULT 'pending',
 status ENUM('draft','submitted','reviewed','closed') NOT NULL DEFAULT 'draft',
 submitted_at DATETIME NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(user_id) REFERENCES users(id), INDEX(user_id), INDEX(status,screening_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE orders (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, order_number VARCHAR(40) NOT NULL UNIQUE,
 user_id BIGINT UNSIGNED NOT NULL, product_id BIGINT UNSIGNED NOT NULL,
 assessment_id BIGINT UNSIGNED NULL, subtotal_paise BIGINT UNSIGNED NOT NULL,
 discount_paise BIGINT UNSIGNED NOT NULL DEFAULT 0, tax_paise BIGINT UNSIGNED NOT NULL DEFAULT 0,
 total_paise BIGINT UNSIGNED NOT NULL, status VARCHAR(40) NOT NULL DEFAULT 'pending',
 affiliate_id BIGINT UNSIGNED NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(user_id) REFERENCES users(id), FOREIGN KEY(product_id) REFERENCES products(id),
 FOREIGN KEY(assessment_id) REFERENCES assessments(id), INDEX(user_id,status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE payments (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, order_id BIGINT UNSIGNED NOT NULL,
 gateway VARCHAR(30) NOT NULL, gateway_order_id VARCHAR(150) NULL,
 gateway_payment_id VARCHAR(150) NULL UNIQUE, amount_paise BIGINT UNSIGNED NOT NULL,
 status VARCHAR(30) NOT NULL DEFAULT 'created', paid_at DATETIME NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY(order_id) REFERENCES orders(id), INDEX(order_id,status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE payment_webhooks (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, provider VARCHAR(40) NOT NULL,
 event_id VARCHAR(190) NOT NULL, event_type VARCHAR(100) NOT NULL,
 payload JSON NOT NULL, signature_valid BOOLEAN NOT NULL DEFAULT FALSE,
 processed_at DATETIME NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY uq_provider_event(provider,event_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE meal_plans (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, order_id BIGINT UNSIGNED NOT NULL,
 assessment_id BIGINT UNSIGNED NOT NULL, version INT NOT NULL DEFAULT 1,
 status ENUM('queued','generating','draft','nutritionist_review','revision_requested','approved','delivered','rejected') NOT NULL DEFAULT 'queued',
 ai_model VARCHAR(100) NULL, plan_json JSON NULL, reviewed_by BIGINT UNSIGNED NULL,
 approved_at DATETIME NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(order_id) REFERENCES orders(id), FOREIGN KEY(assessment_id) REFERENCES assessments(id),
 FOREIGN KEY(reviewed_by) REFERENCES users(id), UNIQUE KEY uq_plan_version(order_id,version)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE nutritionist_reviews (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, meal_plan_id BIGINT UNSIGNED NOT NULL,
 nutritionist_id BIGINT UNSIGNED NOT NULL, decision ENUM('approved','revision','rejected') NOT NULL,
 comments TEXT NULL, reviewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(meal_plan_id) REFERENCES meal_plans(id), FOREIGN KEY(nutritionist_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE pdf_documents (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, meal_plan_id BIGINT UNSIGNED NOT NULL,
 storage_key VARCHAR(255) NOT NULL, mime_type VARCHAR(100) NOT NULL DEFAULT 'application/pdf',
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(meal_plan_id) REFERENCES meal_plans(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE notification_logs (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, user_id BIGINT UNSIGNED NULL,
 channel VARCHAR(30) NOT NULL, template_name VARCHAR(100) NOT NULL,
 status VARCHAR(30) NOT NULL, provider_message_id VARCHAR(150) NULL,
 sent_at DATETIME NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE jobs (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, job_type VARCHAR(100) NOT NULL,
 payload JSON NOT NULL, status ENUM('pending','processing','completed','failed') NOT NULL DEFAULT 'pending',
 attempts INT NOT NULL DEFAULT 0, available_at DATETIME NOT NULL,
 locked_at DATETIME NULL, last_error TEXT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 INDEX idx_jobs_ready(status,available_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
