<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Pattern Lock Library
 * Основная библиотека паттерн-аутентификации
 */
class Pattern_lock {

    protected $CI;
    protected $grid_size;
    protected $min_dots;
    protected $max_attempts;
    protected $lockout_time;
    protected $encryption;
    protected $salt;

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->config('pattern_lock', TRUE);
        $this->CI->load->model('pattern_model');

        $this->grid_size = $this->CI->config->item('pattern_grid_size', 'pattern_lock') ?: 3;
        $this->min_dots = $this->CI->config->item('pattern_min_dots', 'pattern_lock') ?: 4;
        $this->max_attempts = $this->CI->config->item('pattern_max_attempts', 'pattern_lock') ?: 5;
        $this->lockout_time = $this->CI->config->item('pattern_lockout_time', 'pattern_lock') ?: 15;
        $this->encryption = $this->CI->config->item('pattern_encryption', 'pattern_lock') ?: 'sha256';
        $this->salt = $this->CI->config->item('pattern_salt', 'pattern_lock') ?: 'default_salt';
    }

    /**
     * Хеширование паттерна
     */
    public function hash_pattern($pattern)
    {
        if (is_array($pattern)) {
            $pattern = implode('-', $pattern);
        }
        $salted = $this->salt . $pattern . $this->salt;
        return hash($this->encryption, $salted);
    }

    /**
     * Валидация паттерна
     */
    public function validate_pattern($pattern)
    {
        $errors = array();

        if (!is_array($pattern) || empty($pattern)) {
            $errors[] = 'Паттерн не может быть пустым';
            return array('valid' => FALSE, 'errors' => $errors);
        }

        if (count($pattern) < $this->min_dots) {
            $errors[] = "Паттерн должен содержать минимум {$this->min_dots} точек";
        }

        if (count($pattern) !== count(array_unique($pattern))) {
            $errors[] = 'Паттерн не должен содержать повторяющиеся точки';
        }

        $max_dot = ($this->grid_size * $this->grid_size) - 1;
        foreach ($pattern as $dot) {
            if (!is_numeric($dot) || $dot < 0 || $dot > $max_dot) {
                $errors[] = 'Паттерн содержит недопустимые точки';
                break;
            }
        }

        return array('valid' => empty($errors), 'errors' => $errors);
    }

    /**
     * Проверка сложности паттерна
     */
    public function check_pattern_strength($pattern)
    {
        $score = 0;
        $feedback = array();

        $dots_count = count($pattern);
        if ($dots_count >= 7) {
            $score += 3;
        } elseif ($dots_count >= 5) {
            $score += 2;
        } elseif ($dots_count >= 4) {
            $score += 1;
        }

        $corners = array(0, $this->grid_size - 1, ($this->grid_size - 1) * $this->grid_size, ($this->grid_size * $this->grid_size) - 1);
        $corner_count = count(array_intersect($pattern, $corners));
        if ($corner_count >= 2) {
            $score += 2;
        }

        if ($this->is_simple_pattern($pattern)) {
            $score -= 3;
            $feedback[] = 'Паттерн слишком простой';
        }

        if ($score >= 5) {
            $strength = 'strong';
            $feedback[] = 'Отличный паттерн!';
        } elseif ($score >= 3) {
            $strength = 'medium';
            $feedback[] = 'Можно усложнить';
        } else {
            $strength = 'weak';
            $feedback[] = 'Слабый паттерн';
        }

        return array('score' => max(0, $score), 'strength' => $strength, 'feedback' => $feedback);
    }

    /**
     * Проверка простого паттерна
     */
    protected function is_simple_pattern($pattern)
    {
        $pattern_str = implode('-', $pattern);
        $simple = array('0-1-2', '0-3-6', '0-4-8', '2-4-6', '0-1-2-3-4-5-6-7-8', '0-1-2-5-8-7-6-3');
        
        foreach ($simple as $s) {
            if ($pattern_str === $s || $pattern_str === implode('-', array_reverse(explode('-', $s)))) {
                return TRUE;
            }
        }
        return FALSE;
    }

    /**
     * Генерация backup кода
     */
    public function generate_backup_code()
    {
        $code = strtoupper(bin2hex(random_bytes(4)));
        return substr($code, 0, 4) . '-' . substr($code, 4, 4);
    }

    /**
     * Проверка блокировки
     */
    public function check_lockout($ip, $user_id = NULL)
    {
        return $this->CI->pattern_model->check_lockout($ip, $user_id);
    }

    /**
     * Регистрация неудачной попытки
     */
    public function register_failed_attempt($ip, $user_id = NULL)
    {
        $lockout = $this->CI->pattern_model->get_lockout($ip);
        $captcha_threshold = $this->CI->config->item('pattern_captcha_threshold', 'pattern_lock') ?: 3;

        if ($lockout) {
            $attempts = $lockout->attempts + 1;
            $locked_until = NULL;
            $captcha_required = $lockout->captcha_required;

            if ($attempts >= $captcha_threshold) {
                $captcha_required = 1;
            }

            if ($attempts >= $this->max_attempts) {
                $locked_until = date('Y-m-d H:i:s', strtotime("+{$this->lockout_time} minutes"));
            }

            $this->CI->pattern_model->update_lockout($lockout->id, array(
                'attempts' => $attempts,
                'locked_until' => $locked_until,
                'captcha_required' => $captcha_required,
                'updated_at' => date('Y-m-d H:i:s')
            ));

            $remaining = $this->max_attempts - $attempts;
        } else {
            $this->CI->pattern_model->create_lockout(array(
                'ip_address' => $ip,
                'user_id' => $user_id,
                'attempts' => 1,
                'captcha_required' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ));
            $remaining = $this->max_attempts - 1;
        }

        return array(
            'locked' => $remaining <= 0,
            'remaining_attempts' => max(0, $remaining),
            'captcha_required' => ($lockout ? $lockout->attempts + 1 : 1) >= $captcha_threshold
        );
    }

    /**
     * Сброс попыток
     */
    public function reset_attempts($ip)
    {
        $this->CI->pattern_model->delete_lockout($ip);
    }

    /**
     * Генерация отпечатка устройства
     */
    public function generate_device_fingerprint()
    {
        $data = array(
            $this->CI->input->ip_address(),
            $this->CI->input->user_agent(),
            $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? ''
        );
        return hash('sha256', implode('|', $data));
    }

    // Геттеры
    public function get_grid_size() { return $this->grid_size; }
    public function get_min_dots() { return $this->min_dots; }
    public function get_max_attempts() { return $this->max_attempts; }
}