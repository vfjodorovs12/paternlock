<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * reCAPTCHA Library
 * 
 * Handles Google reCAPTCHA v2 and v3 integration
 * 
 * @package    Pattern_Lock
 * @subpackage Libraries
 * @category   Security
 * @author     Pattern Lock Team
 * @version    1.0
 */
class Recaptcha {

    protected $CI;
    protected $config = array();

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->config('recaptcha');
        
        // Load configuration
        $this->config = array(
            'version' => $this->CI->config->item('recaptcha_version'),
            'site_key' => $this->CI->config->item('recaptcha_site_key'),
            'secret_key' => $this->CI->config->item('recaptcha_secret_key'),
            'v3_site_key' => $this->CI->config->item('recaptcha_v3_site_key'),
            'v3_secret_key' => $this->CI->config->item('recaptcha_v3_secret_key'),
            'v3_action' => $this->CI->config->item('recaptcha_v3_action'),
            'v3_threshold' => $this->CI->config->item('recaptcha_v3_threshold'),
            'v2_type' => $this->CI->config->item('recaptcha_v2_type'),
            'theme' => $this->CI->config->item('recaptcha_theme'),
            'size' => $this->CI->config->item('recaptcha_size'),
            'language' => $this->CI->config->item('recaptcha_language'),
            'verify_url' => $this->CI->config->item('recaptcha_verify_url'),
            'error_messages' => $this->CI->config->item('recaptcha_error_messages')
        );
    }

    /**
     * Render reCAPTCHA widget HTML
     * 
     * @param string $version Version to render (defaults to config)
     * @return string HTML for reCAPTCHA widget
     */
    public function render($version = NULL)
    {
        if ($version === NULL) {
            $version = $this->config['version'];
        }

        if ($version === 'v3') {
            return $this->render_v3();
        } else {
            return $this->render_v2();
        }
    }

    /**
     * Render reCAPTCHA v2 widget
     * 
     * @return string HTML for reCAPTCHA v2
     */
    protected function render_v2()
    {
        $site_key = $this->config['site_key'];
        $theme = $this->config['theme'];
        $size = $this->config['size'];
        $lang = $this->config['language'] ? '?hl=' . $this->config['language'] : '';

        $html = '<script src="https://www.google.com/recaptcha/api.js' . $lang . '" async defer></script>';
        
        if ($this->config['v2_type'] === 'invisible') {
            $html .= '<div class="g-recaptcha" data-sitekey="' . $site_key . '" data-size="invisible"></div>';
        } else {
            $html .= '<div class="g-recaptcha" data-sitekey="' . $site_key . '" data-theme="' . $theme . '" data-size="' . $size . '"></div>';
        }

        return $html;
    }

    /**
     * Render reCAPTCHA v3 script
     * 
     * @return string HTML for reCAPTCHA v3
     */
    protected function render_v3()
    {
        $site_key = $this->config['v3_site_key'];
        $action = $this->config['v3_action'];

        $html = '<script src="https://www.google.com/recaptcha/api.js?render=' . $site_key . '"></script>';
        $html .= '<script>
            grecaptcha.ready(function() {
                grecaptcha.execute("' . $site_key . '", {action: "' . $action . '"}).then(function(token) {
                    var recaptchaResponse = document.getElementById("recaptchaResponse");
                    if (recaptchaResponse) {
                        recaptchaResponse.value = token;
                    }
                });
            });
        </script>';
        $html .= '<input type="hidden" id="recaptchaResponse" name="recaptcha_response" value="">';

        return $html;
    }

    /**
     * Verify reCAPTCHA response
     * 
     * @param string $response reCAPTCHA response token
     * @param string $version Version to verify (defaults to config)
     * @return array Result array with 'success' boolean and optional 'error' or 'score'
     */
    public function verify($response, $version = NULL)
    {
        if ($version === NULL) {
            $version = $this->config['version'];
        }

        if (empty($response)) {
            return array(
                'success' => FALSE,
                'error' => $this->config['error_messages']['missing-input-response']
            );
        }

        // Determine which secret key to use
        $secret_key = ($version === 'v3') ? $this->config['v3_secret_key'] : $this->config['secret_key'];

        if (empty($secret_key)) {
            return array(
                'success' => FALSE,
                'error' => 'reCAPTCHA is not configured properly'
            );
        }

        // Prepare verification request
        $data = array(
            'secret' => $secret_key,
            'response' => $response,
            'remoteip' => $this->get_client_ip()
        );

        // Send verification request
        $verify_response = $this->send_verification_request($data);

        if ($verify_response === FALSE) {
            return array(
                'success' => FALSE,
                'error' => $this->config['error_messages']['unknown-error']
            );
        }

        $result = json_decode($verify_response, TRUE);

        if (!isset($result['success'])) {
            return array(
                'success' => FALSE,
                'error' => $this->config['error_messages']['unknown-error']
            );
        }

        if ($result['success']) {
            // For v3, check score threshold
            if ($version === 'v3') {
                $score = isset($result['score']) ? $result['score'] : 0;
                if ($score < $this->config['v3_threshold']) {
                    return array(
                        'success' => FALSE,
                        'score' => $score,
                        'error' => $this->config['error_messages']['score-too-low']
                    );
                }
                return array(
                    'success' => TRUE,
                    'score' => $score
                );
            }

            return array('success' => TRUE);
        }

        // Handle error codes
        $error_codes = isset($result['error-codes']) ? $result['error-codes'] : array('unknown-error');
        $error_code = $error_codes[0];
        $error_message = isset($this->config['error_messages'][$error_code]) 
            ? $this->config['error_messages'][$error_code] 
            : $this->config['error_messages']['unknown-error'];

        return array(
            'success' => FALSE,
            'error' => $error_message,
            'error_code' => $error_code
        );
    }

    /**
     * Send verification request to Google
     * 
     * @param array $data Request data
     * @return string|bool Response body or FALSE on failure
     */
    protected function send_verification_request($data)
    {
        $url = $this->config['verify_url'];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->CI->config->item('recaptcha_timeout') ?: 10);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            log_message('error', 'reCAPTCHA verification error: ' . $error);
            return FALSE;
        }

        return $response;
    }

    /**
     * Get client IP address
     * 
     * @return string IP address
     */
    protected function get_client_ip()
    {
        $ip_keys = array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 
                        'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR');
        
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === TRUE) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP) !== FALSE) {
                        return $ip;
                    }
                }
            }
        }
        
        return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';
    }

    /**
     * Check if reCAPTCHA should be shown based on failed attempts
     * 
     * @param int $failed_attempts Number of failed attempts
     * @return bool TRUE if reCAPTCHA should be shown
     */
    public function should_show($failed_attempts)
    {
        $threshold = $this->CI->config->item('recaptcha_failed_attempts_threshold');
        
        if ($threshold == 0) {
            return TRUE; // Always show
        }
        
        if ($threshold == -1) {
            return FALSE; // Never show automatically
        }
        
        return $failed_attempts >= $threshold;
    }

    /**
     * Get reCAPTCHA version
     * 
     * @return string Version (v2 or v3)
     */
    public function get_version()
    {
        return $this->config['version'];
    }

    /**
     * Check if reCAPTCHA is properly configured
     * 
     * @param string $version Version to check (defaults to config)
     * @return bool TRUE if configured
     */
    public function is_configured($version = NULL)
    {
        if ($version === NULL) {
            $version = $this->config['version'];
        }

        if ($version === 'v3') {
            return !empty($this->config['v3_site_key']) && !empty($this->config['v3_secret_key']);
        } else {
            return !empty($this->config['site_key']) && !empty($this->config['secret_key']);
        }
    }
}
