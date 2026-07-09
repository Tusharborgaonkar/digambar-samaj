-- Jain Digambar Matrimony - Final Production Database Schema (v1.1)

-- 1. admins
CREATE TABLE IF NOT EXISTS admins (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('super_admin', 'admin', 'moderator') DEFAULT 'admin',
    status BOOLEAN DEFAULT TRUE,
    last_login DATETIME NULL,
    last_login_ip VARCHAR(45) NULL,
    password_updated_at DATETIME NULL,
    two_factor_enabled BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_admins_status (status),
    INDEX idx_admins_role (role),
    INDEX idx_admins_last_login (last_login)
);

-- 2. users
CREATE TABLE IF NOT EXISTS users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    profile_id VARCHAR(20) UNIQUE,
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE,
    mobile VARCHAR(20) NOT NULL,
    country_code VARCHAR(10),
    password_hash VARCHAR(255),
    are_you_digambar_jain ENUM('Yes','No') DEFAULT 'Yes',
    gender ENUM('Male','Female'),
    birth_date DATE,
    birth_time TIME,
    birth_place VARCHAR(255),
    native_place VARCHAR(255),
    gotra VARCHAR(255),
    mama_gotra VARCHAR(255),
    manglik ENUM('Yes','No'),
    height VARCHAR(20),
    weight DECIMAL(5,2),
    marital_status ENUM('Never Married', 'Widow', 'Widower', 'Divorce') DEFAULT 'Never Married',
    handicapped ENUM('Yes','No') DEFAULT 'No',
    higher_education TEXT,
    occupation VARCHAR(255),
    company_name VARCHAR(255),
    designation VARCHAR(255),
    monthly_income DECIMAL(12,2),
    languages TEXT,
    hobbies TEXT,
    partner_preference TEXT,
    profile_photo VARCHAR(255),
    family_photo VARCHAR(255),
    profile_photo_drive_url TEXT,
    payment_screenshot VARCHAR(255),
    payment_proof_drive_url TEXT,
    status ENUM('account_pending', 'account_approved', 'pending', 'approved', 'rejected', 'blocked') DEFAULT 'account_pending',
    verified BOOLEAN DEFAULT FALSE,
    approved_by BIGINT UNSIGNED NULL,
    approved_at DATETIME NULL,
    featured_until DATE NULL,
    deleted_at TIMESTAMP NULL,
    registration_source ENUM('website', 'google_form', 'admin') DEFAULT 'website',
    is_public BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (approved_by) REFERENCES admins(id) ON DELETE SET NULL,
    INDEX idx_users_status (status),
    INDEX idx_users_gender (gender),
    INDEX idx_users_gotra (gotra),
    INDEX idx_users_native_place (native_place),
    INDEX idx_users_marital_status (marital_status),
    INDEX idx_users_mobile (mobile),
    INDEX idx_users_email (email),
    INDEX idx_users_featured_until (featured_until),
    INDEX idx_users_birth_date (birth_date),
    INDEX idx_users_verified (verified),
    INDEX idx_users_is_public (is_public),
    INDEX idx_users_approved_by (approved_by)
);

