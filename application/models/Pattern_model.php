<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pattern_model extends CI_Model {

    protected $patterns_table = 'user_patterns';
    protected $logs_table = 'pattern_access_logs';
    protected $lockouts_table = 'pattern_lockouts';
    protected $settings_table = 'pattern_settings';
    protected $devices_table = 'pattern_known_devices';

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    // ==================== User Patterns ====================

    public function get_user_pattern($user_id)
    {
        return $this->db->where('user_id', $user_id)
                       ->where('is_active', 1)
                       ->get($this->patterns_table)
                       ->row();
    }

    public function has_pattern($user_id)
    {
        return $this->db->where('user_id', $user_id)
                       ->where('is_active', 1)
                       ->count_all_results($this->patterns_table) > 0;
    }

    public function create_pattern($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->insert($this->patterns_table, $data);
        return $this->db->insert_id();
    }

    public function update_pattern($user_id, $data)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->db->where('user_id', $user_id)->update($this->patterns_table, $data);
    }

    public function delete_pattern($user_id)
    {
        return $this->db->where('user_id', $user_id)->delete($this->patterns_table);
    }

    public function deactivate_pattern($user_id)
    {
        return $this->update_pattern($user_id, array('is_active' => 0));
    }

    public function verify_pattern($user_id, $pattern_hash)
    {
        return $this->db->where('user_id', $user_id)
                       ->where('pattern_hash', $pattern_hash)
                       ->where('is_active', 1)
                       ->count_all_results($this->patterns_table) > 0;
    }

    public function verify_backup_code($user_id, $backup_code_hash)
    {
        return $this->db->where('user_id', $user_id)
                       ->where('backup_code', $backup_code_hash)
                       ->where('is_active', 1)
                       ->count_all_results($this->patterns_table) > 0;
    }

    // ==================== Access Logs ====================

    public function create_log($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert($this->logs_table, $data);
        return $this->db->insert_id();
    }

    public function get_user_logs($user_id, $limit = 50, $offset = 0)
    {
        return $this->db->where('user_id', $user_id)
                       ->order_by('created_at', 'DESC')
                       ->limit($limit, $offset)
                       ->get($this->logs_table)
                       ->result();
    }

    public function get_ip_logs($ip, $limit = 50)
    {
        return $this->db->where('ip_address', $ip)
                       ->order_by('created_at', 'DESC')
                       ->limit($limit)
                       ->get($this->logs_table)
                       ->result();
    }

    public function get_all_logs($filters = array(), $limit = 100, $offset = 0)
    {
        if (!empty($filters['status'])) {
            $this->db->where('status', $filters['status']);
        }
        if (!empty($filters['date_from'])) {
            $this->db->where('created_at >=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $this->db->where('created_at <=', $filters['date_to']);
        }
        if (!empty($filters['ip_address'])) {
            $this->db->like('ip_address', $filters['ip_address']);
        }

        return $this->db->order_by('created_at', 'DESC')
                       ->limit($limit, $offset)
                       ->get($this->logs_table)
                       ->result();
    }

    public function get_logs_stats($user_id = NULL, $days = 30)
    {
        $date_from = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        
        $this->db->select('status, COUNT(*) as count')
                 ->where('created_at >=', $date_from);
        
        if ($user_id) {
            $this->db->where('user_id', $user_id);
        }
        
        return $this->db->group_by('status')->get($this->logs_table)->result();
    }

    public function cleanup_old_logs($days = 30)
    {
        $date = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        return $this->db->where('created_at <', $date)->delete($this->logs_table);
    }

    // ==================== Lockouts ====================

    public function get_lockout($ip)
    {
        return $this->db->where('ip_address', $ip)->get($this->lockouts_table)->row();
    }

    public function check_lockout($ip, $user_id = NULL)
    {
        $lockout = $this->get_lockout($ip);

        if (!$lockout) {
            return array('locked' => FALSE, 'captcha_required' => FALSE);
        }

        if ($lockout->locked_until && strtotime($lockout->locked_until) > time()) {
            $remaining = strtotime($lockout->locked_until) - time();
            return array(
                'locked' => TRUE,
                'remaining_seconds' => $remaining,
                'locked_until' => $lockout->locked_until,
                'captcha_required' => (bool)$lockout->captcha_required
            );
        }

        if ($lockout->locked_until && strtotime($lockout->locked_until) <= time()) {
            $this->delete_lockout($ip);
            return array('locked' => FALSE, 'captcha_required' => FALSE);
        }

        return array(
            'locked' => FALSE,
            'attempts' => $lockout->attempts,
            'captcha_required' => (bool)$lockout->captcha_required
        );
    }

    public function create_lockout($data)
    {
        $this->db->insert($this->lockouts_table, $data);
        return $this->db->insert_id();
    }

    public function update_lockout($id, $data)
    {
        return $this->db->where('id', $id)->update($this->lockouts_table, $data);
    }

    public function delete_lockout($ip)
    {
        return $this->db->where('ip_address', $ip)->delete($this->lockouts_table);
    }

    // ==================== Known Devices ====================

    public function is_known_device($user_id, $fingerprint)
    {
        return $this->db->where('user_id', $user_id)
                       ->where('device_fingerprint', $fingerprint)
                       ->count_all_results($this->devices_table) > 0;
    }

    public function register_device($user_id, $data)
    {
        $data['user_id'] = $user_id;
        $data['last_used_at'] = date('Y-m-d H:i:s');
        $data['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert($this->devices_table, $data);
        return $this->db->insert_id();
    }

    public function update_device_usage($user_id, $fingerprint)
    {
        return $this->db->where('user_id', $user_id)
                       ->where('device_fingerprint', $fingerprint)
                       ->update($this->devices_table, array('last_used_at' => date('Y-m-d H:i:s')));
    }

    public function get_user_devices($user_id)
    {
        return $this->db->where('user_id', $user_id)
                       ->order_by('last_used_at', 'DESC')
                       ->get($this->devices_table)
                       ->result();
    }

    // ==================== Settings ====================

    public function get_setting($key)
    {
        $result = $this->db->where('setting_key', $key)->get($this->settings_table)->row();
        return $result ? $result->setting_value : NULL;
    }

    public function set_setting($key, $value, $type = 'string')
    {
        $existing = $this->get_setting($key);

        if ($existing !== NULL) {
            return $this->db->where('setting_key', $key)
                           ->update($this->settings_table, array(
                               'setting_value' => $value,
                               'updated_at' => date('Y-m-d H:i:s')
                           ));
        }

        return $this->db->insert($this->settings_table, array(
            'setting_key' => $key,
            'setting_value' => $value,
            'setting_type' => $type,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ));
    }
}