<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Google reCAPTCHA Library
 */
class Recaptcha {

    protected $CI;
    protected $site_key;
    protected $secret_key;
    protected $version;
    protected $theme;
    protected $lang;
    protected $verify_url = 'https://www.google.com/recaptcha/api/siteverify';
    
    protected $last_score;
    protected $last_error;

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->config('recaptcha', TRUE);
        $this->CI->load->config('pattern_lock', TRUE);

        $this->version = $this->CI->config->item('pattern_captcha_version', 'pattern_lock') ?: 'v2';

        if ($this->version === 'v3') {
            $this->site_key = $this->CI->config->item('recaptcha_v3_site_key', 'recaptcha');
            $this->secret_key = $this->CI->config->item('recaptcha_v3_secret_key', 'recaptcha');
        } else {
            $this->site_key = $this->CI->config->item('recaptcha_site_key', 'recaptcha');
            $this->secret_key = $this->CI->config->item('recaptcha_secret_key', 'recaptcha');
        }

        $this->theme = $this->CI->config->item('recaptcha_theme', 'recaptcha') ?: 'light';
        $this->lang = $this->CI->config->item('recaptcha_lang', 'recaptcha') ?: 'ru';
    }

    public function get_script_tag()
    {
        if ($this->version === 'v3') {
            return '<script src="https://www.google.com/recaptcha/api.js?render=' . $this->site_key . '"></script>';
        }
        return '<script src="https://www.google.com/recaptcha/api.js?hl=' . $this->lang . '" async defer></script>';
    }

    public function get_widget($options = array())
    {
        $theme = $options['theme'] ?? $this->theme;
        return '<div class="g-recaptcha" data-sitekey="' . $this->site_key . '" data-theme="' . $theme . '"></div>';
    }

    public function get_v3_script($action = 'login')
    {
        return '
            <script>
                function executeRecaptcha() {
                    grecaptcha.ready(function() {
                        grecaptcha.execute("' . $this->site_key . '", {action: "' . $action . '"}).then(function(token) {
                            document.getElementById("g-recaptcha-response").value = token;
                        });
                    });
                }
                document.addEventListener("DOMContentLoaded", executeRecaptcha);
                setInterval(executeRecaptcha, 120000);
            </script>
            <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response" value="">
        ';
    }

    public function verify($response = NULL)
    {
        if ($response === NULL) {
            $response = $this->CI->input->post('g-recaptcha-response');
        }

        if (empty($response)) {
            $this->last_error = 'CAPTCHA response is empty';
            return FALSE;
        }

        $data = array(
            'secret' => $this->secret_key,
            'response' => $response,
            'remoteip' => $this->CI->input->ip_address()
        );

        $result = $this->send_request($data);

        if ($result === FALSE) {
            $this->last_error = 'Failed to connect to reCAPTCHA API';
            return FALSE;
        }

        $response_data = json_decode($result, TRUE);

        if (!isset($response_data['success']) || !$response_data['success']) {
            $this->last_error = isset($response_data['error-codes']) 
                ? implode(', ', $response_data['error-codes']) 
                : 'Verification failed';
            return FALSE;
        }

        if ($this->version === 'v3') {
            $this->last_score = $response_data['score'] ?? 0;
            $threshold = $this->CI->config->item('pattern_captcha_v3_threshold', 'pattern_lock') ?: 0.5;

            if ($this->last_score < $threshold) {
                $this->last_error = 'Score too low: ' . $this->last_score;
                return FALSE;
            }
        }

        return TRUE;
    }

    protected function send_request($data)
    {
        if (function_exists('curl_init')) {
            $ch = curl_init($this->verify_url);
            curl_setopt_array($ch, array(
                CURLOPT_POST => TRUE,
                CURLOPT_POSTFIELDS => http_build_query($data),
                CURLOPT_RETURNTRANSFER => TRUE,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_SSL_VERIFYPEER => TRUE
            ));
            $result = curl_exec($ch);
            curl_close($ch);
            return $result;
        }

        $context = stream_context_create(array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-Type: application/x-www-form-urlencoded',
                'content' => http_build_query($data),
                'timeout' => 10
            )
        ));
        return @file_get_contents($this->verify_url, FALSE, $context);
    }

    public function is_required_for_ip($ip)
    {
        if (!$this->is_enabled()) return FALSE;

        $threshold = $this->CI->config->item('pattern_captcha_threshold', 'pattern_lock') ?: 0;
        if ($threshold === 0) return TRUE;

        $this->CI->load->model('pattern_model');
        $lockout = $this->CI->pattern_model->get_lockout($ip);

        return $lockout && ($lockout->attempts >= $threshold || $lockout->captcha_required);
    }

    public function is_enabled()
    {
        return $this->CI->config->item('pattern_captcha_enabled', 'pattern_lock') === TRUE;
    }

    public function get_site_key() { return $this->site_key; }
    public function get_version() { return $this->version; }
    public function get_last_score() { return $this->last_score; }
    public function get_last_error() { return $this->last_error; }
}