-- 3. user_addresses
CREATE TABLE IF NOT EXISTS user_addresses (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    permanent_address TEXT,
    permanent_pin_code VARCHAR(20),
    current_address TEXT,
    current_pin_code VARCHAR(20),
    city VARCHAR(100),
    state VARCHAR(100),
    country VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 4. family_details
CREATE TABLE IF NOT EXISTS family_details (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    father_name VARCHAR(255),
    father_mobile VARCHAR(20),
    father_income DECIMAL(12,2),
    father_occupation VARCHAR(255),
    mother_name VARCHAR(255),
    mother_mobile VARCHAR(20),
    mother_occupation VARCHAR(255),
    brothers INT DEFAULT 0,
    brothers_married INT DEFAULT 0,
    brothers_unmarried INT DEFAULT 0,
    sisters INT DEFAULT 0,
    sisters_married INT DEFAULT 0,
    sisters_unmarried INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 5. memberships
CREATE TABLE IF NOT EXISTS memberships (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    plan_name VARCHAR(100) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    duration_days INT NOT NULL,
    contact_limit INT DEFAULT 0,
    featured_profile BOOLEAN DEFAULT FALSE,
    priority_support BOOLEAN DEFAULT FALSE,
    status BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL
);

-- Default Plans
INSERT INTO memberships (plan_name, price, duration_days, contact_limit, featured_profile, priority_support)
VALUES
('Basic', 999, 90, 10, 0, 0),
('Premium', 2499, 180, 50, 1, 1),
('Elite', 4999, 365, 999, 1, 1);

-- 6. user_memberships
CREATE TABLE IF NOT EXISTS user_memberships (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    membership_id BIGINT UNSIGNED NOT NULL,
    start_date DATE,
    end_date DATE,
    status ENUM('active', 'expired', 'cancelled') DEFAULT 'active',
    can_view_contacts BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (membership_id) REFERENCES memberships(id),
    UNIQUE INDEX idx_user_membership_active (user_id, membership_id, status)
);

-- 7. payments
CREATE TABLE IF NOT EXISTS payments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    membership_id BIGINT UNSIGNED,
    amount DECIMAL(10,2),
    transaction_id VARCHAR(255),
    payment_method VARCHAR(100),
    payment_remarks TEXT,
    payment_screenshot VARCHAR(255),
    status ENUM('pending', 'verified', 'rejected') DEFAULT 'pending',
    verified_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (verified_by) REFERENCES admins(id) ON DELETE SET NULL,
    INDEX idx_payments_status (status),
    INDEX idx_payments_transaction (transaction_id)
);

-- 8. contact_messages
CREATE TABLE IF NOT EXISTS contact_messages (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255),
    email VARCHAR(255),
    mobile VARCHAR(20),
    subject VARCHAR(255),
    message TEXT,
    status ENUM('unread', 'read', 'replied') DEFAULT 'unread',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 9. success_stories
CREATE TABLE IF NOT EXISTS success_stories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    couple_name VARCHAR(255),
    engagement_date DATE,
    marriage_date DATE,
    story LONGTEXT,
    photo VARCHAR(255),
    status ENUM('pending', 'approved') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- 10. community_events
CREATE TABLE IF NOT EXISTS community_events (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255),
    description LONGTEXT,
    event_date DATE,
    location VARCHAR(255),
    banner VARCHAR(255),
    status BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 11. advertisements
CREATE TABLE IF NOT EXISTS advertisements (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255),
    image VARCHAR(255),
    link VARCHAR(255),
    position ENUM('home_top', 'home_bottom', 'sidebar'),
    status BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 12. site_settings
CREATE TABLE IF NOT EXISTS site_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value LONGTEXT NULL
);

-- 13. activity_logs
CREATE TABLE IF NOT EXISTS activity_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_type ENUM('admin', 'user'),
    user_id BIGINT UNSIGNED,
    action VARCHAR(255),
    details TEXT,
    ip_address VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 14. import_history
CREATE TABLE IF NOT EXISTS import_history (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    source_type VARCHAR(50),
    imported_records INT,
    imported_by BIGINT UNSIGNED NULL,
    import_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (imported_by) REFERENCES admins(id) ON DELETE SET NULL
);

