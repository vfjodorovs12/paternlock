<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Pattern Logger Library
 * Детальное логирование
 */
class Pattern_logger {

    protected $CI;
    protected $enabled;
    protected $log_level;
    protected $log_file;

    const LEVEL_BASIC = 'basic';
    const LEVEL_DETAILED = 'detailed';
    const LEVEL_DEBUG = 'debug';

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->config('pattern_lock', TRUE);
        $this->CI->load->model('pattern_model');

        $this->enabled = $this->CI->config->item('pattern_logging_enabled', 'pattern_lock') !== FALSE;
        $this->log_level = $this->CI->config->item('pattern_log_level', 'pattern_lock') ?: self::LEVEL_BASIC;
        $this->log_file = $this->CI->config->item('pattern_log_file', 'pattern_lock') ?: 'pattern_auth.log';
    }

    /**
     * Логирование попытки
     */
    public function log_attempt($data)
    {
        if (!$this->enabled) return FALSE;

        $log_data = array(
            'user_id' => $data['user_id'] ?? NULL,
            'username' => $data['username'] ?? NULL,
            'ip_address' => $this->CI->input->ip_address(),
            'user_agent' => $this->CI->input->user_agent(),
            'device_fingerprint' => $data['device_fingerprint'] ?? NULL,
            'attempt_type' => $data['attempt_type'] ?? 'pattern',
            'status' => $data['status'] ?? 'failed',
            'failure_reason' => $data['failure_reason'] ?? NULL,
            'captcha_score' => $data['captcha_score'] ?? NULL,
            'session_id' => session_id(),
            'created_at' => date('Y-m-d H:i:s')
        );

        if ($this->log_level === self::LEVEL_DEBUG) {
            $log_data['request_data'] = json_encode(array(
                'uri' => $_SERVER['REQUEST_URI'] ?? '',
                'method' => $_SERVER['REQUEST_METHOD'] ?? '',
                'referer' => $_SERVER['HTTP_REFERER'] ?? ''
            ));
        }

        $this->CI->pattern_model->create_log($log_data);
        $this->write_to_file($log_data);

        return TRUE;
    }

    /**
     * Логирование успеха
     */
    public function log_success($user_id, $username = NULL, $extra = array())
    {
        return $this->log_attempt(array_merge(array(
            'user_id' => $user_id,
            'username' => $username,
            'status' => 'success',
            'attempt_type' => 'pattern'
        ), $extra));
    }

    /**
     * Логирование неудачи
     */
    public function log_failure($user_id = NULL, $username = NULL, $reason = NULL)
    {
        return $this->log_attempt(array(
            'user_id' => $user_id,
            'username' => $username,
            'status' => 'failed',
            'attempt_type' => 'pattern',
            'failure_reason' => $reason
        ));
    }

    /**
     * Логирование блокировки
     */
    public function log_blocked($ip, $user_id = NULL, $reason = NULL)
    {
        return $this->log_attempt(array(
            'user_id' => $user_id,
            'status' => 'blocked',
            'failure_reason' => $reason ?: 'Too many failed attempts'
        ));
    }

    /**
     * Логирование капчи
     */
    public function log_captcha_failure($user_id = NULL, $score = NULL)
    {
        return $this->log_attempt(array(
            'user_id' => $user_id,
            'status' => 'failed',
            'attempt_type' => 'captcha_fail',
            'failure_reason' => 'CAPTCHA verification failed',
            'captcha_score' => $score
        ));
    }

    /**
     * Запись в файл
     */
    protected function write_to_file($data)
    {
        $log_path = APPPATH . 'logs/' . $this->log_file;
        
        $message = sprintf(
            "[%s] [%s] User: %s | IP: %s | Status: %s | Type: %s | Reason: %s\n",
            date('Y-m-d H:i:s'),
            strtoupper($data['status']),
            $data['username'] ?: 'unknown',
            $data['ip_address'],
            $data['status'],
            $data['attempt_type'],
            $data['failure_reason'] ?: 'N/A'
        );

        @file_put_contents($log_path, $message, FILE_APPEND | LOCK_EX);
    }

    /**
     * Получение статистики
     */
    public function get_statistics($days = 30, $user_id = NULL)
    {
        return $this->CI->pattern_model->get_logs_stats($user_id, $days);
    }

    /**
     * Экспорт в CSV
     */
    public function export_to_csv($filters = array())
    {
        $logs = $this->CI->pattern_model->get_all_logs($filters, 10000, 0);

        $csv = "ID,User ID,Username,IP,Status,Type,Reason,Created At\n";

        foreach ($logs as $log) {
            $csv .= sprintf(
                "%d,%s,%s,%s,%s,%s,%s,%s\n",
                $log->id,
                $log->user_id ?: '',
                $log->username ?: '',
                $log->ip_address,
                $log->status,
                $log->attempt_type,
                str_replace(',', ';', $log->failure_reason ?: ''),
                $log->created_at
            );
        }

        return $csv;
    }
}