<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('pattern_auth_enabled')) {
    function pattern_auth_enabled($user_id)
    {
        $CI =& get_instance();
        $CI->load->model('pattern_model');
        return $CI->pattern_model->has_pattern($user_id);
    }
}

if (!function_exists('pattern_login_url')) {
    function pattern_login_url()
    {
        $CI =& get_instance();
        $CI->load->config('pattern_lock', TRUE);
        $custom_url = $CI->config->item('pattern_custom_login_url', 'pattern_lock');
        return !empty($custom_url) ? site_url($custom_url) : site_url('pattern_auth/login');
    }
}

if (!function_exists('pattern_setup_url')) {
    function pattern_setup_url()
    {
        return site_url('pattern_auth/setup');
    }
}

if (!function_exists('format_lockout_time')) {
    function format_lockout_time($seconds)
    {
        if ($seconds < 60) return $seconds . ' сек.';
        if ($seconds < 3600) return ceil($seconds / 60) . ' мин.';
        return ceil($seconds / 3600) . ' ч.';
    }
}

if (!function_exists('pattern_csrf_token')) {
    function pattern_csrf_token()
    {
        if (!isset($_SESSION['pattern_csrf_token'])) {
            $_SESSION['pattern_csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['pattern_csrf_token'];
    }
}

if (!function_exists('verify_pattern_csrf')) {
    function verify_pattern_csrf($token)
    {
        return isset($_SESSION['pattern_csrf_token']) && 
               hash_equals($_SESSION['pattern_csrf_token'], $token);
    }
}