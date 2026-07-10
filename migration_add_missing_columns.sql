-- ============================================================
-- Migration: Add missing columns to users table
-- Run this on your production database via phpMyAdmin or MySQL CLI
-- ============================================================

ALTER TABLE `users`
    -- Cast / Religion
    ADD COLUMN IF NOT EXISTS `cast`                  VARCHAR(100)   NULL AFTER `are_you_digambar_jain`,
    ADD COLUMN IF NOT EXISTS `subcast`               VARCHAR(100)   NULL AFTER `cast`,
    ADD COLUMN IF NOT EXISTS `custom_subcast`        VARCHAR(100)   NULL AFTER `subcast`,

    -- Address
    ADD COLUMN IF NOT EXISTS `permanent_address`     TEXT           NULL AFTER `custom_subcast`,
    ADD COLUMN IF NOT EXISTS `pin_code`              VARCHAR(10)    NULL AFTER `permanent_address`,
    ADD COLUMN IF NOT EXISTS `current_address`       TEXT           NULL AFTER `pin_code`,

    -- Family
    ADD COLUMN IF NOT EXISTS `father_name`           VARCHAR(255)   NULL AFTER `current_address`,
    ADD COLUMN IF NOT EXISTS `father_mobile`         VARCHAR(20)    NULL AFTER `father_name`,
    ADD COLUMN IF NOT EXISTS `father_income`         DECIMAL(12,2)  NULL AFTER `father_mobile`,
    ADD COLUMN IF NOT EXISTS `father_occupation`     VARCHAR(100)   NULL AFTER `father_income`,
    ADD COLUMN IF NOT EXISTS `mother_name`           VARCHAR(255)   NULL AFTER `father_occupation`,
    ADD COLUMN IF NOT EXISTS `mother_mobile`         VARCHAR(20)    NULL AFTER `mother_name`,
    ADD COLUMN IF NOT EXISTS `mother_occupation`     VARCHAR(100)   NULL AFTER `mother_mobile`,
    ADD COLUMN IF NOT EXISTS `mother_occupation_details` VARCHAR(255) NULL AFTER `mother_occupation`,
    ADD COLUMN IF NOT EXISTS `brothers`              INT            DEFAULT 0 AFTER `mother_occupation_details`,
    ADD COLUMN IF NOT EXISTS `brothers_married`      INT            DEFAULT 0 AFTER `brothers`,
    ADD COLUMN IF NOT EXISTS `brothers_unmarried`    INT            DEFAULT 0 AFTER `brothers_married`,
    ADD COLUMN IF NOT EXISTS `sisters`               INT            DEFAULT 0 AFTER `brothers_unmarried`,
    ADD COLUMN IF NOT EXISTS `sisters_married`       INT            DEFAULT 0 AFTER `sisters`,
    ADD COLUMN IF NOT EXISTS `sisters_unmarried`     INT            DEFAULT 0 AFTER `sisters_married`,

    -- Mandir / Community Verification
    ADD COLUMN IF NOT EXISTS `mandir`                VARCHAR(255)   NULL AFTER `sisters_unmarried`,
    ADD COLUMN IF NOT EXISTS `custom_mandir`         VARCHAR(255)   NULL AFTER `mandir`,
    ADD COLUMN IF NOT EXISTS `mandir_name`           VARCHAR(255)   NULL AFTER `custom_mandir`,
    ADD COLUMN IF NOT EXISTS `mandir_address`        TEXT           NULL AFTER `mandir_name`,
    ADD COLUMN IF NOT EXISTS `mandir_pincode`        VARCHAR(10)    NULL AFTER `mandir_address`,

    -- References
    ADD COLUMN IF NOT EXISTS `ref1_name`             VARCHAR(255)   NULL AFTER `mandir_pincode`,
    ADD COLUMN IF NOT EXISTS `ref1_mobile`           VARCHAR(20)    NULL AFTER `ref1_name`,
    ADD COLUMN IF NOT EXISTS `ref1_relation`         VARCHAR(100)   NULL AFTER `ref1_mobile`,
    ADD COLUMN IF NOT EXISTS `ref2_name`             VARCHAR(255)   NULL AFTER `ref1_relation`,
    ADD COLUMN IF NOT EXISTS `ref2_mobile`           VARCHAR(20)    NULL AFTER `ref2_name`,
    ADD COLUMN IF NOT EXISTS `ref2_relation`         VARCHAR(100)   NULL AFTER `ref2_mobile`,

    -- Form Meta
    ADD COLUMN IF NOT EXISTS `filled_by`             VARCHAR(50)    NULL AFTER `ref2_relation`,
    ADD COLUMN IF NOT EXISTS `id_proof_type`         VARCHAR(100)   NULL AFTER `filled_by`,
    ADD COLUMN IF NOT EXISTS `id_proof_path`         VARCHAR(500)   NULL AFTER `id_proof_type`,

    -- Birth time fix (schema has TIME, PHP sends "08:30 AM" string)
    MODIFY COLUMN IF EXISTS `birth_time`             VARCHAR(20)    NULL;
