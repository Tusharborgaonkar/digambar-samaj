# Digambar Samaj Matrimony - User Registration Flow

This document outlines the step-by-step registration and approval process for users on the customized matrimony website. The flow involves alternating actions between the user and the administrator to ensure all profiles are verified and authentic.

## 1. Initial Account Request
* **Page:** `pre-register.php`
* **Action:** A new user visits the pre-registration page and submits their basic details (Full Name, Mobile Number, Email, and Password).
* **System State:** The user's account is created in the database with the status set to `account_pending`.
* **Result:** The user is informed that their request has been submitted to the admin for approval. If they try to log in, they will be redirected to a waiting page (`waiting-approval.php`).

## 2. Admin Account Acceptance
* **Page:** `admin/account-approvals.php`
* **Action:** The administrator logs into the admin panel and reviews the list of pending account requests. The admin accepts the user's initial request.
* **System State:** The user's status is updated from `account_pending` to `account_approved`.

## 3. Comprehensive Profile Submission
* **Page:** `login.php` & `registration.php`
* **Action:** The user logs in with their credentials. Because their status is `account_approved`, the system automatically redirects them to the detailed registration form (`registration.php`).
* **Process:** The user fills out all required details (Personal details, Family details, Mandir verification, Reference persons, and uploads photos).
* **System State:** Upon submission, the user's profile is saved, and their status changes to `pending`.
* **Result:** The user is redirected to `waiting-approval.php` and must wait for the admin to verify their complete profile.

## 4. Admin Profile Approval
* **Page:** `admin/members-approval.php`
* **Action:** The administrator reviews the complete profile details, photos, and references provided by the user. If everything is authentic, the admin approves the profile.
* **System State:** The user's status is updated from `pending` to `approved` (or `active`).

## 5. Full Access Granted
* **Page:** `index.php`, `profiles.php`, `my-profile.php`, etc.
* **Action:** The user logs in. Since their profile is now fully approved, they are granted access to the main matrimony website.
* **Result:** The user can now view other profiles, use quick search, update their own profile, and utilize all platform features.

---

**Summary of Status Changes:**
1. `account_pending` -> (Admin accepts) -> `account_approved`
2. `account_approved` -> (User fills details) -> `pending`
3. `pending` -> (Admin approves) -> `approved`
