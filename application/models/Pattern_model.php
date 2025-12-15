<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Pattern Model
 * 
 * Database model for pattern lock system
 * 
 * @package    Pattern_Lock
 * @subpackage Models
 * @category   Database
 * @author     Pattern Lock Team
 * @version    1.0
 */
class Pattern_model extends CI_Model {

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    // ========================================================================
    // USER PATTERNS
    // ========================================================================

    /**
     * Get user pattern by user ID
     * 
     * @param int $user_id User ID
     * @return array|null Pattern data
     */
    public function get_user_pattern($user_id)
    {
        return $this->db->where('user_id', $user_id)
                        ->get('user_patterns')
                        ->row_array();
    }

    /**
     * Get user pattern by username
     * 
     * @param string $username Username
     * @return array|null Pattern data
     */
    public function get_user_pattern_by_username($username)
    {
        return $this->db->where('username', $username)
                        ->get('user_patterns')
                        ->row_array();
    }

    /**
     * Create or update user pattern
     * 
     * @param array $data Pattern data
     * @return int Insert/Update ID or FALSE
     */
    public function save_user_pattern($data)
    {
        // Check if pattern exists for this user
        $existing = $this->get_user_pattern($data['user_id']);

        if ($existing) {
            // Update existing pattern
            $data['updated_at'] = date('Y-m-d H:i:s');
            $this->db->where('user_id', $data['user_id'])
                     ->update('user_patterns', $data);
            return $data['user_id'];
        } else {
            // Insert new pattern
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['updated_at'] = date('Y-m-d H:i:s');
            $this->db->insert('user_patterns', $data);
            return $this->db->insert_id();
        }
    }

    /**
     * Delete user pattern
     * 
     * @param int $user_id User ID
     * @return bool Success
     */
    public function delete_user_pattern($user_id)
    {
        return $this->db->where('user_id', $user_id)
                        ->delete('user_patterns');
    }

    /**
     * Activate/deactivate user pattern
     * 
     * @param int $user_id User ID
     * @param bool $active Active status
     * @return bool Success
     */
    public function set_pattern_active($user_id, $active = TRUE)
    {
        return $this->db->where('user_id', $user_id)
                        ->update('user_patterns', array(
                            'is_active' => $active ? 1 : 0,
                            'updated_at' => date('Y-m-d H:i:s')
                        ));
    }

    // ========================================================================
    // ACCESS LOGS
    // ========================================================================

