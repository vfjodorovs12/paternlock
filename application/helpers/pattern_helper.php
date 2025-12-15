<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Pattern Helper
 * 
 * Helper functions for pattern lock system
 * 
 * @package    Pattern_Lock
 * @subpackage Helpers
 * @category   Helpers
 * @author     Pattern Lock Team
 * @version    1.0
 */

if (!function_exists('pattern_strength_label')) {
    /**
     * Get pattern strength label
     * 
     * @param int $strength Strength score (0-5)
     * @return string Strength label
     */
    function pattern_strength_label($strength)
    {
        $labels = array(
            0 => 'Very Weak',
            1 => 'Weak',
            2 => 'Fair',
            3 => 'Good',
            4 => 'Strong',
            5 => 'Very Strong'
        );
        
        return isset($labels[$strength]) ? $labels[$strength] : 'Unknown';
    }
}

if (!function_exists('pattern_strength_color')) {
    /**
     * Get pattern strength color class
     * 
     * @param int $strength Strength score (0-5)
     * @return string Color class name
     */
    function pattern_strength_color($strength)
    {
        if ($strength <= 1) return 'danger';
        if ($strength <= 2) return 'warning';
        if ($strength <= 3) return 'info';
        return 'success';
    }
}

if (!function_exists('format_backup_code')) {
    /**
     * Format backup code with hyphens
     * 
     * @param string $code Backup code
     * @return string Formatted code
     */
    function format_backup_code($code)
    {
        $code = strtoupper(str_replace('-', '', $code));
        return implode('-', str_split($code, 4));
    }
}

if (!function_exists('is_pattern_locked')) {
    /**
     * Check if IP is currently locked out
     * 
     * @param string $ip_address IP address (defaults to current)
     * @return bool TRUE if locked
     */
    function is_pattern_locked($ip_address = NULL)
    {
        $CI =& get_instance();
        $CI->load->model('Pattern_model');
        
        if ($ip_address === NULL) {
            $ip_address = $CI->input->ip_address();
        }
        
        return $CI->Pattern_model->is_ip_locked($ip_address);
    }
}

if (!function_exists('get_lockout_time_remaining')) {
    /**
     * Get remaining lockout time in seconds
     * 
     * @param string $ip_address IP address (defaults to current)
     * @return int Seconds remaining (0 if not locked)
     */
    function get_lockout_time_remaining($ip_address = NULL)
    {
        $CI =& get_instance();
        $CI->load->model('Pattern_model');
        
        if ($ip_address === NULL) {
            $ip_address = $CI->input->ip_address();
        }
        
        $lockout = $CI->Pattern_model->get_lockout($ip_address);
        
        if (!$lockout || !$lockout['locked_until']) {
            return 0;
        }
        
        $remaining = strtotime($lockout['locked_until']) - time();
        return max(0, $remaining);
    }
}

if (!function_exists('format_time_remaining')) {
    /**
     * Format time remaining in human-readable format
     * 
     * @param int $seconds Seconds
     * @return string Formatted time
     */
    function format_time_remaining($seconds)
    {
        if ($seconds <= 0) {
            return '0 seconds';
        }
        
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;
        
        $parts = array();
        if ($hours > 0) $parts[] = $hours . ' hour' . ($hours > 1 ? 's' : '');
        if ($minutes > 0) $parts[] = $minutes . ' minute' . ($minutes > 1 ? 's' : '');
        if ($secs > 0 || empty($parts)) $parts[] = $secs . ' second' . ($secs != 1 ? 's' : '');
        
        return implode(', ', $parts);
    }
}

if (!function_exists('is_pattern_user')) {
    /**
     * Check if user has pattern authentication enabled
     * 
     * @param int $user_id User ID
     * @return bool TRUE if user has pattern
     */
    function is_pattern_user($user_id)
    {
        $CI =& get_instance();
        $CI->load->model('Pattern_model');
        
        $pattern = $CI->Pattern_model->get_user_pattern($user_id);
        return !empty($pattern) && $pattern['is_active'];
    }
}

if (!function_exists('pattern_status_badge')) {
    /**
     * Get HTML badge for log status
     * 
     * @param string $status Status (success, failed, blocked, recovered)
     * @return string HTML badge
     */
    function pattern_status_badge($status)
    {
        $badges = array(
            'success' => '<span class="badge badge-success">Success</span>',
            'failed' => '<span class="badge badge-danger">Failed</span>',
            'blocked' => '<span class="badge badge-warning">Blocked</span>',
            'recovered' => '<span class="badge badge-info">Recovered</span>'
        );
        
        return isset($badges[$status]) ? $badges[$status] : '<span class="badge badge-secondary">' . ucfirst($status) . '</span>';
    }
}

if (!function_exists('check_system_lockout')) {
    /**
     * Check if system is in total lockout mode
     * 
     * @return bool TRUE if system is locked
     */
    function check_system_lockout()
    {
        $CI =& get_instance();
        $CI->load->model('Pattern_model');
        
        $setting = $CI->Pattern_model->get_setting('system_lockout');
        return $setting && $setting['setting_value'] == '1';
    }
}

if (!function_exists('get_pattern_setting')) {
    /**
     * Get pattern lock setting value
     * 
     * @param string $key Setting key
     * @param mixed $default Default value
     * @return mixed Setting value
     */
    function get_pattern_setting($key, $default = NULL)
    {
        $CI =& get_instance();
        $CI->load->model('Pattern_model');
        
        $setting = $CI->Pattern_model->get_setting($key);
        
        if (!$setting) {
            return $default;
        }
        
        $value = $setting['setting_value'];
        
        // Type casting based on setting_type
        switch ($setting['setting_type']) {
            case 'integer':
                return (int)$value;
            case 'boolean':
                return (bool)$value || $value === '1' || $value === 'true';
            case 'json':
                return json_decode($value, TRUE);
            default:
                return $value;
        }
    }
}

if (!function_exists('set_pattern_setting')) {
    /**
     * Set pattern lock setting value
     * 
     * @param string $key Setting key
     * @param mixed $value Setting value
     * @return bool Success
     */
    function set_pattern_setting($key, $value)
    {
        $CI =& get_instance();
        $CI->load->model('Pattern_model');
        
        return $CI->Pattern_model->update_setting($key, $value);
    }
}
