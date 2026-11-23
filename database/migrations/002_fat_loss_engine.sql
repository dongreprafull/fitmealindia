-- Fat-loss engine: stores auto-generated calculator-based meal plans.
-- Independent of the paid `meal_plans` workflow (orders/nutritionist review),
-- used for (a) anonymous "free 3-day plan" leads and (b) plans auto-generated
-- right after a logged-in customer submits their assessment.
CREATE TABLE generated_plans (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 user_id BIGINT UNSIGNED NULL,
 assessment_id BIGINT UNSIGNED NULL,
 source ENUM('lead','assessment') NOT NULL DEFAULT 'lead',
 name VARCHAR(150) NULL,
 mobile VARCHAR(20) NULL,
 email VARCHAR(190) NULL,
 gender ENUM('male','female') NOT NULL,
 age INT NOT NULL,
 height_cm DECIMAL(5,2) NOT NULL,
 weight_kg DECIMAL(5,2) NOT NULL,
 activity_level VARCHAR(30) NOT NULL DEFAULT 'sedentary',
 food_preference VARCHAR(30) NOT NULL DEFAULT 'vegetarian',
 goal VARCHAR(50) NOT NULL DEFAULT 'fat_loss',
 bmi DECIMAL(5,2) NOT NULL,
 ideal_weight_min DECIMAL(5,2) NOT NULL,
 ideal_weight_max DECIMAL(5,2) NOT NULL,
 excess_weight_kg DECIMAL(5,2) NOT NULL DEFAULT 0,
 bmr INT NOT NULL,
 tdee INT NOT NULL,
 target_calories INT NOT NULL,
 plan_days INT NOT NULL DEFAULT 3,
 plan_json JSON NOT NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE SET NULL,
 FOREIGN KEY(assessment_id) REFERENCES assessments(id) ON DELETE SET NULL,
 INDEX idx_generated_plans_user(user_id),
 INDEX idx_generated_plans_assessment(assessment_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Gender is needed for BMR/ideal-weight calculations; assessments.answers JSON
-- already carries free-form fields, but the structured profile column is useful
-- for admin reporting, so make sure it is explicitly nullable (it already is).
ALTER TABLE user_profiles MODIFY gender VARCHAR(30) NULL;