    /**
     * Insert access log
     * 
     * @param array $data Log data
     * @return int Insert ID
     */
    public function insert_log($data)
    {
        $this->db->insert('pattern_access_logs', $data);
        return $this->db->insert_id();
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
        $this->db->select('*');
        $this->db->from('pattern_access_logs');

        // Apply filters
        if (!empty($filters['user_id'])) {
            $this->db->where('user_id', $filters['user_id']);
        }
        if (!empty($filters['username'])) {
            $this->db->like('username', $filters['username']);
        }
        if (!empty($filters['ip_address'])) {
            $this->db->where('ip_address', $filters['ip_address']);
        }
        if (!empty($filters['status'])) {
            $this->db->where('status', $filters['status']);
        }
        if (!empty($filters['attempt_type'])) {
            $this->db->where('attempt_type', $filters['attempt_type']);
        }
        if (!empty($filters['date_from'])) {
            $this->db->where('created_at >=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $this->db->where('created_at <=', $filters['date_to']);
        }

        $this->db->order_by('created_at', 'DESC');

        if ($limit > 0) {
            $this->db->limit($limit, $offset);
        }

        return $this->db->get()->result_array();
    }

    /**
     * Count access logs with filters
     * 
     * @param array $filters Filter parameters
     * @return int Count
     */
    public function count_logs($filters = array())
    {
        $this->db->from('pattern_access_logs');

        // Apply same filters as get_logs
        if (!empty($filters['user_id'])) {
            $this->db->where('user_id', $filters['user_id']);
        }
        if (!empty($filters['username'])) {
            $this->db->like('username', $filters['username']);
        }
        if (!empty($filters['ip_address'])) {
            $this->db->where('ip_address', $filters['ip_address']);
        }
        if (!empty($filters['status'])) {
            $this->db->where('status', $filters['status']);
        }
        if (!empty($filters['attempt_type'])) {
            $this->db->where('attempt_type', $filters['attempt_type']);
        }
        if (!empty($filters['date_from'])) {
            $this->db->where('created_at >=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $this->db->where('created_at <=', $filters['date_to']);
        }

        return $this->db->count_all_results();
    }

    /**
     * Get log statistics
     * 
     * @param int $user_id User ID (optional)
     * @return array Statistics
     */
    public function get_log_statistics($user_id = NULL)
    {
        $stats = array();

        // Build base query
        $this->db->select('status, COUNT(*) as count');
        $this->db->from('pattern_access_logs');
        if ($user_id) {
            $this->db->where('user_id', $user_id);
        }
        $this->db->group_by('status');
        $status_counts = $this->db->get()->result_array();

        foreach ($status_counts as $row) {
            $stats[$row['status']] = $row['count'];
        }

        // Total attempts
        $stats['total'] = array_sum($stats);

        // Recent failed attempts (last 24 hours)
        $this->db->from('pattern_access_logs');
        $this->db->where('status', 'failed');
        $this->db->where('created_at >=', date('Y-m-d H:i:s', strtotime('-24 hours')));
        if ($user_id) {
            $this->db->where('user_id', $user_id);
        }
        $stats['recent_failures'] = $this->db->count_all_results();

        return $stats;
    }

    /**
     * Delete logs before a certain date
     * 
     * @param string $cutoff_date Date in Y-m-d H:i:s format
     * @return int Number of deleted records
     */
    public function delete_logs_before($cutoff_date)
    {
        $this->db->where('created_at <', $cutoff_date);
        $this->db->delete('pattern_access_logs');
        return $this->db->affected_rows();
    }

    // ========================================================================
    // LOCKOUTS
    // ========================================================================

    /**
     * Get lockout record for IP
     * 
     * @param string $ip_address IP address
     * @return array|null Lockout data
     */
    public function get_lockout($ip_address)
    {
        return $this->db->where('ip_address', $ip_address)
                        ->get('pattern_lockouts')
                        ->row_array();
    }

    /**
     * Check if IP is currently locked
     * 
     * @param string $ip_address IP address
     * @return bool TRUE if locked
     */
    public function is_ip_locked($ip_address)
    {
        $lockout = $this->get_lockout($ip_address);

        if (!$lockout) {
            return FALSE;
        }

        // Check permanent lockout
        if ($lockout['is_permanent']) {
            return TRUE;
        }

        // Check if lockout has expired
        if ($lockout['locked_until']) {
            return strtotime($lockout['locked_until']) > time();
        }

        return FALSE;
    }

    /**
     * Increment failed attempts for IP
     * 
     * @param string $ip_address IP address
     * @return bool Success
     */
    public function increment_failed_attempts($ip_address)
    {
        $lockout = $this->get_lockout($ip_address);

        if ($lockout) {
            // Increment existing record
            $this->db->where('ip_address', $ip_address);
            $this->db->set('failed_attempts', 'failed_attempts + 1', FALSE);
            $this->db->set('updated_at', date('Y-m-d H:i:s'));
            return $this->db->update('pattern_lockouts');
        } else {
            // Create new record
            $data = array(
                'ip_address' => $ip_address,
                'failed_attempts' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            );
            return $this->db->insert('pattern_lockouts', $data);
        }
    }

    /**
     * Lock IP address
     * 
     * @param string $ip_address IP address
     * @param int $duration Duration in seconds
     * @param bool $permanent Permanent lockout
     * @return bool Success
     */
    public function lock_ip($ip_address, $duration = 900, $permanent = FALSE)
    {
        $data = array(
            'locked_until' => $permanent ? NULL : date('Y-m-d H:i:s', time() + $duration),
            'is_permanent' => $permanent ? 1 : 0,
            'updated_at' => date('Y-m-d H:i:s')
        );

        $lockout = $this->get_lockout($ip_address);

        if ($lockout) {
            return $this->db->where('ip_address', $ip_address)
                           ->update('pattern_lockouts', $data);
        } else {
            $data['ip_address'] = $ip_address;
            $data['failed_attempts'] = 0;
            $data['created_at'] = date('Y-m-d H:i:s');
            return $this->db->insert('pattern_lockouts', $data);
        }
    }

    /**
     * Reset lockout for IP
     * 
     * @param string $ip_address IP address
     * @return bool Success
     */
    public function reset_lockout($ip_address)
    {
        return $this->db->where('ip_address', $ip_address)
                        ->delete('pattern_lockouts');
    }

    /**
     * Get all active lockouts
     * 
     * @return array Lockouts
     */
    public function get_active_lockouts()
    {
        $this->db->where('(locked_until > NOW() OR is_permanent = 1)');
        return $this->db->get('pattern_lockouts')->result_array();
    }

    // ========================================================================
    // SETTINGS
    // ========================================================================

    /**
     * Get setting by key
     * 
     * @param string $key Setting key
     * @return array|null Setting data
     */
    public function get_setting($key)
    {
        return $this->db->where('setting_key', $key)
                        ->get('pattern_settings')
                        ->row_array();
    }

    /**
     * Get all settings
     * 
     * @return array Settings
     */
    public function get_all_settings()
    {
        return $this->db->get('pattern_settings')->result_array();
    }

    /**
     * Update setting
     * 
     * @param string $key Setting key
     * @param mixed $value Setting value
     * @return bool Success
     */
    public function update_setting($key, $value)
    {
        // Convert value to string
        if (is_array($value) || is_object($value)) {
            $value = json_encode($value);
        } elseif (is_bool($value)) {
            $value = $value ? '1' : '0';
        } else {
            $value = (string)$value;
        }

        $data = array(
            'setting_value' => $value,
            'updated_at' => date('Y-m-d H:i:s')
        );

        return $this->db->where('setting_key', $key)
                        ->update('pattern_settings', $data);
    }

    // ========================================================================
    // KNOWN DEVICES
    // ========================================================================

    /**
     * Check if device is known for user
     * 
     * @param int $user_id User ID
     * @param string $device_fingerprint Device fingerprint
     * @return bool TRUE if known
     */
    public function is_known_device($user_id, $device_fingerprint)
    {
        $count = $this->db->where('user_id', $user_id)
                          ->where('device_fingerprint', $device_fingerprint)
                          ->count_all_results('pattern_known_devices');
        return $count > 0;
    }

    /**
     * Register device for user
     * 
     * @param array $data Device data
     * @return int Insert ID or FALSE
     */
    public function register_device($data)
    {
        // Check if device already exists
        $existing = $this->db->where('user_id', $data['user_id'])
                             ->where('device_fingerprint', $data['device_fingerprint'])
                             ->get('pattern_known_devices')
                             ->row_array();

        if ($existing) {
            // Update last used
            return $this->update_device_last_used($data['user_id'], $data['device_fingerprint']);
        }

        $this->db->insert('pattern_known_devices', $data);
        return $this->db->insert_id();
    }

    /**
     * Update device last used timestamp
     * 
     * @param int $user_id User ID
     * @param string $device_fingerprint Device fingerprint
     * @return bool Success
     */
    public function update_device_last_used($user_id, $device_fingerprint)
    {
        return $this->db->where('user_id', $user_id)
                        ->where('device_fingerprint', $device_fingerprint)
                        ->update('pattern_known_devices', array(
                            'last_used' => date('Y-m-d H:i:s')
                        ));
    }

    /**
     * Get user devices
     * 
     * @param int $user_id User ID
     * @return array Devices
     */
    public function get_user_devices($user_id)
    {
        return $this->db->where('user_id', $user_id)
                        ->order_by('last_used', 'DESC')
                        ->get('pattern_known_devices')
                        ->result_array();
    }

    /**
     * Delete device
     * 
     * @param int $device_id Device ID
     * @param int $user_id User ID
     * @return bool Success
     */
    public function delete_device($device_id, $user_id)
    {
        return $this->db->where('id', $device_id)
                        ->where('user_id', $user_id)
                        ->delete('pattern_known_devices');
    }
}
