<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Google reCAPTCHA Configuration
 */

// reCAPTCHA v2 ключи
$config['recaptcha_site_key'] = 'YOUR_RECAPTCHA_V2_SITE_KEY';
$config['recaptcha_secret_key'] = 'YOUR_RECAPTCHA_V2_SECRET_KEY';

// reCAPTCHA v3 ключи
$config['recaptcha_v3_site_key'] = 'YOUR_RECAPTCHA_V3_SITE_KEY';
$config['recaptcha_v3_secret_key'] = 'YOUR_RECAPTCHA_V3_SECRET_KEY';

// Тема виджета: 'light' или 'dark'
$config['recaptcha_theme'] = 'light';

// Размер виджета: 'normal' или 'compact'
$config['recaptcha_size'] = 'normal';

// Язык
$config['recaptcha_lang'] = 'ru';

// Таймаут запроса (секунды)
$config['recaptcha_timeout'] = 10;