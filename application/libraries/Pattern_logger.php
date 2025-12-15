<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Pattern Logger Library
 * 
 * Handles logging of authentication attempts and security events
 * 
 * @package    Pattern_Lock
 * @subpackage Libraries
 * @category   Logging
 * @author     Pattern Lock Team
 * @version    1.0
 */
class Pattern_logger {

    protected $CI;
    protected $config = array();

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->config('pattern_lock');
        $this->CI->load->model('Pattern_model');
        
        $this->config['enable_logging'] = $this->CI->config->item('pattern_lock_enable_logging');
        $this->config['log_geo_location'] = $this->CI->config->item('pattern_lock_log_geo_location');
    }

    /**
     * Log authentication attempt
     * 
     * @param array $data Log data
     * @return int Insert ID or FALSE
     */
    public function log_attempt($data)
    {
        if (!$this->config['enable_logging']) {
            return FALSE;
        }

        // Prepare log entry
        $log_data = array(
            'user_id' => isset($data['user_id']) ? $data['user_id'] : NULL,
            'username' => isset($data['username']) ? $data['username'] : NULL,
            'ip_address' => $this->get_ip_address(),
            'user_agent' => $this->get_user_agent(),
            'status' => $data['status'], // success, failed, blocked, recovered
            'attempt_type' => isset($data['attempt_type']) ? $data['attempt_type'] : 'pattern',
            'failure_reason' => isset($data['failure_reason']) ? $data['failure_reason'] : NULL,
            'device_fingerprint' => $this->get_device_fingerprint(),
            'created_at' => date('Y-m-d H:i:s')
        );

        // Add geo-location if enabled
        if ($this->config['log_geo_location']) {
            $geo = $this->get_geo_location($log_data['ip_address']);
            $log_data['country'] = isset($geo['country']) ? $geo['country'] : NULL;
            $log_data['city'] = isset($geo['city']) ? $geo['city'] : NULL;
        }

        return $this->CI->Pattern_model->insert_log($log_data);
    }

    /**
     * Get client IP address
     * 
     * @return string IP address
     */
    protected function get_ip_address()
    {
        $ip_keys = array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 
                        'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR');
        
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === TRUE) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, 
                        FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== FALSE) {
                        return $ip;
                    }
                }
            }
        }
        
        return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';
    }

    /**
     * Get user agent string
     * 
     * @return string User agent
     */
    protected function get_user_agent()
    {
        return isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'Unknown';
    }

    /**
     * Generate device fingerprint
     * 
     * @return string Device fingerprint hash
     */
    protected function get_device_fingerprint()
    {
        $data = array(
            'user_agent' => $this->get_user_agent(),
            'accept_language' => isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : '',
            'accept_encoding' => isset($_SERVER['HTTP_ACCEPT_ENCODING']) ? $_SERVER['HTTP_ACCEPT_ENCODING'] : '',
        );
        
        return hash('sha256', implode('|', $data));
    }

    /**
     * Get geo-location data for IP address
     * 
     * @param string $ip_address IP address
     * @return array Geo-location data
     */
    protected function get_geo_location($ip_address)
    {
        // Skip for local IPs
        if (filter_var($ip_address, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === FALSE) {
            return array('country' => 'Local', 'city' => 'Local');
        }

        $api_key = $this->CI->config->item('pattern_lock_geo_api_key');
        
        // If no API key, return empty
        if (empty($api_key)) {
            return array('country' => NULL, 'city' => NULL);
        }

        // Use a free geo-location API (you can customize this)
        // Example: ipapi.co, ip-api.com, ipinfo.io, etc.
        try {
            $url = "http://ip-api.com/json/{$ip_address}";
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            $response = curl_exec($ch);
            curl_close($ch);

            if ($response) {
                $data = json_decode($response, TRUE);
                if (isset($data['status']) && $data['status'] === 'success') {
                    return array(
                        'country' => isset($data['country']) ? $data['country'] : NULL,
                        'city' => isset($data['city']) ? $data['city'] : NULL
                    );
                }
            }
        } catch (Exception $e) {
            // Silently fail
        }

        return array('country' => NULL, 'city' => NULL);
    }

    /**
     * Get access logs with filters
     * 
     * @param array $filters Filter parameters
     * @param int $limit Limit
     * @param int $offset Offset
     * @return array Logs
     */
    public function get_logs($filters = array(), $limit = 50, $offset = 0)
    {
        return $this->CI->Pattern_model->get_logs($filters, $limit, $offset);
    }

    /**
     * Get log statistics
     * 
     * @param int $user_id User ID (optional)
     * @return array Statistics
     */
    public function get_statistics($user_id = NULL)
    {
        return $this->CI->Pattern_model->get_log_statistics($user_id);
    }

    /**
     * Export logs to CSV
     * 
     * @param array $filters Filter parameters
     * @return string CSV content
     */
    public function export_to_csv($filters = array())
    {
        $logs = $this->CI->Pattern_model->get_logs($filters, 0, 0); // Get all matching logs
        
        $csv = "ID,User ID,Username,IP Address,Status,Attempt Type,Failure Reason,Country,City,User Agent,Created At\n";
        
        foreach ($logs as $log) {
            $csv .= sprintf('"%s","%s","%s","%s","%s","%s","%s","%s","%s","%s","%s"' . "\n",
                $log['id'],
                $log['user_id'],
                $log['username'],
                $log['ip_address'],
                $log['status'],
                $log['attempt_type'],
                $log['failure_reason'],
                $log['country'],
                $log['city'],
                str_replace('"', '""', $log['user_agent']),
                $log['created_at']
            );
        }
        
        return $csv;
    }

    /**
     * Clean old logs based on retention policy
     * 
     * @return int Number of deleted records
     */
    public function clean_old_logs()
    {
        $retention_days = $this->CI->config->item('pattern_lock_log_retention_days');
        
        if ($retention_days == 0) {
            return 0; // Keep forever
        }
        
        $cutoff_date = date('Y-m-d H:i:s', strtotime("-{$retention_days} days"));
        return $this->CI->Pattern_model->delete_logs_before($cutoff_date);
    }

    /**
     * Check if device is known for user
     * 
     * @param int $user_id User ID
     * @param string $device_fingerprint Device fingerprint
     * @return bool TRUE if device is known
     */
    public function is_known_device($user_id, $device_fingerprint = NULL)
    {
        if ($device_fingerprint === NULL) {
            $device_fingerprint = $this->get_device_fingerprint();
        }
        
        return $this->CI->Pattern_model->is_known_device($user_id, $device_fingerprint);
    }

    /**
     * Register device for user
     * 
     * @param int $user_id User ID
     * @param bool $trusted Whether device is trusted
     * @return int Insert ID or FALSE
     */
    public function register_device($user_id, $trusted = FALSE)
    {
        $device_data = array(
            'user_id' => $user_id,
            'device_fingerprint' => $this->get_device_fingerprint(),
            'device_name' => $this->parse_device_name(),
            'ip_address' => $this->get_ip_address(),
            'user_agent' => $this->get_user_agent(),
            'is_trusted' => $trusted ? 1 : 0,
            'last_used' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s')
        );
        
        return $this->CI->Pattern_model->register_device($device_data);
    }

    /**
     * Update device last used timestamp
     * 
     * @param int $user_id User ID
     * @param string $device_fingerprint Device fingerprint
     * @return bool Success
     */
    public function update_device_last_used($user_id, $device_fingerprint = NULL)
    {
        if ($device_fingerprint === NULL) {
            $device_fingerprint = $this->get_device_fingerprint();
        }
        
        return $this->CI->Pattern_model->update_device_last_used($user_id, $device_fingerprint);
    }

    /**
     * Parse device name from user agent
     * 
     * @return string Device name
     */
    protected function parse_device_name()
    {
        $user_agent = $this->get_user_agent();
        
        // Simple device detection
        if (preg_match('/Mobile|Android|iPhone|iPad|iPod/', $user_agent)) {
            if (preg_match('/iPhone/', $user_agent)) return 'iPhone';
            if (preg_match('/iPad/', $user_agent)) return 'iPad';
            if (preg_match('/Android/', $user_agent)) return 'Android Device';
            return 'Mobile Device';
        }
        
        if (preg_match('/Windows/', $user_agent)) return 'Windows PC';
        if (preg_match('/Macintosh|Mac OS X/', $user_agent)) return 'Mac';
        if (preg_match('/Linux/', $user_agent)) return 'Linux PC';
        
        return 'Unknown Device';
    }
}
