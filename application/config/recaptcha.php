<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * reCAPTCHA Configuration
 * 
 * Configuration for Google reCAPTCHA integration
 * 
 * @package    Pattern_Lock
 * @subpackage Config
 * @category   Configuration
 * @author     Pattern Lock Team
 * @version    1.0
 */

/*
|--------------------------------------------------------------------------
| reCAPTCHA Version
|--------------------------------------------------------------------------
|
| Choose which version of reCAPTCHA to use: 'v2' or 'v3'
| v2: Shows checkbox or invisible badge
| v3: Returns a score (0.0-1.0) based on user interaction
|
*/
$config['recaptcha_version'] = 'v2';

/*
|--------------------------------------------------------------------------
| reCAPTCHA v2 Settings
|--------------------------------------------------------------------------
|
| Site key and secret for reCAPTCHA v2
| Get your keys at: https://www.google.com/recaptcha/admin
|
*/
$config['recaptcha_site_key'] = ''; // Your reCAPTCHA v2 site key
$config['recaptcha_secret_key'] = ''; // Your reCAPTCHA v2 secret key

/*
|--------------------------------------------------------------------------
| reCAPTCHA v2 Type
|--------------------------------------------------------------------------
|
| Type of v2 reCAPTCHA:
| - 'checkbox': Standard checkbox challenge
| - 'invisible': Invisible reCAPTCHA (auto-triggered)
|
*/
$config['recaptcha_v2_type'] = 'checkbox';

/*
|--------------------------------------------------------------------------
| reCAPTCHA v3 Settings
|--------------------------------------------------------------------------
|
| Site key and secret for reCAPTCHA v3
| Note: v3 keys are different from v2 keys
|
*/
$config['recaptcha_v3_site_key'] = ''; // Your reCAPTCHA v3 site key
$config['recaptcha_v3_secret_key'] = ''; // Your reCAPTCHA v3 secret key

/*
|--------------------------------------------------------------------------
| reCAPTCHA v3 Action Name
|--------------------------------------------------------------------------
|
| Action name for reCAPTCHA v3 verification
| This helps you track different forms/actions in your analytics
|
*/
$config['recaptcha_v3_action'] = 'pattern_login';

/*
|--------------------------------------------------------------------------
| reCAPTCHA v3 Threshold
|--------------------------------------------------------------------------
|
| Minimum score required to pass verification (0.0 - 1.0)
| 0.0 is very likely a bot, 1.0 is very likely a human
| Recommended: 0.5
|
*/
$config['recaptcha_v3_threshold'] = 0.5;

/*
|--------------------------------------------------------------------------
| reCAPTCHA Language
|--------------------------------------------------------------------------
|
| Language code for reCAPTCHA interface
| Leave empty for auto-detect, or use language codes like:
| 'en', 'ru', 'es', 'fr', 'de', etc.
|
*/
$config['recaptcha_language'] = '';

/*
|--------------------------------------------------------------------------
| reCAPTCHA Theme
|--------------------------------------------------------------------------
|
| Theme for reCAPTCHA v2 widget
| Options: 'light' or 'dark'
|
*/
$config['recaptcha_theme'] = 'light';

/*
|--------------------------------------------------------------------------
| reCAPTCHA Size
|--------------------------------------------------------------------------
|
| Size of reCAPTCHA v2 widget
| Options: 'normal' or 'compact'
|
*/
$config['recaptcha_size'] = 'normal';

/*
|--------------------------------------------------------------------------
| Verification API URL
|--------------------------------------------------------------------------
|
| Google's reCAPTCHA verification endpoint
| Usually you don't need to change this
|
*/
$config['recaptcha_verify_url'] = 'https://www.google.com/recaptcha/api/siteverify';

/*
|--------------------------------------------------------------------------
| Enable on Failed Attempts
|--------------------------------------------------------------------------
|
| Number of failed login attempts before showing reCAPTCHA
| Set to 0 to always show reCAPTCHA
| Set to -1 to disable automatic triggering
|
*/
$config['recaptcha_failed_attempts_threshold'] = 3;

/*
|--------------------------------------------------------------------------
| Error Messages
|--------------------------------------------------------------------------
|
| Custom error messages for reCAPTCHA validation
|
*/
$config['recaptcha_error_messages'] = array(
    'missing-input-secret' => 'The secret parameter is missing.',
    'invalid-input-secret' => 'The secret parameter is invalid or malformed.',
    'missing-input-response' => 'Please complete the reCAPTCHA verification.',
    'invalid-input-response' => 'The reCAPTCHA verification failed. Please try again.',
    'bad-request' => 'The request is invalid or malformed.',
    'timeout-or-duplicate' => 'The reCAPTCHA response is no longer valid. Please try again.',
    'unknown-error' => 'An unknown error occurred during reCAPTCHA verification.',
    'score-too-low' => 'reCAPTCHA verification failed. Please try again.'
);

/*
|--------------------------------------------------------------------------
| Connection Settings
|--------------------------------------------------------------------------
|
| Settings for API connection to Google servers
|
*/
$config['recaptcha_timeout'] = 10; // Timeout in seconds for API request
$config['recaptcha_use_ssl'] = TRUE; // Use SSL for API requests
