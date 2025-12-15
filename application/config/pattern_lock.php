<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Pattern Lock Configuration
 * 
 * Configuration settings for the Pattern Lock authentication system
 * 
 * @package    Pattern_Lock
 * @subpackage Config
 * @category   Configuration
 * @author     Pattern Lock Team
 * @version    1.0
 */

/*
|--------------------------------------------------------------------------
| Grid Settings
|--------------------------------------------------------------------------
|
| Configure the pattern grid dimensions and behavior
|
*/
$config['pattern_lock_grid_size'] = 3; // Default grid size (3x3)
$config['pattern_lock_min_grid_size'] = 3; // Minimum grid size
$config['pattern_lock_max_grid_size'] = 20; // Maximum grid size
$config['pattern_lock_min_points'] = 4; // Minimum points required in pattern
$config['pattern_lock_max_points'] = 9; // Maximum points (for 3x3 grid)

/*
|--------------------------------------------------------------------------
| Security Settings
|--------------------------------------------------------------------------
|
| Security-related configuration for pattern authentication
|
*/
$config['pattern_lock_hash_algorithm'] = 'sha256'; // Hash algorithm for patterns
$config['pattern_lock_backup_code_length'] = 16; // Length of backup recovery code
$config['pattern_lock_encryption_key'] = ''; // Leave empty to use CI encryption key

/*
|--------------------------------------------------------------------------
| Brute Force Protection
|--------------------------------------------------------------------------
|
| Settings to prevent brute force attacks
|
*/
$config['pattern_lock_max_failed_attempts'] = 5; // Max failed attempts before lockout
$config['pattern_lock_lockout_duration'] = 900; // Lockout duration in seconds (15 min)
$config['pattern_lock_permanent_lockout_threshold'] = 20; // Failed attempts for permanent lockout

/*
|--------------------------------------------------------------------------
| reCAPTCHA Integration
|--------------------------------------------------------------------------
|
| When to show reCAPTCHA and which version to use
|
*/
$config['pattern_lock_recaptcha_enabled'] = TRUE; // Enable reCAPTCHA
$config['pattern_lock_recaptcha_threshold'] = 3; // Show after N failed attempts
$config['pattern_lock_recaptcha_version'] = 'v2'; // 'v2' or 'v3'

/*
|--------------------------------------------------------------------------
| Logging Settings
|--------------------------------------------------------------------------
|
| Configure access logging behavior
|
*/
$config['pattern_lock_enable_logging'] = TRUE; // Enable access logging
$config['pattern_lock_log_retention_days'] = 90; // Days to keep logs (0 = forever)
$config['pattern_lock_log_geo_location'] = FALSE; // Enable geo-location logging (requires API)
$config['pattern_lock_geo_api_key'] = ''; // API key for geo-location service

/*
|--------------------------------------------------------------------------
| Device Tracking
|--------------------------------------------------------------------------
|
| Track known devices for security notifications
|
*/
$config['pattern_lock_track_devices'] = TRUE; // Enable device tracking
$config['pattern_lock_device_trust_duration'] = 2592000; // Trust device for 30 days

/*
|--------------------------------------------------------------------------
| Email Notifications
|--------------------------------------------------------------------------
|
| Configure email notifications for security events
|
*/
$config['pattern_lock_email_on_lockout'] = TRUE; // Email on account lockout
$config['pattern_lock_email_on_new_device'] = TRUE; // Email on new device login
$config['pattern_lock_email_from'] = 'noreply@yourdomain.com'; // From email address
$config['pattern_lock_email_from_name'] = 'Pattern Lock System'; // From name

/*
|--------------------------------------------------------------------------
| UI Settings
|--------------------------------------------------------------------------
|
| User interface customization
|
*/
$config['pattern_lock_point_radius'] = 20; // Circle radius for pattern points
$config['pattern_lock_line_width'] = 3; // Width of connecting lines
$config['pattern_lock_color_normal'] = '#3498db'; // Normal color
$config['pattern_lock_color_success'] = '#27ae60'; // Success color
$config['pattern_lock_color_error'] = '#e74c3c'; // Error color
$config['pattern_lock_animation_duration'] = 200; // Animation duration in ms

/*
|--------------------------------------------------------------------------
| Pattern Strength Rules
|--------------------------------------------------------------------------
|
| Rules for evaluating pattern strength (0-5 scale)
|
*/
$config['pattern_lock_strength_rules'] = array(
    'min_points' => 4,           // Minimum points required
    'directional_changes' => 2,  // Minimum direction changes for medium strength
    'no_adjacent_only' => TRUE,  // Penalize patterns with only adjacent points
    'no_straight_lines' => TRUE, // Penalize simple straight line patterns
);

/*
|--------------------------------------------------------------------------
| Session and Redirect Settings
|--------------------------------------------------------------------------
|
| Configure session behavior and redirects
|
*/
$config['pattern_lock_session_key'] = 'pattern_lock_user'; // Session key for user data
$config['pattern_lock_login_url'] = 'pattern_auth/login'; // Login page URL
$config['pattern_lock_redirect_after_login'] = 'dashboard'; // Redirect after successful login
$config['pattern_lock_redirect_after_logout'] = 'pattern_auth/login'; // Redirect after logout

/*
|--------------------------------------------------------------------------
| Fallback Authentication
|--------------------------------------------------------------------------
|
| Allow fallback to traditional password authentication
|
*/
$config['pattern_lock_allow_password_fallback'] = TRUE; // Allow password login as fallback
$config['pattern_lock_password_login_url'] = 'auth/login'; // Traditional login URL

/*
|--------------------------------------------------------------------------
| System Lockout
|--------------------------------------------------------------------------
|
| Emergency system-wide lockout settings
|
*/
$config['pattern_lock_system_lockout'] = FALSE; // Emergency system lockout (admin only)
$config['pattern_lock_system_lockout_message'] = 'System is temporarily locked. Please contact administrator.';

/*
|--------------------------------------------------------------------------
| Debug and Development
|--------------------------------------------------------------------------
|
| Development and debugging options
|
*/
$config['pattern_lock_debug_mode'] = FALSE; // Enable debug logging
$config['pattern_lock_show_pattern_in_log'] = FALSE; // Show pattern in logs (INSECURE - dev only)