-- 15. members (Import Table)
CREATE TABLE IF NOT EXISTS members (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    -- Personal
    full_name VARCHAR(255) NOT NULL,
    gender ENUM('Male', 'Female', 'Other'),
    birth_date DATE,
    birth_time TIME,
    birth_place VARCHAR(255),
    native VARCHAR(100),
    gotra VARCHAR(100),
    mama_gotra VARCHAR(100),
    manglik ENUM('yes', 'no'),
    height_cm SMALLINT UNSIGNED,
    weight_kg DECIMAL(5,2),
    
    -- Contact
    country_code VARCHAR(5),
    mobile_number VARCHAR(20),
    email VARCHAR(255) UNIQUE,
    permanent_address TEXT,
    permanent_pin_code CHAR(6),
    current_address TEXT,
    
    -- Professional & Education
    higher_education TEXT,
    occupation VARCHAR(100),
    company_name VARCHAR(255),
    designation VARCHAR(100),
    monthly_income DECIMAL(12,2),
    
    -- Family
    father_name VARCHAR(255),
    father_mobile VARCHAR(20),
    father_occupation VARCHAR(100),
    father_monthly_income DECIMAL(12,2),
    mother_name VARCHAR(255),
    mother_mobile VARCHAR(20),
    mother_occupation VARCHAR(100),
    brothers_total TINYINT UNSIGNED,
    brothers_married TINYINT UNSIGNED,
    brothers_unmarried TINYINT UNSIGNED,
    sisters_total TINYINT UNSIGNED,
    sisters_married TINYINT UNSIGNED,
    sisters_unmarried TINYINT UNSIGNED,
    
    -- Preferences & Other
    partner_preferences TEXT,
    languages_known TEXT,
    hobbies TEXT,
    widow_divorce ENUM('widow', 'divorcee', 'none'),
    handicapped_physical_deficiency TEXT,
    
    -- Media
    profile_photo_path VARCHAR(500),
    
    -- Metadata
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 16. import_images
CREATE TABLE IF NOT EXISTS import_images (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    image_type VARCHAR(50) NOT NULL,
    member_name_key VARCHAR(255),
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size_bytes BIGINT
);

-- 17. password_resets
CREATE TABLE IF NOT EXISTS `password_resets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 18. account_requests
CREATE TABLE IF NOT EXISTS account_requests (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    request_type ENUM('deactivation', 'deletion') DEFAULT 'deletion',
    reason TEXT,
    status ENUM('pending', 'processed', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 19. registration_fields
CREATE TABLE IF NOT EXISTS registration_fields (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    field_group VARCHAR(100) DEFAULT 'Basic Details',
    field_key VARCHAR(100) NOT NULL UNIQUE,
    field_label VARCHAR(255) NOT NULL,
    field_type VARCHAR(50) DEFAULT 'text', 
    field_options TEXT,
    is_custom BOOLEAN DEFAULT FALSE,
    is_visible BOOLEAN DEFAULT TRUE,
    is_required BOOLEAN DEFAULT FALSE,
    is_core BOOLEAN DEFAULT FALSE,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 20. user_custom_data
CREATE TABLE IF NOT EXISTS user_custom_data (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    field_id BIGINT UNSIGNED NOT NULL,
    field_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (field_id) REFERENCES registration_fields(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_field (user_id, field_id)
);

-- Default seeded values for registration_fields
INSERT IGNORE INTO registration_fields (field_group, field_key, field_label, field_type, field_options, is_custom, is_visible, is_required, is_core, sort_order) VALUES
('Basic Details', 'full_name', 'Full Name', 'text', '', 0, 1, 1, 1, 1),
('Basic Details', 'email', 'Email Address', 'email', '', 0, 1, 1, 1, 2),
('Basic Details', 'mobile', 'Mobile Number', 'tel', '', 0, 1, 1, 1, 3),
('Basic Details', 'password', 'Password', 'password', '', 0, 1, 1, 1, 4),
('Basic Details', 'profile_created_for', 'Profile Created For', 'dropdown', 'Self,Son,Daughter,Brother,Sister,Relative,Friend', 0, 1, 1, 0, 5),
('Basic Details', 'gender', 'Gender', 'dropdown', 'Male,Female', 0, 1, 1, 0, 6),
('Basic Details', 'birth_date', 'Date of Birth', 'date', '', 0, 1, 1, 0, 7),
('Religious Details', 'are_you_digambar_jain', 'Are you Digambar Jain?', 'dropdown', 'Yes,No', 0, 1, 0, 0, 8),
('Religious Details', 'gotra', 'Gotra', 'text', '', 0, 1, 0, 0, 9),
('Religious Details', 'manglik', 'Manglik Status', 'dropdown', 'Yes,No', 0, 1, 0, 0, 10),
('Basic Details', 'birth_time', 'Time of Birth', 'time', '', 0, 1, 0, 0, 11),
('Basic Details', 'birth_place', 'Place of Birth', 'text', '', 0, 1, 0, 0, 12),
('Basic Details', 'native_place', 'Native Place', 'text', '', 0, 1, 0, 0, 13),
('Religious Details', 'mama_gotra', 'Mama Gotra', 'text', '', 0, 1, 0, 0, 14),
('Physical Attributes', 'height', 'Height', 'text', '', 0, 1, 0, 0, 15),
('Physical Attributes', 'weight', 'Weight (kg)', 'number', '', 0, 1, 0, 0, 16),
('Physical Attributes', 'handicapped', 'Handicapped/Physical Deficiency', 'dropdown', 'Yes,No', 0, 1, 0, 0, 17),
('Basic Details', 'marital_status', 'Marital Status', 'dropdown', 'Never Married,Widow,Widower,Divorce', 0, 1, 0, 0, 18),
('Education & Profession', 'higher_education', 'Higher Education', 'textarea', '', 0, 1, 0, 0, 19),
('Education & Profession', 'occupation', 'Occupation', 'text', '', 0, 1, 0, 0, 20),
('Education & Profession', 'company_name', 'Company Name', 'text', '', 0, 1, 0, 0, 21),
('Education & Profession', 'designation', 'Designation', 'text', '', 0, 1, 0, 0, 22),
('Education & Profession', 'monthly_income', 'Monthly Income', 'number', '', 0, 1, 0, 0, 23),
('Lifestyle', 'languages', 'Languages Known', 'textarea', '', 0, 1, 0, 0, 24),
('Lifestyle', 'hobbies', 'Hobbies', 'textarea', '', 0, 1, 0, 0, 25),
('Lifestyle', 'partner_preference', 'Partner Preference', 'textarea', '', 0, 1, 0, 0, 26),
('Media & Payment', 'profile_photo', 'Profile Photo', 'file', '', 0, 1, 0, 0, 27),
('Media & Payment', 'family_photo', 'Family Photo', 'file', '', 0, 1, 0, 0, 28),
('Media & Payment', 'payment_screenshot', 'Payment Screenshot', 'file', '', 0, 1, 0, 0, 29),
('Media & Payment', 'profile_photo_drive_url', 'Profile Photo Drive URL', 'url', '', 0, 1, 0, 0, 30),
('Media & Payment', 'payment_proof_drive_url', 'Payment Proof Drive URL', 'url', '', 0, 1, 0, 0, 31);
